@extends('layouts.app')

@section('title', 'Coleta de Peso')

@push('styles')
<style>
    .coleta-card {
        border: 0;
        border-radius: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
    }
    .step-indicator {
        display: flex;
        align-items: center;
        gap: .5rem;
        margin-bottom: 1.25rem;
    }
    .step-dot {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .8rem;
        font-weight: 600;
        background: #e9ecef;
        color: #6c757d;
    }
    .step-dot.active {
        background: #155f4a;
        color: #fff;
    }
    .step-dot.done {
        background: #198754;
        color: #fff;
    }
    .step-line {
        flex: 1;
        height: 2px;
        background: #e9ecef;
    }
    .step-line.done {
        background: #198754;
    }
    .resultado-verde {
        background: #d1e7dd;
        color: #0f5132;
    }
    .resultado-vermelho {
        background: #f8d7da;
        color: #842029;
    }
    .peso-input {
        font-size: 2rem;
        font-weight: 700;
        text-align: center;
        height: auto;
        padding: .75rem .5rem;
    }
    .display-diferenca {
        font-size: 2.5rem;
        font-weight: 700;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title mb-0"><i class="bi bi-clipboard2-check me-2"></i>Coleta de Peso</h1>
    @if(auth()->user()->role !== 'coletor')
        <a href="{{ route('coleta.historico') }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-clock-history me-1"></i> Histórico
        </a>
    @endif
</div>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card coleta-card">
            <div class="card-body p-4">

                <!-- Step indicator -->
                <div class="step-indicator">
                    <div class="step-dot active" id="stepDot1">1</div>
                    <div class="step-line" id="stepLine1"></div>
                    <div class="step-dot" id="stepDot2">2</div>
                    <div class="step-line" id="stepLine2"></div>
                    <div class="step-dot" id="stepDot3">3</div>
                </div>

                <!-- Step 1: Selecionar loja e balança -->
                <div id="step1">
                    <h5 class="fw-semibold mb-3"><i class="bi bi-shop me-2"></i>Loja e Balança</h5>
                    <form id="formStep1" onsubmit="return avancarStep1(event)">
                        <div class="mb-3">
                            <label for="loja_id" class="form-label">Loja <span class="text-danger">*</span></label>
                            <select class="form-select" id="loja_id" required>
                                <option value="">Selecione a loja...</option>
                                @foreach($lojas as $loja)
                                    <option value="{{ $loja->id }}">{{ $loja->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="balanca_id" class="form-label">Balança <span class="text-danger">*</span></label>
                            <select class="form-select" id="balanca_id" required disabled>
                                <option value="">Selecione a loja primeiro...</option>
                            </select>
                        </div>
                        <div id="infoBalanca" class="alert alert-light border small d-none">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Tolerância:</span>
                                <strong><span id="toleranciaLabel">—</span> kg</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Capacidade:</span>
                                <strong><span id="capacidadeLabel">—</span> kg</strong>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-app mt-2" id="btnStep1">
                            <i class="bi bi-arrow-right me-2"></i>Continuar
                        </button>
                    </form>
                </div>

                <!-- Step 2: Informar pesos -->
                <div id="step2" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-semibold mb-0"><i class="bi bi-balance-scale me-2"></i>Pesos</h5>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="voltarStep2()">
                            <i class="bi bi-arrow-left"></i>
                        </button>
                    </div>
                    <div class="alert alert-light border small mb-3 py-2">
                        <strong>{{ $lojas->isEmpty() ? '' : '' }}<span id="balancaNomeLabel">—</span></strong>
                        <span class="text-muted">· Tolerância: <span id="toleranciaLabel2">—</span> kg</span>
                    </div>
                    <div class="mb-3">
                        <label for="descricao_item" class="form-label">Descrição do item</label>
                        <input type="text" class="form-control" id="descricao_item" placeholder="Ex: Caixa de bananas, cesto de pães...">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="peso_esperado" class="form-label">Peso esperado (kg) <span class="text-danger">*</span></label>
                            <input type="text" inputmode="decimal" pattern="[0-9]*[.,]?[0-9]*" autocomplete="off" class="form-control peso-input" id="peso_esperado" placeholder="0,000" required>
                        </div>
                        <div class="col-6">
                            <label for="peso_real" class="form-label">Peso real (kg) <span class="text-danger">*</span></label>
                            <input type="text" inputmode="decimal" pattern="[0-9]*[.,]?[0-9]*" autocomplete="off" class="form-control peso-input" id="peso_real" placeholder="0,000" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="observacao" class="form-label">Observação</label>
                        <textarea class="form-control" id="observacao" rows="2" placeholder="Opcional"></textarea>
                    </div>
                    <button type="button" class="btn btn-primary w-100 btn-app" onclick="calcularPeso(event)">
                        <i class="bi bi-calculator me-2"></i>Calcular conferência
                    </button>
                </div>

                <!-- Step 3: Resultado -->
                <div id="step3" class="d-none">
                    <div id="resultadoCard"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal confirmação chamado GLPI -->
<div class="modal fade" id="modalGlpi" tabindex="-1" aria-labelledby="modalGlpiLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalGlpiLabel"><i class="bi bi-headset me-2 text-danger"></i>Diferença detectada</h5>
            </div>
            <div class="modal-body">
                <p class="mb-1">A diferença de <strong class="text-danger"><span id="modalDiferenca">—</span> kg</strong> está <strong>fora da tolerância</strong> de <span id="modalTolerancia">—</span> kg.</p>
                <p class="mb-3">Deseja abrir um chamado automático no GLPI?</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary btn-app" id="btnAbrirChamado">
                        <i class="bi bi-headset me-2"></i>Sim, abrir chamado no GLPI
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="btnSemChamado">
                        Não, apenas registrar a conferência
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal resultado final -->
<div class="modal fade" id="modalResultado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-4 text-center" id="modalResultadoBody"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const rotaBalancas = '{{ auth()->user()->role === "coletor" ? "/coletor/balancas" : "/coleta/balancas" }}';
    const rotaPreview = '{{ auth()->user()->role === "coletor" ? "/coletor/coleta/preview" : "/coleta/preview" }}';
    const rotaStore = '{{ auth()->user()->role === "coletor" ? "/coletor/coleta" : "/coleta" }}';

    let dadosConferencia = null;
    let balancaAtual = null;

    function getSelectedLoja() {
        return document.getElementById('loja_id').value;
    }

    document.getElementById('loja_id').addEventListener('change', async function () {
        const lojaId = this.value;
        const balancaSelect = document.getElementById('balanca_id');
        const infoBalanca = document.getElementById('infoBalanca');

        balancaSelect.innerHTML = '<option value="">Carregando balanças...</option>';
        balancaSelect.disabled = true;
        infoBalanca.classList.add('d-none');

        if (!lojaId) {
            balancaSelect.innerHTML = '<option value="">Selecione a loja primeiro...</option>';
            return;
        }

        try {
            const resp = await fetch(rotaBalancas + '?loja_id=' + lojaId);
            const balancas = await resp.json();

            balancaSelect.innerHTML = '<option value="">Selecione a balança...</option>';
            balancas.forEach(b => {
                const opt = document.createElement('option');
                opt.value = b.id;
                opt.textContent = b.nome + (b.marca ? ' (' + b.marca + ')' : '');
                opt.dataset.tolerancia = b.tolerancia_kg;
                opt.dataset.capacidade = b.capacidade_kg;
                balancaSelect.appendChild(opt);
            });
            balancaSelect.disabled = false;
        } catch (e) {
            balancaSelect.innerHTML = '<option value="">Erro ao carregar balanças</option>';
        }
    });

    document.getElementById('balanca_id').addEventListener('change', function () {
        const infoBalanca = document.getElementById('infoBalanca');
        const selected = this.selectedOptions[0];

        if (!selected || !selected.value) {
            infoBalanca.classList.add('d-none');
            return;
        }

        document.getElementById('toleranciaLabel').textContent = formatNum(parseFloat(selected.dataset.tolerancia));
        document.getElementById('capacidadeLabel').textContent = formatNum(parseFloat(selected.dataset.capacidade));
        infoBalanca.classList.remove('d-none');
    });

    function avancarStep1(e) {
        e.preventDefault();
        const lojaId = getSelectedLoja();
        const balancaId = document.getElementById('balanca_id').value;

        if (!lojaId || !balancaId) {
            alert('Selecione a loja e a balança.');
            return false;
        }

        const balancaOption = document.getElementById('balanca_id').selectedOptions[0];

        balancaAtual = {
            id: balancaId,
            loja_id: lojaId,
            nome: balancaOption.textContent,
            tolerancia_kg: parseFloat(balancaOption.dataset.tolerancia)
        };

        document.getElementById('balancaNomeLabel').textContent = balancaAtual.nome;
        document.getElementById('toleranciaLabel2').textContent = formatNum(balancaAtual.tolerancia_kg);

        document.getElementById('stepDot1').textContent = '✓';
        document.getElementById('stepDot1').classList.add('done');
        document.getElementById('stepDot1').classList.remove('active');
        document.getElementById('stepLine1').classList.add('done');
        document.getElementById('stepDot2').classList.add('active');

        document.getElementById('step1').classList.add('d-none');
        document.getElementById('step2').classList.remove('d-none');

        return false;
    }

    function voltarStep2() {
        document.getElementById('stepDot2').classList.remove('active');
        document.getElementById('stepDot1').classList.add('active');
        document.getElementById('stepDot1').classList.remove('done');
        document.getElementById('stepDot1').textContent = '1';
        document.getElementById('stepLine1').classList.remove('done');

        document.getElementById('step2').classList.add('d-none');
        document.getElementById('step1').classList.remove('d-none');
    }

    async function reqJson(url, form) {
        const resp = await fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: form,
            credentials: 'same-origin'
        });

        if (resp.status === 419) {
            alert('Sessão expirada. Recarregando a página...');
            location.reload();
            throw new Error('session expired');
        }

        if (!resp.ok) {
            let msg = 'Erro ' + resp.status + '. Tente novamente.';
            try {
                const err = await resp.json();
                if (err.message) msg = err.message;
            } catch (e) {}
            throw new Error(msg);
        }

        return resp.json();
    }

    async function calcularPeso(e) {
        e.preventDefault();

        const pesoEsperado = normalizarNumero(document.getElementById('peso_esperado').value);
        const pesoReal = normalizarNumero(document.getElementById('peso_real').value);

        if (!pesoEsperado || !pesoReal) {
            alert('Informe o peso esperado e o peso real.');
            return;
        }

        if (parseFloat(pesoEsperado) < 0 || parseFloat(pesoReal) < 0) {
            alert('Os pesos devem ser positivos.');
            return;
        }

        const form = new FormData();
        form.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        form.append('balanca_id', balancaAtual.id);
        form.append('peso_esperado', pesoEsperado);
        form.append('peso_real', pesoReal);

        try {
            const data = await reqJson(rotaPreview, form);

            dadosConferencia = {
                ...dadosConferencia,
                ...data,
                loja_id: balancaAtual.loja_id,
                balanca_id: balancaAtual.id,
                descricao_item: document.getElementById('descricao_item').value,
                observacao: document.getElementById('observacao').value,
            };

            mostrarResultado(data);
        } catch (err) {
            if (err.message !== 'session expired') {
                alert(err.message || 'Erro ao calcular. Verifique os dados.');
            }
        }
    }

    function mostrarResultado(data) {
        document.getElementById('stepDot2').textContent = '✓';
        document.getElementById('stepDot2').classList.add('done');
        document.getElementById('stepDot2').classList.remove('active');
        document.getElementById('stepLine2').classList.add('done');
        document.getElementById('stepDot3').classList.add('active');

        document.getElementById('step2').classList.add('d-none');
        document.getElementById('step3').classList.remove('d-none');

        let html = '';

        if (data.dentro_tolerancia) {
            html += `
                <div class="text-center p-4 resultado-verde rounded-3 mb-3">
                    <div class="display-diferenca">${formatNum(data.diferenca)}</div>
                    <div class="small">kg de diferença</div>
                </div>
                <div class="alert alert-success text-center alert-tolerancia">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>Conferência aprovada!</strong><br>
                    <span class="small">Diferença dentro da tolerância de ${formatNum(data.balanca.tolerancia_kg)} kg.</span>
                </div>
            `;
        } else {
            html += `
                <div class="text-center p-4 resultado-vermelho rounded-3 mb-3">
                    <div class="display-diferenca">${formatNum(data.diferenca)}</div>
                    <div class="small">kg de diferença</div>
                </div>
                <div class="alert alert-danger text-center alert-tolerancia">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Atenção! Diferença fora da tolerância!</strong><br>
                    <span class="small">Tolerância permitida: ${formatNum(data.balanca.tolerancia_kg)} kg.</span>
                </div>
            `;
        }

        html += `
            <div class="d-flex justify-content-between mb-2 small">
                <span class="text-muted">Peso esperado:</span>
                <strong>${formatNum(data.peso_esperado)} kg</strong>
            </div>
            <div class="d-flex justify-content-between mb-2 small">
                <span class="text-muted">Peso real:</span>
                <strong>${formatNum(data.peso_real)} kg</strong>
            </div>
            <div class="d-flex justify-content-between mb-3 small">
                <span class="text-muted">Tolerância da balança:</span>
                <strong>${formatNum(data.balanca.tolerancia_kg)} kg</strong>
            </div>
        `;

        if (data.dentro_tolerancia) {
            html += `
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-success btn-app" onclick="salvarConferencia(0)">
                        <i class="bi bi-save me-2"></i>Salvar conferência
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="novaColeta()">
                        <i class="bi bi-plus-circle me-2"></i>Nova coleta
                    </button>
                </div>
            `;
        } else {
            document.getElementById('modalDiferenca').textContent = formatNum(data.diferenca);
            document.getElementById('modalTolerancia').textContent = formatNum(data.balanca.tolerancia_kg);
            const modalGlpi = new bootstrap.Modal(document.getElementById('modalGlpi'));
            modalGlpi.show();
        }

        document.getElementById('resultadoCard').innerHTML = html;
    }

    async function salvarConferencia(abrirChamado) {
        const btn = abrirChamado ? document.getElementById('btnAbrirChamado') : null;
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Abrindo chamado...';
        }

        const form = new FormData();
        form.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        form.append('loja_id', dadosConferencia.loja_id);
        form.append('balanca_id', dadosConferencia.balanca_id);
        form.append('peso_esperado', dadosConferencia.peso_esperado);
        form.append('peso_real', dadosConferencia.peso_real);
        form.append('descricao_item', dadosConferencia.descricao_item || '');
        form.append('observacao', dadosConferencia.observacao || '');
        form.append('abrir_chamado_glpi', abrirChamado ? '1' : '0');

        try {
            const data = await reqJson(rotaStore, form);

            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalGlpi')).hide();
                mostrarResultadoFinal(data);
            } else {
                alert(data.message || 'Erro ao salvar a conferência.');
            }
        } catch (err) {
            if (err.message !== 'session expired') {
                alert(err.message || 'Erro ao salvar. Tente novamente.');
            }
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-headset me-2"></i>Sim, abrir chamado no GLPI';
            }
        }
    }

    function mostrarResultadoFinal(data) {
        let html = '';

        if (data.dentro_tolerancia) {
            html = `
                <div class="text-success mb-2" style="font-size: 3rem;"><i class="bi bi-check-circle-fill"></i></div>
                <h5 class="fw-bold mb-1">Conferência aprovada!</h5>
                <p class="text-muted small mb-1">Diferença de ${formatNum(data.diferenca)} kg registrada dentro da tolerância.</p>
                <p class="text-muted small mb-3">Registrado com sucesso.</p>
            `;
        } else {
            const ticketHtml = data.ticket && data.ticket.success
                ? '<div class="alert alert-success small py-2"><i class="bi bi-check-circle me-2"></i>Chamado GLPI aberto com sucesso!' + (data.ticket.ticket_id ? ' (#' + data.ticket.ticket_id + ')' : '') + '</div>'
                : '<div class="alert alert-warning small py-2"><i class="bi bi-exclamation-triangle me-2"></i>Conferência registrada sem chamado GLPI.</div>';

            html = `
                <div class="text-danger mb-2" style="font-size: 3rem;"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <h5 class="fw-bold mb-1">Conferência reprovada!</h5>
                <p class="text-muted small mb-1">Diferença de ${formatNum(data.diferenca)} kg fora da tolerância.</p>
                ${ticketHtml}
            `;
        }

        html += `
            <hr>
            <div class="d-grid">
                <button type="button" class="btn btn-primary btn-app" onclick="novaColeta()">
                    <i class="bi bi-plus-circle me-2"></i>Nova coleta
                </button>
            </div>
        `;

        document.getElementById('modalResultadoBody').innerHTML = html;
        const modalResultado = new bootstrap.Modal(document.getElementById('modalResultado'));
        modalResultado.show();
    }

    document.getElementById('btnAbrirChamado').addEventListener('click', function () {
        if (document.querySelector('#modalGlpi').classList.contains('show')) {
            salvarConferencia(1);
        }
    });

    document.getElementById('btnSemChamado').addEventListener('click', function () {
        bootstrap.Modal.getInstance(document.getElementById('modalGlpi')).hide();
        salvarConferencia(0);
    });

    function novaColeta() {
        location.reload();
    }

    function formatNum(v) {
        const n = parseFloat(v);
        if (isNaN(n)) return '0,000';
        return n.toLocaleString('pt-BR', { minimumFractionDigits: 3, maximumFractionDigits: 3 });
    }

    function normalizarNumero(v) {
        if (v == null) return '';
        let s = String(v).trim().replace(/\s+/g, '');
        if (s === '') return '';
        if (s.includes(',') && s.includes('.')) {
            s = s.replace(/\./g, '').replace(',', '.');
        } else {
            s = s.replace(',', '.');
        }
        return s;
    }
</script>
@endpush