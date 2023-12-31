@extends('layouts.popup.medium')

@section('content')

    @include('automation2.email._tabs', ['tab' => 'template'])

    <div class="row">
        <div class="col-md-12">
            <div class="sub-section d-flex">
                <div class="mr-auto pr-5">
                    <h5 class="mb-3">{{ trans('messages.campaign.email_content') }}</h5>
                    <p>{{ trans('messages.campaign.email_content.intro') }}</p>

                    <div class="media-left">
                        <div class="main">
                            <label class="font-weight-bold">{{ trans('messages.campaign.html_email') }}</label>
                            <p>{{ trans('messages.campaign.html_email.last_edit', [
                                'date' => Acelle\Library\Tool::formatDateTime($email->updated_at),
                            ]) }}</p>
                            <a href="{{ action('Automation2Controller@templateEdit', [
                                        'uid' => $automation->uid,
                                        'email_uid' => $email->uid,
                                    ]) }}" class="btn btn-secondary mr-1"
                            >
                                {{ trans('messages.campaign.compose_email') }}
                            </a>
                            <a href="{{ action('Automation2Controller@templateRemove', [
                                    'uid' => $automation->uid,
                                    'email_uid' => $email->uid,
                                ]) }}" class="btn btn-outline-primary template-change"
                            >
                                {{ trans('messages.campaign.change_template') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="template-thumb-container">
                    <img class="automation-template-thumb"
                         src="{{ $email->getThumbUrl() }}?v={{ Carbon\Carbon::now() }}"
                    />
                    <a
                            onclick="popupwindow('{{ action('Automation2Controller@templatePreview', [
                            'uid' => $automation->uid,
                            'email_uid' => $email->uid,
                        ]) }}', '{{ $automation->name }}', 800, 800)"
                            href="javascript:;"
                            class="btn btn-info" style="display:none"
                    >
                        {{ trans('messages.automation.template.preview') }}
                    </a>
                </div>
            </div>

            <div class="sub-section">
                <h5 class="mt-5 mb-3">{{ trans('messages.campaign.attachment') }}</h5>
                <p>{{ trans('messages.campaign.attachment.intro') }}</p>

                @include('automation2.email._attachment')
            </div>

            <div class="text-right mt-5">
                <a href="javascript:;" onclick="sidebar.load(); popup.hide()" class="btn btn-inline-secondary mr-1">
                    {{ trans('messages.close') }}
                </a>
                <a href="javascript:;" onclick="popup.load('{{ action('Automation2Controller@emailConfirm', [
                    'uid' => $automation->uid,
                    'email_uid' => $email->uid
                ]) }}')" class="btn btn-secondary">
                    <span class="d-flex align-items-center">
                        <span>{{ trans('messages.automation.email.next_confirm') }}</span> <i class="material-icons">keyboard_arrow_right</i>
                    </span>
                </a>
            </div>
        </div>
    </div>

    <script>
        var builder;

        $('.template-compose').click(function (e) {
            e.preventDefault();

            var url = $(this).attr('href');

            openBuilder(url);
        });

        $('.template-compose-classic').click(function (e) {
            e.preventDefault();

            var url = $(this).attr('href');

            openBuilderClassic(url);
        });

        // Check email content already set if this page accur
        tree.getSelected().setOptions($.extend(tree.getSelected().getOptions(), {template: "true"}));
        tree.getSelected().validate();
        saveData();

        $('.template-change').click(function (e) {
            e.preventDefault();

            var url = $(this).attr('href');
            var confirm = `{{ trans('messages.automation.email.change_template.confirm') }}`;

            var dialog = new Dialog('confirm', {
                message: confirm,
                ok: function (dialog) {
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: {
                            _token: CSRF_TOKEN
                        },
                        statusCode: {
                            // validate error
                            400: function (res) {
                                console.log('Something went wrong!');
                            }
                        },
                        success: function (response) {
                            // Check email content already set if this page accur
                            tree.getSelected().setOptions($.extend(tree.getSelected().getOptions(), {template: "false"}));
                            tree.getSelected().validate();

                            // after remove
                            saveData(function () {
                                // reload
                                popup.load();

                                // notify
                                notify(response.status, '{{ trans('messages.notify.success') }}', response.message);
                            });


                        }
                    });
                },
            });
        });
    </script>
@endsection