@extends('layouts.app')

@section('title', 'Aferição de Temperatura')

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
    .temp-input {
        font-size: 2rem;
        font-weight: 700;
        text-align: center;
        height: auto;
        padding: .75rem .5rem;
    }
    .display-desvio {
        font-size: 2.5rem;
        font-weight: 700;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title mb-0"><i class="bi bi-thermometer-half me-2"></i>Aferição de Temperatura</h1>
    @if(auth()->user()->role !== 'coletor')
        <a href="{{ route('temperatura.historico') }}" class="btn btn-sm btn-outline-primary">
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

                <!-- Step 1: Selecionar loja e equipamento -->
                <div id="step1">
                    <h5 class="fw-semibold mb-3"><i class="bi bi-snow2 me-2"></i>Loja e Equipamento</h5>
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
                            <label for="equipamento_id" class="form-label">Equipamento <span class="text-danger">*</span></label>
                            <select class="form-select" id="equipamento_id" required disabled>
                                <option value="">Selecione a loja primeiro...</option>
                            </select>
                        </div>
                        <div id="infoEquipamento" class="alert alert-light border small d-none">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Tipo:</span>
                                <strong><span id="tipoLabel">—</span></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Faixa:</span>
                                <strong><span id="faixaLibel">—</span></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Tolerância:</span>
                                <strong><span id="toleranciaLabel">—</span> °C</strong>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-app mt-2" id="btnStep1">
                            <i class="bi bi-arrow-right me-2"></i>Continuar
                        </button>
                    </form>
                </div>

                <!-- Step 2: Informar temperatura -->
                <div id="step2" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-semibold mb-0"><i class="bi bi-thermometer-half me-2"></i>Temperatura</h5>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="voltarStep2()">
                            <i class="bi bi-arrow-left"></i>
                        </button>
                    </div>
                    <div class="alert alert-light border small mb-3 py-2">
                        <strong><span id="equipNomeLabel">—</span></strong>
                        <span class="text-muted">· Faixa: <span id="faixaLabel2">—</span> · Tolerância: <span id="toleranciaLabel2">—</span> °C</span>
                    </div>
                    <div class="mb-3">
                        <label for="temperatura_lida" class="form-label">Temperatura lida (°C) <span class="text-danger">*</span></label>
                        <input type="text" inputmode="decimal" pattern="[0-9-]*[.,]?[0-9]*" autocomplete="off" class="form-control temp-input" id="temperatura_lida" placeholder="-18,0" required>
                    </div>
                    <div class="mb-4">
                        <label for="observacao" class="form-label">Observação</label>
                        <textarea class="form-control" id="observacao" rows="2" placeholder="Opcional"></textarea>
                    </div>
                    <button type="button" class="btn btn-primary w-100 btn-app" onclick="calcularAfericao(event)">
                        <i class="bi bi-calculator me-2"></i>Calcular aferição
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
    const rotaEquipamentos = '{{ auth()->user()->role === "coletor" ? "/coletor/temperatura/equipamentos" : "/temperatura/equipamentos" }}';
    const rotaPreview = '{{ auth()->user()->role === "coletor" ? "/coletor/temperatura/preview" : "/temperatura/preview" }}';
    const rotaStore = '{{ auth()->user()->role === "coletor" ? "/coletor/temperatura" : "/temperatura" }}';

    let dadosAfericao = null;
    let equipamentoAtual = null;

    function getSelectedLoja() {
        return document.getElementById('loja_id').value;
    }

    document.getElementById('loja_id').addEventListener('change', async function () {
        const lojaId = this.value;
        const equipSelect = document.getElementById('equipamento_id');
        const infoEquip = document.getElementById('infoEquipamento');

        equipSelect.innerHTML = '<option value="">Carregando equipamentos...</option>';
        equipSelect.disabled = true;
        infoEquip.classList.add('d-none');

        if (!lojaId) {
            equipSelect.innerHTML = '<option value="">Selecione a loja primeiro...</option>';
            return;
        }

        try {
            const resp = await fetch(rotaEquipamentos + '?loja_id=' + lojaId);
            const equipamentos = await resp.json();

            equipSelect.innerHTML = '<option value="">Selecione o equipamento...</option>';
            equipamentos.forEach(e => {
                const opt = document.createElement('option');
                opt.value = e.id;
                opt.textContent = e.nome;
                opt.dataset.tipo = e.tipo;
                opt.dataset.tempMin = e.temp_min;
                opt.dataset.tempMax = e.temp_max;
                opt.dataset.tolerancia = e.tolerancia_c;
                equipSelect.appendChild(opt);
            });
            equipSelect.disabled = false;
        } catch (err) {
            equipSelect.innerHTML = '<option value="">Erro ao carregar equipamentos</option>';
        }
    });

    document.getElementById('equipamento_id').addEventListener('change', function () {
        const infoEquip = document.getElementById('infoEquipamento');
        const selected = this.selectedOptions[0];

        if (!selected || !selected.value) {
            infoEquip.classList.add('d-none');
            return;
        }

        document.getElementById('tipoLabel').textContent = textoTipo(selected.dataset.tipo);
        document.getElementById('faixaLibel').textContent = textoFaixa(selected.dataset.tempMin, selected.dataset.tempMax);
        document.getElementById('toleranciaLabel').textContent = formatNum(parseFloat(selected.dataset.tolerancia));
        infoEquip.classList.remove('d-none');
    });

    function avancarStep1(e) {
        e.preventDefault();
        const lojaId = getSelectedLoja();
        const equipamentoId = document.getElementById('equipamento_id').value;

        if (!lojaId || !equipamentoId) {
            alert('Selecione a loja e o equipamento.');
            return false;
        }

        const opt = document.getElementById('equipamento_id').selectedOptions[0];

        equipamentoAtual = {
            id: equipamentoId,
            loja_id: lojaId,
            nome: opt.textContent,
            tipo: opt.dataset.tipo,
            temp_min: opt.dataset.tempMin,
            temp_max: opt.dataset.tempMax,
            tolerancia_c: parseFloat(opt.dataset.tolerancia)
        };

        document.getElementById('equipNomeLabel').textContent = equipamentoAtual.nome;
        document.getElementById('faixaLabel2').textContent = textoFaixa(equipamentoAtual.temp_min, equipamentoAtual.temp_max);
        document.getElementById('toleranciaLabel2').textContent = formatNum(equipamentoAtual.tolerancia_c);

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
            } catch (ex) {}
            throw new Error(msg);
        }

        return resp.json();
    }

    async function calcularAfericao(e) {
        e.preventDefault();

        const temperatura = normalizarNumero(document.getElementById('temperatura_lida').value);

        if (temperatura === '') {
            alert('Informe a temperatura lida.');
            return;
        }

        const form = new FormData();
        form.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        form.append('equipamento_id', equipamentoAtual.id);
        form.append('temperatura_lida', temperatura);

        try {
            const data = await reqJson(rotaPreview, form);

            dadosAfericao = {
                ...dadosAfericao,
                ...data,
                loja_id: equipamentoAtual.loja_id,
                equipamento_id: equipamentoAtual.id,
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
                    <div class="display-desvio">${formatNum(data.desvio)}</div>
                    <div class="small">°C de desvio</div>
                </div>
                <div class="alert alert-success text-center alert-tolerancia">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>Aferição aprovada!</strong><br>
                    <span class="small">Temperatura dentro da tolerância de ${formatNum(data.equipamento.tolerancia_c)} °C.</span>
                </div>
            `;
        } else {
            html += `
                <div class="text-center p-4 resultado-vermelho rounded-3 mb-3">
                    <div class="display-desvio">${formatNum(data.desvio)}</div>
                    <div class="small">°C de desvio</div>
                </div>
                <div class="alert alert-danger text-center alert-tolerancia">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Atenção! Temperatura fora da tolerância!</strong><br>
                    <span class="small">Tolerância permitida: ${formatNum(data.equipamento.tolerancia_c)} °C.</span>
                </div>
            `;
        }

        html += `
            <div class="d-flex justify-content-between mb-2 small">
                <span class="text-muted">Temperatura lida:</span>
                <strong>${formatNum(data.temperatura_lida)} °C</strong>
            </div>
            <div class="d-flex justify-content-between mb-2 small">
                <span class="text-muted">Faixa de referência:</span>
                <strong>${textoFaixa(data.equipamento.temp_min, data.equipamento.temp_max)}</strong>
            </div>
            <div class="d-flex justify-content-between mb-3 small">
                <span class="text-muted">Tolerância do equipamento:</span>
                <strong>${formatNum(data.equipamento.tolerancia_c)} °C</strong>
            </div>
        `;

        html += `
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-success btn-app" onclick="salvarAfericao()">
                    <i class="bi bi-save me-2"></i>Salvar aferição
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="novaAfericao()">
                    <i class="bi bi-plus-circle me-2"></i>Nova aferição
                </button>
            </div>
        `;

        document.getElementById('resultadoCard').innerHTML = html;
    }

    async function salvarAfericao() {
        const form = new FormData();
        form.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        form.append('loja_id', dadosAfericao.loja_id);
        form.append('equipamento_id', dadosAfericao.equipamento_id);
        form.append('temperatura_lida', dadosAfericao.temperatura_lida);
        form.append('observacao', dadosAfericao.observacao || '');

        try {
            const data = await reqJson(rotaStore, form);

            if (data.success) {
                mostrarResultadoFinal(data);
            } else {
                alert(data.message || 'Erro ao salvar a aferição.');
            }
        } catch (err) {
            if (err.message !== 'session expired') {
                alert(err.message || 'Erro ao salvar. Tente novamente.');
            }
        }
    }

    function mostrarResultadoFinal(data) {
        let html = '';

        if (data.dentro_tolerancia) {
            html = `
                <div class="text-success mb-2" style="font-size: 3rem;"><i class="bi bi-check-circle-fill"></i></div>
                <h5 class="fw-bold mb-1">Aferição aprovada!</h5>
                <p class="text-muted small mb-1">Temperatura registrada dentro da tolerância.</p>
                <p class="text-muted small mb-3">Registrado com sucesso.</p>
            `;
        } else {
            html = `
                <div class="text-danger mb-2" style="font-size: 3rem;"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <h5 class="fw-bold mb-1">Aferição reprovada!</h5>
                <p class="text-muted small mb-1">Desvio de ${formatNum(data.desvio)} °C fora da tolerância.</p>
            `;
        }

        html += `
            <hr>
            <div class="d-grid">
                <button type="button" class="btn btn-primary btn-app" onclick="novaAfericao()">
                    <i class="bi bi-plus-circle me-2"></i>Nova aferição
                </button>
            </div>
        `;

        document.getElementById('modalResultadoBody').innerHTML = html;
        const modalResultado = new bootstrap.Modal(document.getElementById('modalResultado'));
        modalResultado.show();
    }

    function novaAfericao() {
        location.reload();
    }

    function textoTipo(tipo) {
        return {
            camara_congelada: 'Câmara congelada',
            camara_refrigerada: 'Câmara refrigerada',
            freezer_domestico: 'Freezer doméstico',
            balcao_refrigerado: 'Geladeira / balcão refrigerado',
            freezer_supermercado: 'Freezer de supermercado'
        }[tipo] || (tipo || '—');
    }

    function textoFaixa(min, max) {
        const numMax = parseFloat(max);
        const numMin = parseFloat(min);
        if (isNaN(numMax)) return '—';
        if (isNaN(numMin)) return '≤ ' + formatNum(numMax) + ' °C';
        return formatNum(numMin) + ' a ' + formatNum(numMax) + ' °C';
    }

    function formatNum(v) {
        const n = parseFloat(v);
        if (isNaN(n)) return '0,0';
        return n.toLocaleString('pt-BR', { minimumFractionDigits: 1, maximumFractionDigits: 1 });
    }

    function normalizarNumero(v) {
        if (v == null) return '';
        let s = String(v).trim().replace(/\s+/g, '');
        if (s === '') return '';
        const negativo = s.startsWith('-');
        s = s.replace('-', '');
        if (s.includes(',') && s.includes('.')) {
            s = s.replace(/\./g, '').replace(',', '.');
        } else {
            s = s.replace(',', '.');
        }
        return (negativo ? '-' : '') + s;
    }
</script>
@endpush