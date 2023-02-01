@extends('ew_dynamic.preview.base')

@section('content')
    {!! \Acelle\Model\ShopifyCheckout::getHtmlRepresentation(null, new \Acelle\Library\Automation\DynamicWidgetConfig\CartConfig(request()->getQueryString())) !!}
@endsection