<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
    <style>
        @import url('//fonts.googleapis.com/css?family=Open+Sans:300,400,600,800'); /* default font */

        body {
            margin: 0;
            overflow-x: hidden;
            overflow-y: auto;
            font-family: Open Sans, sans-serif
        }

        .container {
        }

        .container > div {
            text-align: center;
            font-size: 24px;
            cursor: pointer;
            margin: 0;
            display: inline-block;
            float: left;
            width: 25%;
            height: 80px;
            line-height: 80px;
            border: #eee 1px solid;
            box-sizing: border-box;
        }

        .clearfix:before, .clearfix:after {
            content: " ";
            display: table;
        }

        .clearfix:after {
            clear: both;
        }

        .clearfix {
            zoom: 1;
        }

        button {
            width: 55px;
            height: 50px;
            line-height: 1;
            display: inline-block;
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            cursor: pointer;
            background-color: #fff;
            color: #4a4a4a;
            border: 1px solid transparent;
            font-family: sans-serif;
            letter-spacing: 1px;
            font-size: 12px;
            font-weight: normal;
            text-transform: uppercase;
            text-align: center;
            position: relative;
            border-radius: 0;
            transition: all ease 0.3s
        }

        button:focus {
            outline: none;
        }

        button.classic-primary {
            display: inline-block;
            width: auto;
            height: 50px;
            padding-left: 10px;
            padding-right: 10px;
            min-width: 135px;
            background: #f7f7f7;
        }

        button.classic-secondary {
            display: inline-block;
            width: auto;
            height: 50px;
            padding-left: 10px;
            padding-right: 10px;
            background: transparent;
        }

        select {
            font-size: 14px;
            letter-spacing: 1px;
            height: 50px;
            line-height: 1.7;
            color: #454545;
            border-radius: 0;
            border: none;
            background-color: #eee;
            width: auto;
            display: inline-block;
            background-image: none;
            padding: 0 5px;
        }

        select:focus {
            outline: none
        }

        /*
            Tabs
        */
        .is-tabs.simple {
            white-space: nowrap;
            padding: 20px;
            padding-bottom: 5px;
            padding-top: 10px;
            box-sizing: border-box;
            font-family: sans-serif;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 2px;
            background: #f9f9f9;
        }

        .is-tabs.simple a {
            display: inline-block;
            float: left;
            padding: 3px 3px 0;
            color: #4a4a4a;
            border-bottom: transparent 1px solid;

            margin: 0 16px 16px 0;
            text-decoration: none;
            transition: box-shadow ease 0.3s;
        }

        .is-tabs.simple a:hover {

        }

        .is-tabs.simple a.active {
            background: transparent;
            box-shadow: none;
            cursor: default;
            border-bottom: rgba(103, 103, 103, 0.72) 1px solid;
        }

        .is-tab-content.simple {
            display: none;
            padding: 20px;
        }

        /* Overide */
        .is-tabs.simple {
            border-bottom: #ececec 1px solid;
            padding-bottom: 15px;
        }

        .is-tabs.simple a {
            margin: 0 16px 0 0;
            line-height: 1.8
        }

        .is-tab-content {
            border: none;
            padding: 20px;
            box-sizing: border-box;
            display: none;
            height: 100%;
            position: absolute;
            width: 100%;
            box-sizing: border-box;
            top: 0px;
        }


        .is-button-remove {
            position: absolute;
            top: 0px;
            right: 0px;
            width: 20px;
            height: 20px;
            background: rgba(95, 94, 94, 0.26);
            color: #fff;
            line-height: 20px;
            text-align: center;
            font-size: 12px;
            cursor: pointer;
            display: none;
        }

        #divMyButtonList {
            height: 100%;
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
            padding: 20px;
            border-top: transparent 90px solid;
            box-sizing: border-box;
            overflow: hidden;
            overflow-y: auto;
        }

        #divMyButtonList a {
            position: relative
        }

        #divMyButtonList a.active .is-button-remove {
            display: block;
        }

        #divButtonTemplates {
            height: 100%;
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
            padding: 20px;
            box-sizing: border-box;
            overflow: hidden;
            overflow-y: auto;
        }

        /* Templates */
        #divMyButtonList > a, #divButtonTemplates > a {
            margin: 0px 13px 15px 0;
            padding: 10px 50px;
            font-size: 1rem;
            line-height: 2rem;
            border-radius: 0;
            letter-spacing: 3px;
            display: inline-block;
            font-weight: normal;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            background-image: none;
            border: 1px solid transparent;
            white-space: nowrap;
            -webkit-transition: all 0.16s ease;
            transition: all 0.16s ease;
            text-decoration: none;
            color: #000;
        }

        button.is-btn-color {
            width: 50px;
            height: 50px;
            padding: 0;
            background: transparent;
            border: rgba(0, 0, 0, 0.09) 1px solid;
        }
    </style>

    <!-- Bootstrap -->
    <link href="{{ URL::asset('assets2/lib/bootstrap-4.4.0/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ URL::asset('assets2/lib/select2-4.0.12/css/select2.min.css') }}" rel="stylesheet"/>

    <link rel="stylesheet" href="{{ URL::asset('assets/css/icons/linearicons/style.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('assets/timepicker/jquery.timepicker.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('assets/datepicker/dist/datepicker.min.css') }}">
    <link href="{{ URL::asset('assets/css/icons/icomoon/styles.css') }}" rel="stylesheet" type="text/css">

    <link href="{{ URL::asset('css/loading.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ URL::asset('css/popup.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ URL::asset('css/autofill.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ URL::asset('assets2/css/dialog.css') }}" rel="stylesheet" type="text/css">

    <!-- Google icons -->
    <link href="{{ URL::asset('assets/css/material_icons.css') }}" rel="stylesheet">

    <!-- Core JS files -->
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/loaders/pace.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/bootstrap.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/loaders/blockui.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/ui/nicescroll.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/ui/drilldown.js') }}"></script>
    <script type="text/javascript"
            src="{{ URL::asset('assets/js/plugins/forms/validation/validate.min.js') }}"></script>
    <!-- /core JS files -->

    <!-- Theme JS files -->
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/switchery.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
    <script type="text/javascript"
            src="{{ URL::asset('assets/js/plugins/forms/selects/bootstrap_multiselect.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/ui/moment/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/pickers/daterangepicker.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/notifications/bootbox.min.js') }}"></script>
    <script type="text/javascript"
            src="{{ URL::asset('assets/js/plugins/notifications/sweet_alert.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/switch.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/pickers/pickadate/picker.js') }}"></script>
    <script type="text/javascript"
            src="{{ URL::asset('assets/js/plugins/pickers/pickadate/picker.date.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/jquery.numeric.min.js') }}"></script>
    <link rel="stylesheet" href="{{ URL::asset('js/scrollbar/jquery.mCustomScrollbar.css') }}">
    <script type="text/javascript"
            src="{{ URL::asset('js/scrollbar/jquery.mCustomScrollbar.concat.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/jquery.cookie.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('rangeslider/bootstrap-slider.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('bootstrap3-editable/js/bootstrap-editable.min.js') }}"></script>

    <!-- Tooltip -->
    <link rel="stylesheet" href="{{ URL::asset('assets/tooltipster/css/tooltipster.bundle.min.css') }}">
    <link rel="stylesheet"
          href="{{ URL::asset('assets/tooltipster/css/plugins/tooltipster/sideTip/themes/tooltipster-sideTip-light.min.css') }}">
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
    <script>
        var LANG_CODE = 'en-US';
    </script>
</head>
<body>
@yield('content')
</body>
</html>
