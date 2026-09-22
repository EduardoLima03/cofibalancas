<?php

namespace App\Http\Controllers;

use App\Models\EquipamentoFrio;
use App\Models\Loja;
use Illuminate\Http\Request;

class EquipamentoFrioController extends Controller
{
    protected const TIPOS = [
        'camara_congelada' => [
            'label' => 'Câmara congelada',
            'temp_min' => -25.00,
            'temp_max' => -18.00,
        ],
        'camara_refrigerada' => [
            'label' => 'Câmara refrigerada',
            'temp_min' => 0.00,
            'temp_max' => 5.00,
        ],
        'freezer_domestico' => [
            'label' => 'Freezer doméstico',
            'temp_min' => null,
            'temp_max' => -18.00,
        ],
        'balcao_refrigerado' => [
            'label' => 'Geladeira / balcão refrigerado',
            'temp_min' => 0.00,
            'temp_max' => 5.00,
        ],
        'freezer_supermercado' => [
            'label' => 'Freezer de supermercado',
            'temp_min' => null,
            'temp_max' => -18.00,
        ],
    ];

    protected function tipos(): array
    {
        return static::TIPOS;
    }

    public function index(Request $request)
    {
        $lojaId = $request->get('loja_id');

        $lojas = Loja::where('is_active', true)->orderBy('nome')->get();

        $equipamentos = EquipamentoFrio::with('loja')
            ->when($lojaId, fn ($q) => $q->where('loja_id', $lojaId))
            ->orderBy('nome')
            ->paginate(15)
            ->withQueryString();

        return view('equipamentos.index', compact('equipamentos', 'lojas', 'lojaId'));
    }

    public function create(Request $request)
    {
        $lojas = Loja::where('is_active', true)->orderBy('nome')->get();

        return view('equipamentos.create', [
            'lojas' => $lojas,
            'lojaSelecionada' => $request->get('loja_id'),
            'tipos' => $this->tipos(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'loja_id' => 'required|exists:lojas,id',
            'nome' => 'required|string|max:255',
            'tipo' => 'required|in:' . implode(',', array_keys(static::TIPOS)),
            'marca' => 'nullable|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'temp_min' => 'nullable|numeric',
            'temp_max' => 'required|numeric',
            'tolerancia_c' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($data['temp_min'] !== null && (float) $data['temp_min'] >= (float) $data['temp_max']) {
            return back()
                ->withInput()
                ->withErrors(['temp_min' => 'A temperatura mínima deve ser menor que a máxima.']);
        }

        $data['is_active'] = $request->boolean('is_active');

        EquipamentoFrio::create($data);

        return redirect()->route('equipamentos.index')
            ->with('success', 'Equipamento de frio cadastrado com sucesso!');
    }

    public function edit(EquipamentoFrio $equipamento)
    {
        $lojas = Loja::where('is_active', true)->orderBy('nome')->get();

        return view('equipamentos.edit', [
            'equipamento' => $equipamento,
            'lojas' => $lojas,
            'tipos' => $this->tipos(),
        ]);
    }

    public function update(Request $request, EquipamentoFrio $equipamento)
    {
        $data = $request->validate([
            'loja_id' => 'required|exists:lojas,id',
            'nome' => 'required|string|max:255',
            'tipo' => 'required|in:' . implode(',', array_keys(static::TIPOS)),
            'marca' => 'nullable|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'temp_min' => 'nullable|numeric',
            'temp_max' => 'required|numeric',
            'tolerancia_c' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($data['temp_min'] !== null && (float) $data['temp_min'] >= (float) $data['temp_max']) {
            return back()
                ->withInput()
                ->withErrors(['temp_min' => 'A temperatura mínima deve ser menor que a máxima.']);
        }

        $data['is_active'] = $request->boolean('is_active');

        $equipamento->update($data);

        return redirect()->route('equipamentos.index')
            ->with('success', 'Equipamento de frio atualizado com sucesso!');
    }

    public function destroy(EquipamentoFrio $equipamento)
    {
        $equipamento->delete();

        return redirect()->route('equipamentos.index')
            ->with('success', 'Equipamento de frio excluído com sucesso!');
    }
}