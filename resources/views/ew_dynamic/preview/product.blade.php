@extends('ew_dynamic.preview.base')

@section('content')
    {!! \Acelle\Model\ShopifyProduct::getHtmlRepresentation(new \Acelle\Library\Automation\DynamicWidgetConfig\ProductConfig(null, request()->getQueryString())) !!}
@endsection