<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conferencia extends Model
{
    use HasFactory;

    protected $fillable = [
        'loja_id',
        'balanca_id',
        'user_id',
        'data_conferencia',
        'status',
        'peso_esperado',
        'peso_real',
        'diferenca',
        'tolerancia_usada',
    ];

    protected $casts = [
        'data_conferencia' => 'date',
        'peso_esperado' => 'float',
        'peso_real' => 'float',
        'diferenca' => 'float',
        'tolerancia_usada' => 'float',
    ];

    protected $dates = ['data_conferencia'];

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class);
    }

    public function balanca(): BelongsTo
    {
        return $this->belongsTo(Balanca::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function itens(): HasMany
    {
        return $this->hasMany(ConferenciaItem::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(TicketGlpi::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'aprovado' => 'Aprovado',
            'reprovado' => 'Reprovado',
            'aprovado_com_ressalva' => 'Aprovado com Ressalva',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'aprovado' => 'success',
            'reprovado' => 'danger',
            'aprovado_com_ressalva' => 'warning',
            default => 'secondary',
        };
    }
}