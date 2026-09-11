@extends('layouts.app')

@section('title', 'Relatórios')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title mb-0"><i class="bi bi-graph-up me-2"></i>Relatórios</h1>
</div>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('relatorios.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label for="loja_id" class="form-label small mb-1">Loja</label>
                <select class="form-select form-select-sm" id="loja_id" name="loja_id">
                    <option value="">Todas as lojas</option>
                    @foreach($lojas as $loja)
                        <option value="{{ $loja->id }}" {{ $lojaId == $loja->id ? 'selected' : '' }}>{{ $loja->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label for="data_inicio" class="form-label small mb-1">Data início</label>
                <input type="date" class="form-control form-control-sm" id="data_inicio" name="data_inicio" value="{{ $dataInicio }}">
            </div>
            <div class="col-6 col-md-3">
                <label for="data_fim" class="form-label small mb-1">Data fim</label>
                <input type="date" class="form-control form-control-sm" id="data_fim" name="data_fim" value="{{ $dataFim }}">
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">
                    <i class="bi bi-search me-1"></i>Filtrar
                </button>
                <a href="{{ route('relatorios.export', ['loja_id' => $lojaId, 'data_inicio' => $dataInicio, 'data_fim' => $dataFim]) }}" class="btn btn-sm btn-success flex-fill">
                    <i class="bi bi-file-earmark-excel me-1"></i>CSV
                </a>
            </div>
        </form>
    </div>
</div>

@php
    $totalGeral = $resumo->total ?? 0;
    $totalAprovadas = $resumo->aprovadas ?? 0;
    $totalReprovadas = $resumo->reprovadas ?? 0;
    $totalRessalvas = $resumo->ressalvas ?? 0;
    $taxaAprovacao = $totalGeral > 0 ? ($totalAprovadas / $totalGeral) * 100 : 0;
@endphp

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="h4 mb-0">{{ $totalGeral }}</div>
                <div class="small text-muted">Total</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="h4 mb-0 text-success">{{ $totalAprovadas }}</div>
                <div class="small text-muted">Aprovadas</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="h4 mb-0 text-danger">{{ $totalReprovadas }}</div>
                <div class="small text-muted">Fora da tolerância</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="h4 mb-0 text-primary">{{ number_format($taxaAprovacao, 1, ',', '.') }}%</div>
                <div class="small text-muted">Taxa de aprovação</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Por loja</div>
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
                            @foreach($porLoja as $l)
                            <tr>
                                <td>{{ $l->loja?->nome }}</td>
                                <td class="text-center text-success">{{ $l->aprovadas }}</td>
                                <td class="text-center text-danger">{{ $l->reprovadas }}</td>
                                <td class="text-center fw-semibold">{{ $l->total }}</td>
                            </tr>
                            @endforeach
                            @if($porLoja->isEmpty())
                            <tr><td colspan="4" class="text-center text-muted py-3">Sem dados no período.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Por balança</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Balança</th>
                                <th class="text-center">Aprov.</th>
                                <th class="text-center">Reprov.</th>
                                <th class="text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($porBalanca as $b)
                            <tr>
                                <td>{{ $b->balanca?->nome }}</td>
                                <td class="text-center text-success">{{ $b->aprovadas }}</td>
                                <td class="text-center text-danger">{{ $b->reprovadas }}</td>
                                <td class="text-center fw-semibold">{{ $b->total }}</td>
                            </tr>
                            @endforeach
                            @if($porBalanca->isEmpty())
                            <tr><td colspan="4" class="text-center text-muted py-3">Sem dados no período.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if($reprovadas->isNotEmpty())
<div class="card mt-3">
    <div class="card-header">Conferências fora da tolerância</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Data</th>
                        <th>Loja</th>
                        <th>Balança</th>
                        <th>Item</th>
                        <th class="text-end">Esperado</th>
                        <th class="text-end">Real</th>
                        <th class="text-end">Diferença</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reprovadas as $c)
                    <tr>
                        <td class="small">{{ $c->data_conferencia->format('d/m/Y H:i') }}</td>
                        <td>{{ $c->loja?->nome }}</td>
                        <td class="small">{{ $c->balanca?->nome }}</td>
                        <td class="small">{{ $c->itens->first()?->descricao_item ?? '—' }}</td>
                        <td class="text-end small">{{ number_format($c->peso_esperado, 3, ',', '.') }}</td>
                        <td class="text-end small">{{ number_format($c->peso_real, 3, ',', '.') }}</td>
                        <td class="text-end small fw-semibold text-danger">{{ number_format($c->diferenca, 3, ',', '.') }} kg</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection