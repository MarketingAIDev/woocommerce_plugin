                        @if ($items->count() > 0)
							<table class="table table-box pml-table table-log"
                                current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}"
                            >
								<tr>
									<th>{{ trans('messages.recipient') }}</th>
									<th>{{ trans('messages.status') }}</th>
									<th>{{ trans('messages.campaign') }}</th>
									<th>{{ trans('messages.sending_server') }}</th>
									<th>{{ trans('messages.created_at') }}</th>
								</tr>
								@foreach ($items as $key => $item)
									<tr>
										<td>
											<span class="no-margin kq_search">{{ $item->subscriber ? $item->subscriber->email : "[deleted]"}}</span>
											<span class="text-muted second-line-mobile">{{ trans('messages.recipient') }}</span>
										</td>
										<td>
											<span class="no-margin">
												<span data-popup="tooltip" title="{{ $item->error }}" class="label label-flat bg-{{ $item->status }} kq_search">{{ trans('messages.tracking_log_status_' . $item->status) }}</span>
											</span>
											<span class="text-muted second-line-mobile">{{ trans('messages.status') }}</span>
										</td>
										<td>
											<span class="no-margin kq_search">{{ $item->campaign ? $item->campaign->name : "[deleted]" }}</span>
											<span class="text-muted second-line-mobile">{{ trans('messages.campaign') }}</span>
										</td>
										<td>
											<span class="no-margin kq_search">{{ $item->sendingServer ? $item->sendingServer->name : "[deleted]"}}</span>
											<span class="text-muted second-line-mobile">{{ trans('messages.sending_server') }}</span>
										</td>
										<td>
											<span class="no-margin kq_search">{{ Tool::formatDateTime($item->created_at) }}</span>
											<span class="text-muted second-line-mobile">{{ trans('messages.created_at') }}</span>
										</td>
									</tr>
								@endforeach
							</table>
              @include('elements/_per_page_select')
							{{ $items->links() }}
						@elseif (!empty(request()->keyword) || !empty(request()->filters["campaign_uid"]))
							<div class="empty-list">
								<i class="icon-file-text2"></i>
								<span class="line-1">
									{{ trans('messages.no_search_result') }}
								</span>
							</div>
						@else
							<div class="empty-list">
								<i class="icon-file-text2"></i>
								<span class="line-1">
									{{ trans('messages.log_empty_line_1') }}
								</span>
							</div>
						@endif