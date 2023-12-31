<!DOCTYPE html>
<html lang="en">
<head>
	<title>@yield('title') - {{ \Acelle\Model\Setting::get("site_name") }}</title>

	@include('layouts._favicon')

	@include('layouts._head')
	
	<!-- Bootstrap -->
	<link href="{{ URL::asset('assets2/lib/bootstrap-4.4.0/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
	<link href="{{ URL::asset('assets2/lib/select2-4.0.12/css/select2.min.css') }}" rel="stylesheet" />
	
	<link rel="stylesheet" href="{{ URL::asset('assets/css/icons/linearicons/style.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('assets/timepicker/jquery.timepicker.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('assets/datepicker/dist/datepicker.min.css') }}">
	<link href="{{ URL::asset('assets/css/icons/icomoon/styles.css') }}" rel="stylesheet" type="text/css">
	
	<link href="{{ URL::asset('css/loading.css') }}" rel="stylesheet" type="text/css">
	<link href="{{ URL::asset('css/popup.css') }}" rel="stylesheet" type="text/css">
	<link href="{{ URL::asset('css/autofill.css') }}" rel="stylesheet" type="text/css">
	<link href="{{ URL::asset('assets2/css/dialog.css') }}" rel="stylesheet" type="text/css">
	
	<!-- Google icons -->
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    
	
	
	<!-- Core JS files -->
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/loaders/pace.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/bootstrap.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/loaders/blockui.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/ui/nicescroll.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/ui/drilldown.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/validation/validate.min.js') }}"></script>
	<!-- /core JS files -->

	<!-- Theme JS files -->
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/switchery.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/selects/bootstrap_multiselect.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/ui/moment/moment.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/pickers/daterangepicker.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/notifications/bootbox.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/notifications/sweet_alert.min.js') }}"></script>		
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/switch.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/pickers/pickadate/picker.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/pickers/pickadate/picker.date.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/jquery.numeric.min.js') }}"></script>
    <link rel="stylesheet" href="{{ URL::asset('js/scrollbar/jquery.mCustomScrollbar.css') }}">
    <script type="text/javascript" src="{{ URL::asset('js/scrollbar/jquery.mCustomScrollbar.concat.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/jquery.cookie.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('rangeslider/bootstrap-slider.js') }}"></script>	
	<script type="text/javascript" src="{{ URL::asset('bootstrap3-editable/js/bootstrap-editable.min.js') }}"></script>		

	<!-- Tooltip -->
	<link rel="stylesheet" href="{{ URL::asset('assets/tooltipster/css/tooltipster.bundle.min.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('assets/tooltipster/css/plugins/tooltipster/sideTip/themes/tooltipster-sideTip-light.min.css') }}">
	<script type="text/javascript" src="{{ URL::asset('assets/tooltipster/js/tooltipster.bundle.min.js') }}"></script>		

	<script type="text/javascript" src="{{ URL::asset('assets/timepicker/jquery.timepicker.js') }}"></script>		
	<script type="text/javascript" src="{{ URL::asset('assets/datepicker/dist/datepicker.min.js') }}"></script>		
	
	<!-- Select2 -->
	<script src="{{ URL::asset('assets2/lib/select2-4.0.12/js/select2.min.js') }}"></script>
	<!-- PNotify -->
	<link href="{{ URL::asset('assets2/lib/pnotify-4.0.0/PNotifyBrightTheme.css') }}" rel="stylesheet" type="text/css">
	<script src="{{ URL::asset('assets2/lib/pnotify-4.0.0/iife/PNotify.js') }}"></script>
	<script src="{{ URL::asset('assets2/lib/pnotify-4.0.0/iife/PNotifyButtons.js') }}"></script>
	<script src="{{ URL::asset('assets2/lib/nonblockjs/NonBlock.js') }}"></script>
	<script>
		PNotify.defaults.styling = 'bootstrap4';
	</script>
		
	<!-- Diagram -->
	<script src="{{ URL::asset('assets2/lib/automation.js') }}"></script>
		
	<!-- App -->
	<link href="{{ URL::asset('assets2/css/app.css') }}" rel="stylesheet" type="text/css">
	
	<!-- Dropzone -->
	<script type="text/javascript" src="{{ URL::asset('assets2/lib/dropzone/dropzone.js') }}"></script>
	<link href="{{ URL::asset('assets2/lib/dropzone/dropzone.css') }}" rel="stylesheet" type="text/css">
	
	<!-- Ajax box -->
	<script type="text/javascript" src="{{ URL::asset('assets2/js/box.js') }}"></script>
		
	<!-- Scrollbar -->
	<script type="text/javascript" src="{{ URL::asset('assets2/lib/scrollbar/jquery.scrollbar.min.js') }}"></script>
	<link href="{{ URL::asset('assets2/lib/scrollbar/jquery.scrollbar.css') }}" rel="stylesheet" type="text/css">

	@yield('page_script')
	
	<script type="text/javascript" src="{{ URL::asset('js/autofill.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/select-custom.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/modal.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets2/js/app.js') }}?v={{ app_version() }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/mc_modal.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/iframe_modal.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/mc.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/popup.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets2/js/dialog.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets2/js/list.js') }}"></script>
	<!-- /theme JS files -->

	@include('layouts._script_vars')
	@include('layouts._menu_script')

	<!-- display flash message -->
	@include('common.flash')

	
	<!-- Custom langue -->
	<script>
		var LANG_CODE = 'en-US';
	</script>
	@if (request()->selected_customer->getLanguageCodeFull())
		<script t	ype="text/javascript" src="{{ URL::asset('assets/datepicker/i18n/datepicker.' . request()->selected_customer->getLanguageCodeFull() . '.js') }}"></script>
		<script>
			LANG_CODE = '{{ request()->selected_customer->getLanguageCodeFull() }}';
		</script>
	@endif

	<script>
		$.cookie('last_language_code', '{{ request()->selected_customer->getLanguageCode() }}');
	</script>

</head>

<body class="" style="overflow:hidden">

	<!-- display flash message -->
	@include('common.errors')

	<!-- main inner content -->
	@yield('content')
    
</body>
</html>
