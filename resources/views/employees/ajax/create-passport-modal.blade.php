
<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/persiandate/jquery.md.bootstrap.datetimepicker.style.css') }}" />
<div class="modal-header">
    <h5 class="modal-title">@lang('app.addPassport')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <x-form id="save-passport-data-form" method="POST" class="ajax-form">

            <input type="hidden" value="{{ request()->empid }}" name="emp_id">

            <div class="row">

                <div class="col-lg-3">
                    <x-forms.text :fieldLabel="__('modules.employees.passportNumber')"
                        fieldName="passport_number" fieldId="passport_number"
                        fieldValue="" :fieldRequired="true" />
                </div>
                <div class="col-lg-3">
                    <x-forms.select fieldId="nationality" :fieldLabel="__('app.nationality')" fieldName="nationality"
                        search="true" :fieldRequired="true">
                        <option value="">--</option>
                        @foreach ($countries as $item)
                            <option data-tokens="{{ $item->iso3 }}"
                                data-content="<span class='flag-icon flag-icon-{{ strtolower($item->iso) }} flag-icon-squared'></span> {{ $item->nationality .' ('.  $item->name . ')'}}"
                                value="{{ $item->id }}">{{ $item->nationality }}</option>
                        @endforeach
                    </x-forms.select>
                </div>
                @if (app()->getLocale() =='fa')
                <div class="col-lg-3">
                    <x-forms.text fieldId="issue_date" fieldRequired="true"
                                        :fieldLabel="__('modules.employees.issueDate')" fieldName="issue_date_n"
                                        :fieldPlaceholder="__('placeholders.date')"/>
                    <input type="hidden" class="form-control height-35 f-14" style="font-family: sans-serif" name="issue_date" id="issue_date_h" />
                </div>

                <div class="col-lg-3">
                    <x-forms.text fieldId="expiry_date" fieldRequired="true"
                                        :fieldLabel="__('modules.employees.expiryDate')" fieldName="expiry_date_n"
                                        :fieldPlaceholder="__('placeholders.date')"/>
                    <input type="hidden" class="form-control height-35 f-14" style="font-family: sans-serif" name="expiry_date" id="expiry_date_h" />                
                </div>
                @else
                <div class="col-lg-3">
                    <x-forms.datepicker fieldId="issue_date" fieldRequired="true"
                                        :fieldLabel="__('modules.employees.issueDate')" fieldName="issue_date"
                                        :fieldValue="now(company()->timezone)->format(company()->date_format)"
                                        :fieldPlaceholder="__('placeholders.date')"/>
                </div>

                <div class="col-lg-3">
                    <x-forms.datepicker fieldId="expiry_date" fieldRequired="true"
                                        :fieldLabel="__('modules.employees.expiryDate')" fieldName="expiry_date"
                                        :fieldValue="now(company()->timezone)->format(company()->date_format)"
                                        :fieldPlaceholder="__('placeholders.date')"/>
                </div>
                @endif
                <div class="col-lg-12">
                    <x-forms.file allowedFileExtensions="png jpg jpeg svg pdf doc docx" class="mr-0 mr-lg-2 mr-md-2"
                        :fieldLabel="__('modules.employees.scanCopy')" fieldName="file"
                        fieldId="file">
                    </x-forms.file>
                </div>



            </div>
        </x-form>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-passport-form" icon="check">@lang('app.save')</x-forms.button-primary>
</div>
@if (app()->getLocale() =='fa')
<script src="{{ asset('vendor/persiandate/popper.min.js') }}" ></script>
<script src="{{ asset('vendor/persiandate/bootstrap.min.js') }}"></script>
<script src="{{ asset('vendor/persiandate/jquery.md.bootstrap.datetimepicker.js') }}"></script>
<script>
   
   $('#issue_date').MdPersianDateTimePicker({
     targetDateSelector: '#issue_date_h',
     targetTextSelector: '#issue_date',
     selectedDate: new Date(),
     fromDate: true,
     groupId: 'rangeSelector1',
     dateFormat: '{{persiandateformat()}}',
     textFormat: '{{persiandateformat()}}',
   });
   $('#expiry_date').MdPersianDateTimePicker({
     targetDateSelector: '#expiry_date_h',
     targetTextSelector: '#expiry_date',
     toDate: true,
     groupId: 'rangeSelector1',
     dateFormat: '{{persiandateformat()}}',
     textFormat: '{{persiandateformat()}}',
   });
</script>
@else
<script>
    datepicker('#issue_date', {
            position: 'bl',
            ...datepickerConfig
        });

    datepicker('#expiry_date', {
        position: 'bl',
        ...datepickerConfig
    });
</script>
@endif
<script>
    $('#save-passport-form').click(function(){
        $.easyAjax({
                    url: "{{ route('passport.store') }}",
                    container: '#save-passport-data-form',
                    type: "POST",
                    disableButton: true,
                    blockUI: true,
                    buttonSelector: 'save-passport-form',
                    file: true,
                    data: $('#save-passport-data-form').serialize(),
                    success: function (response) {
                        if (response.status === 'success') {
                            window.location.reload();
                        }
                    }
        });
    });

    init(MODAL_LG);
</script>
