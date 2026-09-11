<?php

namespace App\Http\Controllers;

use App\Models\Balanca;
use App\Models\Loja;
use Illuminate\Http\Request;

class BalancaController extends Controller
{
    public function index(Request $request)
    {
        $lojaId = $request->get('loja_id');

        $lojas = Loja::where('is_active', true)->orderBy('nome')->get();

        $balancas = Balanca::with('loja')
            ->when($lojaId, fn ($q) => $q->where('loja_id', $lojaId))
            ->orderBy('nome')
            ->paginate(15)
            ->withQueryString();

        return view('balancas.index', compact('balancas', 'lojas', 'lojaId'));
    }

    public function create(Request $request)
    {
        $lojas = Loja::where('is_active', true)->orderBy('nome')->get();

        return view('balancas.create', [
            'lojas' => $lojas,
            'lojaSelecionada' => $request->get('loja_id'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'loja_id' => 'required|exists:lojas,id',
            'nome' => 'required|string|max:255',
            'marca' => 'nullable|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'serial' => 'nullable|string|max:100',
            'capacidade_kg' => 'nullable|numeric|min:0',
            'tolerancia_kg' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        Balanca::create($data);

        return redirect()->route('balancas.index')
            ->with('success', 'Balança cadastrada com sucesso!');
    }

    public function show(Balanca $balanca)
    {
        $balanca->load('loja');

        return view('balancas.show', compact('balanca'));
    }

    public function edit(Balanca $balanca)
    {
        $lojas = Loja::where('is_active', true)->orderBy('nome')->get();

        return view('balancas.edit', compact('balanca', 'lojas'));
    }

    public function update(Request $request, Balanca $balanca)
    {
        $data = $request->validate([
            'loja_id' => 'required|exists:lojas,id',
            'nome' => 'required|string|max:255',
            'marca' => 'nullable|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'serial' => 'nullable|string|max:100',
            'capacidade_kg' => 'nullable|numeric|min:0',
            'tolerancia_kg' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $balanca->update($data);

        return redirect()->route('balancas.index')
            ->with('success', 'Balança atualizada com sucesso!');
    }

    public function destroy(Balanca $balanca)
    {
        $balanca->delete();

        return redirect()->route('balancas.index')
            ->with('success', 'Balança excluída com sucesso!');
    }
}