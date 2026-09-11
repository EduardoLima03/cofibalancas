<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public const ENCRYPTED_KEYS = [
        'glpi_client_secret',
        'glpi_password',
    ];

    public static function get(string $key, $default = null)
    {
        $row = static::where('key', $key)->first();

        if (! $row) {
            return $default;
        }

        $value = $row->value;

        if (in_array($key, self::ENCRYPTED_KEYS, true) && $value !== null && $value !== '') {
            try {
                return Crypt::decryptString($value);
            } catch (\Throwable $e) {
                return null;
            }
        }

        return $value;
    }

    public static function set(string $key, $value): void
    {
        $value = (string) $value;

        if (in_array($key, self::ENCRYPTED_KEYS, true) && $value !== '') {
            $value = Crypt::encryptString($value);
        }

        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function forget(string $key): void
    {
        static::where('key', $key)->delete();
    }

    public static function allSettings(): array
    {
        $result = [];

        foreach (static::all() as $row) {
            $value = $row->value;

            if (in_array($row->key, self::ENCRYPTED_KEYS, true) && $value !== null && $value !== '') {
                try {
                    $value = Crypt::decryptString($value);
                } catch (\Throwable $e) {
                    $value = null;
                }
            }

            $result[$row->key] = $value;
        }

        return $result;
    }
}