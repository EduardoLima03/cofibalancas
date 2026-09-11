@extends('layouts.app')

@section('title', 'Calendário de Conferências')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title mb-0"><i class="bi bi-calendar3 me-2"></i>Calendário de Conferências</h1>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('calendario') }}" class="row g-2 align-items-end">
            <div class="col-6 col-md-4 col-lg-3">
                <label for="mes" class="form-label small mb-1">Mês</label>
                <input type="month" class="form-control form-control-sm" id="mes" name="mes" value="{{ $mes }}" max="{{ now()->format('Y-m') }}">
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <label for="loja_id" class="form-label small mb-1">Loja</label>
                <select class="form-select form-select-sm" id="loja_id" name="loja_id">
                    @foreach($lojas as $loja)
                        <option value="{{ $loja->id }}" {{ $lojaSelecionada == $loja->id ? 'selected' : '' }}>{{ $loja->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search me-1"></i>Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

@if(!$lojaSelecionada)
<div class="alert alert-info alert-tolerancia">
    <i class="bi bi-info-circle me-2"></i>Selecione uma loja para visualizar o calendário.
</div>
@else
<div class="card">
    <div class="card-body">
        @php
            $mesesBR = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
            $diasSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
            $nomeMes = $mesesBR[$mesNum - 1];
        @endphp

        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('calendario', ['mes' => \Carbon\Carbon::create($ano, $mesNum, 1)->subMonth()->format('Y-m'), 'loja_id' => $lojaSelecionada]) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-chevron-left"></i>
            </a>
            <h5 class="fw-semibold mb-0 text-uppercase">{{ $nomeMes }} {{ $ano }}</h5>
            <a href="{{ route('calendario', ['mes' => \Carbon\Carbon::create($ano, $mesNum, 1)->addMonth()->format('Y-m'), 'loja_id' => $lojaSelecionada]) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-3 small">
            <span><span class="badge bg-success me-1">&nbsp;</span> Todos aprovados</span>
            <span><span class="badge bg-warning me-1">&nbsp;</span> Com ressalva</span>
            <span><span class="badge bg-danger me-1">&nbsp;</span> Fora da tolerância</span>
        </div>

        <div class="row g-2 mb-2 d-none d-md-flex">
            @foreach($diasSemana as $ds)
            <div class="col text-center small fw-semibold text-muted">{{ $ds }}</div>
            @endforeach
        </div>

        <div class="row g-2">
            @foreach($dias as $dia)
                @if($dia === null)
                    <div class="col cal-day empty d-none d-md-block"></div>
                @else
                    @php
                        $dataKey = sprintf('%04d-%02d-%02d', $ano, $mesNum, $dia);
                        $resumo = $resumoPorData[$dataKey] ?? null;
                        $dataAtual = \Carbon\Carbon::today();
                        $ehHoje = $ano == $dataAtual->year && $mesNum == $dataAtual->month && $dia == $dataAtual->day;
                        @endphp
                    <div class="col mb-2">
                        <a href="{{ route('calendario', ['mes' => $mes, 'loja_id' => $lojaSelecionada, 'dia' => $dia]) }}"
                           class="text-decoration-none text-dark"
                           data-bs-toggle="modal"
                           data-bs-target="#modalDia{{ $dia }}">
                            <div class="card cal-day {{ $ehHoje ? 'today' : '' }} {{ $resumo ? 'bg-opacity-10 border-1' : '' }} bg-{{ $resumo ? ($resumo['status'] == 'aprovado' ? 'success' : ($resumo['status'] == 'reprovado' ? 'danger' : 'warning')) : 'light' }} border-subtle">
                                <div class="card-body p-2">
                                    <div class="day-num">{{ $dia }}</div>
                                    @if($resumo)
                                        <div class="cal-badge d-block mt-1">
                                            <i class="bi {{ $resumo['status'] == 'aprovado' ? 'bi-check-circle-fill text-success' : ($resumo['status'] == 'reprovado' ? 'bi-x-circle-fill text-danger' : 'bi-exclamation-circle-fill text-warning') }}"></i>
                                            <span class="badge bg-{{ $resumo['status'] == 'aprovado' ? 'success' : ($resumo['status'] == 'reprovado' ? 'danger' : 'warning text-dark') }}">
                                                {{ $resumo['total'] }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Modal detalhe do dia -->
                    @if($resumo)
                    <div class="modal fade" id="modalDia{{ $dia }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        {{ $dia }} de {{ $nomeMes }} {{ $ano }} — {{ $resumo['total'] }} conferência(s)
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-0">
                                    @php
                                        $consultaDia = \App\Models\Conferencia::with(['loja', 'balanca', 'user'])
                                            ->whereDate('data_conferencia', $dataKey)
                                            ->where('loja_id', $lojaSelecionada)
                                            ->get();
                                    @endphp
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Hora</th>
                                                    <th>Balança</th>
                                                    <th>Item</th>
                                                    <th class="text-end">Diferença</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($consultaDia as $c)
                                                <tr>
                                                    <td class="small">{{ $c->created_at->format('H:i') }}</td>
                                                    <td class="small">{{ $c->balanca?->nome}}</td>
                                                    <td class="small">{{ $c->itens->first()?->descricao_item ?? '—' }}</td>
                                                    <td class="text-end small fw-semibold">{{ number_format($c->diferenca, 3, ',', '.') }} kg</td>
                                                    <td><span class="badge bg-{{ $c->status_color }}">{{ $c->status_label }}</span></td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="5" class="text-center text-muted py-3">Sem conferências.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                @endif
            @endforeach
        </div>

        @if($conferenciasDia && $conferenciasDia->isNotEmpty())
        <div class="card mt-3">
            <div class="card-header">
                Conferências em {{ $detalhesDia }}/{{ $mesNum }}/{{ $ano }}
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Hora</th>
                                <th>Balança</th>
                                <th>Item</th>
                                <th class="text-end">Diferença</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($conferenciasDia as $c)
                            <tr>
                                <td class="small">{{ $c->created_at->format('H:i') }}</td>
                                <td class="small">{{ $c->balanca?->nome }}</td>
                                <td class="small">{{ $c->itens->first()?->descricao_item ?? '—' }}</td>
                                <td class="text-end small fw-semibold">{{ number_format($c->diferenca, 3, ',', '.') }} kg</td>
                                <td><span class="badge bg-{{ $c->status_color }}">{{ $c->status_label }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endif
@endsection