@extends('layouts.app')

@section('title', 'Configuração do GLPI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title mb-0"><i class="bi bi-plug me-2"></i>Configuração da API GLPI</h1>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-key me-2"></i>Credenciais OAuth</span>
                @if($configurado)
                    <span class="badge bg-success">Configurado</span>
                @else
                    <span class="badge bg-warning text-dark">Incompleto</span>
                @endif
            </div>
            <div class="card-body">
                <p class="text-muted small mb-4">
                    Dados de acesso OAuth2 do GLPI. Crie um cliente OAuth em
                    <strong>Configuração → OAuth Clients</strong> no GLPI com scope <code>api</code>.
                </p>

                <form action="{{ route('glpi.config.save') }}" method="POST" id="formGlpi">
                    @csrf
                    <div class="mb-3">
                        <label for="glpi_api_url" class="form-label">URL da API GLPI <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="glpi_api_url" name="glpi_api_url"
                               value="{{ old('glpi_api_url', $settings['glpi_api_url'] ?? '') }}"
                               placeholder="https://glpi.suaempresa.com.br/api.php/v2" required>
                        <div class="form-text">Endereço base da API, deve terminar com <code>/api.php/v2</code>.</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="glpi_client_id" class="form-label">OAuth Client ID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="glpi_client_id" name="glpi_client_id"
                                   value="{{ old('glpi_client_id', $settings['glpi_client_id'] ?? '') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="glpi_client_secret" class="form-label">OAuth Client Secret <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="glpi_client_secret" name="glpi_client_secret"
                                   placeholder="{{ isset($settings['glpi_client_secret']) && $settings['glpi_client_secret'] !== '' ? '•••••••• (já configurado — deixe vazio para manter)' : '' }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="glpi_username" class="form-label">Usuário do GLPI <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="glpi_username" name="glpi_username"
                                   value="{{ old('glpi_username', $settings['glpi_username'] ?? '') }}" required autocomplete="off">
                            <div class="form-text">Usuário da conta API (não precisa ser um funcionário).</div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="glpi_password" class="form-label">Senha <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="glpi_password" name="glpi_password"
                                   placeholder="{{ isset($settings['glpi_password']) && $settings['glpi_password'] !== '' ? '•••••••• (já configurado — deixe vazio para manter)' : '' }}" autocomplete="off">
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="fw-semibold mb-3"><i class="bi bi-diagram-3 me-2"></i>Chamados</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="glpi_entity_id" class="form-label">Entidade padrão (ID)</label>
                            <input type="number" class="form-control" id="glpi_entity_id" name="glpi_entity_id"
                                   value="{{ old('glpi_entity_id', $settings['glpi_entity_id'] ?? '') }}" placeholder="0">
                            <div class="form-text">
                                Usada nas lojas que não tiverem entidade própria definida.
                                Cada loja pode ter uma entidade diferente no cadastro da loja.
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="glpi_category_id" class="form-label">Categoria do chamado (ID)</label>
                            <input type="number" class="form-control" id="glpi_category_id" name="glpi_category_id"
                                   value="{{ old('glpi_category_id', $settings['glpi_category_id'] ?? '') }}">
                            <div class="form-text">Categoria ITIL aplicada ao chamado (opcional).</div>
                        </div>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" role="switch" id="glpi_entity_recursive" name="glpi_entity_recursive"
                               value="1" {{ old('glpi_entity_recursive', $settings['glpi_entity_recursive'] ?? '0') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="glpi_entity_recursive">Entidades recursivas</label>
                        <div class="form-text">Permite abrir chamados nas sub-entidades da entidade escolhida.</div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-primary btn-app px-4">
                            <i class="bi bi-save me-2"></i>Salvar configuração
                        </button>
                        <button type="button" class="btn btn-outline-success btn-app px-4" id="btnTestar" onclick="testarConexao()">
                            <i class="bi bi-patch-check me-2"></i>Testar conexão
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-diagram-2 me-2"></i>Entidade por loja</div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small text-muted">Cadastre a entidade GLPI responsável por cada balança.</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnCarregarEntidades"
                            onclick="buscarEntidades()" data-bs-toggle="collapse" data-bs-target="#painelEntidades">
                        <i class="bi bi-arrow-down-up me-1"></i>Buscar entidades
                    </button>
                </div>

                <div class="collapse" id="painelEntidades">
                    <div class="alert alert-light border small mt-2" id="entidadesBox">Clique em "Buscar entidades" para listar as entidades do GLPI.</div>
                </div>

                <ul class="list-group list-group-flush mt-2">
                    @forelse($lojas as $loja)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div>
                            <i class="bi bi-shop me-2 text-muted"></i>{{ $loja->nome }}
                            @if($loja->glpi_entity_id)
                                <span class="badge bg-light text-dark ms-1">Entidade #{{ $loja->glpi_entity_id }}</span>
                            @endif
                        </div>
                        <a href="{{ route('lojas.edit', $loja) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </li>
                    @empty
                    <li class="list-group-item text-muted px-0">Nenhuma loja cadastrada.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="bi bi-info-circle me-2"></i>Como funciona</div>
            <div class="card-body small text-muted">
                <ol class="mb-0 ps-3">
                    <li class="mb-2">Configure as credenciais OAuth e teste a conexão.</li>
                    <li class="mb-2">No cadastro de cada <strong>loja</strong>, informe a <strong>entidade GLPI</strong> responsável por suas balanças.</li>
                    <li>Quando uma conferência sair da tolerância e o colaborador confirmar, o chamado será aberto na <strong>entidade específica da loja</strong> (ou na padrão, se a loja não tiver).</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Modal resultado do teste -->
<div class="modal fade" id="modalTeste" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-4 text-center">
                <div id="testeIcon" class="mb-2" style="font-size:3rem;"></div>
                <h5 class="fw-bold" id="testeTitulo">—</h5>
                <p class="text-muted small mb-0" id="testeMsg">—</p>
                <div id="testeEntidades" class="mt-2 text-start small"></div>
                <div class="d-grid mt-3">
                    <button type="button" class="btn btn-primary btn-app" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function testeToken() {
        return document.querySelector('meta[name="csrf-token"]').content;
    }

    async function testarConexao() {
        const btn = document.getElementById('btnTestar');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Testando...';

        const form = document.getElementById('formGlpi');
        const dados = new FormData(form);
        dados.set('_token', testeToken());

        // usa os valores digitados no formulário (mesmo que não salvos ainda)
        const campos = ['glpi_api_url', 'glpi_client_id', 'glpi_client_secret', 'glpi_username', 'glpi_password',
            'glpi_entity_id', 'glpi_entity_recursive', 'glpi_category_id'];
        const url = new URL('{{ route("glpi.config.test") }}', window.location.origin);
        campos.forEach(c => { if (dados.get(c)) url.searchParams.set(c, dados.get(c)); });

        try {
            const resp = await fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, body: dados, credentials: 'same-origin' });
            const data = await resp.json();
            mostrarTeste(data);
        } catch (e) {
            mostrarTeste({ success: false, message: 'Erro de comunicação. Verifique a URL da API.' });
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-patch-check me-2"></i>Testar conexão';
        }
    }

    function mostrarTeste(data) {
        const icone = document.getElementById('testeIcon');
        const titulo = document.getElementById('testeTitulo');
        const msg = document.getElementById('testeMsg');
        const box = document.getElementById('testeEntidades');

        icone.className = '';
        if (data.success) {
            icone.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i>';
            titulo.textContent = 'Conexão OK';
            titulo.className = 'fw-bold text-success mb-1';
        } else {
            icone.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i>';
            titulo.textContent = 'Falha na conexão';
            titulo.className = 'fw-bold text-danger mb-1';
        }
        msg.textContent = data.message || '';

        if (data.entities && data.entities.length) {
            box.innerHTML = '<hr><div class="fw-semibold mb-1">Entidades encontradas:</div>' +
                data.entities.map(e => '<div><span class="badge bg-light text-dark me-1">#' + e.id + '</span>' + (e.complete_name || e.name) + '</div>').join('');
        } else {
            box.innerHTML = '';
        }

        new bootstrap.Modal(document.getElementById('modalTeste')).show();
    }

    async function buscarEntidades() {
        const box = document.getElementById('entidadesBox');
        box.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Buscando...';

        try {
            const resp = await fetch('{{ route("glpi.config.entities") }}', { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, credentials: 'same-origin' });
            const data = await resp.json();

            if (data.success && data.entities.length) {
                box.className = 'alert alert-light border small mt-2';
                box.innerHTML = '<div class="fw-semibold mb-1">' + data.entities.length + ' entidade(s):</div>' +
                    data.entities.map(e => '<div class="d-flex justify-content-between ms-1"><span>' + (e.complete_name || e.name) + '</span><span class="badge bg-light text-dark">#' + e.id + '</span></div>').join('') +
                    '<div class="text-muted mt-1">Anote o ID e informe no cadastro da loja.</div>';
            } else {
                box.className = 'alert alert-warning border small mt-2';
                box.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>' + (data.error || 'Nenhuma entidade encontrada. Configure e teste a conexão antes.');
            }
        } catch (e) {
            box.className = 'alert alert-danger border small mt-2';
            box.innerHTML = 'Erro ao buscar as entidades.';
        }
    }
</script>
@endpush