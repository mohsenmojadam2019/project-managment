@extends('layouts.app')

@push('datatable-styles')
    <script src="{{ asset('vendor/jquery/frappe-charts.min.iife.js') }}"></script>
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
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.employee')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="employee" id="employee_id" data-live-search="true"
                    data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($employees as $employee)
                        <x-user-option :user="$employee" />
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
        <div class="d-flex flex-column w-tables rounded mt-4 bg-white">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- Task Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->

@endsection

@push('scripts')
    @include('sections.datatable_js')
    @if (app()->getLocale() == 'fa')
        <script src="{{ asset('vendor/persiandate/popper.min.js') }}"></script>
        <script src="{{ asset('vendor/persiandate/bootstrap.min.js') }}"></script>
        <script src="{{ asset('vendor/persiandate/jquery.md.bootstrap.datetimepicker.js') }}"></script>
        <script>
            var start = moment().clone().startOf('month');
            var end = moment();
            $('#datatableRange_h').MdPersianDateTimePicker({
                targetDateSelector: '#datatableRange2',
                targetTextSelector: '#datatableRange_h',
                rangeSelector: true,
                selectedRangeDate: [new Date(start), new Date(end)],
                monthsToShow: [0,1],
                dateFormat: '{{ persiandateformat() }}',
                textFormat: '{{ persiandateformat() }}',
            });
        </script> 
    @endif
    <script type="text/javascript">
        // متغیرهای جهانی برای تاریخ
        var globalStart = moment().clone().startOf('month');
        var globalEnd = moment();

        // تابع تنظیم تاریخ
        function setDate(start, end) {
            $('#datatableRange2').daterangepicker({
                locale: daterangeLocale,
                linkedCalendars: false,
                startDate: start,
                endDate: end,
                ranges: daterangeConfig
            }, function(start, end) {
                globalStart = start; // به‌روزرسانی متغیرهای جهانی
                globalEnd = end;
                showTable(); // رفرش جدول بعد از انتخاب تاریخ
            });
        }

        $(function() {
            var locale = "{{ app()->getLocale() }}";

            // تنظیم مقدار اولیه
            setDate(globalStart, globalEnd);

            // رویداد تغییر تاریخ (apply.daterangepicker و change)
            $('#datatableRange2').on('change apply.daterangepicker', function(ev, picker) {
                if (ev.type === 'change' && !picker) {
                    // برای رویداد change که picker نداره
                    var datefa = $(this).val();
                    if (datefa) {
                        globalStart = moment(new Date(datefa.split(' - ')[0]));
                        globalEnd = moment(new Date(datefa.split(' - ')[1]));
                    }
                } else if (ev.type === 'apply') {
                    // برای رویداد apply.daterangepicker
                    globalStart = picker.startDate;
                    globalEnd = picker.endDate;
                }
                console.log('Date changed:', globalStart.format('YYYY-MM-DD'), globalEnd.format('YYYY-MM-DD')); // برای دیباگ
                showTable(); // رفرش جدول
            });
        });

        $('#attendance-report-table').on('preXhr.dt', function(e, settings, data) {
            var employeeID = $('#employee_id').val();
            var locale = "{{ app()->getLocale() }}";
            var startDate, endDate;

            // گرفتن مقادیر تاریخ از متغیرهای جهانی
            if (locale == 'fa') {
                var datefa = $('#datatableRange2').val();
                if (datefa) {
                    startDate = moment(new Date(datefa.split(' - ')[0])).format('{{ company()->moment_date_format }}');
                    endDate = moment(new Date(datefa.split(' - ')[1])).format('{{ company()->moment_date_format }}');
                } else {
                    startDate = globalStart.format('{{ company()->moment_date_format }}');
                    endDate = globalEnd.format('{{ company()->moment_date_format }}');
                }
            } else {
                startDate = globalStart.format('{{ company()->moment_date_format }}');
                endDate = globalEnd.format('{{ company()->moment_date_format }}');
            }

            console.log('Sending to server:', { startDate, endDate, employeeID }); // برای دیباگ
            data['startDate'] = startDate;
            data['endDate'] = endDate;
            data['employee'] = employeeID;
            data['_token'] = '{{ csrf_token() }}';
        });

        const showTable = () => {
            console.log('Refreshing table...'); // برای دیباگ
            window.LaravelDataTables["attendance-report-table"].draw(true);
        }

        $('#employee_id').on('change keyup', function() {
            if ($('#employee_id').val() != "all") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            } else {
                $('#reset-filters').addClass('d-none');
                showTable();
            }
        });

        $('#reset-filters').click(function() {
            $('#filter-form')[0].reset();
            globalStart = moment().clone().startOf('month');
            globalEnd = moment();
            setDate(globalStart, globalEnd);
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });
    </script>
@endpush