<?php

namespace App\Http\Controllers;

use App\Models\Loja;
use App\Models\Setting;
use App\Services\GlpiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GlpiConfigController extends Controller
{
    public function index()
    {
        $settings = Setting::allSettings();
        $glpi = app(GlpiService::class);
        $lojas = Loja::orderBy('nome')->get();

        return view('configuracoes.glpi', [
            'settings' => $settings,
            'configurado' => $glpi->isConfigured(),
            'glpi' => $glpi,
            'lojas' => $lojas,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'glpi_api_url' => 'nullable|string|max:255',
            'glpi_client_id' => 'nullable|string|max:255',
            'glpi_client_secret' => 'nullable|string|max:500',
            'glpi_username' => 'nullable|string|max:255',
            'glpi_password' => 'nullable|string|max:500',
            'glpi_entity_id' => 'nullable|integer',
            'glpi_entity_recursive' => 'nullable|boolean',
            'glpi_category_id' => 'nullable|integer',
        ]);

        foreach ($data as $key => $value) {
            if ($key === 'glpi_client_secret' && $value === '') {
                continue;
            }

            if ($key === 'glpi_password' && $value === '') {
                continue;
            }

            Setting::set($key, $value === null ? '' : $value);
        }

        Setting::set('glpi_entity_recursive', $request->boolean('glpi_entity_recursive') ? '1' : '0');

        return redirect()->route('glpi.config')
            ->with('success', 'Configuração do GLPI salva com sucesso!');
    }

    public function testar(Request $request): JsonResponse
    {
        $glpi = app(GlpiService::class);

        if (! $glpi->isConfigured()) {
            $faltantes = [];

            if (! $glpi->baseUrl()) {
                $faltantes[] = 'URL da API';
            }

            if (! $glpi->clientId()) {
                $faltantes[] = 'Client ID';
            }

            if (! $glpi->clientSecret()) {
                $faltantes[] = 'Client Secret';
            }

            if (! $glpi->username()) {
                $faltantes[] = 'Usuário';
            }

            if (! $glpi->password()) {
                $faltantes[] = 'Senha';
            }

            return response()->json([
                'success' => false,
                'message' => 'Preencha os dados obrigatórios: ' . implode(', ', $faltantes) . '.',
            ]);
        }

        $auth = $glpi->authenticate();

        if (! $auth['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Falha na autenticação: ' . $auth['error'],
            ]);
        }

        $entidades = $glpi->getEntities();

        if ($entidades['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Conexão OK. Autenticado com sucesso (' . count($entidades['entities']) . ' entidades disponíveis).',
                'entities' => $entidades['entities'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Conexão OK, mas não foi possível listar as entidades: ' . $entidades['error'],
            'entities' => [],
        ]);
    }

    public function entidades(): JsonResponse
    {
        $glpi = app(GlpiService::class);

        $entities = $glpi->getEntities();

        return response()->json($entities);
    }
}