@extends('vendor.apm.emails.common.layout')
@section('content')
    <p>{{$title}}</p>
    <table border="1" style="border-collapse:collapse;">
        @include("{$emailTemplatePath}.table-head")
        <tbody>
        @if($data)
            @foreach($data as $stats)
                @include("{$emailTemplatePath}.table-tr-result")
            @endforeach
        @else
            @include("{$emailTemplatePath}.table-tr-no-record")
        @endif
        </tbody>
    </table>
@endsection
