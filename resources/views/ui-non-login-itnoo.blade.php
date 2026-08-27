   <!-- Itnoo CSS UI -->
    @php
    $locale=app()->getLocale();
    @endphp
    <link type="text/css" rel="stylesheet" media="all" @if($locale === 'fa') href="{{ asset('itnoo/css/user-non-login.css') }}" defer="defer" @endif>