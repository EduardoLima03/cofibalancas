@extends('layouts.app')

@section('title', 'Histórico de Conferências')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title mb-0"><i class="bi bi-clock-history me-2"></i>Histórico de Conferências</h1>
    <a href="{{ route('coleta.create') }}" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Nova coleta
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Data</th>
                        <th>Loja</th>
                        <th>Item</th>
                        <th class="text-end">Esperado</th>
                        <th class="text-end">Real</th>
                        <th class="text-end">Diferença</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conferencias as $c)
                    <tr>
                        <td class="small">{{ $c->data_conferencia->format('d/m/Y H:i') }}</td>
                        <td>{{ $c->loja?->nome }}</td>
                        <td class="small">{{ $c->itens->first()?->descricao_item ?? '—' }}</td>
                        <td class="text-end small">{{ number_format($c->peso_esperado, 3, ',', '.') }}</td>
                        <td class="text-end small">{{ number_format($c->peso_real, 3, ',', '.') }}</td>
                        <td class="text-end small fw-semibold">{{ number_format($c->diferenca, 3, ',', '.') }}</td>
                        <td>
                            <span class="badge bg-{{ $c->status_color }}">{{ $c->status_label }}</span>
                        </td>
                        <td class="text-end">
                            @if(auth()->user()->role === 'coletor')
                                <a href="{{ route('coletor.show', $c) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @else
                                <a href="{{ route('coleta.show', $c) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-inbox display-6 d-block mb-2"></i>
                            Nenhuma conferência realizada ainda.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $conferencias->links() }}
</div>
@endsection