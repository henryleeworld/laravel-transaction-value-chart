@extends('layouts.admin')
@section('content')
@can('transaction_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.transactions.create") }}">
                {{ trans('global.add') }} {{ trans('cruds.transaction.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.transaction.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
            <form action="" id="filtersForm">
                <div class="input-group">
                <input type="text" name="from-to" class="form-control mr-2" id="date_filter">
                <span class="input-group-btn">
                    <input type="submit" class="btn btn-primary" value="篩選">
                </span> 
                </div>
            </form>
            </div>
        </div>
        <div class="row my-2" id="chart">
            <div class="{{ $chart->options['column_class'] }}">
                <h3>{!! $chart->options['chart_title'] !!}</h3>
                {!! $chart->renderHtml() !!}
            </div>
        </div>
        <table class=" table table-bordered table-striped table-hover ajaxTable datatable datatable-Transaction">
            <thead>
                <tr>
                    <th width="10">

                    </th>
                    <th>
                        {{ trans('cruds.transaction.fields.id') }}
                    </th>
                    <th>
                        {{ trans('cruds.transaction.fields.transaction_date') }}
                    </th>
                    <th>
                        {{ trans('cruds.transaction.fields.amount') }}
                    </th>
                    <th>
                        {{ trans('cruds.transaction.fields.description') }}
                    </th>
                    <th>
                        &nbsp;
                    </th>
                </tr>
            </thead>
        </table>
    </div>
</div>



@endsection

@section('styles')
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css">
@endsection

@section('scripts')
@parent
<script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
<script>
    $(function () {
  let searchParams = new URLSearchParams(window.location.search)
  let dateInterval = searchParams.get('from-to');
  let start = moment().subtract(29, 'days');
  let end = moment();

  if (dateInterval) {
      dateInterval = dateInterval.split(' - ');
      start = dateInterval[0];
      end = dateInterval[1];
  }

  $('#date_filter').daterangepicker({
      "showDropdowns": true,
      "showWeekNumbers": true,
      "alwaysShowCalendars": true,
      showCustomRangeLabel: false,
      startDate: start,
      endDate: end,
      locale: {
          format: 'YYYY-MM-DD',
          firstDay: 1,
          applyLabel: '套用',
          cancelLabel: '取消',
      },
      ranges: {
          '今天': [moment(), moment()],
          '昨天': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
          '前 7 天': [moment().subtract(6, 'days'), moment()],
          '前 30 天': [moment().subtract(29, 'days'), moment()],
          '本月': [moment().startOf('month'), moment().endOf('month')],
          '上個月': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
          '今年': [moment().startOf('year'), moment().endOf('year')],
          '去年': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
          '全部時間': [moment().subtract(30, 'year').startOf('month'), moment().endOf('month')]
      }
  });

  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
@can('transaction_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}';
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.transactions.massDestroy') }}",
    className: 'btn-danger',
    action: function (e, dt, node, config) {
      var ids = $.map(dt.rows({ selected: true }).data(), function (entry) {
          return entry.id
      });

      if (ids.length === 0) {
        alert('{{ trans('global.datatables.zero_selected') }}')

        return
      }

      if (confirm('{{ trans('global.areYouSure') }}')) {
        $.ajax({
          headers: {'x-csrf-token': _token},
          method: 'POST',
          url: config.url,
          data: { ids: ids, _method: 'DELETE' }})
          .done(function () { location.reload() })
      }
    }
  }
  dtButtons.push(deleteButton)
@endcan

  let dtOverrideGlobals = {
    buttons: dtButtons,
    processing: true,
    serverSide: true,
    retrieve: true,
    aaSorting: [],
    ajax: {
      url: "{{ route('admin.transactions.index') }}",
      data: {
        'from-to': searchParams.get('from-to'),
      }
    },
    columns: [
      { data: 'placeholder', name: 'placeholder' },
{ data: 'id', name: 'id' },
{ data: 'transaction_date', name: 'transaction_date' },
{ data: 'amount', name: 'amount' },
{ data: 'description', name: 'description' },
{ data: 'actions', name: '{{ trans('global.actions') }}' }
    ],
    order: [[ 1, 'asc' ]],
    pageLength: 100,
  };
  $('.datatable-Transaction').DataTable(dtOverrideGlobals);
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });
});

</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
{!! $chart->renderJs() !!}
@endsection