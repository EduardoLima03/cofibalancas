@extends('layouts.app')

@section('title', 'Detalhes da Loja')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title mb-0"><i class="bi bi-eye me-2"></i>{{ $loja->nome }}</h1>
    <a href="{{ route('lojas.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="row g-3">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">Informações</div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Nome</span>
                        <strong>{{ $loja->nome }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Código</span>
                        <strong>{{ $loja->codigo ?? '—' }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">CNPJ</span>
                        <strong>{{ $loja->cnpj ?? '—' }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Status</span>
                        <span class="badge bg-{{ $loja->is_active ? 'success' : 'secondary' }}">{{ $loja->is_active ? 'Ativa' : 'Inativa' }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Balanças da loja</span>
                <a href="{{ route('balancas.create', ['loja_id' => $loja->id]) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus-circle me-1"></i> Nova balança
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th>Marca/Modelo</th>
                                <th class="text-end">Tolerância</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($loja->balancas as $balanca)
                            <tr>
                                <td>{{ $balanca->nome }}</td>
                                <td class="small">{{ $balanca->marca ?? '—' }} / {{ $balanca->modelo ?? '—' }}</td>
                                <td class="text-end small">{{ number_format($balanca->tolerancia_kg, 3, ',', '.') }} kg</td>
                                <td>
                                    <span class="badge bg-{{ $balanca->is_active ? 'success' : 'secondary' }}">
                                        {{ $balanca->is_active ? 'Ativa' : 'Inativa' }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Nenhuma balança cadastrada.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection