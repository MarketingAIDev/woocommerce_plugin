<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
   <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      
	  @if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
	     <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests" />
	  @endif

	  <!-- CSRF Token -->
      <meta name="csrf-token" content="{{ csrf_token() }}">

      <!-- Fonts -->
      <link href='https://fonts.googleapis.com/css?family=Raleway:400,300,700' rel='stylesheet' type='text/css'>
      <!-- Bootstrap -->
      <link href="{{ asset('css/bootstrap.css') }}" rel="stylesheet">
      <!-- Include roboto.css to use the Roboto web font, material.css to include the theme and ripples.css to style the ripple effect -->
      <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css" rel="stylesheet">
      <link href="{{ asset('css/style.css?v='.time()) }}" rel="stylesheet">
      <link href="{{ asset('css/widget.css?v='.time()) }}" rel="stylesheet">

	<script type="text/javascript">
	  var _url = "{{ asset('/') }}";
	  var u_s = "{{ get_option('max_upload_size') }}";
	  var vendor_token = "{{ $token }}";
	</script>
	
	<style type="text/css">
	   .panel-default>.panel-heading,.btn-success{
		   background-color: {{ get_company_option('secondary_color','#263238') }} !important;
		   color: {{ get_company_option('label_color','#FFFFFF') }};
	   }
	   .btn-info{
		   background-color: {{ get_company_option('primary_color','#007bff') }} !important;
		   color: {{ get_company_option('label_color','#FFFFFF') }};
	       border: none;
		   border-radius:0px;
	   }
	   .chat-expand{
			color: {{ get_company_option('label_color','#FFFFFF') }};
		}
		.panel{
			border: 1px solid {{ get_company_option('secondary_color','#263238') }};
		}
	</style>
   </head>
   <body>
   <?php //var_dump(\Session::get("guest_id")); ?>
	<div class="panel panel-default tricky_chat_widget">
		<div class="panel-heading">
		    {!! 'Live Chat' !!}
		    <a href="#" class="pull-right chat-expand"><i class="fa fa-angle-up" aria-hidden="true"></i></a>
			
			<div class="dropdown pull-right">
			  <button class="widget-chat-settings dropdown-toggle" type="button" data-toggle="dropdown"><i class="fa fa-cog" aria-hidden="true"></i></button>
			  <ul class="dropdown-menu widget-menu">
				<li><a href="#" id="chat_fullscreen"><i class="fa fa-arrows-alt" aria-hidden="true"></i>&nbsp;&nbsp;{{ 'Toggle Full Screen' }}</a></li>
				<li><a href="#" id="chat_mute_sound"><i class="fa fa-bell-slash-o" aria-hidden="true"></i> {{ 'Mute Sound' }}</a></li>
				<li><a href="#" id="chat_end"><i class="fa fa-power-off" aria-hidden="true"></i>&nbsp;&nbsp;{{ 'End Chat' }}</a></li>
			  </ul>
			</div>
		</div>
		<div class="panel-body">
		   @if(\Session::get("guest_id")=="")
			   
		    <!--if Offline mode is enabled -->
		    @if(get_company_option('offline_mode','no') == 'yes')
			   <div id="widget-offline-area">
				  <div class="col-md-12">
				    <div class="alert alert-danger text-center">
					   <p>{{ get_company_option('offline_text') }}</p>
					</div>
					@if (\Session::has('success'))
					  <div class="alert alert-success text-center">
				        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
						<p>{{ \Session::get('success') }}</p>
					  </div>
					@endif
					
					 @if ($errors->any())
						 <div class="alert alert-danger">
							 <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
							 @foreach ($errors->all() as $error)
								 <p>{{$error}}</p>
							 @endforeach
						 </div>
					 @endif
					
					
					<form id="send_offline_message" method="POST" action="{{ url('guest/send_offline_message') }}" autocomplete="off" novalidate>
					  {{ csrf_field() }}
					  <div class="form-group">
						<label for="name">{{ 'Name' }}</label>
						<input type="text" name="name" class="form-control" id="name" required>
					  </div>
					  <input type="hidden" name="url" value="{{ $_GET['url'] }}">
					  <input type="hidden" name="vendor_token" value="{{ $_GET['token'] }}">
					  
					  @if(get_company_option('mobile_field') == 'yes')
						  <div class="form-group">
							<label for="email">{{ 'Mobile' }} *</label>
							<input type="text" name="mobile" class="form-control" id="mobile" required>
						  </div>
					  @endif
					  
					  <div class="form-group">
						<label for="email">{{ 'Email' }} *</label>
						<input type="email" name="email" class="form-control" id="email" required>
					  </div>
					  
					  <div class="form-group">
						<label for="email">{{ 'Message' }} *</label>
						<textarea name="message" class="form-control" id="message" required></textarea>
					  </div>
					  
					  <button type="submit" class="btn btn-success btn-start-chat btn-block">{{ 'Send Message' }}</button>
					</form>
				  </div>
			   </div>
		    
			@else
				<div id="chat-login-area">
				  <div class="col-md-12">
					<form id="guest-login-form" method="POST" action="{{ url('guest/store_guest_user') }}" autocomplete="off" novalidate>
					  {{ csrf_field() }}
					  @if(get_company_option('allow_department') == "yes")
						  <div class="form-group">
							<label for="name">{{ 'Department' }} *</label>
							<select class="form-control select2" name="department" id="department" required>
							   <option value="">{{ '- Select -' }}</option>
							   {{ create_option('departments','id','department','',array("company_id="=>$_GET['token'])) }}
							</select>
						  </div>
					  @endif
					  <div class="form-group">
						<label for="name">{{ 'Name' }} *</label>
						<input type="text" name="name" class="form-control" id="name" required>
					  </div>
					  
					  <input type="hidden" name="url" value="{{ $_GET['url'] }}">
					  <input type="hidden" name="vendor_token" value="{{ $_GET['token'] }}">
					  
					  @if(get_company_option('mobile_field') == 'yes')
						  <div class="form-group">
							<label for="email">{{ 'Mobile' }} *</label>
							<input type="text" name="mobile" class="form-control" id="mobile" required>
						  </div>
					  @endif
					  
					  <div class="form-group">
						<label for="email">{{ 'Email' }} *</label>
						<input type="email" name="email" class="form-control" id="email" required>
					  </div>
					  <button type="submit" class="btn btn-success btn-start-chat btn-block">{{ 'Start Chat' }}</button>
					</form>
				  </div>
			   </div>
			@endif
		   
			   
		   @else
			   <div id="tricky-chat-box">
				   <!--new_message_head-->
				   <div class="chat">
					  <div class="chat_area">
						  <ul class="chat-history">
  
						  </ul>
						  <p class="typing-status" style="display:none"><b>{{ 'typing' }} ...</b></p>
						</div>
				   </div>
				   <!--chat_area-->
				   <div class="message_write">
					  <div class="chat-input-box" contentEditable="true"></div>
					  <div class="clearfix"></div>
					  <div class="emboji-container">
						{!! load_emboji() !!}
					  </div>
					  <div class="chat_buttons">
						 <a href="#" class="btn-send pull-left btn btn-success btn-sm">{{ 'Send' }}</a>
						 <a href="#" class="btn-emboji pull-right btn btn-info btn-sm">{{ 'Emoji' }}</a>
						 <input type="hidden" name="l_id" id="l_id">
						 <input type="hidden" name="name" id="name" value="{{ \Session::get('guest_name') }}">
						 @if(get_company_option("file_sharing") == "yes")
						 <input type="file" name="file" id="file" style="display:none">
						 <a href="#" class="btn-fileupload pull-right btn btn-info btn-sm upload_btn"><i class="fa fa-cloud-upload" aria-hidden="true"></i> {{ 'Add File' }}</a>
					     @endif
					  </div>
				   </div>
			   </div>
		   @endif
		   <p class="text-center" style="font-size:12px;">{!! get_option('powered_by','Powered By TrickyCode') !!}</p>
		</div>
	 </div>


<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/bootstrap.min.js') }}"></script>
<script src="{{ asset('js/moment.min.js') }}"></script>
<script src="{{ asset('js/toastr.js') }}"></script>
<script src="{{ asset('js/widget.js?v='.time()) }}"></script>
<script type="text/javascript">	
	updateMessage();
	
	setInterval(function(){ 
		updateUserActivity('{{ csrf_token() }}');
	}, 60000);
	
	setInterval(function(){ 
		updateMessage();
	}, 6000);
	
	function playSound(){
		if(localStorage.getItem("mute") !="yes"){
			var file = $(this).find(':selected').data("audio");
			var audio = document.createElement('audio');
			  audio.style.display = "none";
			  audio.src = "{{ asset('sounds/default.mp3') }}"
			  audio.autoplay = true;
			  audio.onended = function(){
			  audio.remove();
			};
			document.body.appendChild(audio);
		}
	}
</script>

</body>
</html>