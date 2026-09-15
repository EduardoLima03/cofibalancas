<?php

namespace App\Http\Controllers;

use App\Models\Balanca;
use App\Models\Conferencia;
use App\Models\Loja;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $lojasIds = $user->lojasPermitidasIds();

        $query = Conferencia::query()->with(['loja', 'balanca', 'user']);
        $query->whereIn('loja_id', $lojasIds);

        $periodoInicio = Carbon::today()->subDays(30);
        $conferencias = clone $query;
        $conferenciasPeriodo = $conferencias->whereDate('data_conferencia', '>=', $periodoInicio)->get();

        $totalConferencias = $conferenciasPeriodo->count();
        $totalAprovadas = $conferenciasPeriodo->where('status', 'aprovado')->count();
        $totalReprovadas = $conferenciasPeriodo->where('status', 'reprovado')->count();

        $totalLojas = Loja::query()
            ->whereIn('id', $lojasIds)
            ->where('is_active', true)
            ->count();

        $totalBalancas = Balanca::query()
            ->whereIn('loja_id', $lojasIds)
            ->where('is_active', true)
            ->count();

        $ultimasConferencias = $conferencias
            ->latest('data_conferencia')
            ->latest('id')
            ->limit(10)
            ->get();

        $conferenciasPorLoja = Conferencia::query()
            ->whereIn('loja_id', $lojasIds)
            ->selectRaw('loja_id, COUNT(*) as total, SUM(status = "aprovado") as aprovadas, SUM(status = "reprovado") as reprovadas')
            ->groupBy('loja_id')
            ->with('loja')
            ->get();

        $conferenciasPorDia = Conferencia::query()
            ->whereIn('loja_id', $lojasIds)
            ->whereDate('data_conferencia', '>=', $periodoInicio)
            ->selectRaw('DATE(data_conferencia) as data, COUNT(*) as total')
            ->groupBy('data')
            ->orderBy('data')
            ->get();

        return view('dashboard', compact(
            'totalConferencias',
            'totalAprovadas',
            'totalReprovadas',
            'totalLojas',
            'totalBalancas',
            'ultimasConferencias',
            'conferenciasPorLoja',
            'conferenciasPorDia'
        ));
    }
}