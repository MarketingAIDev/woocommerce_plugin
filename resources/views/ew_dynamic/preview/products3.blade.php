@extends('ew_dynamic.preview.base')

@section('content')
    {!! \Acelle\Model\ShopifyProduct::getProduct3HtmlRepresentation(new \Acelle\Library\Automation\DynamicWidgetConfig\Products3Config(request()->getQueryString())) !!}
@endsection