<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketGlpi extends Model
{
    use HasFactory;

    protected $table = 'tickets_glpi';

    protected $fillable = [
        'ticket_id',
        'conferencia_id',
        'conferencia_item_id',
        'titulo',
        'descricao',
        'status',
        'resposta_glpi',
        'diferenca',
    ];

    protected $casts = [
        'diferenca' => 'float',
    ];

    public function conferencia(): BelongsTo
    {
        return $this->belongsTo(Conferencia::class);
    }
}