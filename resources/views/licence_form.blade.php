<!DOCTYPE html>
<html>
<head>
    <title>فعال سازی سامانه</title>
    <!-- Itnoo CSS include -->
    <link type="text/css" rel="stylesheet" media="all" href="{{ asset('itnoo/css/itnoo-licence.css') }}" defer="defer">
    <link type="text/css" rel="stylesheet" media="all" href="{{ asset('itnoo/css/farsi-fonts.css') }}" defer="defer">
</head>
<body>
    <div class="container">
        <h1>فعال سازی لایسنس</h1>
        <p>کاربر گرامی برای فعال سازی  بخش تنظیمات اتوماسیون لطفا اطلاعات لاینسس خریداری شده در وب سایت  را وارد نمایید</p>
        <p> در صورتی که اطلاعات لایسنس به درستی وارد شود به طور خودکار به داشبورد هدایت میشوید</p>
        <form action="{{ route('licences.store') }}" method="POST">
            @if(session('error'))
                <div class="erorr" style="color: red;">
                    {{ session('error') }}
                </div>
                       
            @elseif ($licenceValid == true )
            <div class="success" style="color: blue; text-align:center;">
                اطلاعات لایسنس شما صحیح است.
            </div>
            @endif
            @csrf
            <div class="field">
                <label for="rtl_username">نام کاربری</label>
                <input type="text" id="rtl_username" placeholder="username" name="rtl_username" value="{{ $settings->Rtl_username ?? '' }}" required>
            </div>
            <div class="field">
                <label for="rtl_purchase_code">شناسه خرید</label>
                <input type="text" id="rtl_purchase_code" placeholder="465139997551" name="rtl_purchase_code" value="{{ $settings->Rtl_purchase_code ?? '' }}" required>
            </div>
            <button class="btn" style="cursor: pointer;" type="submit">ارسال</button>
        </form>
        <button style="cursor: pointer;" class="btn" onclick="window.location.href='{{ URL::to('') }}'">بازگشت به صفحه اصلی</button>
    <p class="success center">تمامی حقوق محفوظ می باشد </p>
    </div>
</body>
</html>