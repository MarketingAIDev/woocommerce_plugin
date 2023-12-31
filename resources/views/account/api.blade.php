@extends('layouts.frontend')

@section('title', trans('messages.api_token'))

@section('page_script')
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>

    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')

	<div class="page-title">
		<ul class="breadcrumb breadcrumb-caret position-right">
			<li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
			<li class="active">{{ trans('messages.api_token') }}</li>
		</ul>
		<h1>
			<span class="text-semibold"><i class="icon-profile"></i> {{ request()->selected_customer->displayName() }}</span>
		</h1>
	</div>

@endsection

@section('content')

	@include("account._menu")

	<h4 class="text-semibold">{{ trans('messages.api_docs') }}</h4>

	<p style="margin-bottom: 30px"><code style="font-size: 18px">
    <a target="_blank" href="{{ action("Controller@docsApiV1") }}">{{ action("Controller@docsApiV1") }} <i class="icon-link"></i></a></code></p>

	<h4 class="text-semibold">{{ trans('messages.api_endpoint') }}</h4>

	<p style="margin-bottom: 30px"><code style="font-size: 18px">
		{{ url('api/v1') }}</code></p>

	<h4 class="text-semibold mt-20">{{ trans('messages.your_api_token') }}</h4>

	<p style="margin-bottom: 30px"><code style="font-size: 18px">
		{{ Auth::user()->api_token }}</code></p>
	<p class="alert alert-info">{!! trans('messages.api_token_guide', ["link" => action("Api\MailListController@index", ["api_token" => "YOUR_API_TOKEN"])]) !!}</p>

	<a class="btn btn-info bg-teal-600" href="{{ action("AccountController@renewToken") }}">{{ trans('messages.renew_token')}}</a>

@endsection
