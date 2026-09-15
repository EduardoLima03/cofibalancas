<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function lojas(): BelongsToMany
    {
        return $this->belongsToMany(Loja::class)->withTimestamps();
    }

    public function conferencias(): HasMany
    {
        return $this->hasMany(Conferencia::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isGerente(): bool
    {
        return $this->role === 'gerente';
    }

    public function isColetor(): bool
    {
        return $this->role === 'coletor';
    }

    public function canManageAll(): bool
    {
        return $this->isAdmin();
    }

    public function podeAcessarLoja(int $lojaId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->lojas()->where('lojas.id', $lojaId)->exists();
    }

    public function lojasPermitidasIds(): Collection
    {
        if ($this->isAdmin()) {
            return Loja::where('is_active', true)->pluck('id');
        }

        return $this->lojas()->pluck('lojas.id');
    }
}