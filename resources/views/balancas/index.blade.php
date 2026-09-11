@extends('layouts.app')

@section('title', 'Balanças')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title mb-0"><i class="bi bi-basket2 me-2"></i>Balanças</h1>
    <a href="{{ route('balancas.create') }}" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Nova balança
    </a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('balancas.index') }}" class="row g-2 align-items-center">
            <div class="col-8 col-md-4">
                <label for="loja_id" class="visually-hidden">Loja</label>
                <select class="form-select form-select-sm" id="loja_id" name="loja_id" onchange="this.form.submit()">
                    <option value="">Todas as lojas</option>
                    @foreach($lojas as $loja)
                        <option value="{{ $loja->id }}" {{ $lojaId == $loja->id ? 'selected' : '' }}>{{ $loja->nome }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Loja</th>
                        <th>Marca/Modelo</th>
                        <th class="text-end">Capacidade</th>
                        <th class="text-end">Tolerância</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($balancas as $balanca)
                    <tr>
                        <td class="fw-semibold">{{ $balanca->nome }}</td>
                        <td>{{ $balanca->loja?->nome }}</td>
                        <td class="small">{{ $balanca->marca ?? '—' }}@if($balanca->modelo) {{ $balanca->modelo }}@endif</td>
                        <td class="text-end small">{{ number_format($balanca->capacidade_kg, 0, ',', '.') }} kg</td>
                        <td class="text-end small fw-semibold">{{ number_format($balanca->tolerancia_kg, 3, ',', '.') }} kg</td>
                        <td>
                            <span class="badge bg-{{ $balanca->is_active ? 'success' : 'secondary' }}">
                                {{ $balanca->is_active ? 'Ativa' : 'Inativa' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('balancas.edit', $balanca) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('balancas.destroy', $balanca) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir a balança {{ $balanca->nome }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-basket2 display-6 d-block mb-2"></i>
                            Nenhuma balança cadastrada.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $balancas->links() }}
</div>
@endsection