"use strict"


const POPUP_CLIENT_UID = new URL(document.currentScript.src).searchParams.get("client_uid");
const POPUP_API_URL = "https://builder.emailwish.com/_shopify/popups";
//const POPUP_API_URL = "http://127.0.0.1/popups.json";	


let popup_fetch_url = new URL(POPUP_API_URL);
popup_fetch_url.searchParams.append("client_uid", POPUP_CLIENT_UID);


let fetch_popups = () => {

    let popup_ajax = new XMLHttpRequest();

    popup_ajax.open("GET", popup_fetch_url);
    popup_ajax.responseType = "json";

    popup_ajax.onloadstart = (event) => {
    };
    popup_ajax.onload = event => {
        if (popup_ajax.status === 200) {
            popup_ajax.response.popups.forEach(popup => {
                buildPopupDOM(popup);
            });
        }
    }
    popup_ajax.onerror = event => {
    }
    popup_ajax.ontimeout = event => {
    }

    popup_ajax.send();
}
/**
 * Popup DOM related
 */
let createPopupSkleton = (popup) => {


    let popup_overlay_justify_content = (
        popup.popup_position === "center" ||
        popup.popup_position === "top-middle" ||
        popup.popup_position === "bottom-middle"
    ) ? "center" :
        (
            popup.popup_position === "top-left" ||
            popup.popup_position === "middle-left" ||
            popup.popup_position === "bottom-left"
        ) ? "flex-start" : (
            popup.popup_position === "top-right" ||
            popup.popup_position === "middle-right" ||
            popup.popup_position === "bottom-right"
        ) ? "flex-end" : "center";
    let popup_overlay_align_items = (
        popup.popup_position === "top-left" ||
        popup.popup_position === "top-middle" ||
        popup.popup_position === "top-right"
    ) ? "flex-start" :
        (
            popup.popup_position === "middle-left" ||
            popup.popup_position === "center" ||
            popup.popup_position === "middle-right"
        ) ? "center" : (
            popup.popup_position === "bottom-right" ||
            popup.popup_position === "bottom-middle" ||
            popup.popup_position === "bottom-left"
        ) ? "flex-end" : "center";

    let popupOverlayString = `
			<style>
			
			.popup-overlay{
				position: fixed;
				top: 0;
				left: 0;
				z-index : 999;
	
				height: 100vh;
				width: 100vw;
	
				display: flex;
			
				background-color: #000000a6;
				
			}
	
			.popup-wrapper{
			  margin: 18px;
				position: relative;
				animation : 0.5s appearTopAnimation;
			}
	
			.popup-frame{
				border: none;
				background-color: white;
	
			}
	
			.popup-close-btn{
				position: absolute;
				top: -1rem;
				right : -1rem;
	
				height: 2rem;
				width: 2rem;
				border-radius: 50%;
				font-size : 1rem;
	
				display: flex;
				justify-content: center;
				align-items: center;
	
				background-color: black;
				color: white;
	
				cursor: pointer;
	
				outline: none;
				border: none;
			}			
	
			.no-scroll{
				overflow:hidden !important;
			}
             

			/* ANimation Keyframes */

			@keyframes appearTopAnimation{

				0%{
					opacity : 0.5;
					top: -3rem;
				}
			
				100%{
					opacity : 1;
					top : 0em;
				}
			}


		</style>
	
		<div class="popup-overlay" style="	justify-content: ${popup_overlay_justify_content};
				align-items:  ${popup_overlay_align_items};" >
			<div class="popup-wrapper">
				<iframe class="popup-frame"></iframe>
				<button class="popup-close-btn"> ✕ </button>
			</div>		
		</div>
		`;

    let tmp = document.createElement("div");
    tmp.innerHTML = popupOverlayString;

    return tmp.cloneNode(true);
}


let buildPopupDOM = popup => {


    let popupDOM = createPopupSkleton(popup);

    let closeButton = popupDOM.querySelector(".popup-close-btn");
    closeButton.addEventListener("click", event => {
        hidePopup(popupDOM);
    });

    let theIframe = popupDOM.querySelector(".popup-frame");
    theIframe.height = popup.height;

    if (popup.width > document.body.clientWidth) {
        theIframe.width = document.body.clientWidth - 18;
    } else {
        theIframe.width = popup.width;
    }

    theIframe.setAttribute("src", popup.url);

    theIframe.addEventListener("load", event => {
        registerPopupTriggers(popupDOM, popup);
    });


    popupDOM.style.display = "none";
    document.body.appendChild(popupDOM);
}


/**
 * Trigger Related Functions
 */


