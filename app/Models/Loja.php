<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loja extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'codigo',
        'cnpj',
        'glpi_entity_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'glpi_entity_id' => 'integer',
    ];

    public function balancas(): HasMany
    {
        return $this->hasMany(Balanca::class);
    }

    public function conferencias(): HasMany
    {
        return $this->hasMany(Conferencia::class);
    }

    public function colaboradores(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function balancasAtivas(): HasMany
    {
        return $this->balancas()->where('is_active', true);
    }
}