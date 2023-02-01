<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
   <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <!-- CSRF Token -->
      <meta name="csrf-token" content="{{ csrf_token() }}">

      <!-- Fonts -->
      <link href='https://fonts.googleapis.com/css?family=Raleway:400,300,700' rel='stylesheet' type='text/css'>
      <!-- Bootstrap -->
      <link href="{{ asset('public/css/bootstrap.css') }}" rel="stylesheet">
   </head>
   <body style="background:#e74c3c">
	
    <h4 class="text-center" style="margin-top:16px; color: #FFF;">{{ _lang('Service Not Available !') }}</h4>
	
	<script src="{{ asset('public/js/jquery.min.js') }}"></script>
	<script src="{{ asset('public/js/bootstrap.min.js') }}"></script>

</body>
</html>