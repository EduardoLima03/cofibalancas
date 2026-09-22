@extends('layouts.app')

@section('title', 'Histórico de Aferições de Temperatura')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title mb-0"><i class="bi bi-clock-history me-2"></i>Histórico de Aferições de Temperatura</h1>
    <a href="{{ route('temperatura.create') }}" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Nova aferição
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
                        <th>Equipamento</th>
                        <th class="text-end">Temperatura</th>
                        <th class="text-end">Desvio</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($afericoes as $a)
                    <tr>
                        <td class="small">{{ $a->data_afericao->format('d/m/Y H:i') }}</td>
                        <td>{{ $a->loja?->nome }}</td>
                        <td class="small">{{ $a->equipamento?->nome ?? '—' }}
                            @if($a->equipamento)
                                <span class="text-muted">({{ $a->equipamento->tipo_label }})</span>
                            @endif
                        </td>
                        <td class="text-end small fw-semibold">{{ number_format($a->temperatura_lida, 1, ',', '.') }} °C</td>
                        <td class="text-end small">{{ number_format($a->desvio, 2, ',', '.') }} °C</td>
                        <td>
                            <span class="badge bg-{{ $a->status_color }}">{{ $a->status_label }}</span>
                        </td>
                        <td class="text-end">
                            @if(auth()->user()->role === 'coletor')
                                <a href="{{ route('coletor.temperatura.show', $a) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @else
                                <a href="{{ route('temperatura.show', $a) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-thermometer-half display-6 d-block mb-2"></i>
                            Nenhuma aferição de temperatura realizada ainda.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $afericoes->links() }}
</div>
@endsection