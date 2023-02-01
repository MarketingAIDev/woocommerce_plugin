@extends('ew_dynamic.preview.base')

@section('content')
    {!! \Acelle\Model\ChatSession::getHtmlRepresentation(null, new \Acelle\Library\Automation\DynamicWidgetConfig\ChatConfig(request()->getQueryString())) !!}
@endsection