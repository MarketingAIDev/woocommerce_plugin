@include('automation2._info')

@include('automation2._tabs', ['tab' => 'settings'])

<form class="action-edit" action="{{ action("Automation2Controller@actionEdit", ['uid' => $automation->uid, 'key' => $key]) }}" method="POST" class="form-validate-jqueryz">
    {{ csrf_field() }}
    
    <input type="hidden" name="key" value="{{ $key }}" />
    
    @if(View::exists('automation2.action.' . $key))
        @include('automation2.action.' . $key)
    @endif
    
    <div class="trigger-action mt-2">    
        <button class="btn btn-secondary action-save-change mr-1"
            data-url="{{ action('Automation2Controller@triggerSelect', ['uid' => $automation->uid, 'key' => $key]) }}"
        >
                {{ trans('messages.automation.action.save_change') }}
        </button>
    </div>
</form>

<div class="mt-3">
    <a href="javascript:;" data-confirm="{{ trans('messages.automation.action.delete.confirm') }}" class="btn btn-danger action-delete">
        <i class='lnr lnr-trash mr-2'></i> {{ trans('messages.automation.delete_this_action') }}
    </a>
</div>
    
<script>
    $('.action-edit').submit(function(e) {
        e.preventDefault();
        
        var form = $(this);
        var data = form.serialize();
        var url = form.attr('action');
        
        sidebar.loading();
        
        $.ajax({
            url: url,
            type: 'POST',
            data: data,
        }).always(function(response) {
            // set node title
            tree.getSelected().setTitle(response.title);
            // merge options with reponse options
            tree.getSelected().setOptions($.extend(tree.getSelected().getOptions(), response.options));
            tree.getSelected().setOptions($.extend(tree.getSelected().getOptions(), {init: true}));
            tree.getSelected().validate();
            // save tree
            saveData(function() {
                // notify
                notify('success', '{{ trans('messages.notify.success') }}', response.message);
                
                // reload sidebar
                sidebar.load();
            });
        });        
    });
    
    $('.action-delete').click(function(e) {
        e.preventDefault();
        
        var confirm = $(this).attr('data-confirm');
        var dialog = new Dialog('confirm', {
            message: confirm,
            ok: function(dialog) {
                // remove current node
                tree.getSelected().remove();
                
                // save tree
                saveData(function() {
                    // notify
                    notify('success', '{{ trans('messages.notify.success') }}', '{{ trans('messages.automation.action.deteled') }}');
                    
                    // load default sidebar
                    sidebar.load('{{ action('Automation2Controller@settings', $automation->uid) }}');
                });
            },
        });
    });
</script>