@extends('layouts.app')

@section('title', 'Editar Usuário')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title mb-0"><i class="bi bi-pencil me-2"></i>Editar Usuário</h1>
    <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card">
            <div class="card-body p-4">
                <form action="{{ route('users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome completo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuário (login) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" value="{{ old('username', $user->username) }}" required autocomplete="off" placeholder="ex.: joao.silva">
                        <div class="form-text">Usado para acessar o sistema na tela de login.</div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail <span class="text-muted">(opcional)</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}">
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Perfil de acesso <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="coletor" {{ $user->role === 'coletor' ? 'selected' : '' }}>Coletor — apenas realiza conferências</option>
                            <option value="gerente" {{ $user->role === 'gerente' ? 'selected' : '' }}>Gerente — coleta e relatórios</option>
                            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin — acesso total</option>
                        </select>
                    </div>
                    <div class="mb-3" id="campoLojas">
                        <label class="form-label">Lojas <span class="text-danger">*</span></label>
                        <div class="border rounded p-2 bg-body" style="max-height:200px;overflow-y:auto;">
                            @php $lojasIds = old('lojas', $user->lojas->pluck('id')->map(fn($id) => (string) $id)->toArray()); @endphp
                            @foreach($lojas as $loja)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="lojas[]" value="{{ $loja->id }}" id="loja_{{ $loja->id }}"
                                        {{ in_array((string) $loja->id, $lojasIds) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="loja_{{ $loja->id }}">{{ $loja->nome }}</label>
                                </div>
                            @endforeach
                        </div>
                        @error('lojas')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-text mb-3" id="textoAdmin">O administrador tem acesso a todas as lojas.</div>
                    <div class="form-text mb-3"><strong>Atenção:</strong> Deixe a senha em branco para manter a atual.</div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="password" class="form-label">Nova senha</label>
                            <input type="password" class="form-control" id="password" name="password" minlength="6" autocomplete="new-password">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="password_confirmation" class="form-label">Confirmar nova senha</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                        </div>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Usuário ativo</label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-app">
                            <i class="bi bi-save me-2"></i>Atualizar usuário
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
    const campoLojas = document.getElementById('campoLojas');
    const checkboxes = document.querySelectorAll('input[name="lojas[]"]');

    function atualizarCampoLojas() {
        if (roleSelect.value === 'admin') {
            campoLojas.classList.add('d-none');
            checkboxes.forEach(cb => cb.checked = false);
        } else {
            campoLojas.classList.remove('d-none');
        }
    }

    roleSelect.addEventListener('change', atualizarCampoLojas);
    atualizarCampoLojas();

    const form = document.querySelector('form');
    form.addEventListener('submit', function () {
        if (roleSelect.value !== 'admin') {
            const checked = document.querySelectorAll('input[name="lojas[]"]:checked').length;
            if (checked === 0) {
                event.preventDefault();
                alert('Selecione ao menos uma loja.');
            }
        }
    });
</script>
@endpush