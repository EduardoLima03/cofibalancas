<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipamentoFrio extends Model
{
    use HasFactory;

    protected $table = 'equipamentos_frio';

    protected $fillable = [
        'loja_id',
        'nome',
        'tipo',
        'marca',
        'modelo',
        'temp_min',
        'temp_max',
        'tolerancia_c',
        'is_active',
    ];

    protected $casts = [
        'temp_min' => 'float',
        'temp_max' => 'float',
        'tolerancia_c' => 'float',
        'is_active' => 'boolean',
    ];

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class);
    }

    public function afericoes(): HasMany
    {
        return $this->hasMany(AfericaoTemperatura::class);
    }

    public function getTipoLabelAttribute(): string
    {
        return match ($this->tipo) {
            'camara_congelada' => 'Câmara congelada',
            'camara_refrigerada' => 'Câmara refrigerada',
            'freezer_domestico' => 'Freezer doméstico',
            'balcao_refrigerado' => 'Geladeira / balcão refrigerado',
            'freezer_supermercado' => 'Freezer de supermercado',
            default => ucfirst($this->tipo),
        };
    }
}