<?php

namespace App\Http\Controllers;

use App\Models\Balanca;
use App\Models\Conferencia;
use App\Models\ConferenciaItem;
use App\Models\Loja;
use App\Models\TicketGlpi;
use App\Services\GlpiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConferenciaController extends Controller
{
    public function create()
    {
        $user = Auth::user();

        $lojas = Loja::where('is_active', true)
            ->when($user->role !== 'admin', fn ($q) => $q->where('id', $user->loja_id))
            ->orderBy('nome')
            ->get();

        return view('coleta.create', compact('lojas'));
    }

    public function getBalancas(Request $request)
    {
        $lojaId = $request->get('loja_id');

        return response()->json(
            Balanca::where('loja_id', $lojaId)
                ->where('is_active', true)
                ->orderBy('nome')
                ->get(['id', 'nome', 'marca', 'modelo', 'capacidade_kg', 'tolerancia_kg', 'serial'])
        );
    }

    protected function normalizePeso(Request $request): void
    {
        foreach (['peso_esperado', 'peso_real'] as $campo) {
            if ($request->has($campo)) {
                $valor = trim((string) $request->input($campo));
                if (str_contains($valor, ',') && str_contains($valor, '.')) {
                    $valor = str_replace('.', '', $valor);
                    $valor = str_replace(',', '.', $valor);
                } else {
                    $valor = str_replace(',', '.', $valor);
                }
                $request->merge([$campo => $valor]);
            }
        }
    }

    public function preview(Request $request)
    {
        $this->normalizePeso($request);

        $data = $request->validate([
            'balanca_id' => 'required|exists:balancas,id',
            'descricao_item' => 'nullable|string|max:255',
            'peso_esperado' => 'required|numeric|min:0',
            'peso_real' => 'required|numeric|min:0',
            'observacao' => 'nullable|string|max:500',
        ]);

        $balanca = Balanca::findOrFail($data['balanca_id']);

        $diferenca = abs($data['peso_real'] - $data['peso_esperado']);
        $dentroTolerancia = $diferenca <= $balanca->tolerancia_kg;

        return response()->json([
            'balanca' => $balanca->only(['id', 'nome', 'tolerancia_kg']),
            'peso_esperado' => (float) $data['peso_esperado'],
            'peso_real' => (float) $data['peso_real'],
            'diferenca' => round($diferenca, 3),
            'dentro_tolerancia' => $dentroTolerancia,
            'diferenca_label' => number_format($diferenca, 3, ',', '.'),
        ]);
    }

    public function store(Request $request)
    {
        $this->normalizePeso($request);

        $data = $request->validate([
            'loja_id' => 'required|exists:lojas,id',
            'balanca_id' => 'required|exists:balancas,id',
            'descricao_item' => 'nullable|string|max:255',
            'peso_esperado' => 'required|numeric|min:0',
            'peso_real' => 'required|numeric|min:0',
            'observacao' => 'nullable|string|max:500',
            'abrir_chamado_glpi' => 'nullable|in:0,1',
        ]);

        $user = Auth::user();
        $balanca = Balanca::with('loja')->findOrFail($data['balanca_id']);
        $loja = $balanca->loja;

        if ($user->role !== 'admin' && $loja->id !== $user->loja_id) {
            abort(403, 'Você não tem permissão para esta loja.');
        }

        $pesoEsperado = round((float) $data['peso_esperado'], 3);
        $pesoReal = round((float) $data['peso_real'], 3);
        $diferenca = round(abs($pesoReal - $pesoEsperado), 3);
        $dentroTolerancia = $diferenca <= $balanca->tolerancia_kg;
        $status = $dentroTolerancia ? 'aprovado' : 'reprovado';

        return DB::transaction(function () use ($data, $user, $balanca, $loja, $pesoEsperado, $pesoReal, $diferenca, $dentroTolerancia, $status) {
            $conferencia = Conferencia::create([
                'loja_id' => $loja->id,
                'balanca_id' => $balanca->id,
                'user_id' => $user->id,
                'data_conferencia' => now(),
                'status' => $status,
                'peso_esperado' => $pesoEsperado,
                'peso_real' => $pesoReal,
                'diferenca' => $diferenca,
                'tolerancia_usada' => $balanca->tolerancia_kg,
            ]);

            $item = ConferenciaItem::create([
                'conferencia_id' => $conferencia->id,
                'descricao_item' => $data['descricao_item'] ?? null,
                'peso_esperado' => $pesoEsperado,
                'peso_real' => $pesoReal,
                'diferenca' => $diferenca,
                'dentro_tolerancia' => $dentroTolerancia,
                'observacao' => $data['observacao'] ?? null,
            ]);

            $ticketInfo = null;

            if (!$dentroTolerancia && ($data['abrir_chamado_glpi'] ?? false)) {
                $ticketInfo = $this->openGlpiTicket($conferencia, $item, $balanca, $loja);
            }

            return response()->json([
                'success' => true,
                'conferencia_id' => $conferencia->id,
                'status' => $status,
                'dentro_tolerancia' => $dentroTolerancia,
                'diferenca' => $diferenca,
                'ticket' => $ticketInfo,
                'message' => $dentroTolerancia
                    ? 'Conferência aprovada dentro da tolerância.'
                    : 'Conferência reprovada. Diferença fora da tolerância.',
            ]);
        });
    }

    protected function openGlpiTicket(Conferencia $conferencia, ConferenciaItem $item, Balanca $balanca, Loja $loja): array
    {
        $glpi = app(GlpiService::class);

        if (! $glpi->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Integração GLPI não configurada. Acesse Configurações > GLPI.',
            ];
        }

        $diferencaText = number_format(abs($item->diferenca), 3, ',', '.');
        $pesoEsperadoText = number_format($item->peso_esperado, 3, ',', '.');
        $pesoRealText = number_format($item->peso_real, 3, ',', '.');

        $priority = $this->calculatePriority(abs($item->diferenca), $balanca->tolerancia_kg);

        $titulo = '[Conferência de Peso] ' . $loja->nome . ' - diferença de ' . $diferencaText . ' kg';

        $variacaoText = number_format($item->peso_esperado > 0 ? ($item->diferenca / $item->peso_esperado) * 100 : 0, 2, ',', '.') . '%';

        $descricao = "Foi detectada uma diferença de {$diferencaText} kg na balança {$balanca->nome}(loja {$loja->nome})\n"
            . "Balança\n"
            . "- Nome: {$balanca->nome}\n";

        if ($balanca->serial) {
            $descricao .= "- Serial: {$balanca->serial}\n";
        }

        $descricao .= "\nConferência\n"
            . "- Data: {$conferencia->data_conferencia->format('d/m/Y H:i')}\n"
            . "- Coletado por: {$conferencia->user->name}\n"
            . "\nPesos (kg)\n"
            . "- Esperado: {$pesoEsperadoText}\n"
            . "- Real: {$pesoRealText}\n"
            . "- Diferença: {$diferencaText}\n"
            . "- Variação: {$variacaoText}\n";

        $payload = [
            'name' => $titulo,
            'content' => $descricao,
            'priority' => $priority,
            'type' => 1,
        ];

        if ($glpi->categoryId()) {
            $payload['category'] = $glpi->categoryId();
        }

        $result = $glpi->openTicket($payload, $loja->glpi_entity_id);

        TicketGlpi::create([
            'ticket_id' => $result['success'] ? $result['ticket_id'] : null,
            'conferencia_id' => $conferencia->id,
            'conferencia_item_id' => $item->id,
            'titulo' => $titulo,
            'descricao' => $descricao,
            'status' => $result['success'] ? 'enviado' : 'erro',
            'resposta_glpi' => json_encode($result),
            'diferenca' => $item->diferenca,
        ]);

        return $result;
    }

    protected function calculatePriority(float $diferenca, float $tolerancia): int
    {
        $razao = $tolerancia > 0 ? $diferenca / $tolerancia : 1;

        return match (true) {
            $razao >= 10 => 5,
            $razao >= 5 => 4,
            $razao >= 3 => 3,
            $razao >= 1.5 => 2,
            default => 1,
        };
    }

    public function historico()
    {
        $user = Auth::user();

        $conferencias = Conferencia::with(['loja', 'balanca', 'user'])
            ->when($user->role !== 'admin', fn ($q) => $q->where('loja_id', $user->loja_id))
            ->latest('data_conferencia')
            ->latest('id')
            ->paginate(20);

        return view('coleta.historico', compact('conferencias'));
    }

    public function show(Conferencia $conferencia)
    {
        $this->authorizeView($conferencia);

        $conferencia->load(['loja', 'balanca', 'user', 'itens', 'tickets']);

        return view('coleta.show', compact('conferencia'));
    }

    protected function authorizeView(Conferencia $conferencia): void
    {
        $user = Auth::user();

        if ($user->role !== 'admin' && $conferencia->loja_id !== $user->loja_id) {
            abort(403, 'Acesso negado.');
        }
    }
}