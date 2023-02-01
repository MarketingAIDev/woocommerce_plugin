@extends('layouts.popup.medium')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-3">{{ trans('messages.automation.automation_trigger') }}</h2>
            <p>{{ trans('messages.automation.trigger.intro') }}</p>

            <div class="box-list mt-3">
                <div class="box-list mt-40">
                    @foreach ($automation->getTriggerTypes() as $type)
                        <a class="box-item trigger-select-but {{ $trigger->getOption('key') == $type ? 'current' : '' }}"
                           data-key="{{ $type }}">
                            <h6 class="d-flex align-items-center text-center justify-content-center">
                                @if(in_array($type, \Acelle\Library\Automation\ShopifyAutomationContext::AUTOMATION_TRIGGERS))
                                    <img width="25" height="28" src="/images/logo_shopify.svg" alt="Shopify"/>
                                @elseif(in_array($type, \Acelle\Library\Automation\ShopifyAutomationContext::ADMIN_AUTOMATION_TRIGGERS))
                                    <img width="25" height="28" src="/images/ew_icon.png" alt="Emailwish"/>
                                @else
                                    <i class="material-icons-outlined mr-2">{{ trans('messages.automation.trigger.icon.' . $type) }}</i>
                                @endif
                                {{ trans('messages.automation.trigger.' . $type) }}</h6>
                            <p>{{ trans('messages.automation.trigger.' . $type . '.desc') }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
@endsection