let registerPopupTriggers = (popupDOM, popup) => {


    let popupTriggers = popup.triggers;
    popupTriggers.forEach(trigger => {

        switch (trigger.type) {
            case "on-load" : {

                if (popup.fired) {
                    console.log("Ignored for  : ", trigger.type);
                    return;
                }
                console.log("Marking : ", popup, " as been opened");
                popup.fired = true;

                setTimeout(() => {
                    showPopup(popupDOM);
                    console.log("Showing popup for : ", trigger.type);
                }, trigger.delay_seconds * 1000);

                break;
            }
            case "scroll-start" : {

                window.addEventListener("scroll", event => {

                    if (popup.fired) {
                        console.log("Ignored for  : ", trigger.type);
                        return;
                    }
                    console.log("Marking : ", popup, " as been opened");
                    popup.fired = true;

                    setTimeout(() => {
                        showPopup(popupDOM);
                        console.log("Showing popup for : ", trigger.type);
                    }, trigger.delay_seconds * 1000);

                });
                break;
            }
            case "scroll-middle" : {
                window.addEventListener("scroll", event => {


                    // Check if user has scrolled to the middle of the page

                    let bodyParent = document.body.parentNode;
                    let scrollHeight = bodyParent.scrollHeight;
                    let height = bodyParent.clientHeight;

                    let centerMargin = 0.1;
                    let scrollYcenter = (1 / 2 * scrollHeight) - (1 / 2 * height);
                    let scrollYcenterRangeMin = scrollYcenter - (centerMargin * scrollHeight);
                    let scrollYcenterRangeMax = scrollYcenter + (centerMargin * scrollHeight);

                    let scrollY = Math.round(bodyParent.scrollTop);

                    if (scrollY >= scrollYcenterRangeMin && scrollY <= scrollYcenterRangeMax) {


                        if (popup.fired) {
                            console.log("Ignored for  : ", trigger.type);
                            return;
                        }
                        console.log("Marking : ", popup, " as been opened");
                        popup.fired = true;

                        setTimeout(() => {
                            showPopup(popupDOM);
                            console.log("Showing popup for : ", trigger.type);
                        }, trigger.delay_seconds * 1000);

                    }
                });
                break;
            }
            case "scroll-end" : {


                window.addEventListener("scroll", event => {
                    // Check if user has scrolled to the end of the page

                    let bodyParent = document.body.parentNode;
                    let scrollHeight = bodyParent.scrollHeight;
                    let scrollY = Math.round(bodyParent.scrollTop);

                    let scrollYButtom = scrollY + bodyParent.clientHeight;

                    let bottomMargin = 0.1;
                    let scrollYButtomRangeMin = scrollHeight - (bottomMargin * scrollHeight);

                    if (scrollYButtom >= scrollYButtomRangeMin) {

                        if (popup.fired) {
                            console.log("Ignored for  : ", trigger.type);
                            return;
                        }
                        console.log("Marking : ", popup, " as been opened");
                        popup.fired = true;


                        setTimeout(() => {
                            showPopup(popupDOM);
                            console.log("Showing popup for : ", trigger.type);
                        }, trigger.delay_seconds * 1000);

                    }

                });

                break;
            }
            case "idle" : {
                let timeoutHandle;

                let idleEventHandler = () => {


                    if (popup.fired) {
                        console.log("Ignored for  : ", trigger.type);
                        return;
                    }
                    console.log("Marking : ", popup, " as been opened");
                    popup.fired = true;

                    console.log("Showing popup for : ", trigger.type);
                    showPopup(popupDOM);
                }

                let restartTimeout = () => {
                    clearTimeout(timeoutHandle);
                    timeoutHandle = setTimeout(idleEventHandler, trigger.delay_seconds * 1000);
                }

                window.addEventListener("keydown", event => {
                    restartTimeout();
                });

                window.addEventListener("scroll", event => {
                    restartTimeout();
                });

                timeoutHandle = setTimeout(idleEventHandler, trigger.delay_seconds * 1000);


                break;
            }

            case "leaving" : {

                document.body.addEventListener("mouseleave", event => {


                    if (popup.fired) {
                        console.log("Ignored for  : ", trigger.type);
                        return;
                    }
                    console.log("Marking : ", popup, " as been opened");
                    popup.fired = true;


                    setTimeout(() => {
                        showPopup(popupDOM);
                        console.log("Showing popup for : ", trigger.type);
                    }, popup.delay_seconds * 1000);

                })


                break;
            }

            default : {

                throw(trigger.type + " , This trigger does not have handle yet!! ");
            }


        }
    });
}


/**
 * Related with showing and hidding popup
 */


let popupQueue = [];
let isPopupOpen = false;
let activePopupHandle;


let showPopup = (popupDOM) => {
    popupQueue.unshift(popupDOM);
    if (!isPopupOpen) {
        addPopuptoBody(popupQueue.pop());
    }
}

let hidePopup = (popupDOM) => {
    removePopupFromBody(popupDOM);
    if (popupQueue.length > 0) {
        setTimeout(() => {
            addPopuptoBody(popupQueue.pop());
        }, 1000);
    }
}

// Adds the popup DOM to the body of document
let addPopuptoBody = (popupDOM) => {

    popupDOM.style.display = "block";

    // Re enable the document to be able to scroll
    document.body.parentElement.classList.add("no-scroll");

    isPopupOpen = true;
    activePopupHandle = popupDOM;


    // Start listening for Escape key
    window.addEventListener("keyup", escapeKeyListener);

}

// Removes the popup DOM from the body of document
let removePopupFromBody = (popupDOM) => {

    popupDOM.style.display = "none";

    // Disable  the document from being able to scroll
    document.body.parentElement.classList.remove("no-scroll");

    isPopupOpen = false;
    activePopupHandle = undefined;

    // Stop listening for Escape key
    window.removeEventListener("keyup", escapeKeyListener);

}


window.addEventListener("message", message => {
    let data = message.data;
    switch (data.action) {
        case "close_popup" : {
            closeButtonListener();
        }
    }
});


let closeButtonListener = () => {
    console.log("closing popup")
    let closeButton = activePopupHandle.querySelector(".popup-close-btn");
    closeButton.click();
}

let escapeKeyListener = (event) => {
    if (event.key == "Escape") {
        let closeButton = activePopupHandle.querySelector(".popup-close-btn");
        closeButton.click();
    }
}

/*
 * Automatically Call Important Functions
 *
 */

(function () {
    fetch_popups();
})();


