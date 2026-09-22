@extends('layouts.app')

@section('title', 'Equipamentos de Frio')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title mb-0"><i class="bi bi-snow2 me-2"></i>Equipamentos de Frio</h1>
    <a href="{{ route('equipamentos.create') }}" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Novo equipamento
    </a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('equipamentos.index') }}" class="row g-2 align-items-center">
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
                        <th>Tipo</th>
                        <th class="text-end">Faixa</th>
                        <th class="text-end">Tolerância</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($equipamentos as $equipamento)
                    <tr>
                        <td class="fw-semibold">{{ $equipamento->nome }}</td>
                        <td>{{ $equipamento->loja?->nome }}</td>
                        <td class="small">{{ $equipamento->tipo_label }}</td>
                        <td class="text-end small">
                            {{ $equipamento->temp_min !== null ? number_format($equipamento->temp_min, 1, ',', '.') . ' a ' : '≤ ' }}{{ number_format($equipamento->temp_max, 1, ',', '.') }} °C
                        </td>
                        <td class="text-end small fw-semibold">{{ number_format($equipamento->tolerancia_c, 2, ',', '.') }} °C</td>
                        <td>
                            <span class="badge bg-{{ $equipamento->is_active ? 'success' : 'secondary' }}">
                                {{ $equipamento->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('equipamentos.edit', $equipamento) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('equipamentos.destroy', $equipamento) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir o equipamento {{ $equipamento->nome }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-snow2 display-6 d-block mb-2"></i>
                            Nenhum equipamento de frio cadastrado.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $equipamentos->links() }}
</div>
@endsection