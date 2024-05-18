<div>
    <form action="#" id="form-filter">

        <div class="row">

            <div class="col-md-3">
                <select name="closed_period" class="form-control form-control-sm">
                    <option value="">Por Período de Tempo</option>
                    <option value="1">Últimos 24 horas</option>
                    <option value="15">Últimos 15 dias</option>
                    <option value="30" selected>Últimos 30 dias</option>
                    <option value="60" >Últimos 60 dias</option>
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
                <button id="btn-filter" type="submit" class="btn btn-outline-secondary btn-sm">
                    FILTRAR
                </button>
                <button type="button" onclick="document.getElementById('form-filter').reset()" class="btn btn-outline-secondary btn-sm float-right">
                    LIMPAR
                </button>
            </div>
        </div>

    </form>
</div>
