

// Attach keydown listener to message input

    
let chatHeaderNotice;
//  let chatBodySection;


let chatCloseButton;


 let chatBodySection;
 let chatActionsSection;
 let chatInitiatorSection;
 let chatInitiatorProgressAnimatationSection;



  // Attachment Message related fields
  let messageAttachment;
  let messageAttachmentSelector;
  let selectedFileOutput;
  let attachementRemoveButton;
  let attachementFileName;
  let attachementFileSize;

  // Text Message related fiels 
  let messageInput;

  let messageSendButton;
  


  

  window.addEventListener('load', event=>{


        // Initialize chat header elements
          
        chatHeaderNotice = document.querySelector("#chat-notice");
        chatCloseButton = document.querySelector(".chat-close-button");
            
            chatCloseButton.addEventListener("click", event=>{

                event.preventDefault();            
                let messageData = {action : "hide_chat" };
                window.parent.postMessage(messageData,'*');

            });

            // Initialize main chat sections
         chatBodySection = document.querySelector("#chat-body");
         chatActionsSection = document.querySelector("#chat-actions");
         chatInitiatorSection = document.querySelector("#initiate-chat");
         chatInitiatorProgressAnimatationSection = document.querySelector("#initiate-chat-progress-animation");

            
                
            // Initialize attachment related fields
          messageAttachment = document.querySelector("#action-attachment");
          messageAttachmentSelector = document.querySelector('#action-attachment-selector');
          selectedFileOutput = document.querySelector("#action-attachment-output");
          attachementRemoveButton = document.querySelector("#action-attachment-output-cancel");
          attachementFileName = document.querySelector("#action-attachment-output-filename");
          attachementFileSize = document.querySelector("#action-attachment-output-filesize");

            // Initialize Text message related fields
          messageInput = document.querySelector('#action-input');
          messageSendButton = document.querySelector('#action-send');            
                       

                    messageAttachment.addEventListener("change",event=>{                                                                    
                        let selectedFile = messageAttachment.files[0]; 
                        let selectedFileName = selectedFile.name;
                            
                        let selectedFileSize = selectedFile.size;

                            /** Check for file size */
                            if( selectedFileSize > ( chatSettings.file_sharing_max_size_kb * 1000)   ){                                
                                alert( "Maximum file size is " + roundBytesToAppropriateSize( chatSettings.file_sharing_max_size_kb * 1000)  );
                                messageAttachment.value = "";
                                return;        
                            }

                            /**Check for valid file format */
                            let selectedFileExtension = selectedFileName.slice( selectedFileName.lastIndexOf(".")+1 );
                            if( !chatSettings.file_sharing_extensions.includes(selectedFileExtension) ){
                                alert( ` ${selectedFileExtension} is not a supported file type! please use one of this  ${chatSettings.file_sharing_extensions.toString()} ` );
                                messageAttachment.value = "";
                                return;   
                            }
                                                    
                                attachementFileName.innerText = selectedFileName;
                                attachementFileSize.innerText = roundBytesToAppropriateSize( selectedFileSize );
                                showAttachementOutput();                
                    });

                     // Adding click event listener for attachement selector
                     messageAttachmentSelector.addEventListener("click",event=>{                        
                        messageAttachment.click();
                     });


                     // Add click event listener  for attchment remover
                     attachementRemoveButton.addEventListener( "click", event=>{                                            
                        
                        // Reset #messageAttachment filected files
                        messageAttachment.value = "";
                        
                        // Hide selected file output

                        hideAttachementOutput();
                         
                     } );


          
          // Make the chat header disapper when user srolls the messages
          chatBodySection.addEventListener("scroll",event=>{

                let scrollTopBak = chatBodySection.scrollTop;

                if(chatBodySection.scrollTop > 00){

                    chatHeaderNotice.classList.add("chat-notice-gone");
                   
                 } else{
                    chatHeaderNotice.classList.remove("chat-notice-gone");
                    chatHeaderNotice.style.height = chatHeaderNotice.scrollHeight + "px";

                 }
                 
                 chatBodySection.scrollTop = scrollTopBak;

            
            });

          // Make the input area autoexpand
          messageInput.addEventListener('input',event=>{              
                  target = event.target;
                  expandField(target);
          });

          // Hide the attachement selector, when user starts typing to #messageInput
          messageInput.addEventListener('input',event=>{
              if( messageInput.value.length > 0   ){
                  messageAttachmentSelector.classList.add("hidden");
              } else{
                  messageAttachmentSelector.classList.remove("hidden");
              }
          });



          // Initiate sending message, when "Enter" key is pressed
          messageInput.addEventListener('keydown', event=>{                    
                  target = event.target;
                      
                      /**
                       * When ENTER is pressed, without shift key
                       *  Initiate send message
                       */
                      if( event.key.toUpperCase()=="ENTER" && !event.shiftKey ){
                          event.preventDefault();

                          let messageText  = messageInput.value;
                              messageText = messageText.trim();
          
                          if( messageText.length > 0 ){
                              sendMessage( messageText,null );
                              messageInput.value = "";

                              // Dispatch event for input change
                              let inputClearedEvent = new InputEvent("input",{"inputType":"delete"});                     
                                  messageInput.dispatchEvent(inputClearedEvent);                                
          
                          } 
                          else{
                              console.log( { "message-text" : messageText } , "is empty cannot be sent" );
                              // Cannot send empty message 
                          }
                          // Resize the #messageInput to original size
                          expandField( target );    

                      }        
          } );

          // Send message, when the #send button is clicked
          messageSendButton.addEventListener("click",event=>{
            target = event.target;
            
              let messageText  = messageInput.value;
                  messageText = messageText.trim();

              if( messageText.length > 0 ){

                  // If there is any text in message input send as text message
                  sendMessage( messageText,null );


                        messageInput.value = "";
                      // Dispatch event for input change
                      let inputClearedEvent = new InputEvent("input",{"inputType":"delete"});                     
                          messageInput.dispatchEvent(inputClearedEvent);
             // Else if there is no text in message input , but there is a selected attachment,  send as an attachement
              } else if(  messageAttachment.files.length > 0 ){
                  sendMessage(null, messageAttachment.files[0] );
                 
                  hideAttachementOutput();

                  // clear selected attachemt
                  messageAttachment.value = "";
                  

              }
              else{
                  console.log( { "message-text" : messageText } , "is empty cannot be sent" );
                  // Cannot send empty message 
              }
              // Resize the #messageInput to original size
              expandField( target );           
          
          });

          /**
           * Sometimes the text area may contain  cached text,
           * Incase the textarea has text already, 
           * So it is helpful, to make the input text expandable as the page loads.
           */

          expandField( messageInput );

  } );
  


  let expandField = (element) => {

      /*
      * Set the input textarea's heigh to be limited to max of content inside,
      * This is helpful when the contened inside the text area is reduced, so this make the text area to reduce its size
      *
      */
      element.style.height = 'max-content';
      element.style.height = element.scrollHeight+"px";
  }



  /**
   * Hide/Unhide selected file displayer
   */


        let showAttachementOutput = () =>{
           
            messageAttachmentSelector.classList.add("hidden");
            messageInput.classList.add("hidden");

            selectedFileOutput.classList.remove("hidden");
        }


        let hideAttachementOutput = () =>{
            messageAttachmentSelector.classList.remove("hidden");
            messageInput.classList.remove("hidden");

            selectedFileOutput.classList.add("hidden");
        }

        



  /**
   * Hide/Unhide the chat session creator UI
   */

        // Hide the chat session creator UI 
        let showChatInitiator = () =>{
                        
            chatBodySection.classList.add('hidden');
            chatActionsSection.classList.add('hidden');

            chatInitiatorSection.classList.remove('hidden');
            
            chatInitiatorProgressAnimatationSection.classList.add("hidden");

        }

        // Shows of  the chat session creator UI 
        let hideChatInitiator = () =>{
            
            chatBodySection.classList.remove("hidden");
            chatActionsSection.classList.remove("hidden");
            chatInitiatorSection.classList.add("hidden");
            
            chatInitiatorProgressAnimatationSection.classList.add("hidden");

        }


    /**
     * File upload related function
     */
        // Disable fileuploader
        let disableFileUpload = () =>{
            messageAttachmentSelector.style.display = "none";
        }


    /**
     * Theme related functions
     */


        let applyTheme = chatSettings =>{
            
            let theme = {
                
                primary_textcolor : chatSettings.primary_text_color,
                primary_bgcolor : chatSettings.primary_background_color,

                secondary_textcolor : chatSettings.secondary_text_color,
                secondary_bgcolor : chatSettings.secondary_background_color
                            
            }

            updateTheme( theme );
            dispatchTheme( theme );
        }


        /**Update Local theme */
        let updateTheme = theme =>{

            document.documentElement.style.setProperty( "--chat-primay-bgcolor",theme.primary_bgcolor );
            document.documentElement.style.setProperty( "--chat-primay-color",theme.primary_textcolor );
            
            document.documentElement.style.setProperty( "--chat-secondary-bgcolor",theme.secondary_bgcolor );
            document.documentElement.style.setProperty( "--chat-secondary-color",theme.secondary_textcolor );

            // document.documentElement.style.setProperty("--chat-primay-color", theme.secondary_color );
        }
      
        /**
         * Broadcast theme to other windows/iframes
         */

         let dispatchTheme = theme =>{
            
            let messageData = {
                action : "chat_theme",
                theme : theme
            }
            parent.postMessage(messageData,"*");

         }