

const  API_SERVER = "https://builder.emailwish.com";

const API_CREATE_NEW_SESSION_URL =  API_SERVER + "/_shopify/chat/sessions/new";
const API_GET_EXISTING_SESSION_URL = API_SERVER + "/_shopify/chat/sessions/get";
const API_GET_CHAT_SETTINGS_URL = API_SERVER + "/_shopify/chat/settings/readGuest";
const API_SEND_MESSAGE_URL = API_SERVER + "/_shopify/chat/messages/storeGuest";
const API_RETRIEVE_MESSAGE_URL = API_SERVER + "/_shopify/chat/messages/listGuest";


// client identifier
let pageUrl = new URL ( window.location.href );
const CLIENT_UID = pageUrl.searchParams.get("client_uid");

// chat session object
let chatSession;

// chat settings/options
let chatSettings;


/**
 * Polling related fieds
 */

let isOnline = window.navigator.onLine;

const POLLING_INTERVAL_TIME = 5000;

// used when  clearing interval is needed
let messagePollerIteratorId;


// the interval at which message age is updated
const MESSAGE_AGE_UPDATOR_INTERVAL = 15000;

// the id of the last read/recieved message
let lastFetcedMessageID; 

let islastSeenMessageValid = false;

// Array of messages printed automatically without being fetched from server
let autoSentMessages = new Array();


	// Perform on page load initialization
	window.addEventListener('load', event=>{

		// Get reference to on page elements
		
		chatBodySection = document.querySelector("#chat-body");
		chatActionsSection = document.querySelector("#chat-actions");
		chatInitiatorSection = document.querySelector("#initiate-chat");
		
			getChatSettings();
			getExistingSession();

	});

	// Listen for messages from the parent window
	window.addEventListener("message",message=>{

		action = message.data.action;

		if (action == undefined) {
			return;
		}

		switch (action) {

			// Listen for user to unhides the chat
			// And mark unseen messages as seen
			case "chat_unhidden": {

				/**
				 * This is a FIX
				 * The chat body were not scrolled to the last seen message, for hidden  DIVs can't not scroll so.cursor-pointer
				 * When the chat body became unhidden it is important to scroll it to the last seen message.
				 * 					 
				 */

				lastSeenMessage = chatBodySection.querySelector("#MSG_" + localStorage.getItem("lastSeenMessageID"));
				lastSeenMessage.scrollIntoView(false);

				markUnseeenMessageAsSeen();
				break;
			}

			default: {
				console.log(`${action} : Not implemented Yet`);
			}
		}

	});

	
	window.addEventListener("online",event=>{
		isOnline = true;
	});
	window.addEventListener("offline",event=>{
		isOnline = false;
	});
	/**
	* Retrive the chat options/prefference/settings	 
	*/
	let getChatSettings = () =>{
	

		let settingsAjax =  new XMLHttpRequest();

		let settingsUrl = new URL( API_GET_CHAT_SETTINGS_URL );
		settingsUrl.searchParams.append("client_uid", CLIENT_UID);

		settingsAjax.open("GET",settingsUrl);
		settingsAjax.onload = ()=>{
		if( settingsAjax.status ==200 ){
			let responseObject =  JSON.parse(settingsAjax.responseText);
			if( responseObject ){
				chatSettings = responseObject.chat_settings;

				//disbale file uploader, if file sharing is not enabled
				if( !chatSettings.file_sharing_enabled ){
					disableFileUpload();
				}
				// Update theme
				applyTheme( chatSettings );
			} else{
				console.error("Faild to load chat options");
			}
		}
		console.log( "Chat Options :> ",   );
		}
			settingsAjax.send();
	}


	let getExistingSession = () =>{


		let ajax =  new XMLHttpRequest();
				let url = new URL( API_GET_EXISTING_SESSION_URL );
				url.searchParams.append("client_uid",CLIENT_UID);
			
			ajax.open("GET",url);
			ajax.withCredentials = true;
			ajax.onload = ()=>{

					let sessionObject = JSON.parse( ajax.responseText );
					if( sessionObject.id!=undefined ){
						chatSession = sessionObject;
						hideChatInitiator();
					
						// Start polling messages
						retrieveMessage();
						startPollingMessages();
						//Start message age updater
						startUpdatingMessageAge();

					}

					// Existing session did not found, creating new one
					else{
						showChatInitiator();
					
						let chatInitiateCancel = document.querySelector(".initiate-chat--action-cancel");							
							chatInitiateCancel.addEventListener("click", event=>{								             									
								message = { 
									action : "hide_chat"
								};							
								window.parent.postMessage(message,'*');
							} );
					}							
			}
		ajax.send();
	}

	let createChatSession =(event) =>{

				event.preventDefault();

				// Show  animation gif and hode the chat initiator form
				chatInitiatorSection.classList.add("hidden");
				chatInitiatorProgressAnimatationSection.classList.remove("hidden");

				let chatInitiateForm = new FormData( event.target );
					chatInitiateForm.append("client_uid",CLIENT_UID);						

					let ajax = new XMLHttpRequest();

						ajax.open("POST",API_CREATE_NEW_SESSION_URL);
						ajax.withCredentials = true;
						ajax.onload = () =>{
							// New session is been created
							if(ajax.status>=200 && ajax.status<=300){		
								console.info( "Old  last seen : ", localStorage.getItem("lastSeenMessageID") );																		
								localStorage.setItem("lastSeenMessageID",-1);
								console.info( "Updated last seen to : ", localStorage.getItem("lastSeenMessageID") );
								
								// Recieve the created session										
								getExistingSession();									
							}								
						}
							
						ajax.onerror = () =>{

								alert("Failed to create session");

								// Hide  animation gif and show the chat initiator form
								chatInitiatorSection.classList.remove("hidden");
								chatInitiatorProgressAnimatationSection.classList.add("hidden");

						}
						ajax.send( chatInitiateForm );
	}

	let sendMessage = (message,attachment) =>{

			let messageForm = new FormData();							
				messageForm.append( "session_id", chatSession.id );
				messageForm.append("secret_key",chatSession.secret_key);

			let ajax = new XMLHttpRequest();		

			// The autoPrinted Message
			let messageDOM;
				let messsageStatusSending;
				let messsageStatusSent;
				let messsageStatusFaild;
			
				// Append either message text or message attachement, based on what present
				if(message){
					// Message text has been present
					messageForm.append("message",message);

					let date = new Date();
					let utc_time = date.getTime() + date.getTimezoneOffset();
					messageObject = {

						from_guest:"1",
						created_at:  getUTCTime() ,
						updated_at: getUTCTime(),
						message: message,
					}
					
					// Print attachement on chat body,
					messageDOM = printMessage( messageObject );
						
						// Scroll to message sending indicator
						setTimeout(()=>{
							messageDOM.scrollIntoView( false );
						},500);
					
						// Message sending status
						messsageStatusSent = messageDOM.querySelector(".message-info-status-sent"); 							
						messsageStatusSending = messageDOM.querySelector(".message-info-status-sending");
						messsageStatusFaild =   messageDOM.querySelector(".message-info-status-faild");																		
				}
				
				else if( attachment ){

					// Message attachement has been present
					messageForm.append("attachment",attachment);

					let date = new Date();
					let utc_time = date.getTime() + date.getTimezoneOffset();
					messageObject = {

						"from_guest":"1",
						"created_at":  getUTCTime() ,
						"updated_at" : getUTCTime(),

						"attachment_size" : ""+attachment.size,
						"attachment_path":attachment.name,
						"attachment_mime_type": attachment.type,
						"attachment_full_url": URL.createObjectURL( attachment )
					}
					
					// Print attachement on chat body,
					messageDOM = printMessage( messageObject );
					// Scroll to message sending indicator
					// wait a few moment until messageDOM loads all image
					setTimeout(()=>{
						messageDOM.scrollIntoView( false );
					},500);
					console.log( "Scrolling into ", messageDOM );

						
						// Message sending status
						messsageStatusSent = messageDOM.querySelector(".message-info-status-sent"); 							
						messsageStatusSending = messageDOM.querySelector(".message-info-status-sending");	
						messsageStatusFaild =   messageDOM.querySelector(".message-info-status-faild");					
							

						// Attachement Related
						let attachmentFileSize = messageDOM.querySelector(".attachment-file-filesize");														
						let progress = messageDOM.querySelector( ".attachment-upload-progress" );
						let progressThumb = messageDOM.querySelector(".attachment-upload-progress-thumb");						
						let progressText = messageDOM.querySelector(".attachment-upload-progress-text");

				

					ajax.upload.onloadstart = event =>{
						
						// Show the attachement  upload progrss
						progress.classList.remove("hidden");	

						// Hide the attchament file size
						attachmentFileSize.classList.add("hidden");	

					}

					ajax.upload.onprogress = event=>{
						let completedPercent =  Math.round( (event.loaded/event.total) * 100 );

						// Update the progress thumb with
						progressThumb.style.width = completedPercent + "%";

						// Update the progress text

						progressText.innerText = completedPercent+" %";

					}

					ajax.upload.onload = event=>{

						// Hide the upload progress bar
						progress.classList.add("hidden");
						
						// show the total file size 
						attachmentFileSize.classList.remove("hidden");																			
					}

					ajax.upload.onerror = event =>{

							// hide the attachement  upload progrss
							progress.classList.add("hidden");	

							// show the attchament file size
							attachmentFileSize.classList.remove("hidden");	
						
							// show  message faild indicator							
							messsageStatusFaild.classList.remove("hidden");
							messsageStatusSent.classList.add("hidden"); 	
							messsageStatusSending.classList.add("hidden");
					}

				}	
												

				ajax.withCredentials = true;
				ajax.open("POST",API_SEND_MESSAGE_URL);
								
				ajax.onloadstart = event => {
					// Hide the double-check indicator and faild icon, and show the stopwatch

					messsageStatusSending.classList.remove("hidden");
					messsageStatusSent.classList.add("hidden"); 									
					messsageStatusFaild.classList.add("hidden");

				}
				
				ajax.onload = () =>{

					if( ajax.status==200 ){								
						let responseObject = JSON.parse( ajax.responseText );
							if( responseObject.message_id ){

								// Set ID for the auto printed message
								messageDOM.setAttribute("ID","MSG_"+responseObject.message_id);

								// Save the message in autoSentMessages
								autoSentMessages.push( responseObject.message_id );
								
								// Show the double-check indicator, and hidde the stopwatch && faild icon
								messsageStatusSent.classList.remove("hidden"); 	

								messsageStatusSending.classList.add("hidden");
								messsageStatusFaild.classList.add("hidden");
								
								

							}
							
							else{
								// Message id failed to be created
								console.error("Message failed to be  created", responseObject );
							}
					} 
					
					// Server returned other than 200 
					else{
						console.error("Error sending message", ajax.status);
					}
				} 

				ajax.onerror = ()=>{
					// show retry button on messages
					// attempt to resend message
					console.error("Error sending message ",ajax);

					// Show the double-check indicator, and hidde the stopwatch && faild icon
					messsageStatusFaild.classList.remove("hidden");
										
					messsageStatusSent.classList.add("hidden"); 	
					messsageStatusSending.classList.add("hidden");
					
				}
				
				ajax.ontimeout = () =>{
					console.error("Timeover sending the message ",ajax);
				}
				ajax.send( messageForm );					
	}

	let isPollingInProgress = false;
	
	let retrieveMessage = () => {


		// If not online, do not attempt to fetch message
		if( !isOnline ){
			console.log("Navigator is offline, cannot poll messages");
			return;
		}

		// Check if there is another polling is in progress.


		if( isPollingInProgress ){
			console.log("Another message polling is in progress");
			return;
		}

		isPollingInProgress = true;

		let retrieveRequestForm = new FormData();
			retrieveRequestForm.append("session_id", chatSession.id );
			retrieveRequestForm.append("secret_key", chatSession.secret_key );
				if( lastFetcedMessageID ){
					retrieveRequestForm.append("last_id",lastFetcedMessageID);
				}

		let ajax =  new XMLHttpRequest();

		ajax.open("POST",API_RETRIEVE_MESSAGE_URL);
		ajax.withCredentials = true;
		
		ajax.onload = () =>{
				if(ajax.status==200){						
					let messagesArray =  JSON.parse( ajax.responseText ).messages;								

						if( messagesArray.length > 0 ){

							let lastMessage = messagesArray[  messagesArray.length - 1 ];
							lastFetcedMessageID = lastMessage.id;
							
							printRetrievedMesssage( messagesArray );


									let lastSeenMessageID = localStorage.getItem("lastSeenMessageID");											

									if(  lastSeenMessageID < lastFetcedMessageID ){
										
										/**
										 * There is unseen message, but it could be from guest itself,
										 * Only count the recieved messages,
										 * 
										 */

											// Extract recieved messages						
											let recievedMessagesArray = new Array();
											messagesArray.forEach( message=>{

												if( message.from_guest!="1" ){
													recievedMessagesArray.push( message );
												}

											} );
												
											// Alert for recieved messages,
											if(recievedMessagesArray.length > 0){
												
												//But only alert if document dont has a focus
												if( !document.hasFocus() ){

													alertUserForNewUNseenMessage( recievedMessagesArray.length );
													printNewMessagesMarker();

												} else{
													// User can see the unseen message, so make the new messages as marked
													console.log("User can see the message ", messageObject)
													
													/**
													 * Update the last seen message ID to the last fetched message ID because, user is able to see the reveoeve messages
													 */
													
														console.info( "Old  last seen : ", localStorage.getItem("lastSeenMessageID") );
													localStorage.setItem("lastSeenMessageID",lastFetcedMessageID);
													console.info( "Updated last seen to : ", localStorage.getItem("lastSeenMessageID") );
												}

											}
											
											else{

												console.log("There is new unseen messages, but all are from guest it self");
												/**
												 * Update the last seen message ID to the last fetched message ID because, all are sent by the user it self,
												 */
														console.info( "Old  last seen : ", localStorage.getItem("lastSeenMessageID") );

												localStorage.setItem("lastSeenMessageID",lastFetcedMessageID);
												console.info( "Updated last seen to : ", localStorage.getItem("lastSeenMessageID") );


											}  

									} 
									
									else{

										/**
										 * There is/are polled messsage, but none are unseen
										 * This if for the messages are polled then firts time.
										 * 
										 */

										console.log( "**********************************" )
										console.log( "Messages are polled for first time" )
										console.log( "**********************************" )

									}											
							
							
							/**
							 * Scroll to the last seen message, 
							 */
							lastSeenMessage = chatBodySection.querySelector( "#MSG_"+ localStorage.getItem("lastSeenMessageID")  );											
							lastSeenMessage.scrollIntoView(false);	
							
						}

				}

				isPollingInProgress = false;
		}

		ajax.onerror = () => {

			console.info("MESSAGE_POLL : ", "error polling messages");
			isPollingInProgress = false;

		}

		ajax.ontimeout = () =>{
			console.info("MESSAGE_POLL : ", "timeout during polling messages");
			isPollingInProgress = false;

		}

		ajax.send( retrieveRequestForm );

	}

	window.addEventListener("focus",event=>{	

	// The chat frame has got a focus
	// Marking unseen as seen

	markUnseeenMessageAsSeen();

	});


	let markUnseeenMessageAsSeen=()=>{

	// Mark the unseen messages seen
	if(  islastSeenMessageValid ){
		console.log("Marking the unseen messages as seen");
		console.info( "Old  last seen : ", localStorage.getItem("lastSeenMessageID") );									
		
		//Mark the unseen messages as been seen											
		localStorage.setItem("lastSeenMessageID",lastFetcedMessageID);

		console.info( "Updated last seen to : ", localStorage.getItem("lastSeenMessageID") );

		// Discard the to to reupda
		islastSeenMessageValid = false;

	} else{
		console.log("FAke : Marking the unseen messages as seen");

		// lastSeenMessageID is already updated
	}	

	data =  {
		action : "unread_messages_marked_seen"
	}

	window.parent.postMessage(data,"*");					

	}

	let printMessage = messageObject =>{

		let rndm  =  Math.round( Math.random() * 100) 
		if( rndm%2==0 ){
		//	messageObject.from_guest="0";
		}

		let sentMessageTemplate = document.querySelector("#sent-msg-template");
		let recievedMessageTemplate = document.querySelector("#recieved-msg-template");
		let clone;
		let cloneMessage;
		
		if( messageObject.from_guest=="1" ){
			clone = sentMessageTemplate.content.cloneNode( true );
		} else{
			clone = recievedMessageTemplate.content.cloneNode( true );
		}

			cloneMessage =  clone.querySelector(".message");
				cloneMessage.setAttribute("id", "MSG_"+messageObject.id );

				// Check if the mesage is an attachemenr
				if( messageObject.attachment_path ){
					
					console.log('About to print A attachement');

					cloneMessageAttachment = clone.querySelector(".attachment");
					cloneMessageAttachment.classList.remove("hidden");

						let attachementMimeType = messageObject.attachment_mime_type;
						

							if( attachementMimeType.includes( "image/" )  ){
								// The attachment is an image file

								let attachmentImageViewer = cloneMessageAttachment.querySelector(".attachment-image-viewer");
									attachmentImageViewer.setAttribute("src",messageObject.attachment_full_url);
									attachmentImageViewer.classList.remove('hidden');
																		
							}
							
							else if( attachementMimeType.includes( "audio/" ) ){
								// The attachment is an Audio file

								let attachmentAudioPlayer = cloneMessageAttachment.querySelector(".attachment-audio-player");
									attachmentAudioPlayer.setAttribute("src",messageObject.attachment_full_url);
									attachmentAudioPlayer.classList.remove('hidden');

							}

							else if( attachementMimeType.includes( "video/" ) ){
								// The attachment is a video file

								let attachmentVideoPlayer = cloneMessageAttachment.querySelector(".attachment-video-player");
									attachmentVideoPlayer.setAttribute("src",messageObject.attachment_full_url);
									attachmentVideoPlayer.classList.remove('hidden');
							}

							let attachmentLink = cloneMessageAttachment.querySelector(".attachment-file-link");
								attachmentLink.setAttribute("href",messageObject.attachment_full_url);
							
							
								let attachmentFileName = cloneMessageAttachment.querySelector(".attachment-file-filename");

									let filePath = messageObject.attachment_path;
										let fileName = filePath.slice( filePath.lastIndexOf("/") + 1 );												
											let fileNameNoExt = fileName.slice(0, fileName.lastIndexOf(".") );
											let fileExtension = fileName.slice( fileName.lastIndexOf(".") );

									attachmentFileName.innerText = fileNameNoExt;

								let attachmentFileExtension = cloneMessageAttachment.querySelector(".attachment-file-extension");
									attachmentFileExtension.innerText = fileExtension;

							let attachmentFileSize = cloneMessageAttachment.querySelector(".attachment-file-filesize");
								attachmentFileSize.innerText = roundBytesToAppropriateSize( messageObject.attachment_size );


				} else{
					// This is normal text message
					cloneMessageText = clone.querySelector(".text");
					cloneMessageText.innerText = messageObject.message;
					cloneMessageText.classList.remove("hidden");
				}

			cloneTimeSent =  clone.querySelector(".time-sent");
				cloneTimeSent.setAttribute("utc_time",  new Date(messageObject.created_at).getTime()  );
				cloneTimeSent.innerText = getMessageAge( messageObject.created_at );

			chatBodySection.appendChild( cloneMessage );
			return cloneMessage;

		// The message sender may wants to update the upload progress
	}

	// Output all messages to chatBody		
	let printRetrievedMesssage = ( messagesArray ) =>{


		messagesArray.forEach( messageObject=>{								
				
				let index = autoSentMessages.indexOf(  messageObject.id  );
				// Skip if already printed by auto print
				if( index >=0  ){
					autoSentMessages.splice( index,1 );
					return;
				} else{					
					printMessage( messageObject );
				}
			
		} );


	}		

	let printNewMessagesMarker = () => {

				oldNewMessageMarker = document.getElementById("new-message-marker");
				if( oldNewMessageMarker){
					oldNewMessageMarker.remove();
				}

					newMessageMarkerTemplate = document.querySelector("#new-message-marker-template");					
					newMessageMarker = newMessageMarkerTemplate.content.cloneNode(true);
				
					let lastSeenMessage = chatBodySection.querySelector("#MSG_"+ localStorage.getItem("lastSeenMessageID") );
					chatBodySection.insertBefore( newMessageMarker , lastSeenMessage.nextSibling );
	}
	
	let alertUserForNewUNseenMessage = ( newMessagesCount ) =>{

			// comment this
			islastSeenMessageValid = true;

			let data = {
				action : "new_unread_message",
				count : newMessagesCount,
			}

			window.parent.postMessage(data,"*");
	}
	// Updates the message age,
	let updateMessageAge= () =>{

		let allMessageTimeDOM = document.querySelectorAll(".time-sent");
			allMessageTimeDOM.forEach( timeDOM=>{
				
				let timeSent  =  Number.parseInt( timeDOM.getAttribute("utc_time") );
				let messagesAge = getMessageAge( timeSent );
				timeDOM.innerText = messagesAge;
							
			} );

	}
	// call 'updateMessageAge' function to update message age with specified interval
	let startUpdatingMessageAge = () =>{
		setInterval(updateMessageAge, MESSAGE_AGE_UPDATOR_INTERVAL);
	}

	let startPollingMessages = () => {
		messagePollerIteratorId = setInterval( retrieveMessage,POLLING_INTERVAL_TIME );
	}

	let stopPollingMessages = () =>{
		clearInterval( messagePollerIteratorId );
	}