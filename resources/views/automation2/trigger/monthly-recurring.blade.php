<div class="mb-4">
    <input type="hidden" name="options[type]" value="datetime" />

    <div class="form-group">
        <label>{{ trans('messages.days_of_month') }}<span class="text-danger">*</span></label>
        <div>
            <div class="btn-group day-month-select d-block" role="group" aria-label="Basic example">
                @php
                    $days = $trigger->getOption('days_of_month') ? $trigger->getOption('days_of_month') : []
                @endphp
                @for($i = 1; $i < 32; $i++)
                    <button type="button" class="btn btn-{{ in_array($i, $days) ? 'primary' : 'light' }} mb-1">
                        {{ $i }}
                        <input
                            {{ in_array($i, $days) ? 'checked' : '' }}
                            class="day-month-checkbox hide"
                            type="checkbox" name="options[days_of_month][]" value="{{ $i }}" />
                    </button>
                @endfor
            </div>
        </div>
    </div>

    <script>
        $('.day-month-select button').click(function(e) {
            e.preventDefault();

            if ($(this).find('.day-month-checkbox').is(':checked')) {
                $(this).find('.day-month-checkbox').prop('checked', false);
                $(this).removeClass('btn-info');
                $(this).addClass('btn-light');
            } else {
                $(this).find('.day-month-checkbox').prop('checked', true);
                $(this).addClass('btn-info');
                $(this).removeClass('btn-light');
            }
        });
    </script>
    
    @include('helpers.form_control', [
        'type' => 'time2',
        'name' => 'options[at]',
        'label' => trans('messages.automation.at'),
        'value' => ($trigger->getOption('at') ? $trigger->getOption('at') : toTimeString(\Carbon\Carbon::now())),
        'rules' => $rules,
        'help_class' => 'trigger'
    ])

    @include('helpers.form_control', [
        'type' => 'select',
        'name' => 'timezone',
        'value' => request()->selected_customer->timezone,
        'options' => Tool::getTimezoneSelectOptions(),
        'include_blank' => trans('messages.choose'),
        'disabled' => true
    ])
</div>