@extends('layouts.app')

@section('title', 'Detalhes da Aferição de Temperatura')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title mb-0"><i class="bi bi-eye me-2"></i>Detalhes da Aferição de Temperatura</h1>
    @if(auth()->user()->role === 'coletor')
        <a href="{{ route('coletor.temperatura') }}" class="btn btn-sm btn-outline-secondary">
    @else
        <a href="{{ route('temperatura.historico') }}" class="btn btn-sm btn-outline-secondary">
    @endif
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Informações gerais</div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Loja</span>
                        <strong>{{ $afericao->loja?->nome }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Equipamento</span>
                        <strong>{{ $afericao->equipamento?->nome ?? '—' }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Tipo</span>
                        <strong>{{ $afericao->equipamento?->tipo_label ?? '—' }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Colaborador</span>
                        <strong>{{ $afericao->user?->name }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Data</span>
                        <strong>{{ $afericao->data_afericao->format('d/m/Y H:i') }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Status</span>
                        <span class="badge bg-{{ $afericao->status_color }}">{{ $afericao->status_label }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">Resultado da aferição</div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Temperatura lida</span>
                        <strong>{{ number_format($afericao->temperatura_lida, 1, ',', '.') }} °C</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Faixa de referência</span>
                        <strong>{{ $afericao->faixa_label }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Desvio</span>
                        <span class="fw-semibold {{ $afericao->dentro_tolerancia ? 'text-success' : 'text-danger' }}">
                            {{ number_format($afericao->desvio, 2, ',', '.') }} °C
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Tolerância usada</span>
                        <strong>{{ number_format($afericao->tolerancia_usada, 2, ',', '.') }} °C</strong>
                    </li>
                    @if($afericao->observacao)
                    <li class="list-group-item px-0">
                        <span class="text-muted d-block mb-1">Observação</span>
                        <small>{{ $afericao->observacao }}</small>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection