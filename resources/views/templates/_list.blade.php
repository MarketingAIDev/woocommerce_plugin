@if ($templates->count() > 0)
    <table class="table table-box pml-table"
        current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}">
        @foreach ($templates as $key => $template)
            <tr>
                <td width="1%">
                    <div class="text-nowrap">
                        <div class="checkbox inline">
                            <label>
                                <input type="checkbox" class="node styled"
                                    custom-order="{{ $template->custom_order }}"
                                    name="ids[]"
                                    value="{{ $template->uid }}"
                                />
                            </label>
                        </div>
                        @if (request()->sort_order == 'custom_order' && request()->from == 'mine' && empty(request()->keyword))
                            <i data-action="move" class="icon icon-more2 list-drag-button"></i>
                        @endif
                    </div>
                </td>
                <td width="1%">
                    <a href="#"  onclick="popupwindow('{{ action('TemplateController@preview', $template->uid) }}', '{{ $template->name }}', 800, 800)">
                        <img class="template-thumb" width="100" height="120" src="{{ $template->getThumbUrl() }}?v={{ rand(0,10) }}" />
                    </a>
                </td>
                <td>
                    <h5 class="no-margin text-bold">
                        <a class="kq_search" href="#" onclick="popupwindow('{{ action('TemplateController@preview', $template->uid) }}', '{{ $template->name }}', 800, 800)">
                            {{ $template->name }}
                        </a>
                    </h5>
                    <span class="text-muted">
                        {!! is_object($template->admin) ? '<i class="icon-user-tie"></i>' . $template->admin->displayName() : '' !!}
                        {!! is_object($template->customer) ? '<i class="icon-user"></i>' . $template->customer->displayName() : '' !!}
                    </span>
                    <br>
                    <span class="text-muted">{{ trans('messages.updated_at') }}: {{ Tool::formatDateTime($template->created_at) }}</span>
                </td>

                <td>
                    <div class="single-stat-box pull-left">
                        <span class="no-margin stat-num">{{ trans('messages.template_type_' . $template->source) }}</span>
                        <br>
                        <span class="text-muted text-nowrap">{{ trans('messages.type') }}</span>
                    </div>
                </td>

                <td class="text-right">
                    @if (request()->selected_customer->can('update', $template))
                        <a href="{{ action('TemplateController@edit', $template->uid) }}" type="button" class="btn bg-grey btn-icon template-compose-classic">
                            {{ trans('messages.template.classic_builder') }}
                        </a>
                    @endif
                    @if (request()->selected_customer->can('preview', $template) ||
                        request()->selected_customer->can('copy', $template) ||
                        request()->selected_customer->can('delete', $template) ||
                        request()->selected_customer->can('update', $template))
                        <div class="btn-group">
                            <button type="button" class="btn dropdown-toggle" data-toggle="dropdown"><span class="caret ml-0"></span></button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                @if (request()->selected_customer->can('preview', $template))
                                    <li><a href="#preview" onclick="popupwindow('{{ action('TemplateController@preview', $template->uid) }}', '{{ $template->name }}', 800, 800)"><i class="icon-zoomin3"></i> {{ trans("messages.preview") }}</a></li>
                                @endif
                                @if (request()->selected_customer->can('update', $template))
                                    <li>
                                        <a class="upload-thumb-button" href="{{ action('TemplateController@updateThumb', $template->uid) }}">
                                            <i class="icon-file-picture"></i> {{ trans("messages.template.upload_thumbnail") }}
                                        </a>
                                    </li>
                                @endif
                                @if (request()->selected_customer->can('copy', $template))
                                    <li>
                                        <a
                                            href="{{ action('TemplateController@copy', $template->uid) }}"
                                            type="button"
                                            class="modal_link"
                                            data-method="GET"
                                        >
                                            <i class="icon-copy4"></i> {{ trans("messages.template.copy") }}
                                        </a>
                                    </li>
                                @endif
                                @if (request()->selected_customer->can('delete', $template))
                                    <li><a delete-confirm="{{ trans('messages.delete_templates_confirm') }}" href="{{ action('TemplateController@delete', ["uids" => $template->uid]) }}"><i class="icon-trash"></i> {{ trans("messages.delete") }}</a></li>
                                @endif
                            </ul>
                        </div>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>
    @include('elements/_per_page_select', ["items" => $templates])
    {{ $templates->links() }}

    <script>
		var thumbPopup = new Popup();

        $('.upload-thumb-button').click(function(e) {
            e.preventDefault();

            var url = $(this).attr('href');

            thumbPopup.load(url);
        });
    </script>

@elseif (!empty(request()->keyword))
    <div class="empty-list">
        <i class="icon-magazine"></i>
        <span class="line-1">
            {{ trans('messages.no_search_result') }}
        </span>
    </div>
@else
    <div class="empty-list">
        <i class="icon-magazine"></i>
        <span class="line-1">
            {{ trans('messages.template_empty_line_1') }}
        </span>
    </div>
@endif
