<div>
    <form action="#" id="form-filter">

        <div class="row">

            <div class="col-md-3">
                <select {{ $closedPeriodDisabled ? 'disabled' : '' }} name="closed_period" class="form-control form-control-sm">
                    <option value="">Por Período de Tempo</option>
                    <option value="0.125">🕐 Últimas 3 horas</option>
                    <option value="0.25">🕐 Últimas 6 horas</option>
                    <option value="0.5">🕐 Últimas 12 horas</option>
                    <option value="1">🕐 Últimas 24 horas</option>
                    <option value="2" selected>Últimos 2 dias</option>
                    <option value="5">Últimos 5 dias</option>
                    <option value="10">Últimos 10 dias</option>
                    <option value="15">Últimos 15 dias</option>
                    <option value="30">Últimos 30 dias</option>
                    <option value="60">Últimos 60 dias</option>
                    <option value="90">Últimos 90 dias</option>
                </select>
            </div>

            <div class="col-md-3">
                <input class="form-control form-control-sm" type="{{ $withTime ? 'datetime-local' : 'date' }}" name="start_date" id="start_date">
            </div>
            <div class="col-md-3">
                <input class="form-control form-control-sm" type="{{ $withTime ? 'datetime-local' : 'date' }}" name="end_date" id="end_date">
            </div>
            <div class="col-md-3">
                <div id="menu-id"></div>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-12">
                <button id="btn-filter" type="submit" class="btn btn-secondary btn-sm">
                    FILTRAR
                </button>
                <button type="button" onclick="document.getElementById('form-filter').reset()" class="btn btn-secondary btn-sm float-right">
                    LIMPAR
                </button>
            </div>
        </div>

    </form>
</div>
