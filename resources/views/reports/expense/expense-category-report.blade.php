@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@push('styles')
    <style>
        .content-wrapper{
            padding: 15px 18px !important;
        }
    </style>
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

        <!-- EXPENSE CATEGORY START -->
        <div class="select-box d-flex  py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.category')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="category" id="category_id" data-live-search="true"
                    data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- EXPENSE CATEGORY END -->

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
        <div class="row">
            <div class="col-lg-6">
                <div id="table-actions" class="flex-grow-1 align-items-center mt-1">
                </div>
            </div>
            <div class="col-lg-6">
                <div class="d-lg-flex d-md-flex justify-content-end" id="reports">
                    <div class="btn-group mt-3 mt-lg-0 mt-md-0 ml-lg-3" role="group">
                        <a href="{{ route('expense-report.index') }}" class="btn btn-secondary f-14" data-toggle="tooltip"
                            data-original-title="@lang('app.menu.expenseReport')"><i class="side-icon bi bi-list-ul"></i></a>

                        <a href="{{ route('expense-report.expense_category_report') }}" class="btn btn-secondary f-14 btn-active" data-toggle="tooltip"
                            data-original-title="@lang('modules.expenseCategory.expenseCategoryReport')"><i class="side-icon bi bi-receipt"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">
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
<script>
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
</script> 
<script>     
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

        

        $('#expense-category-report-table').on('preXhr.dt', function(e, settings, data) {

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
            var categoryID = $('#category_id').val();
            var searchText = $('#search-text-field').val();

            data['categoryID'] = categoryID;
            data['startDate'] = startDate;
            data['endDate'] = endDate;
            data['searchText'] = searchText;
        });

        const showTable = () => {
            window.LaravelDataTables["expense-category-report-table"].draw(true);
        }

        $('#category_id').on('change keyup', function() {
            console.log('fd');

            if ($('#category_id').val() != "all") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            } else {
                $('#reset-filters').addClass('d-none');
                showTable();
            }
        });

        $('#search-text-field').on('keyup', function() {
            if ($('#search-text-field').val() != "") {
                $('#reset-filters').removeClass('d-none');
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

        $('#reset-filters-2').click(function() {
            $('#filter-form')[0].reset();

            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });

    });
</script>
@endpush
