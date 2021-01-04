@include('vendor.apm.emails.common.style')
@stack('styles')
<div class="main" style='
    font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Segoe UI Light", "Helvetica Neue", "Segoe UI", Helvetica, Arial, sans-serif;
    font-weight: normal;
    line-height: 1.5;
    font-size: 18px;
    max-width: 600px;
    margin: 18px auto;
    border-radius: 3px;
'>
    @include('vendor.apm.emails.common.header')

    <div style="padding: 36px;
        color: #000;
        background-color: #fff;
        border-radius: 3px;">
        @yield('content')
    </div>

    @include('vendor.apm.emails.common.footer')
</div>
