@extends('layouts.app')

@section('title', 'Usuários')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title mb-0"><i class="bi bi-people me-2"></i>Usuários</h1>
    <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Novo usuário
    </a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-center">
            <div class="col-6 col-md-3">
                <select class="form-select form-select-sm" name="role" onchange="this.form.submit()">
                    <option value="">Todos os perfis</option>
                    <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="gerente" {{ $role === 'gerente' ? 'selected' : '' }}>Gerente</option>
                    <option value="coletor" {{ $role === 'coletor' ? 'selected' : '' }}>Coletor</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select class="form-select form-select-sm" name="loja_id" onchange="this.form.submit()">
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
                        <th>Usuário</th>
                        <th>Perfil</th>
                        <th>Lojas</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td class="fw-semibold">{{ $user->name }}</td>
                        <td class="small">{{ $user->username }}</td>
                        <td>
                            <span class="badge bg-{{ $user->role === 'admin' ? 'primary' : ($user->role === 'gerente' ? 'warning text-dark' : 'info text-dark') }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="small">
                            @if($user->role === 'admin')
                                <span class="text-muted">Todas</span>
                            @elseif($user->lojas->isEmpty())
                                <span class="text-muted">—</span>
                            @else
                                {{ $user->lojas->pluck('nome')->join(', ') }}
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}">
                                {{ $user->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir o usuário {{ $user->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-people display-6 d-block mb-2"></i>
                            Nenhum usuário encontrado.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $users->links() }}
</div>
@endsection