@extends('layouts.app')

@section('title', 'Detalhes da Conferência')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title mb-0"><i class="bi bi-eye me-2"></i>Detalhes da Conferência</h1>
    <a href="{{ route('coleta.historico') }}" class="btn btn-sm btn-outline-secondary">
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
                        <strong>{{ $conferencia->loja?->nome }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Balança</span>
                        <strong>{{ $conferencia->balanca?->nome }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Colaborador</span>
                        <strong>{{ $conferencia->user?->name }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Data</span>
                        <strong>{{ $conferencia->data_conferencia->format('d/m/Y H:i') }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Status</span>
                        <span class="badge bg-{{ $conferencia->status_color }}">{{ $conferencia->status_label }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Tolerância usada</span>
                        <strong>{{ number_format($conferencia->tolerancia_usada, 3, ',', '.') }} kg</strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">Itens conferidos</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th class="text-end">Esperado</th>
                                <th class="text-end">Real</th>
                                <th class="text-end">Diferença</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($conferencia->itens as $item)
                            <tr>
                                <td class="small">{{ $item->descricao_item ?? '—' }}</td>
                                <td class="text-end small">{{ number_format($item->peso_esperado, 3, ',', '.') }}</td>
                                <td class="text-end small">{{ number_format($item->peso_real, 3, ',', '.') }}</td>
                                <td class="text-end small fw-semibold {{ $item->dentro_tolerancia ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($item->diferenca, 3, ',', '.') }}
                                </td>
                            </tr>
                            @if($item->observacao)
                            <tr>
                                <td colspan="4" class="small text-muted ps-4 py-1">
                                    <i class="bi bi-chat-left-text me-1"></i>{{ $item->observacao }}
                                </td>
                            </tr>
                            @endif
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Nenhum item.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($conferencia->tickets->isNotEmpty())
        <div class="card">
            <div class="card-header">Chamados GLPI</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nº Chamado</th>
                                <th>Título</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($conferencia->tickets as $ticket)
                            <tr>
                                <td class="small">{{ $ticket->ticket_id ? '#' . $ticket->ticket_id : '—' }}</td>
                                <td class="small">{{ $ticket->titulo }}</td>
                                <td>
                                    <span class="badge bg-{{ $ticket->status === 'enviado' ? 'success' : ($ticket->status === 'erro' ? 'danger' : 'secondary') }}">
                                        {{ $ticket->status }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection