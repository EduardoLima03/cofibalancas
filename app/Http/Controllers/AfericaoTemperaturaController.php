<?php

namespace App\Http\Controllers;

use App\Models\AfericaoTemperatura;
use App\Models\EquipamentoFrio;
use App\Models\Loja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AfericaoTemperaturaController extends Controller
{
    public function create()
    {
        $user = Auth::user();

        $lojas = Loja::where('is_active', true)
            ->whereIn('id', $user->lojasPermitidasIds())
            ->orderBy('nome')
            ->get();

        return view('temperatura.create', compact('lojas'));
    }

    public function getEquipamentos(Request $request)
    {
        $user = Auth::user();
        $lojaId = $request->get('loja_id');

        if ($lojaId && ! $user->podeAcessarLoja((int) $lojaId)) {
            abort(403);
        }

        return response()->json(
            EquipamentoFrio::where('loja_id', $lojaId)
                ->where('is_active', true)
                ->orderBy('nome')
                ->get([
                    'id',
                    'nome',
                    'tipo',
                    'marca',
                    'modelo',
                    'temp_min',
                    'temp_max',
                    'tolerancia_c',
                ])
        );
    }

    public function normalizeTemperatura(Request $request): void
    {
        if ($request->has('temperatura_lida')) {
            $valor = trim((string) $request->input('temperatura_lida'));
            if (str_contains($valor, ',') && str_contains($valor, '.')) {
                $valor = str_replace('.', '', $valor);
                $valor = str_replace(',', '.', $valor);
            } else {
                $valor = str_replace(',', '.', $valor);
            }
            $request->merge(['temperatura_lida' => $valor]);
        }
    }

    public function preview(Request $request)
    {
        $this->normalizeTemperatura($request);

        $data = $request->validate([
            'equipamento_id' => 'required|exists:equipamentos_frio,id',
            'temperatura_lida' => 'required|numeric',
        ]);

        $equipamento = EquipamentoFrio::findOrFail($data['equipamento_id']);
        $temperatura = (float) $data['temperatura_lida'];

        $desvio = $this->calcularDesvio($temperatura, $equipamento->temp_min, $equipamento->temp_max);
        $dentroTolerancia = $desvio <= $equipamento->tolerancia_c;

        return response()->json([
            'equipamento' => $equipamento->only([
                'id', 'nome', 'tipo', 'temp_min', 'temp_max', 'tolerancia_c',
            ]),
            'temperatura_lida' => $temperatura,
            'desvio' => round($desvio, 2),
            'dentro_tolerancia' => $dentroTolerancia,
            'desvio_label' => number_format($desvio, 2, ',', '.'),
        ]);
    }

    protected function calcularDesvio(float $temperatura, ?float $tempMin, float $tempMax): float
    {
        if ($tempMin !== null && $temperatura >= $tempMin && $temperatura <= $tempMax) {
            return 0.0;
        }

        if ($tempMin !== null && $temperatura < $tempMin) {
            return $tempMin - $temperatura;
        }

        return max(0, $temperatura - $tempMax);
    }

    public function store(Request $request)
    {
        $this->normalizeTemperatura($request);

        $data = $request->validate([
            'loja_id' => 'required|exists:lojas,id',
            'equipamento_id' => 'required|exists:equipamentos_frio,id',
            'temperatura_lida' => 'required|numeric',
            'observacao' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $equipamento = EquipamentoFrio::with('loja')->findOrFail($data['equipamento_id']);
        $loja = $equipamento->loja;

        if (! $user->podeAcessarLoja($loja->id)) {
            abort(403, 'Você não tem permissão para esta loja.');
        }

        $temperatura = round((float) $data['temperatura_lida'], 2);
        $desvio = round($this->calcularDesvio($temperatura, $equipamento->temp_min, $equipamento->temp_max), 2);
        $dentroTolerancia = $desvio <= $equipamento->tolerancia_c;
        $status = $dentroTolerancia ? 'aprovado' : 'reprovado';

        $afericao = AfericaoTemperatura::create([
            'loja_id' => $loja->id,
            'equipamento_id' => $equipamento->id,
            'user_id' => $user->id,
            'data_afericao' => now(),
            'status' => $status,
            'temperatura_lida' => $temperatura,
            'temp_min_usada' => $equipamento->temp_min,
            'temp_max_usada' => $equipamento->temp_max,
            'desvio' => $desvio,
            'dentro_tolerancia' => $dentroTolerancia,
            'tolerancia_usada' => $equipamento->tolerancia_c,
            'observacao' => $data['observacao'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'afericao_id' => $afericao->id,
            'status' => $status,
            'dentro_tolerancia' => $dentroTolerancia,
            'desvio' => $desvio,
            'message' => $dentroTolerancia
                ? 'Aferição aprovada dentro da tolerância.'
                : 'Aferição reprovada. Temperatura fora da tolerância.',
        ]);
    }

    public function historico()
    {
        $user = Auth::user();

        $afericoes = AfericaoTemperatura::with(['loja', 'equipamento', 'user'])
            ->whereIn('loja_id', $user->lojasPermitidasIds())
            ->latest('data_afericao')
            ->latest('id')
            ->paginate(20);

        return view('temperatura.historico', compact('afericoes'));
    }

    public function show(AfericaoTemperatura $afericao)
    {
        $this->authorizeView($afericao);

        $afericao->load(['loja', 'equipamento', 'user']);

        return view('temperatura.show', compact('afericao'));
    }

    protected function authorizeView(AfericaoTemperatura $afericao): void
    {
        $user = Auth::user();

        if (! $user->podeAcessarLoja($afericao->loja_id)) {
            abort(403, 'Acesso negado.');
        }
    }
}