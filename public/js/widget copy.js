$(function() {
	$(document).on('click','.chat-expand,.panel-heading',function(){
		window.parent.postMessage('show_hide', '*');
		return false;
	});
	
	$(document).on('click','#chat_fullscreen',function(){
		$('.dropdown.open').removeClass('open');
		window.parent.postMessage('show_fullscreen', '*');
		return false;
	});
	
	$(document).on('click','#chat_mute_sound',function(){
		if(localStorage.getItem("mute") =="yes"){
			localStorage.removeItem("mute");
			$("#chat_mute_sound").css("text-decoration","none");
			
		}else{
			localStorage.setItem("mute", "yes");
			$("#chat_mute_sound").css("text-decoration","line-through");	
		}
		$('.dropdown.open').removeClass('open');
		return false;
	});
	
	//Emboji
	$(document).on('click','.btn-emboji',function(){
		$(".emboji-container").fadeToggle();
		return false;
	});
	
	$(document).on('click','.emboji',function(){
		$(".chat-input-box").append($(this).html());
		$(".emboji-container").fadeOut(100);
		return false;
	});
	
	//File Upload
	$(document).on('click','.btn-fileupload',function(){
		$("#file").click();
		return false;
	});
	
	$(document).on('change','#file',function(){
		$(".btn-fileupload").prop("disabled",true);
		readChatFileURL(this);
	});
	
	//Send Message
	$(document).on('click','.btn-send',function(){
		$.ajax({
			method: "POST",
			url: _url+"guest/send_message",
			data: {_token: $('meta[name="csrf-token"]').attr('content'), message: $(".chat-input-box").html()},
			success: function(data){
			  var json = JSON.parse(data); 
			  if(json['result'] == 'send'){
				  send_message(json['message']);
			  }			
			}
		});
		
		return false;
	});
	
	$(document).on('keypress','.chat-input-box',function(e){
		if(e.which == 13) {
			$.ajax({
				method: "POST",
				url: _url+"guest/send_message",
				data: {_token: $('meta[name="csrf-token"]').attr('content'), message: $(".chat-input-box").html()},
				success: function(data){
				  var json = JSON.parse(data); 
				  if(json['result'] == 'send'){
					  send_message(json['message']);
				  }		
				},
				error: function (request, status, error) {
					console.log(request.responseText);
				}
			});
			return false;
		}
	});
	
	$('.chat-input-box').keydown(function (e) {
	  if (e.ctrlKey && e.keyCode == 13) {
		 if($(".chat-input-box").html() == ''){
		     $(".chat-input-box").append('<div><br></div><br>').focusEnd();
		 }else{
			 $(".chat-input-box").append('<div><br></div>').focusEnd();
		 }
	  }
	});
	
	
	//End Chat
	$(document).on('click','#chat_end',function(){
		chat_end();
		$('.dropdown.open').removeClass('open');
		return false;
	});
	
	
	if(localStorage.getItem("mute") =="yes"){
		$("#chat_mute_sound").css("text-decoration","line-through");
	}
});

var end_chat = false;
var is_typing = 0;

$(window).load(function() {	
	if($(".chat_area").length){
	    $(".chat_area").stop().animate({ scrollTop: $(".chat_area")[0].scrollHeight}, 500);
	}
});

// Mechanism for typing status
$(".chat-input-box").keypress( function() {
   if($(this).html() !=""){
	  is_typing = 1; 
   }
});

$(".chat-input-box").blur( function() {
   is_typing = 0;
});

updateUserActivity = function (token) {
	if(end_chat == true){
		return;
	}
	
	if($("#tricky-chat-box").length){	
		$.ajax({
			method: "POST",
			url: _url+"guest/update_user_activity",
			data: {_token: token, typing:is_typing},
			success: function(data){
				var json = JSON.parse(data);
				if(json['status'] == "chat_end"){
					//Chat End By Admin
					chat_end();	
				}else{
					var chat_request = json['chat_request'];
					var operator_is_typing = chat_request['operator_is_typing'];
					if(operator_is_typing == 1 && end_chat==false){
						$(".typing-status").fadeIn();
						$(".chat_area").stop().animate({ scrollTop: $(".chat_area")[0].scrollHeight}, 500);
					}else{
						$(".typing-status").fadeOut();
					}
				}	
				//Typing status Change
				is_typing = 0;
			}
		});
	}
	
};

