@extends('layouts.app')

@section('title', 'Nova Loja')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title mb-0"><i class="bi bi-plus-circle me-2"></i>Nova Loja</h1>
    <a href="{{ route('lojas.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card">
            <div class="card-body p-4">
                <form action="{{ route('lojas.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="nome" class="form-label">Nome da loja <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nome" name="nome" value="{{ old('nome') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="codigo" class="form-label">Código</label>
                            <input type="text" class="form-control" id="codigo" name="codigo" value="{{ old('codigo') }}">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="cnpj" class="form-label">CNPJ</label>
                        <input type="text" class="form-control" id="cnpj" name="cnpj" value="{{ old('cnpj') }}" maxlength="20" placeholder="00.000.000/0000-00">
                    </div>
                    <div class="mb-4">
                        <label for="glpi_entity_id" class="form-label">Entidade GLPI (ID)</label>
                        <input type="number" class="form-control" id="glpi_entity_id" name="glpi_entity_id" value="{{ old('glpi_entity_id') }}" min="0" placeholder="0">
                        <div class="form-text">Entidade do GLPI responsável por esta loja. Deixe vazio para usar a entidade padrão da configuração.</div>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Loja ativa</label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-app">
                            <i class="bi bi-save me-2"></i>Salvar loja
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection