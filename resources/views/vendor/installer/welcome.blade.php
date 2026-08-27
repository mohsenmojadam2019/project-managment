@extends('vendor.installer.layouts.master')

@section('title', "نصب کننده سریع")
@section('container')
    <p class="paragraph" style="text-align: center;">{{ "به  نصب کننده ورک سوئیت خوش آمدید" }}</p>
    <div class="buttons">
        <a href="{{ route('LaravelInstaller::environment') }}" class="button">{{ "ادامه مراحل "}}</a>
    </div>
@stop
