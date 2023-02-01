@extends('ew_dynamic.preview.base')

@section('content')
    {!! \Acelle\Model\ShopifyOrder::getHtmlRepresentation(null, new \Acelle\Library\Automation\DynamicWidgetConfig\OrderConfig(request()->getQueryString())) !!}
@endsection