updateMessage = function () {
	if(end_chat == true){
		return;
	}
    if($("#tricky-chat-box").length){
		$.ajax({
			method: "GET",
			url: _url+"guest/get_messages/"+$("#l_id").val(),
			success: function(data){
				var html = '';
				var sound = false;
				var json = JSON.parse(data);
				if(json['messages'].length > 0){
					$.each(json['messages'], function(n, elem) {
						$("#l_id").val(elem["id"]);
						
						if(elem['sender'] == "operator"){
							sound = true;
							html += '<li class="clearfix">'
								+'<div class="message-data align-right">'
								  +'<span class="message-data-time">'+moment(elem['created_at']).format('LLL')+'</span> &nbsp; &nbsp;'
								  +'<span class="message-data-name">'+json['operator']['name']+'</span> <i class="fa fa-circle me"></i>'
								  
								+'</div>'
								+'<div class="message other-message float-right">'
								  +elem['message']
								+'</div>'
							   +'</li>';
						}else{
							html += '<li>'
								+'<div class="message-data">'
								  +'<span class="message-data-name">'+$("#name").val()+'</span> <i class="fa fa-circle online"></i>'
								  +'<span class="message-data-time">'+moment(elem['created_at']).format('LLL')+'</span> &nbsp; &nbsp;'								  
								+'</div>'
								+'<div class="message my-message">'
								   +elem['message']
								+'</div>'
							   +'</li>';
						}							   
					});
					
                    $(".s_content").remove();
					$(".chat-history").append(html);
					$(".typing-status").fadeOut();
					if(sound == true){
						playSound();
					}
					$(".chat_area").stop().animate({ scrollTop: $(".chat_area")[0].scrollHeight}, 500);						
				}else{
					$(".s_content").remove();
					$(".chat-history").append(html);
					//$(".chat_area").stop().animate({ scrollTop: $(".chat_area")[0].scrollHeight}, 500);	
			    }
	
			}
		});
	}
};

$(document).on('submit','#guest-login-form',function(){
	var error = false;
	$(this).find('input,select').each(function(){
	   if($(this).attr("required") && $(this).val()==""){
		   $(this).css("border","1px solid red");
		   error = true;
	   }else{
		   $(this).css("border","1px solid #95a5a6");
	   }
	   
	   if($(this).attr("required") && $(this).attr("type")=="email" && $(this).val() != ""){
		   if( ! isValidEmailAddress($(this).val())){
			   $(this).css("border","1px solid red");
			   error = true;
		   }else{
			   $(this).css("border","1px solid #95a5a6");
		   }
	   }
	});
	if(error == true){
		return false;
	}
	return true;
});


//Send Offline Message
$(document).on('submit','#send_offline_message',function(){
	var error = false;
	var elem = $(this);
	$(elem).find("button[type=submit]").prop("disabled",true);
	button_val = $(elem).find("button[type=submit]").text();
	$(elem).find("button[type=submit]").html('<i class="fa fa-circle-o-notch fa-spin" aria-hidden="true"></i>');
		
	$(this).find('input,select,textarea').each(function(){
	   if($(this).attr("required") && $(this).val()==""){
		   $(this).css("border","1px solid red");
		   error = true;
	   }else{
		   $(this).css("border","1px solid #95a5a6");
	   }
	   
	   if($(this).attr("required") && $(this).attr("type")=="email" && $(this).val() != ""){
		   if( ! isValidEmailAddress($(this).val())){
			   $(this).css("border","1px solid red");
			   error = true;
		   }else{
			   $(this).css("border","1px solid #95a5a6");
		   }
	   }
	});
	if(error == true){
        $(elem).find("button[type=submit]").html(button_val);
		$(elem).find("button[type=submit]").attr("disabled",false);	
		return false;
	}
	return true;
});


function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^(("[\w-+\s]+")|([\w-+]+(?:\.[\w-+]+)*)|("[\w-+\s]+")([\w-+]+(?:\.[\w-+]+)*))(@((?:[\w-+]+\.)*\w[\w-+]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][\d]\.|1[\d]{2}\.|[\d]{1,2}\.))((25[0-5]|2[0-4][\d]|1[\d]{2}|[\d]{1,2})\.){2}(25[0-5]|2[0-4][\d]|1[\d]{2}|[\d]{1,2})\]?$)/i);
    return pattern.test(emailAddress);
}

$.fn.focusEnd = function() {
    $(this).focus();
    var tmp = $('<span />').appendTo($(this)),
        node = tmp.get(0),
        range = null,
        sel = null;

    if (document.selection) {
        range = document.body.createTextRange();
        range.moveToElementText(node);
        range.select();
    } else if (window.getSelection) {
        range = document.createRange();
        range.selectNode(node);
        sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
    }
    tmp.remove();
    return this;
}

