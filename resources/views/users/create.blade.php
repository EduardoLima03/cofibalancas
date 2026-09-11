@extends('layouts.app')

@section('title', 'Novo Usuário')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title mb-0"><i class="bi bi-person-plus me-2"></i>Novo Usuário</h1>
    <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card">
            <div class="card-body p-4">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome completo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuário (login) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}" required autocomplete="off" placeholder="ex.: joao.silva">
                        <div class="form-text">Usado para acessar o sistema na tela de login.</div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail <span class="text-muted">(opcional)</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Perfil de acesso <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="coletor" {{ old('role') === 'coletor' ? 'selected' : '' }}>Coletor — apenas realiza conferências</option>
                            <option value="gerente" {{ old('role') === 'gerente' ? 'selected' : '' }}>Gerente — coleta e relatórios</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin — acesso total</option>
                        </select>
                    </div>
                    <div class="mb-3" id="campoLoja">
                        <label for="loja_id" class="form-label">Loja <span class="text-danger">*</span></label>
                        <select class="form-select" id="loja_id" name="loja_id">
                            <option value="">Selecione...</option>
                            @foreach($lojas as $loja)
                                <option value="{{ $loja->id }}" {{ old('loja_id') == $loja->id ? 'selected' : '' }}>{{ $loja->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-text mb-3" id="textoAdmin" class="d-none">O administrador tem acesso a todas as lojas.</div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="password" class="form-label">Senha <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="6">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="password_confirmation" class="form-label">Confirmar senha <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required minlength="6">
                        </div>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Usuário ativo</label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-app">
                            <i class="bi bi-save me-2"></i>Salvar usuário
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
    const roleSelect = document.getElementById('role');
    const campoLoja = document.getElementById('campoLoja');
    const lojaSelect = document.getElementById('loja_id');

    function atualizarCampoLoja() {
        if (roleSelect.value === 'admin') {
            campoLoja.classList.add('d-none');
            lojaSelect.removeAttribute('required');
            lojaSelect.value = '';
        } else {
            campoLoja.classList.remove('d-none');
            lojaSelect.setAttribute('required', 'required');
        }
    }

    roleSelect.addEventListener('change', atualizarCampoLoja);
    atualizarCampoLoja();
</script>
@endpush