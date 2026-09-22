@extends('layouts.app')

@section('title', 'Novo Equipamento de Frio')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title mb-0"><i class="bi bi-plus-circle me-2"></i>Novo Equipamento de Frio</h1>
    <a href="{{ route('equipamentos.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card">
            <div class="card-body p-4">
                <form action="{{ route('equipamentos.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="loja_id" class="form-label">Loja <span class="text-danger">*</span></label>
                        <select class="form-select" id="loja_id" name="loja_id" required>
                            <option value="">Selecione...</option>
                            @foreach($lojas as $loja)
                                <option value="{{ $loja->id }}" {{ (string) $loja->id === (string) $lojaSelecionada ? 'selected' : '' }}>{{ $loja->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nome" name="nome" value="{{ old('nome') }}" required placeholder="Ex: Câmara congelados ala sul">
                    </div>
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            @foreach($tipos as $k => $tipo)
                                <option value="{{ $k }}"
                                    data-temp-min="{{ $tipo['temp_min'] ?? '' }}"
                                    data-temp-max="{{ $tipo['temp_max'] }}"
                                    {{ old('tipo', 'camara_congelada') === $k ? 'selected' : '' }}
                                >{{ $tipo['label'] }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Ao selecionar o tipo, as temperaturas padrão de referência da RDC 216/2004 são preenchidas automaticamente. Ajuste conforme necessário.</div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="marca" class="form-label">Marca</label>
                            <input type="text" class="form-control" id="marca" name="marca" value="{{ old('marca') }}" placeholder="Ex: Metalfrigo">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="modelo" class="form-label">Modelo</label>
                            <input type="text" class="form-control" id="modelo" name="modelo" value="{{ old('modelo') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="temp_min" class="form-label">Temperatura mín. (°C)</label>
                            <input type="number" step="0.10" class="form-control" id="temp_min" name="temp_min" value="{{ old('temp_min') }}" inputmode="decimal">
                            <div class="form-text">Quando a faixa não possui limite inferior, deixe em branco.</div>
                            @error('temp_min') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-6 mb-3">
                            <label for="temp_max" class="form-label">Temperatura máx. (°C) <span class="text-danger">*</span></label>
                            <input type="number" step="0.10" class="form-control" id="temp_max" name="temp_max" value="{{ old('temp_max') }}" required inputmode="decimal">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="tolerancia_c" class="form-label">Tolerância (°C) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" class="form-control" id="tolerancia_c" name="tolerancia_c" value="{{ old('tolerancia_c', '0.50') }}" required inputmode="decimal">
                        <div class="form-text">Margem admitida além da faixa para caracterizar aprovação (ex.: 0,5 °C).</div>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Equipamento ativo</label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-app">
                            <i class="bi bi-save me-2"></i>Salvar equipamento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('tipo').addEventListener('change', function () {
    const opt = this.selectedOptions[0];
    const tempMinInput = document.getElementById('temp_min');
    const tempMaxInput = document.getElementById('temp_max');

    if (!tempMinInput.value) {
        tempMinInput.value = opt.dataset.tempMin ?? '';
    }
    if (!tempMaxInput.value) {
        tempMaxInput.value = opt.dataset.tempMax ?? '';
    }
});
</script>
@endpush