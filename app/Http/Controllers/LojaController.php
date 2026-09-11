<?php

namespace App\Http\Controllers;

use App\Models\Balanca;
use App\Models\Loja;
use Illuminate\Http\Request;

class LojaController extends Controller
{
    public function index()
    {
        $lojas = Loja::withCount('balancas', 'conferencias')
            ->orderBy('nome')
            ->paginate(15);

        return view('lojas.index', compact('lojas'));
    }

    public function create()
    {
        return view('lojas.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'codigo' => 'nullable|string|max:50|unique:lojas,codigo',
            'cnpj' => 'nullable|string|max:20',
            'glpi_entity_id' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        Loja::create($data);

        return redirect()->route('lojas.index')
            ->with('success', 'Loja cadastrada com sucesso!');
    }

    public function show(Loja $loja)
    {
        $loja->load(['balancas', 'conferencias' => fn ($q) => $q->latest()->limit(10)]);

        return view('lojas.show', compact('loja'));
    }

    public function edit(Loja $loja)
    {
        return view('lojas.edit', compact('loja'));
    }

    public function update(Request $request, Loja $loja)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'codigo' => 'nullable|string|max:50|unique:lojas,codigo,' . $loja->id,
            'cnpj' => 'nullable|string|max:20',
            'glpi_entity_id' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $loja->update($data);

        return redirect()->route('lojas.index')
            ->with('success', 'Loja atualizada com sucesso!');
    }

    public function destroy(Loja $loja)
    {
        $loja->delete();

        return redirect()->route('lojas.index')
            ->with('success', 'Loja excluída com sucesso!');
    }
}