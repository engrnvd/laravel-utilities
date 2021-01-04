@extends('emails.common.layout')
@section('content')
    <h2>{{$title}}</h2>
    @foreach($data as $group=>$report)
        <h3>{{$group}}</h3>
        <table border="1" style="border-collapse:collapse;">
            @include("{$emailTemplatePath}.table-head")
            <tbody>
            @if($report)
                @foreach($report as $stats)
                    @include("{$emailTemplatePath}.table-tr-result")
                @endforeach
            @else
                @include("{$emailTemplatePath}.table-tr-no-record")
            @endif
            </tbody>
            @if($report)
                <tfoot>
                @include("{$emailTemplatePath}.table-tr-no-record")
                </tfoot>
            @endif
        </table>
    @endforeach
    @if(!$data || !count($data))
        <h2>No data found</h2>
    @endif
@endsection