function readChatFileURL(input) {
	if(end_chat == true){
		return;
	}
	if (input.files && input.files[0]) {
		 if((input.files[0].size/1024) > (u_s*1024)){
			 alert("Maximum Fle Upload Size is "+parseFloat(u_s) * 1024+ "MB");
			 $(".btn-fileupload").attr("disabled",false);
			 return;
		 }
		 var link = _url+"/guest/upload_file";
		 var form_data = new FormData();                  
		 form_data.append('_token', $('meta[name="csrf-token"]').attr('content'));
		 form_data.append('file', input.files[0]);
		 $.ajax({
			 method: "POST",
			 url: link,
			 data:  form_data,
			 mimeType:"multipart/form-data",
			 contentType: false,
			 cache: false,
			 processData:false,
			 beforeSend: function(){
				button_val = $(".btn-fileupload").text();
				$(".btn-fileupload").html('<i class="fa fa-circle-o-notch fa-spin" aria-hidden="true"></i>'); 
			 },success: function(data){
				var json =JSON.parse(data);
				if(json['result'] == "success"){
				  //Show Uploaded File
				  $(".chat-history").append('<li class="s_content">'
					+'<div class="message-data">'
					  +'<span class="message-data-name">'+$("#name").val()+'</span> <i class="fa fa-circle online"></i>'
					  +'<span class="message-data-time">'+moment().format('LLL')+'</span> &nbsp; &nbsp;'  
					+'</div>'
					+'<div class="message my-message">'
					  +'<a target="_blank" href="'+json['file_path']+'">'+json['file']+'</a>'
					+'</div>'
				  +'</li>');
				  $(".chat_area").stop().animate({ scrollTop: $(".chat_area")[0].scrollHeight}, 500);
				}else{
				  //Show Error
				  alert(json['message']);
				}
			 },complete: function (data) {  
				$(".btn-fileupload").html(button_val);
				$(".btn-fileupload").attr("disabled",false);
			 },error: function(xhr, status, error) {
				console.log(xhr.responseText);
			 }
		 });
	}
}

function chat_end(){
	$.ajax({
		method: "GET",
		url: _url+"guest/end_chat",
		success: function(data){
			send_message(data);
			end_chat = true;
			$(".message_write").fadeOut(100);
			$(".tricky_chat_widget .chat_area").css("height","345px");	
		}
	});
}

function send_message(message){
	$(".chat-history").append('<li class="s_content">'
		+'<div class="message-data">'
		   +'<span class="message-data-name">'+$("#name").val()+'</span> <i class="fa fa-circle online"></i>'
		   +'<span class="message-data-time">'+moment().format('LLL')+'</span> &nbsp; &nbsp;'
		+'</div>'
		+'<div class="message my-message">'
		  +message
		+'</div>'
	  +'</li>');
	$(".chat_area").stop().animate({ scrollTop: $(".chat_area")[0].scrollHeight}, 500);
	$(".chat-input-box").html("");
	
	//Set Typing Status 0
	is_typing = 0;
}

$(window).on('load', function(e) {
	if($(".tricky_chat_widget").height() != 423){
		$(".tricky_chat_widget .chat_area").css("height",$(window).height()-200);
	}
});

window.addEventListener('message', receiveMessage, true);
	
function receiveMessage(event) {
	if(event.data=="class_up"){
		$(".chat-expand").removeClass("down");
		$(".tricky_chat_widget .panel-heading").removeClass("open");
	}else if(event.data=="class_down"){
		$(".chat-expand").addClass("down");
		$(".tricky_chat_widget .panel-heading").addClass("open");
	}else if(event.data=="full_screen"){
		$(".tricky_chat_widget .chat_area").css("height",$(window).height()-200);	
	}else if(event.data=="small_screen"){
		$(".tricky_chat_widget .chat_area").css("height","225px");
	}else if(event.data=="responsive"){
		//Hide Fullscreen Feature
		$("#chat_fullscreen").hide();
		$(".tricky_chat_widget .chat_area").css("height",$(window).height()-200);
		$(".tricky_chat_widget").addClass("mobile");
	}else if(event.data=="desktop"){
		//Show Fullscreen Feature
		$("#chat_fullscreen").show();
		$(".tricky_chat_widget .chat_area").css("height","225px");
		$(".tricky_chat_widget").removeClass("mobile");
	}
}
	


