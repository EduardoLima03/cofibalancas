<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GlpiService
{
    private ?string $baseUrl = null;
    private ?string $accessToken = null;

    public function baseUrl(): ?string
    {
        if ($this->baseUrl === null) {
            $this->baseUrl = rtrim((string) ($this->setting('glpi_api_url') ?: ''), '/');
        }

        return $this->baseUrl ?: null;
    }

    public function clientId(): string
    {
        return (string) ($this->setting('glpi_client_id') ?: '');
    }

    public function clientSecret(): string
    {
        return (string) ($this->setting('glpi_client_secret') ?: '');
    }

    public function username(): string
    {
        return (string) ($this->setting('glpi_username') ?: '');
    }

    public function password(): string
    {
        return (string) ($this->setting('glpi_password') ?: '');
    }

    public function defaultEntityId(): ?int
    {
        return $this->setting('glpi_entity_id') !== null
            ? (int) $this->setting('glpi_entity_id')
            : null;
    }

    public function isRecursive(): bool
    {
        return (bool) $this->setting('glpi_entity_recursive', false);
    }

    public function categoryId(): ?int
    {
        return $this->setting('glpi_category_id') ? (int) $this->setting('glpi_category_id') : null;
    }

    public function isConfigured(): bool
    {
        return $this->baseUrl() !== null
            && $this->clientId() !== ''
            && $this->clientSecret() !== ''
            && $this->username() !== ''
            && $this->password() !== '';
    }

    public function authenticate(): array
    {
        if ($this->accessToken) {
            return ['success' => true, 'token' => $this->accessToken];
        }

        if (! $this->baseUrl()) {
            return ['success' => false, 'error' => 'URL da API GLPI não configurada.'];
        }

        try {
            $response = Http::asForm()->post($this->baseUrl() . '/token', [
                'grant_type' => 'password',
                'client_id' => $this->clientId(),
                'client_secret' => $this->clientSecret(),
                'username' => $this->username(),
                'password' => $this->password(),
                'scope' => 'api',
            ]);

            if ($response->successful() && isset($response->json()['access_token'])) {
                $this->accessToken = $response->json()['access_token'];

                return ['success' => true, 'token' => $this->accessToken];
            }

            $message = $response->json()['error_description']
                ?? $response->json()['error']
                ?? $response->status();

            Log::error('GLPI authentication failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['success' => false, 'error' => (string) $message, 'status' => $response->status()];
        } catch (\Throwable $e) {
            Log::error('GLPI authentication error: ' . $e->getMessage());

            return ['success' => false, 'error' => 'Erro de comunicação com o GLPI: ' . $e->getMessage()];
        }
    }

    public function openTicket(array $payload, ?int $entityId = null, ?bool $recursive = null): array
    {
        $auth = $this->authenticate();

        if (! $auth['success']) {
            return $auth;
        }

        $entity = $entityId ?? $this->defaultEntityId();

        if ($recursive === null) {
            $recursive = $this->isRecursive();
        }

        $payload['category'] = $payload['category'] ?? $this->categoryId();

        try {
            $request = Http::withToken($auth['token'])
                ->withHeaders(['Accept' => 'application/json']);

            if ($entity !== null) {
                $request->withHeaders(['GLPI-Entity' => (string) $entity]);
            }

            if ($recursive) {
                $request->withHeaders(['GLPI-Entity-Recursive' => 'true']);
            }

            $response = $request->post($this->baseUrl() . '/Assistance/Ticket', $payload);

            $body = $response->json();

            if ($response->created() || $response->successful()) {
                return [
                    'success' => true,
                    'ticket_id' => $body['id'] ?? $body['ticket_id'] ?? null,
                    'response' => $body,
                ];
            }

            Log::error('GLPI ticket creation failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => 'Erro ao criar chamado: ' . ($body['message'] ?? $body['error'] ?? $response->status()),
                'response' => $body,
            ];
        } catch (\Throwable $e) {
            Log::error('GLPI ticket error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => 'Erro de comunicação com o GLPI: ' . $e->getMessage(),
            ];
        }
    }

    public function getEntities(?int $limit = 100): array
    {
        $auth = $this->authenticate();

        if (! $auth['success']) {
            return ['success' => false, 'error' => $auth['error']];
        }

        try {
            $response = Http::withToken($auth['token'])
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Range' => 'items=0-' . ($limit - 1),
                ])
                ->get($this->baseUrl() . '/Entity');

            if ($response->successful()) {
                $entities = collect($response->json())
                    ->map(fn ($e) => [
                        'id' => $e['id'],
                        'name' => $e['name'] ?? 'Entidade #' . $e['id'],
                        'complete_name' => $e['completename'] ?? ($e['complete_name'] ?? null),
                    ])
                    ->values()
                    ->all();

                return ['success' => true, 'entities' => $entities];
            }

            return [
                'success' => false,
                'error' => 'Erro ao buscar entidades: ' . ($response->json()['message'] ?? $response->status()),
            ];
        } catch (\Throwable $e) {
            Log::error('GLPI entities error: ' . $e->getMessage());

            return ['success' => false, 'error' => 'Erro de comunicação com o GLPI: ' . $e->getMessage()];
        }
    }

    public function searchCategories(string $name = ''): array
    {
        $auth = $this->authenticate();

        if (! $auth['success']) {
            return [];
        }

        try {
            $response = Http::withToken($auth['token'])
                ->withHeaders(['Accept' => 'application/json'])
                ->get($this->baseUrl() . '/Dropdowns/ITILCategory', [
                    'filter' => 'name=ilike=' . $name,
                    'limit' => 20,
                ]);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('GLPI category search error: ' . $e->getMessage());

            return [];
        }
    }

    private function setting(string $key, $default = null)
    {
        $dbValue = Setting::get($key, null);

        if ($dbValue !== null) {
            return $dbValue;
        }

        $configKey = str_replace('glpi_', '', $key);

        return config('services.glpi.' . $configKey, $default);
    }
}