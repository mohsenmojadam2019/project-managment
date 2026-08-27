@extends('layouts.app')

@section('content')
<style>
    .note {
        margin-bottom: 15px;
        padding: 15px;
        background-color: #e7f3fe;
        border-left: 6px solid #2196F3;
    }

    ul,
    li {
        list-style: inherit;
        line-height: 20px;
    }

    .note ul {
        margin-bottom: 20px;
        margin-top: 2px;
        margin-left: 10px;
    }

    .version-update-heading {
        color: #39bee6;
    }

    .update-summary-title {
        border-bottom: 1px solid black;
        padding-bottom: 8px
    }

</style>
    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        <x-setting-sidebar :activeMenu="$activeSettingMenu"></x-setting-sidebar>

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <h2 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                        @lang($pageTitle)</h2>
                </div>
            </x-slot>

            <div class="col-lg-12 col-md-12 w-100 p-4 ">
{{--                @php($updateVersionInfo = \Froiden\Envato\Functions\EnvatoUpdate::updateVersionInfo())--}}
                <div class="row">
                    <div class="col-sm-12">
                        {{-- @php($envatoUpdateCompanySetting = \Froiden\Envato\Functions\EnvatoUpdate::companySetting()) --}}

                        <div id="update-area" class="mt-20 mb-20 col-md-12 white-box d-none">

                        </div>
                        @if ($isUpToDate)

                        <x-alert type="info" icon="info-circle">
                           شما از آخرین نسخه برنامه استفاده میکنید.
                        </x-alert>
                        @else

                            <x-alert type="danger">
                                <ol class="mb-0">
                                    <li>@lang('messages.updateAlert')</li>
                                    <li>@lang('messages.updateBackupNotice')</li>
                                </ol>
                            </x-alert>


                            <div class="note alert alert-primary">
                                <div class="row p-20" style="line-height: 22px">
                                    <div class="col-md-8">
                                        <h6 class="f-24">
                                            <i class="fa fa-gift f-20"></i> @lang('modules.update.newUpdate') <span
                                                class="badge badge-success">{{ $lastVersion }}</span>
                                        </h6>
                                        <div class="mt-3"><span class="font-weight-bold text-red">توجه:</span> پس از شروع عملیات بروز رسانی تمامی کاربران از سیستم خارج خواهند شد و سیستم دقایقی از کارخواهد افتاد لطفا عملیات بروز رسانی را در ساعات غیر اداری انجام دهید تا در روند ذخیره سازی اطلاعات کاربران مشکلی پیش نیاید
                                        </div>
                                        <div class="font-12 mt-3">@lang('modules.update.updateAlternate')</div>
                                    </div>
                                    <div class="col-md-4 text-right mt-3">
                                        <x-forms.link-primary id="update-app" link="javascript:;" icon="download">
                                            @lang('modules.update.updateNow')</x-forms.link-primary>
                                    </div>
                                </div>

                                <div class="col-md-12 mt-5">
                                    <h6 class="update-summary-title"><i class="fa fa-history f-20"></i> لیست تغییرات </h6>
                                    <div>{!! nl2br(e($changelog)) !!}</div>
                                </div>
                            </div>


                        @endif
                    </div>
                </div>
{{--                @include('vendor.froiden-envato.update.version_info')--}}
                {{-- @include('vendor.froiden-envato.update.changelog')
                 @include('vendor.froiden-envato.update.plugins') --}}
            </div>

        </x-setting-card>

    </div>
    <!-- SETTINGS END -->
@endsection

{{--@push('scripts')--}}
{{--    @include('vendor.froiden-envato.update.update_script')--}}
{{--@endpush--}}
