<?php
use Acelle\Library\Automation\Evaluate;

$review_options = [
    ['text'=> "1", 'value' => "1"],
    ['text'=> "2", 'value' => "2"],
    ['text'=> "3", 'value' => "3"],
    ['text'=> "4", 'value' => "4"],
    ['text'=> "5", 'value' => "5"],
];
/** @var \Acelle\Model\Automation2 $automation */
$shop = $automation->customer->shopify_shop;
$discount_codes = [];
foreach ($shop->shopify_discount_codes as $code)
    $discount_codes[] = ['text'=> $code->discount_code, 'value' => $code->discount_code];
?>
<h5 class="mb-3">
    {{ trans('messages.automation.action.set_up_your_condition') }}
</h5>
<p class="mb-3">
    {{ trans('messages.automation.action.condition.intro') }}
</p>

<div class="mb-20">
    @include('helpers.form_control', [
        'type' => 'select',
        'class' => '',
        'label' => 'Select criterion',
        'name' => 'type',
        'value' => $element->getOption('type'),
        'help_class' => 'trigger',
        'options' => [
            ['text' => 'Subscriber read an Email', 'value' => \Acelle\Library\Automation\Evaluate::CONDITION_open],
            ['text' => 'Subscriber clicks on a Link', 'value' => \Acelle\Library\Automation\Evaluate::CONDITION_click],
            ['text' => 'Review has image', 'value' => \Acelle\Library\Automation\Evaluate::CONDITION_review_image],
            ['text' => 'Review rating stars', 'value' => \Acelle\Library\Automation\Evaluate::CONDITION_review_stars],
            ['text' => 'Review submitted', 'value' => \Acelle\Library\Automation\Evaluate::CONDITION_review_submitted],
            ['text' => 'Chat rating stars', 'value' => \Acelle\Library\Automation\Evaluate::CONDITION_chat_stars],
            ['text' => 'Discount Coupon Used', 'value' => \Acelle\Library\Automation\Evaluate::CONDITION_discount_coupon_used],
            ['text' => 'Checkout still abandoned', 'value' => \Acelle\Library\Automation\Evaluate::CONDITION_checkout_abandoned],
        ],
        'rules' => [],
    ])
</div>
    
<div class="mb-20" data-condition="<?= Evaluate::CONDITION_open ?>" style="display:none">
    @include('helpers.form_control', [
        'type' => 'select',
        'class' => 'required',
        'label' => 'Which email subscriber reads',
        'name' => 'email',
        'value' => $element->getOption('email'),
        'help_class' => 'trigger',
        'include_blank' => trans('messages.automation.condition.choose_email'),
        'required' => true,
        'options' => $automation->getEmailOptions(),
        'rules' => [],
    ])
</div>
    
<div class="mb-20" data-condition="<?= Evaluate::CONDITION_click ?>" style="display:none">
    @include('helpers.form_control', [
        'type' => 'select',
        'class' => 'required',
        'label' => 'Which Link subscriber clicks',
        'name' => 'email_link',
        'value' => $element->getOption('email_link'),
        'help_class' => 'trigger',
        'options' => $automation->getEmailLinkOptions(),
        'include_blank' => trans('messages.automation.condition.choose_link'),
        'required' => true,
        'rules' => [],
    ])
</div>

<div class="mb-20" data-condition="<?= Evaluate::CONDITION_review_image ?>" style="display:none">
    <p class="mb-3">
        This condition returns true if: <br/>
    </p>
    <ul>
        <li>The automation was triggered by a review, AND</li>
        <li>The reviewer had uploaded images in the review.</li>
    </ul>
</div>

<div class="mb-20" data-condition="<?= Evaluate::CONDITION_review_stars ?>" style="display:none">
    @include('helpers.form_control', [
        'type' => 'select',
        'class' => 'required',
        'label' => 'Greater than or equal to',
        'name' => 'review_stars_gte',
        'value' => $element->getOption('review_stars_gte'),
        'help_class' => 'trigger',
        'options' => $review_options,
        'required' => true,
        'rules' => [],
    ])
    @include('helpers.form_control', [
        'type' => 'select',
        'class' => 'required',
        'label' => 'Less than or equal to',
        'name' => 'review_stars_lte',
        'value' => $element->getOption('review_stars_lte'),
        'help_class' => 'trigger',
        'options' => $review_options,
        'required' => true,
        'rules' => [],
    ])
</div>

<div class="mb-20" data-condition="<?= Evaluate::CONDITION_review_submitted ?>" style="display:none">
    @include('helpers.form_control', [
        'type' => 'select',
        'class' => 'required',
        'label' => 'Select Option',
        'name' => 'review_products_type',
        'value' => $element->getOption('review_products_type'),
        'help_class' => 'trigger',
        'options' => [
            ["text" => "Review submitted for ALL products in the order", "value" => "all"],
            ["text" => "Review submitted for ANY product in the order", "value" => "any"],
        ],
        'required' => true,
        'rules' => [],
    ])
</div>

<div class="mb-20" data-condition="<?= Evaluate::CONDITION_chat_stars ?>" style="display:none">
    @include('helpers.form_control', [
        'type' => 'select',
        'class' => 'required',
        'label' => 'Greater than or equal to',
        'name' => 'chat_stars_gte',
        'value' => $element->getOption('chat_stars_gte'),
        'help_class' => 'trigger',
        'options' => $review_options,
        'required' => true,
        'rules' => [],
    ])
    @include('helpers.form_control', [
        'type' => 'select',
        'class' => 'required',
        'label' => 'Less than or equal to',
        'name' => 'chat_stars_lte',
        'value' => $element->getOption('chat_stars_lte'),
        'help_class' => 'trigger',
        'options' => $review_options,
        'required' => true,
        'rules' => [],
    ])
</div>

<div class="mb-20" data-condition="<?= Evaluate::CONDITION_discount_coupon_used ?>" style="display:none">
    @include('helpers.form_control', [
        'type' => 'select',
        'class' => 'required',
        'label' => 'Discount Coupon',
        'name' => 'discount_coupon',
        'value' => $element->getOption('discount_coupon'),
        'help_class' => 'trigger',
        'options' => $discount_codes,
        'required' => true,
        'rules' => [],
    ])
</div>

<div class="mb-20" data-condition="<?= Evaluate::CONDITION_checkout_abandoned ?>" style="display:none">
    <p class="mb-3">
        This condition returns true if: <br/>
    </p>
    <ul>
        <li>The automation was triggered by an abandoned checkout, AND</li>
        <li>The checkout is still abandoned.</li>
    </ul>
</div>
    
<script>
    function toggleCriterion() {
        var value = $('[name=type]').val();
        
        $('[data-condition]').hide();
        $('[data-condition='+value+']').show();
    }

    // Toggle condition options
    $(document).on('change', '[name=type]', function() {
        toggleCriterion();
    });
    
    toggleCriterion();
</script>