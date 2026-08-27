
@if (Module::find('ZibalPayment') && Module::find('ZibalPayment')->isEnabled())
    @if (zibalpayment_setting()->license_type === "active" && zibalpayment_setting()->purchase_code != null) 
    {{-- zibal gateway develop by itnoo --}}
    <div class="col-xl-12 col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-20">
        @include('sections.password-autocomplete-hide')
        <input type="hidden" name="payment_method" value="zibal">

        <div class="row">
            <div class="col-lg-12 mb-3">
                <x-forms.checkbox :fieldLabel="__('paymentgateway.zibal_active')" fieldName="zibal_status"
                    fieldId="zibal_status" fieldValue="active" fieldRequired="true"
                    :checked="$credentials->zibal_status == 'active'" />
            </div>
        </div>
        <div  class="row @if ($credentials->zibal_status == 'deactive') d-none @endif" id="zibal_details">
            <div class="col-lg-12">
                <div id="live_zibal_details" class="row">
                    <div class="col-lg-6">
                        <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('paymentgateway.zibal_merchentid')"
                            fieldName="zibal_merchant_id" fieldId="zibal_merchant_id" :fieldValue="$credentials->zibal_merchant_id"
                            fieldRequired="true"></x-forms.text>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- Buttons Start -->
    <div class="w-100 border-top-grey">
        <x-setting-form-actions>
            <div class="d-flex">
                <x-forms.button-primary class="mr-3 w-100" icon="check" id="save_zibal_data">@lang('app.save')
                </x-forms.button-primary>
            </div>
        </x-setting-form-actions>
    </div>
    @else
        <div class="col-xl-12 col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-20">
            <div class="d-flex">
                <p>مشتری گرامی، برای استفاده از سیستم درگاه پرداخت زیبال لایسنس خود را فعال نمایید</p>
            </div>
        </div>
        <div class="col-xl-12 col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-20">
            <div class="d-flex">
                <a href="/account/settings/custom-modules?tab=custom" class="btn btn-primary mr-3">
                    <i class="fa fa-download"></i> فعال سازی لاینسس
                </a>
            </div>
        </div>
    @endif
@else

    <div class="col-xl-12 col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-20">
        <div class="d-flex">
            <p>مشتری گرامی، برای استفاده از سیستم درگاه پرداخت زیبال می توانید با خرید ماژول مربوطه، درگاه زیبال خود را به اتوماسیون متصل نمایید</p>
        </div>
    </div>
    <div class="col-xl-12 col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-20">
        <div class="d-flex">
            <p>پس از خرید ماژول از بخش تنظیمات > تنظیمات ماژول نسبت به نصب و فعال سازی اقدام فرمایید</p>
        </div>
    </div>
    <div class="col-xl-12 col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-20">
        <div class="d-flex">
            <a href="https://erpit.ir/kod1" class="btn btn-primary mr-3">
                <i class="fa fa-download"></i> خرید و دانلود ماژول زیبال
            </a>
        </div>
    </div>
@endif