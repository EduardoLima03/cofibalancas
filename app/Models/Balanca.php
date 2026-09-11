<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Balanca extends Model
{
    use HasFactory;

    protected $fillable = [
        'loja_id',
        'nome',
        'marca',
        'modelo',
        'serial',
        'capacidade_kg',
        'tolerancia_kg',
        'is_active',
    ];

    protected $casts = [
        'capacidade_kg' => 'float',
        'tolerancia_kg' => 'float',
        'is_active' => 'boolean',
    ];

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class);
    }

    public function conferencias(): HasMany
    {
        return $this->hasMany(Conferencia::class);
    }
}