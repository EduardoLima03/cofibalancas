@extends('layouts.app')

@section('title', 'Lojas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title mb-0"><i class="bi bi-shop me-2"></i>Lojas</h1>
    <a href="{{ route('lojas.create') }}" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Nova loja
    </a>
</div>

<div class="row g-3">
    @forelse($lojas as $loja)
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="mb-0">{{ $loja->nome }}</h5>
                    @if($loja->is_active)
                        <span class="badge bg-success">Ativa</span>
                    @else
                        <span class="badge bg-secondary">Inativa</span>
                    @endif
                </div>
                <p class="text-muted small mb-2">
                    @if($loja->cnpj)
                        <i class="bi bi-file-earmark-text me-1"></i>{{ $loja->cnpj }}
                    @else
                        <span class="opacity-50"><i class="bi bi-file-earmark-text me-1"></i>Sem CNPJ</span>
                    @endif
                </p>
                <p class="small mb-3">
                    <span class="badge bg-light text-dark me-1"><i class="bi bi-basket2 me-1"></i>{{ $loja->balancas_count }} balança(s)</span>
                    <span class="badge bg-light text-dark"><i class="bi bi-clipboard-check me-1"></i>{{ $loja->conferencias_count }} conferência(s)</span>
                </p>
                <div class="d-flex gap-2">
                    <a href="{{ route('lojas.show', $loja) }}" class="btn btn-sm btn-outline-primary flex-fill"><i class="bi bi-eye me-1"></i>Ver</a>
                    <a href="{{ route('lojas.edit', $loja) }}" class="btn btn-sm btn-outline-secondary flex-fill"><i class="bi bi-pencil me-1"></i>Editar</a>
                    <form action="{{ route('lojas.destroy', $loja) }}" method="POST" class="flex-fill" onsubmit="return confirm('Excluir a loja {{ $loja->nome }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-trash me-1"></i>Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-shop display-6 d-block mb-2"></i>
                Nenhuma loja cadastrada.
                <a href="{{ route('lojas.create') }}" class="d-block mt-2 text-decoration-none">Cadastrar primeira loja</a>
            </div>
        </div>
    </div>
    @endforelse
</div>

<div class="mt-3">
    {{ $lojas->links() }}
</div>
@endsection