@extends('layouts.popup.small')

@section('content')
    <h4 class="mt-0 mb-4 d-flex align-items-center">
        <i class="material-icons mr-2">multiline_chart</i>
        <span>{{ trans("messages.used_quota") }}</span>
    </h4>

    <!-- Alert if customer don't have any subscription -->
    @if (is_object(request()->selected_customer) &&
        !is_object(request()->selected_customer->subscription))
        <div class="alert alert-warning mt-20">
            <h4 class="ui-pnotify-title text-nowrap">
            {!! trans('messages.not_have_any_plan_notification', [
                'link' => action('AccountSubscriptionController@index'),
            ]) !!}
            </h4>
            <div style="margin-top: 10px; clear: both; text-align: right; display: none;"></div>
        </div>
    @else
        <div class="row quota_box">
            <div class="col-md-12 mb-4">
                <div class="content-group-sm mt-20">
                    <div class="pull-right text-teal-800 text-semibold">
                        <span class="text-muted">{{ \Acelle\Library\Tool::format_number(request()->selected_customer->getSendingQuotaUsage()) }}/{{ (request()->selected_customer->getSendingQuota() == -1) ? '∞' : \Acelle\Library\Tool::format_number(request()->selected_customer->getSendingQuota()) }}</span>
                        &nbsp;&nbsp;&nbsp;{{ request()->selected_customer->displaySendingQuotaUsage() }}
                    </div>
                    <label class="text-semibold">{{ trans('messages.sending_quota') }}</label>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: {{ request()->selected_customer->getSendingQuotaUsagePercentage() }}%">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 mb-4">
                <div class="content-group-sm">
                    <div class="pull-right text-teal-800 text-semibold">
                        <span class="text-muted">{{ \Acelle\Library\Tool::format_number(request()->selected_customer->listsCount()) }}/{{ \Acelle\Library\Tool::format_number(request()->selected_customer->maxLists()) }}</span>
                        &nbsp;&nbsp;&nbsp;{{ request()->selected_customer->displayListsUsage() }}
                    </div>
                    <label class="text-semibold">{{ trans('messages.list') }}</label>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: {{ request()->selected_customer->listsUsage() }}%">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 mb-4">
                <div class="content-group-sm mt-20">
                    <div class="pull-right text-teal-800 text-semibold">
                        <span class="text-muted progress-xxs">{{ \Acelle\Library\Tool::format_number(request()->selected_customer->campaignsCount()) }}/{{ \Acelle\Library\Tool::format_number(request()->selected_customer->maxCampaigns()) }}</span>
                        &nbsp;&nbsp;&nbsp;{{ request()->selected_customer->displayCampaignsUsage() }}
                    </div>
                    <label class="text-semibold">{{ trans('messages.campaign') }}</label>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: {{ request()->selected_customer->campaignsUsage() }}%">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 mb-4">
                <div class="content-group-sm">
                    <div class="pull-right text-teal-800 text-semibold">
                        <span class="text-muted">{{ \Acelle\Library\Tool::format_number(request()->selected_customer->readCache('SubscriberCount')) }}/{{ \Acelle\Library\Tool::format_number(request()->selected_customer->maxSubscribers()) }}</span>
                        &nbsp;&nbsp;&nbsp;{{ request()->selected_customer->displaySubscribersUsage() }}
                    </div>
                    <label class="text-semibold">{{ trans('messages.subscriber') }}</label>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: {{ request()->selected_customer->readCache('SubscriberUsage') }}%">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12 mb-4">
                <div class="content-group-sm mt-20">
                    <div class="pull-right text-teal-800 text-semibold">
                        <span class="text-muted progress-xxs">{{ \Acelle\Library\Tool::format_number(request()->selected_customer->automationsCount()) }}/{{ \Acelle\Library\Tool::format_number(request()->selected_customer->maxAutomations()) }}</span>
                        &nbsp;&nbsp;&nbsp;{{ request()->selected_customer->displayAutomationsUsage() }}
                    </div>
                    <label class="text-semibold">{{ trans('messages.automation') }}</label>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: {{ request()->selected_customer->automationsUsage() }}%">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12 mb-4">
                <div class="content-group-sm mt-20">
                    <div class="pull-right text-teal-800 text-semibold">
                        <span class="text-muted progress-xxs">{{ \Acelle\Library\Tool::format_number(round(request()->selected_customer->totalUploadSize(),2)) }}/{{ \Acelle\Library\Tool::format_number(request()->selected_customer->maxTotalUploadSize()) }} (MB)</span>
                        &nbsp;&nbsp;&nbsp;{{ request()->selected_customer->totalUploadSizeUsage() }}%
                    </div>
                    <label class="text-semibold">{{ trans('messages.total_upload_size') }}</label>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: {{ request()->selected_customer->totalUploadSizeUsage() }}%">
                        </div>
                    </div>
                </div>
            </div>

            @if (request()->selected_customer->can("create", new Acelle\Model\SendingServer()))
                <div class="col-md-12 mb-4">
                    <div class="content-group-sm">
                        <div class="pull-right text-teal-800 text-semibold">
                            <span class="text-muted">{{ \Acelle\Library\Tool::format_number(request()->selected_customer->sendingServersCount()) }}/{{ \Acelle\Library\Tool::format_number(request()->selected_customer->maxSendingServers()) }}</span>
                            &nbsp;&nbsp;&nbsp;{{ request()->selected_customer->displaySendingServersUsage() }}
                        </div>
                        <label class="text-semibold">{{ trans('messages.sending_server') }}</label>
                        <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: {{ request()->selected_customer->sendingServersUsage() }}%">
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if (request()->selected_customer->can("create", new Acelle\Model\SendingDomain()))
                <div class="col-md-12 mb-4">
                    <div class="content-group-sm">
                        <div class="pull-right text-teal-800 text-semibold">
                            <span class="text-muted">{{ \Acelle\Library\Tool::format_number(request()->selected_customer->sendingDomainsCount()) }}/{{ \Acelle\Library\Tool::format_number(request()->selected_customer->maxSendingDomains()) }}</span>
                            &nbsp;&nbsp;&nbsp;{{ request()->selected_customer->displaySendingDomainsUsage() }}
                        </div>
                        <label class="text-semibold">{{ trans('messages.sending_domain') }}</label>
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: {{ request()->selected_customer->sendingDomainsUsage() }}%">
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if (request()->selected_customer->can("create", new Acelle\Model\EmailVerificationServer()))
                <div class="col-md-12 mb-4">
                    <div class="content-group-sm">
                        <div class="pull-right text-teal-800 text-semibold">
                            <span class="text-muted">{{ \Acelle\Library\Tool::format_number(request()->selected_customer->emailVerificationServersCount()) }}/{{ \Acelle\Library\Tool::format_number(request()->selected_customer->maxEmailVerificationServers()) }}</span>
                            &nbsp;&nbsp;&nbsp;{{ request()->selected_customer->displayEmailVerificationServersUsage() }}
                        </div>
                        <label class="text-semibold">{{ trans('messages.email_verification_server') }}</label>
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: {{ request()->selected_customer->emailVerificationServersUsage() }}%">
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

@endsection