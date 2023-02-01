<div class="mb-20">
    @include('helpers.form_control', [
        'type' => 'select',
        'class' => '',
        'label' => 'Select Popup',
        'name' => 'options[popup_uid]',
        'value' => $trigger->getOption('popup_uid'),
        'help_class' => 'trigger',
        'options' => $automation->getPopupOptions(),
        'rules' => $rules,
    ])

</div>