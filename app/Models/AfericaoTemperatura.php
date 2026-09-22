<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AfericaoTemperatura extends Model
{
    use HasFactory;

    protected $table = 'afericoes_temperatura';

    protected $fillable = [
        'loja_id',
        'equipamento_id',
        'user_id',
        'data_afericao',
        'status',
        'temperatura_lida',
        'temp_min_usada',
        'temp_max_usada',
        'desvio',
        'dentro_tolerancia',
        'tolerancia_usada',
        'observacao',
    ];

    protected $casts = [
        'data_afericao' => 'datetime',
        'temperatura_lida' => 'float',
        'temp_min_usada' => 'float',
        'temp_max_usada' => 'float',
        'desvio' => 'float',
        'dentro_tolerancia' => 'boolean',
        'tolerancia_usada' => 'float',
    ];

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class);
    }

    public function equipamento(): BelongsTo
    {
        return $this->belongsTo(EquipamentoFrio::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'aprovado' => 'Aprovado',
            'reprovado' => 'Reprovado',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return $this->status === 'reprovado' ? 'danger' : 'success';
    }

    public function getFaixaLabelAttribute(): string
    {
        $min = $this->temp_min_usada !== null ? number_format($this->temp_min_usada, 1, ',', '.') . ' °C a ' : '≤ ';
        return $min . number_format($this->temp_max_usada ?? 0, 1, ',', '.') . ' °C';
    }
}