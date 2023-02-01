<table class="table table-trans tbody-white" class="table-layout:fixed">
    <thead>
        <tr>
            <th style="width:10%" class="trans-upcase text-semibold">{{ trans('messages.type') }}</th>
            <th style="width:30%" class="trans-upcase text-semibold">{{ trans('messages.host') }}</th>
            <th class="trans-upcase text-semibold">{{ trans('messages.value') }}</th>
        </tr>
    </thead>
    <tbody class="bg-white">
        <tr>
            <td>
                <span class="text-muted2 list-status pull-left">
                    <span class="label label-flat bg-pending square-tag">TXT</span>
                </span>
            </td>
            <td>{{ $server->getDKIMRecords()[0] }}</td>
            <td>
                <textarea style="width:100%;border:0;height:100px;resize:none;">{{ $server->getDKIMRecords()[1] }}</textarea>
            </td>
        </tr>
    </tbody>
</table>
