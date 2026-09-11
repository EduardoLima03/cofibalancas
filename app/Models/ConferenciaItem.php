<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConferenciaItem extends Model
{
    use HasFactory;

    protected $table = 'conferencia_itens';

    protected $fillable = [
        'conferencia_id',
        'descricao_item',
        'peso_esperado',
        'peso_real',
        'diferenca',
        'dentro_tolerancia',
        'observacao',
    ];

    protected $casts = [
        'peso_esperado' => 'float',
        'peso_real' => 'float',
        'diferenca' => 'float',
        'dentro_tolerancia' => 'boolean',
    ];

    public function conferencia(): BelongsTo
    {
        return $this->belongsTo(Conferencia::class);
    }
}