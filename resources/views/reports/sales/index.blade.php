@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')
<link rel="stylesheet" href="{{ asset('vendor/persiandate/jquery.md.bootstrap.datetimepicker.style.css') }}" />

    <x-filters.filter-box>
        <!-- DATE START -->
        <div class="select-box d-flex pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.duration')</p>
            <div class="select-status d-flex">
            @if (app()->getLocale()=='fa')
                <input type="text" class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey"
                    id="datatableRange_h" placeholder="@lang('placeholders.dateRange')">
            <input type="hidden" class="form-control height-35 f-14" id="datatableRange2" />
            @else
                <input type="text" class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey"
                    id="datatableRange2" placeholder="@lang('placeholders.dateRange')">
            @endif
            </div>
        </div>
        <!-- DATE END -->

        <!-- CLIENT START -->
        <div class="select-box d-flex  py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.client')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="employee" id="clientID" data-live-search="true"
                    data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($clients as $client)
                        <x-user-option :user="$client" />
                    @endforeach
                </select>
            </div>
        </div>
        <!-- CLIENT END -->

        <!-- RESET START -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->

    </x-filters.filter-box>

@endsection

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Add Task Export Buttons Start -->
        <div class="d-flex flex-column">
            <div id="table-actions" class="flex-grow-1 align-items-center mt-4">
            </div>

        </div>

        <!-- Add Task Export Buttons End -->
        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-4 bg-white table-responsive">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- Task Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->

@endsection

@push('scripts')
    @include('sections.datatable_js')
@if (app()->getLocale() =='fa')
    <script src="{{ asset('vendor/persiandate/popper.min.js') }}" ></script>
    <script src="{{ asset('vendor/persiandate/bootstrap.min.js') }}"></script>
    <script src="{{ asset('vendor/persiandate/jquery.md.bootstrap.datetimepicker.js') }}"></script>
    <script>

    var start = moment().clone().startOf('month');
    var end = moment();
    $('#datatableRange_h').MdPersianDateTimePicker({
        targetDateSelector: '#datatableRange2',
        targetTextSelector: '#datatableRange_h',
        rangeSelector: true,
        selectedRangeDate:[new Date(start),new Date(end)],
        monthsToShow: [0,1],
        dateFormat: '{{persiandateformat()}}',
        textFormat: '{{persiandateformat()}}',
    });
</script> 
@endif
    <script type="text/javascript">

        function getDate()
            {
                $('#datatableRange2').daterangepicker({
                    locale: daterangeLocale,
                    linkedCalendars: false,
                    startDate: start,
                    endDate: end,
                    ranges: daterangeConfig
                }, cb);
            }

        $(function() {
            var locale = "{{app()->getLocale()}}";
            if(locale == 'fa'){
                var datefa = $('#datatableRange2').val();
                var start = moment(new Date(datefa.split(' - ')[0]));
                
                var end = moment(new Date(datefa.split(' - ')[1]));  
                
            }else{
                var start = moment().clone().startOf('month');
                var end = moment();
            }
            getDate(start,end);

            $('#datatableRange2').on('change apply.daterangepicker', function(ev, picker) {
                showTable();
            });

        });

    </script>


    <script>
        $('#sales-report-table').on('preXhr.dt', function(e, settings, data) {

            var locale = "{{app()->getLocale()}}";
            var dateRangePicker = $('#datatableRange2').data('daterangepicker');
            var startDate = $('#datatableRange2').val();

            if (startDate == '') {
                startDate = null;
                endDate = null;
            } else {
                
                if(locale == 'fa'){
                    var datefa = $('#datatableRange2').val();
                    var startDate = moment(new Date(datefa.split(' - ')[0]));
                    startDate = startDate.format('{{ company()->moment_date_format }}');
                    var endDate = moment(new Date(datefa.split(' - ')[1]));  
                    endDate = endDate.format('{{ company()->moment_date_format }}');
                }else{
                    startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                    endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
                }    
            }
            var clientID = $('#clientID').val();

            data['startDate'] = startDate;
            data['endDate'] = endDate;
            data['clientID'] = clientID;
        });
        const showTable = () => {
            window.LaravelDataTables["sales-report-table"].draw(true);
        }

        $('#clientID').on('change keyup',
            function() {
                if ($('#clientID').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else {
                    $('#reset-filters').addClass('d-none');
                    showTable();
                }
            });

        $('#reset-filters').click(function() {
            $('#filter-form')[0].reset();
            // getDate()

            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });

        $('#reset-filters-2').click(function() {
            $('#filter-form')[0].reset();

            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });

    </script>
@endpush
