@extends('vendor.installer.layouts.master')


@section('style')
    <style>
        .button.disabled {
            pointer-events: none;
            cursor: not-allowed;
            background: #c2c2c2;
        }
        .hide{
            display: none;
        }
    </style>
@endsection

@section('title', "مجوزات دسترسی به فایل ها")
@section('container')
    @if (isset($permissions['errors']))
        <div class="alert alert-danger">لطفا خطاهای زیر را برطرف کنید 
            "و روی دکمه بررسی مجدد کلیک کنید" </div>
    @endif
    <ul class="list">
        @foreach ($permissions['permissions'] as $permission)
            <li class="list__item list__item--permissions {{ $permission['isSet'] ? 'success' : 'error' }}">
                {{ $permission['folder'] }}
                <span>
                    <i class="fa fa-fw fa-{{ $permission['isSet'] ? 'check-circle-o' : 'exclamation-circle' }}"></i>
                    {{ $permission['permission'] }}
                </span>

            </li>
        @endforeach

    </ul>

    @if (isset($permissions['errors']))
        <span>اگر به ترمینال دسترسی دارید دستور زیر را اجرا کنید</span>
        <p style="background: #f7f7f9;padding: 10px;">
            chmod -R 775 storage/app/ storage/framework/ storage/logs/ bootstrap/cache/
        </p>
    @endif

    <div class="buttons">
        <ul  class="hide" id="messageWait">
            <ol>لطفا تا زمان آماده سازی برنامه منتظر بمانید </ol>
        </ul>
        @if (!isset($permissions['errors']))
            <a class="button" href="{{ route('LaravelInstaller::database') }}">
                {{ "ادامه مراحل "}}
            </a>
        @else

            <a class="button" href="javascript:window.location.href='';">
                {{ "بررسی مجدد مجوزات" }}
            </a>
        @endif

    </div>

@stop

@section('scripts')
    <script src="{{ asset('installer/js/jQuery-2.2.0.min.js') }}"></script>

    <script>
        $('.button').click(function () {
            const button = $('.button');

            const text = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> در حال ارسال . . .';

            $(button).addClass('disabled');
            $('#messageWait').show()
            button.html(text);
        });
    </script>
@endsection

