   <!-- Itnoo CSS fonts -->
    @php
    $locale=app()->getLocale();
    @endphp
    <link type="text/css" rel="stylesheet" media="all" @if($locale === 'fa') href="{{ asset('itnoo/css/farsi-fonts.css') }}" defer="defer" @endif>