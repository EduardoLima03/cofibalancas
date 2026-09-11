@extends('layouts.app')

@section('title', 'Editar Balança')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title mb-0"><i class="bi bi-pencil me-2"></i>Editar Balança</h1>
    <a href="{{ route('balancas.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card">
            <div class="card-body p-4">
                <form action="{{ route('balancas.update', $balanca) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="loja_id" class="form-label">Loja <span class="text-danger">*</span></label>
                        <select class="form-select" id="loja_id" name="loja_id" required>
                            <option value="">Selecione...</option>
                            @foreach($lojas as $loja)
                                <option value="{{ $loja->id }}" {{ $balanca->loja_id == $loja->id ? 'selected' : '' }}>{{ $loja->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome da balança <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nome" name="nome" value="{{ old('nome', $balanca->nome) }}" required placeholder="Ex: Balança de entrada">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="marca" class="form-label">Marca</label>
                            <input type="text" class="form-control" id="marca" name="marca" value="{{ old('marca', $balanca->marca) }}">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="modelo" class="form-label">Modelo</label>
                            <input type="text" class="form-control" id="modelo" name="modelo" value="{{ old('modelo', $balanca->modelo) }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="serial" class="form-label">Número de série</label>
                        <input type="text" class="form-control" id="serial" name="serial" value="{{ old('serial', $balanca->serial) }}">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="capacidade_kg" class="form-label">Capacidade (kg)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="capacidade_kg" name="capacidade_kg" value="{{ old('capacidade_kg', $balanca->capacidade_kg) }}">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="tolerancia_kg" class="form-label">Tolerância (kg) <span class="text-danger">*</span></label>
                            <input type="number" step="0.001" min="0" class="form-control" id="tolerancia_kg" name="tolerancia_kg" value="{{ old('tolerancia_kg', $balanca->tolerancia_kg) }}" required inputmode="decimal">
                        </div>
                    </div>
                    <div class="form-text mb-3">A tolerância é a diferença máxima permitida entre o peso esperado e o peso real.</div>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ $balanca->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Balança ativa</label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-app">
                            <i class="bi bi-save me-2"></i>Atualizar balança
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection