<?php

namespace App\Http\Controllers;

use App\Models\AfericaoTemperatura;
use App\Models\Conferencia;
use App\Models\Loja;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarioController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $lojasIds = $user->lojasPermitidasIds();

        $lojas = Loja::where('is_active', true)
            ->whereIn('id', $lojasIds)
            ->orderBy('nome')
            ->get();

        $mes = $request->get('mes', now()->format('Y-m'));
        $lojaSelecionada = $request->get('loja_id', $user->role === 'admin' ? null : $user->lojas->first()?->id);

        if ($lojaSelecionada && ! in_array((int) $lojaSelecionada, $lojasIds->all(), true)) {
            abort(403, 'Você não tem acesso a esta loja.');
        }

        [$ano, $mesNum] = array_map('intval', explode('-', $mes));

        $dias = [];
        $primeiroDia = Carbon::create($ano, $mesNum, 1);
        $diasNoMes = $primeiroDia->daysInMonth;
        $offset = $primeiroDia->dayOfWeek;
        $totalCelulas = ceil(($offset + $diasNoMes) / 7) * 7;

        for ($i = 0; $i < $totalCelulas; $i++) {
            $dia = $i - $offset + 1;
            $dias[] = ($i < $offset || $dia > $diasNoMes) ? null : $dia;
        }

        $query = Conferencia::with('loja')
            ->whereYear('data_conferencia', $ano)
            ->whereMonth('data_conferencia', $mesNum)
            ->whereIn('loja_id', $lojasIds)
            ->when($lojaSelecionada, fn ($q) => $q->where('loja_id', $lojaSelecionada))
            ->selectRaw('DATE(data_conferencia) as data, loja_id,
                COUNT(*) as total,
                SUM(status = "aprovado") as aprovadas,
                SUM(status = "reprovado") as reprovadas,
                SUM(status = "aprovado_com_ressalva") as ressalvas')
            ->groupBy('data', 'loja_id')
            ->orderBy('data')
            ->get();

        $resumoPorData = [];
        $statusGeralPorData = [];

        foreach ($query as $row) {
            $data = $row->data;

            if (! isset($resumoPorData[$data])) {
                $resumoPorData[$data] = [
                    'total' => 0,
                    'aprovadas' => 0,
                    'reprovadas' => 0,
                    'ressalvas' => 0,
                    'afericoes' => 0,
                    'afericoes_aprovadas' => 0,
                    'afericoes_reprovadas' => 0,
                    'status' => 'aprovado',
                ];
            }

            $resumoPorData[$data]['total'] += $row->total;
            $resumoPorData[$data]['aprovadas'] += $row->aprovadas;
            $resumoPorData[$data]['reprovadas'] += $row->reprovadas;
            $resumoPorData[$data]['ressalvas'] += $row->ressalvas;
        }

        $afericoesQuery = AfericaoTemperatura::query()
            ->whereYear('data_afericao', $ano)
            ->whereMonth('data_afericao', $mesNum)
            ->whereIn('loja_id', $lojasIds)
            ->when($lojaSelecionada, fn ($q) => $q->where('loja_id', $lojaSelecionada))
            ->selectRaw('DATE(data_afericao) as data, loja_id,
                COUNT(*) as total,
                SUM(status = "aprovado") as aprovadas,
                SUM(status = "reprovado") as reprovadas')
            ->groupBy('data', 'loja_id')
            ->orderBy('data')
            ->get();

        foreach ($afericoesQuery as $row) {
            $data = $row->data;

            if (! isset($resumoPorData[$data])) {
                $resumoPorData[$data] = [
                    'total' => 0,
                    'aprovadas' => 0,
                    'reprovadas' => 0,
                    'ressalvas' => 0,
                    'afericoes' => 0,
                    'afericoes_aprovadas' => 0,
                    'afericoes_reprovadas' => 0,
                    'status' => 'aprovado',
                ];
            }

            $resumoPorData[$data]['afericoes'] += $row->total;
            $resumoPorData[$data]['afericoes_aprovadas'] += $row->aprovadas;
            $resumoPorData[$data]['afericoes_reprovadas'] += $row->reprovadas;
        }

        foreach ($resumoPorData as $data => &$resumo) {
            if ($resumo['reprovadas'] > 0 || $resumo['afericoes_reprovadas'] > 0) {
                $resumo['status'] = 'reprovado';
            } elseif ($resumo['ressalvas'] > 0) {
                $resumo['status'] = 'ressalva';
            }
        }
        unset($resumo);

        $detalhesDia = $request->get('dia');

        $conferenciasDia = null;
        $afericoesDia = null;
        if ($detalhesDia && $lojaSelecionada) {
            $conferenciasDia = Conferencia::with(['loja', 'balanca', 'user'])
                ->whereDate('data_conferencia', "$ano-$mesNum-$detalhesDia")
                ->where('loja_id', $lojaSelecionada)
                ->get();

            $afericoesDia = AfericaoTemperatura::with(['loja', 'equipamento', 'user'])
                ->whereDate('data_afericao', "$ano-$mesNum-$detalhesDia")
                ->where('loja_id', $lojaSelecionada)
                ->get();
        }

        $conferenciasPorData = collect();
        $afericoesPorData = collect();
        if ($lojaSelecionada) {
            $conferenciasPorData = Conferencia::with(['loja', 'balanca', 'user', 'itens'])
                ->whereYear('data_conferencia', $ano)
                ->whereMonth('data_conferencia', $mesNum)
                ->where('loja_id', $lojaSelecionada)
                ->orderBy('data_conferencia')
                ->get()
                ->groupBy(fn ($c) => $c->data_conferencia->format('Y-m-d'));

            $afericoesPorData = AfericaoTemperatura::with(['loja', 'equipamento', 'user'])
                ->whereYear('data_afericao', $ano)
                ->whereMonth('data_afericao', $mesNum)
                ->where('loja_id', $lojaSelecionada)
                ->orderBy('data_afericao')
                ->get()
                ->groupBy(fn ($a) => $a->data_afericao->format('Y-m-d'));
        }

        return view('calendario.index', compact(
            'lojas',
            'mes',
            'ano',
            'mesNum',
            'lojaSelecionada',
            'dias',
            'diasNoMes',
            'offset',
            'resumoPorData',
            'detalhesDia',
            'conferenciasDia',
            'afericoesDia',
            'conferenciasPorData',
            'afericoesPorData'
        ));
    }
}