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
        <div class="d-grid d-lg-flex d-md-flex action-bar">

            <div id="table-actions" class="flex-grow-1 align-items-center mb-2 mb-lg-0 mb-md-0">
            </div>
            <div class="btn-group mt-2 mt-lg-0 mt-md-0 ml-0 ml-lg-3 ml-md-3" role="group">
                <a href="{{ route('leave-report.leave_quota') }}" class="btn btn-secondary f-14 show-leaves-quota" data-toggle="tooltip"
                    data-original-title="@lang('app.menu.leavesQuota')"><i class="side-icon bi bi-pie-chart-fill"></i></a>

                <a href="{{ route('leave-report.index') }}" class="btn btn-secondary f-14 btn-active leave-report" data-toggle="tooltip"
                    data-original-title="@lang('app.menu.leaveReport')"><i class="side-icon bi bi-list-ul"></i></a>

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

function setDate()
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
            setDate(start,end);

            $('#datatableRange2').on('change apply.daterangepicker', function(ev, picker) {
                showTable();
            });

        });
    </script>

    <script>
        $('#leave-report-table').on('preXhr.dt', function(e, settings, data) {
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


            var employeeId = $('#employee_id').val();
            if (!employeeId) {
                employeeId = 0;
            }

            data['startDate'] = startDate;
            data['endDate'] = endDate;
            data['employeeId'] = employeeId;
            data['_token'] = '{{ csrf_token() }}';
        });

        const showTable = () => {
            window.LaravelDataTables["leave-report-table"].draw(true);
        }

        $('#employee_id')
            .on('change keyup',
                function() {
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
            var locale = "{{app()->getLocale()}}";
            if(locale == 'fa'){
                var datefa = $('#datatableRange2').val();
                var start = moment(new Date(datefa.split(' - ')[0]));
                
                var end = moment(new Date(datefa.split(' - ')[1]));  
                
            }else{
                var start = moment().clone().startOf('month');
                var end = moment();
            }
            setDate(start,end);

            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });

        $('#leave-report-table').on('click', '.view-leaves', function(event) {
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


            event.preventDefault();
            var id = $(this).data('user-id');
            var url = "{{ route('leave-report.show', ':id') }}?startDate=" + encodeURIComponent(startDate) +
                '&endDate=' + encodeURIComponent(endDate);
            url = url.replace(':id', id);

            $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_XL, url);
        });

    </script>
    <script>
        $("body").on("click", ".ajax-tab", function(event) {
            event.preventDefault();

            $('.task-tabs .ajax-tab').removeClass('active');
            $(this).addClass('active');

            const requestUrl = this.href;

            $.easyAjax({
                url: requestUrl,
                blockUI: true,
                container: "#nav-tabContent",
                historyPush: false,
                data: {
                    'json': true
                },
                success: function(response) {
                    if (response.status == "success") {
                        $('#nav-tabContent').html(response.html);
                    }
                }
            });
        });

    </script>
@endpush
