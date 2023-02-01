/**
 * Find the client UID from this script query URL
 */

let scriptUrl = new URL(document.currentScript.getAttribute("src"));
const CLIENT_UID = scriptUrl.searchParams.get("client_uid");

const EM_ROOT_URL = "https://builder.emailwish.com/";
//const EM_ROOT_URL = "http://local.emailwish.com/";

const emPlaceHolderStyleURL = EM_ROOT_URL + "ew-front/chat/embeder.css?v=0.1";
const chatFrameURL = EM_ROOT_URL + "ew-front/chat/index.html?client_uid=" + CLIENT_UID;
const chatControlFrameURL = EM_ROOT_URL + "ew-front/chat/controll.htm";

// Pointer to chat iframe
let emChatFrame;
let isEmChatFrameHidden = true;

//Pointer to chat controller iframe
let emChatFrameControl;


let unSeenMessagesCount = 0;

// if true, there is previously unseen messages
let isUnseenExist = false;


let emPlaceholder;


/* Solve  Webkit Mobile Adress bAR overlapping 100vh */

// First we get the viewport height and we multiple it by 1% to get a value for a vh unit
let vh = window.innerHeight * 0.01;
// Then we set the value in the --vh custom property to the root of the document
document.documentElement.style.setProperty('--vh', `${vh}px`);

window.addEventListener('resize', () => {
    // We execute the same script as before
    let vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty('--vh', `${vh}px`);


});


let initEmailishChat = () => {

    // Include addtional CSS used to style emPlaceholder
    let emPlaceHolderStyle = document.createElement("link");
    emPlaceHolderStyle.setAttribute("rel", "stylesheet");
    emPlaceHolderStyle.setAttribute("href", emPlaceHolderStyleURL);

    document.head.appendChild(emPlaceHolderStyle);


    // DOM element where the em chat's i frame will be embeded
    emPlaceholder = document.createElement("div");
    emPlaceholder.setAttribute('id', "em-placeholder");
    emPlaceholder.classList.add("hidden");

    // Append to body
    document.body.appendChild(emPlaceholder);


    // The frame that contails the main chat
    emChatFrame = document.createElement("iframe");
    emChatFrame.setAttribute("src", chatFrameURL);
    emChatFrame.setAttribute("id", "em-chat");
    emChatFrame.classList.add("zero-heigh");

    /**
     * Perform on transition end ,operation,
     */
    emChatFrame.addEventListener("transitionend", event => {

        /**
         * The event could fire for multiple properyNames
         * Just listening for opacity
         */
        if (event.propertyName = "opacity") {
            /**
             * Transition may have fired because of emChatFrame is shown
             * So check what caused the transition
             */
            if (isEmChatFrameHidden) {
                emChatFrame.classList.add("zero-heigh");

                // Show the chat controller
                emChatFrameControl.classList.remove("hidden-controller");
            }
        }
    });


    /**
     * Perform operations the  transiotion begun
     */
    emChatFrame.addEventListener("transitionstart", event => {

        /**
         * The event could fire for multiple properyNames
         * Just listening for opacity
         */
        if (event.propertyName = "opacity") {
            /**
             * Transition may have fired because of emChatFrame is shown
             * So check what caused the transition
             */
            if (!isEmChatFrameHidden) {
                emChatFrame.classList.remove("zero-heigh");
                // Show the chat controller
                emChatFrameControl.classList.add("hidden-controller");
            }
        }
    });


    emChatFrameControl = document.createElement("iframe");
    emChatFrameControl.setAttribute("src", chatControlFrameURL);
    emChatFrameControl.setAttribute("id", "em-chat-control");

    emPlaceholder.appendChild(emChatFrame);
    emPlaceholder.appendChild(emChatFrameControl);

}


window.addEventListener("message", message => {

    let messageData = message.data;
    switch (messageData.action) {
        case 'hide_chat' : {

            // Tell the iframes about the event
            let newData = {
                action: 'chat_hidden'
            };


            emChatFrameControl.contentWindow.postMessage(newData, "*");
            emChatFrame.contentWindow.postMessage(newData, "*");

            // Save the status of chat frame
            isEmChatFrameHidden = true;

            // animate the chat window
            emChatFrame.classList.remove("em-chat-anim");

            break;
        }

        case 'unhide_chat' : {

            // Tell the iframes about the event
            let newData = {
                action: 'chat_unhidden'
            }

            emChatFrameControl.contentWindow.postMessage(newData, "*");
            emChatFrame.contentWindow.postMessage(newData, "*");

            // Clear any alerts when user opens the hidden chat
            clearMessageAlerts();

            // Save the status of chat frame
            isEmChatFrameHidden = false;

            // animate the chat window
            emChatFrame.classList.add("em-chat-anim");

            break;
        }

        case "new_unread_message" : {
            alertForNewMessage(message);
            break;
        }

        case "unread_messages_marked_seen" : {
            clearMessageAlerts();
            break;
        }

        case "chat_theme" : {
            emChatFrameControl.contentWindow.postMessage(messageData, "*");
            emPlaceholder.classList.remove("hidden");
            break;
        }

        case "" : {
            emPlaceholder.classList.remove("hidden");

        }

    }
})

let alertForNewMessage = (message) => {

    if (isUnseenExist) {
        unSeenMessagesCount += message.data.count;
    } else {
        unSeenMessagesCount = message.data.count;
        startAnimatingDocumentTitle();
    }

    let notificationAudio = new Audio(EM_ROOT_URL + "/ew-front/res/audio/new_message_alert.mp3");
    notificationAudio.play();

    // Make vibaration
    navigator.vibrate(1);

    if (isEmChatFrameHidden) {

        // Send a message chat controller about the new message
        data = message.data;
        data.count = unSeenMessagesCount;
        emChatFrameControl.contentWindow.postMessage(data, "*");
    } else {

        // Send a message chat controller about the new message
        data = message.data;
        data.count = unSeenMessagesCount;
        emChatFrameControl.contentWindow.postMessage(data, "*");

    }

    isUnseenExist = true;

}

let clearMessageAlerts = () => {

    console.log("Clearing message alerts");
    // There exist active alerts
    if (isUnseenExist) {
        stopAnimatingDocumentTitle();
    } else {
        // No active alerts, Nothing to clear
    }

    // clear controller too
    isUnseenExist = false;

}


/**
 *
 * Functions and Variables related with document title animation are ahead
 *
 */

    // A reference to a loop( SetInterval ) that regularly update/change the document title
    // This reference is used to call the "clearInterval" method latter.
let documentTitleAnimationInteralReference;

// This indicates, whether the notification or document's original title is set
let isOriginalTitleSet;

//Document original title.
let originalDocumentTitle;


let startAnimatingDocumentTitle = () => {

    originalDocumentTitle = document.title;
    isOriginalTitleSet = true;

    documentTitleAnimationInteralReference = setInterval(animateDocumentTitle, 500);
}

let stopAnimatingDocumentTitle = () => {
    restoreDocumentOriginalTitle();
    isOriginalTitleSet = true;

    clearInterval(documentTitleAnimationInteralReference);
}

let animateDocumentTitle = () => {

    if (isOriginalTitleSet) {
        updateDocumentTitleToNewMessage();
        isOriginalTitleSet = false;
    } else {
        restoreDocumentOriginalTitle();
        isOriginalTitleSet = true;
    }

}

let restoreDocumentOriginalTitle = () => {
    document.title = originalDocumentTitle;
}
let updateDocumentTitleToNewMessage = () => {
    document.title = `(${unSeenMessagesCount}) New Message`;
}


/**
 * Automatically call functions
 */


initEmailishChat();