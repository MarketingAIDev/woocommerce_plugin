@extends('layouts.popup.small')

@section('content')
    <div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-10">
            <h2>{{ trans('messages.campaign.template.builder.select') }}</h2>
            <p>{{ trans('messages.campaign.template.builder.select.intro') }}</p>

            <a href="{{ action('CampaignController@templateEdit', $campaign->uid) }}"
               class="btn btn-info mr-10 template-compose">
                {{ trans('messages.campaign.email_builder_pro') }}
            </a>
        </div>
    </div>
@endsection