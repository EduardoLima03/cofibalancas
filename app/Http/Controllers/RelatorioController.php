<?php

namespace App\Http\Controllers;

use App\Models\Conferencia;
use App\Models\Loja;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RelatorioController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $lojasIds = $user->lojasPermitidasIds();

        $lojas = Loja::where('is_active', true)
            ->whereIn('id', $lojasIds)
            ->orderBy('nome')
            ->get();

        $lojaId = $request->get('loja_id', $user->role === 'admin' ? null : $user->lojas->first()?->id);
        $dataInicio = $request->get('data_inicio') ?: Carbon::today()->subDays(30)->format('Y-m-d');
        $dataFim = $request->get('data_fim') ?: Carbon::today()->format('Y-m-d');

        $query = Conferencia::with(['loja', 'balanca', 'user', 'itens'])
            ->when($lojaId, fn ($q) => $q->where('loja_id', $lojaId))
            ->whereIn('loja_id', $lojasIds)
            ->whereDate('data_conferencia', '>=', $dataInicio)
            ->whereDate('data_conferencia', '<=', $dataFim);

        $conferencias = (clone $query)->latest('data_conferencia')->get();

        $resumo = (clone $query)
            ->selectRaw('COUNT(*) as total,
                SUM(status = "aprovado") as aprovadas,
                SUM(status = "reprovado") as reprovadas,
                SUM(status = "aprovado_com_ressalva") as ressalvas,
                AVG(diferenca) as media_diferenca')
            ->first();

        $porLoja = (clone $query)
            ->selectRaw('loja_id, COUNT(*) as total,
                SUM(status = "aprovado") as aprovadas,
                SUM(status = "reprovado") as reprovadas')
            ->groupBy('loja_id')
            ->with('loja')
            ->get();

        $porBalanca = (clone $query)
            ->selectRaw('balanca_id, COUNT(*) as total,
                SUM(status = "aprovado") as aprovadas,
                SUM(status = "reprovado") as reprovadas')
            ->groupBy('balanca_id')
            ->with('balanca')
            ->get();

        $reprovadas = (clone $query)
            ->where('status', 'reprovado')
            ->latest('data_conferencia')
            ->get();

        return view('relatorios.index', compact(
            'lojas',
            'lojaId',
            'dataInicio',
            'dataFim',
            'conferencias',
            'resumo',
            'porLoja',
            'porBalanca',
            'reprovadas'
        ));
    }

    public function exportCsv(Request $request)
    {
        $user = Auth::user();
        $lojasIds = $user->lojasPermitidasIds();

        $lojaId = $request->get('loja_id');

        if ($lojaId && ! $user->podeAcessarLoja((int) $lojaId)) {
            abort(403);
        }

        $dataInicio = $request->get('data_inicio', Carbon::today()->subDays(30)->format('Y-m-d'));
        $dataFim = $request->get('data_fim', Carbon::today()->format('Y-m-d'));

        $conferencias = Conferencia::with(['loja', 'balanca', 'user'])
            ->when($lojaId, fn ($q) => $q->where('loja_id', $lojaId))
            ->whereIn('loja_id', $lojasIds)
            ->whereDate('data_conferencia', '>=', $dataInicio)
            ->whereDate('data_conferencia', '<=', $dataFim)
            ->orderBy('data_conferencia')
            ->get();

        $output = fopen('php://temp', 'w');

        fputcsv($output, [
            'Data', 'Hora', 'Loja', 'Balança', 'Colaborador',
            'Item', 'Peso Esperado (kg)', 'Peso Real (kg)',
            'Diferença (kg)', 'Tolerância (kg)', 'Status', 'Observação',
        ], ';');

        foreach ($conferencias as $c) {
            foreach (($c->itens ?? collect()) as $item) {
                fputcsv($output, [
                    $c->data_conferencia->format('d/m/Y'),
                    $c->created_at->format('H:i'),
                    $c->loja?->nome,
                    $c->balanca?->nome,
                    $c->user?->name,
                    $item->descricao_item,
                    number_format($item->peso_esperado, 3, ',', '.'),
                    number_format($item->peso_real, 3, ',', '.'),
                    number_format($item->diferenca, 3, ',', '.'),
                    number_format($c->tolerancia_usada, 3, ',', '.'),
                    $c->status_label,
                    $item->observacao,
                ], ';');
            }
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        $content = "\xEF\xBB\xBF" . $content;

        $nomeArquivo = 'conferencias_' . $dataInicio . '_' . $dataFim . '.csv';

        return response($content)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $nomeArquivo . '"');
    }
}