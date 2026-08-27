@extends('vendor.installer.layouts.master')

@section('title', "پایان نصب")
@section('container')
    <p @class([
            'alert alert-success',
            'alert-danger'=> session()->has('message') && session('message')['status'] !=='success',
        ])
       style="text-align: center;">{{ session()->has('message')? session('message')['message']:"برنامه با موفقیت نصب شد" }}</p>
    <div class="buttons">
        <a href="{{ url('/') }}" class="button">پایان</a>
    </div>
@stop
