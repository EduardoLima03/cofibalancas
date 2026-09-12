<?php

namespace App\Http\Controllers;

use App\Models\Loja;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $lojaId = $request->get('loja_id');
        $role = $request->get('role');

        $lojas = Loja::orderBy('nome')->get();

        $users = User::with('loja')
            ->when($lojaId, fn ($q) => $q->where('loja_id', $lojaId))
            ->when($role, fn ($q) => $q->where('role', $role))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users', 'lojas', 'lojaId', 'role'));
    }

    public function create()
    {
        $lojas = Loja::orderBy('nome')->get();

        return view('users.create', compact('lojas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,gerente,coletor',
            'loja_id' => 'nullable|exists:lojas,id',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        if (! $request->filled('email')) {
            $data['email'] = '';
        }

        if ($data['role'] !== 'admin' && empty($data['loja_id'])) {
            return back()->withErrors([
                'loja_id' => 'A loja é obrigatória para ' . $data['role'] . ' e coletor.',
            ])->withInput();
        }

        User::create($data);

        return redirect()->route('users.index')
            ->with('success', 'Usuário cadastrado com sucesso!');
    }

    public function edit(User $user)
    {
        $lojas = Loja::orderBy('nome')->get();

        return view('users.edit', compact('user', 'lojas'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:admin,gerente,coletor',
            'loja_id' => 'nullable|exists:lojas,id',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        if (! $request->filled('email')) {
            $data['email'] = '';
        }

        if ($data['role'] !== 'admin' && empty($data['loja_id'])) {
            return back()->withErrors([
                'loja_id' => 'A loja é obrigatória para gerente e coletor.',
            ])->withInput();
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Você não pode excluir a si mesmo.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuário excluído com sucesso!');
    }
}