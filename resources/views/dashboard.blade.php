@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title mb-0"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h1>
    <div class="text-muted small">{{ now()->format('d/m/Y H:i') }}</div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-clipboard-check"></i></div>
                <div>
                    <div class="h4 mb-0">{{ $totalConferencias }}</div>
                    <div class="small text-muted">Conferências (30d)</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-check-circle"></i></div>
                <div>
                    <div class="h4 mb-0">{{ $totalAprovadas }}</div>
                    <div class="small text-muted">Aprovadas</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-x-circle"></i></div>
                <div>
                    <div class="h4 mb-0">{{ $totalReprovadas }}</div>
                    <div class="small text-muted">Fora da tolerância</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-shop"></i></div>
                <div>
                    <div class="h4 mb-0">{{ $totalLojas }}</div>
                    <div class="small text-muted">Lojas ativas</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Últimas conferências</span>
                @if(auth()->user()->role !== 'coletor')
                    <a href="{{ route('coleta.historico') }}" class="btn btn-sm btn-outline-primary">Ver histórico</a>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Loja</th>
                                <th>Item</th>
                                <th class="text-end">Diferença</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimasConferencias as $c)
                            <tr>
                                <td class="small">{{ $c->data_conferencia->format('d/m/Y H:i') }}</td>
                                <td>{{ $c->loja?->nome }}</td>
                                <td class="small">{{ $c->itens->first()?->descricao_item ?? '—' }}</td>
                                <td class="text-end small fw-semibold">{{ number_format($c->diferenca, 3, ',', '.') }} kg</td>
                                <td>
                                    <span class="badge bg-{{ $c->status_color }}">
                                        <i class="bi {{ $c->status == 'aprovado' ? 'bi-check-circle' : 'bi-x-circle' }} me-1"></i>
                                        {{ $c->status_label }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Nenhuma conferência realizada.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header">Conferências por loja</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Loja</th>
                                <th class="text-center">Aprov.</th>
                                <th class="text-center">Reprov.</th>
                                <th class="text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($conferenciasPorLoja as $l)
                            <tr>
                                <td>{{ $l->loja?->nome }}</td>
                                <td class="text-center text-success">{{ $l->aprovadas ?? 0 }}</td>
                                <td class="text-center text-danger">{{ $l->reprovadas ?? 0 }}</td>
                                <td class="text-center fw-semibold">{{ $l->total }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Sem dados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Atividade dos últimos 30 dias</div>
            <div class="card-body">
                @php
                    $maxDia = $conferenciasPorDia->max('total') ?: 1;
                @endphp
                <div class="d-flex align-items-end gap-1" style="height: 120px;">
                    @foreach($conferenciasPorDia as $dia)
                        @php
                            $altura = max(4, round(($dia->total / $maxDia) * 100));
                            $data = \Illuminate\Support\Carbon::parse($dia->data);
                        @endphp
                        <div class="flex-fill d-flex flex-column justify-content-end align-items-center" title="{{ $data->format('d/m') }} — {{ $dia->total }} conferências">
                            <div class="bg-primary rounded" style="height: {{ $altura }}px; width: 80%; opacity: 0.85;"></div>
                        </div>
                    @endforeach
                </div>
                <div class="small text-muted mt-2">Total de conferências por dia (últimos 30 dias).</div>
            </div>
        </div>
    </div>
</div>
@endsection