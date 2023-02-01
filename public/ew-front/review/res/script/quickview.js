"use strict"


let review;

let quikviewPicture;
let quickviewImagePlaceholder;
let quickviewImageTemplate;
let quickviewImageBackground;
let quickviewImage;
let quickviewLoadingPicOverlay;


// Image Navigation related

let quickviewImageSliderTemplate;
let quickviewImageSlider;

let quickviewImageSliderLeft;
let quickviewImageSliderRight;


/**QuickView Image Navigation */

let setQuickviewImage = (index) => {
    /**
     * Preload the image
     */
    let preloadImg = document.createElement("img");
    preloadImg.setAttribute("src", review.images[index].full_path);

    // Show animation while the image preload is in progress.
    quickviewLoadingPicOverlay.classList.remove("hidden");

    //Append load loaded image
    preloadImg.addEventListener("load", event => {

        // Hidde/Stop the overlay animation
        quickviewLoadingPicOverlay.classList.add("hidden");

        //Set the loaded images
        quickviewImageBackground.setAttribute("src", review.images[index].full_path);
        quickviewImage.setAttribute("src", review.images[index].full_path);
        quickviewImage.dataset.imageIndex = index;

    });
}

let quickviewNextImage = () => {

    let oldIndex = parseInt(quickviewImage.dataset.imageIndex);
    let index;
    if (oldIndex == (review.images.length - 1)) {
        index = 0;
    } else {
        index = oldIndex + 1;
    }
    setQuickviewImage(index);
}

let quickviewPreviousImage = () => {

    let oldIndex = parseInt(quickviewImage.dataset.imageIndex);
    let index;
    if (oldIndex == 0) {
        index = (review.images.length - 1);
    } else {
        index = oldIndex - 1;
    }
    setQuickviewImage(index);
}


let previewQuickviewReview = localReview => {
    review = localReview;

    let quickview = buildQuickview(review);
    showQuickview(quickview);
}


let buildQuickview = (review) => {


    let quickviewOverlay = reviewShadowRoot.querySelector(".quickview-overlay");
    let quickviewTemplate = quickviewOverlay.querySelector("#quickview-template");
    let quickview = quickviewTemplate.content.firstElementChild.cloneNode(true);


    if (review.images.length > 0) {

        quikviewPicture = quickview.querySelector(".quikview-picture");

        quickviewImagePlaceholder = quickview.querySelector(".quikview--customer-pictures");
        quickviewImageTemplate = quickview.querySelector(".quikview--customer-picture--template");

        quickviewImageBackground = quickviewImageTemplate.content.querySelector(".quikview-picture-background");
        quickviewImage = quickviewImageTemplate.content.querySelector(".quikview-picture-img");
        quickviewLoadingPicOverlay = quickviewImageTemplate.content.querySelector(".fadingOverlayAnimationPlaceholder");

        quickviewImageBackground.setAttribute("src", review.images[0].full_path);
        quickviewImage.setAttribute("src", review.images[0].full_path);
        quickviewImage.dataset.imageIndex = 0;
        quickviewImagePlaceholder.append(quickviewImageTemplate.content);

        /**
         * Add image navigation if there exist more than 1 image
         */

        if (review.images.length > 1) {

            quickviewImageSliderTemplate = quikviewPicture.querySelector("#quikview-picture-nav-template");
            quickviewImageSlider = quickviewImageSliderTemplate.content.firstElementChild;

            // Add the image navigation to UI
            quikviewPicture.append(quickviewImageSlider);

            let mouseMoveEventBeingHandled = false;
            let mouseMoveEventTimeoutHandle;
            quikviewPicture.addEventListener("mousemove", event => {


                if (mouseMoveEventBeingHandled) {
                    // ignore the event
                    clearTimeout(mouseMoveEventTimeoutHandle);
                    mouseMoveEventTimeoutHandle = setTimeout(() => {
                        quickviewImageSlider.classList.add("hidden");
                        mouseMoveEventBeingHandled = false;
                    }, 2000);
                    return;
                }

                mouseMoveEventBeingHandled = true;
                quickviewImageSlider.classList.remove("hidden");
                console.log(event);


                mouseMoveEventTimeoutHandle = setTimeout(() => {
                    quickviewImageSlider.classList.add("hidden");
                    mouseMoveEventBeingHandled = false;
                }, 2000);

            });

            /**
             * Add Event listeners to navigations buttons
             */
            quickviewImageSliderLeft = quickviewImageSlider.querySelector(".left-button");
            quickviewImageSliderRight = quickviewImageSlider.querySelector(".right-button");

            quickviewImageSliderLeft.addEventListener("click", quickviewPreviousImage);
            quickviewImageSliderRight.addEventListener("click", quickviewNextImage);


        } // More than 1 image ends here

    }  // Image related ends here

    let quickviewName = quickview.querySelector(".customer-review--customer-name");
    quickviewName.innerText = review.reviewer_name;

    let quickviewVerification = quickview.querySelector(".customer-review--customer-verification");

    if (!parseInt(review.verified_purchase)) {
        let verifiedTemplate = quickviewVerification.querySelector("#verified");
        quickviewVerification.appendChild(verifiedTemplate.content);
    }

    let quickviewDateCreated = quickview.querySelector(".customer-review--review-date");
    let dateCreated = new Date(review.created_at);
    quickviewDateCreated.innerText = dateCreated.toLocaleDateString();
    quickviewDateCreated.setAttribute("datetime", dateCreated.toUTCString());


    let quickviewStar = quickview.querySelector(".customer-review--stars");
    let startOnTempleate = quickviewStar.querySelector("#star-on-template");
    let startOffTempleate = quickviewStar.querySelector("#star-off-template");
    let reviewStar = parseInt(review.stars);
    // Printing starts
    for (let i = 1; i <= 5; i++) {
        if (i <= reviewStar) {
            quickviewStar.append(startOnTempleate.content.cloneNode(true));
        } else {
            quickviewStar.append(startOffTempleate.content.cloneNode(true));
        }
    }


    let quickviewMessage = quickview.querySelector(".customer-review--comment");
    quickviewMessage.innerText = review.message;

    let quickviewtitle = quickview.querySelector(".customer-review--title");
    quickviewtitle.innerText = review.title;

    /**
     * Add Event Listeners
     *
     */
    let quickviewCloseBUtton = quickview.querySelector(".quikview-review--close");

    quickviewCloseBUtton.addEventListener("click", event => {
        hiddeQuickview();
    });

    return quickview;
}

let showQuickview = (quickview) => {

    quickviewOverlay.classList.remove("hidden");
    document.documentElement.classList.add("no-scroll");


    let quickviewReviewPlaceholder = quickviewOverlay.querySelector(".customer-review-placeholder");
    quickviewReviewPlaceholder.appendChild(quickview);

    // Add keyup listner to body, to listen for escape key
    document.body.addEventListener('keyup', quickviewKeyListener);


    /***
     * Shopify Only
     *
     * Elements with fixed positioning, that has anccestors with transform applied
     * Will be posioned relative to that parent.
     *
     *
     * Here is a quick fix.
     *
     * Note :
     *     The ancesstor element that has transform applied is : #PageContainer
     *
     *     So need to remove that  CSS transfrom property from the element
     *
     */


    let theShopifyElementWithTransformApplied = document.querySelector("#PageContainer");
    if (theShopifyElementWithTransformApplied) {
        theShopifyElementWithTransformApplied.style.transform = "none";
    }


}

let hiddeQuickview = () => {

    let quickviewReviewPlaceholder = quickviewOverlay.querySelector(".customer-review-placeholder");
    while (quickviewReviewPlaceholder.firstElementChild) {
        quickviewReviewPlaceholder.firstElementChild.remove();
    }

    quickviewOverlay.classList.add("hidden");
    document.documentElement.classList.remove("no-scroll");

    // Remove keyup listner from body, after the quick view is closed
    document.body.removeEventListener('keyup', quickviewKeyListener);


    /***
     * Shopify Only
     *
     * Elements with fixed positioning, that has anccestors with transform applied
     * Will be posioned relative to that parent.
     *
     *
     * Here is a quick fix.
     *
     * Note :
     *     The ancesstor element that has transform applied is : #PageContainer
     *
     *     So need to remove that  CSS transfrom property from the element
     *
     */


    let theShopifyElementWithTransformApplied = document.querySelector("#PageContainer");
    if (theShopifyElementWithTransformApplied) {
        theShopifyElementWithTransformApplied.style.transform = "";

    }

}

let quickviewKeyListener = event => {
    switch (event.code) {
        case "Escape" : {
            // Hide the quick view
            hiddeQuickview();
            break;
        }


        case "ArrowLeft" : {
            if (quickviewImageSliderLeft) {
                quickviewPreviousImage();
            }
            break;
        }

        case "ArrowRight" : {

            quickviewNextImage();
            break;
        }
    }
}


class QuickView {

    constructor(review) {

        let quickviewOverlay = reviewShadowRoot.querySelector(".quickview-overlay");
        let quickviewTemplate = quickviewOverlay.querySelector("#quickview-template");
        let quickview = quickviewTemplate.content.firstElementChild.cloneNode(true);


        if (review.images.length > 0) {

            quikviewPicture = quickview.querySelector(".quikview-picture");

            quickviewImagePlaceholder = quickview.querySelector(".quikview--customer-pictures");
            quickviewImageTemplate = quickview.querySelector(".quikview--customer-picture--template");

            quickviewImageBackground = quickviewImageTemplate.content.querySelector(".quikview-picture-background");
            quickviewImage = quickviewImageTemplate.content.querySelector(".quikview-picture-img");
            quickviewLoadingPicOverlay = quickviewImageTemplate.content.querySelector(".fadingOverlayAnimationPlaceholder");

            quickviewImageBackground.setAttribute("src", review.images[0].full_path);
            quickviewImage.setAttribute("src", review.images[0].full_path);
            quickviewImage.dataset.imageIndex = 0;
            quickviewImagePlaceholder.append(quickviewImageTemplate.content);

            /**
             * Add image navigation if there exist more than 1 image
             */

            if (review.images.length > 1) {

                quickviewImageSliderTemplate = quikviewPicture.querySelector("#quikview-picture-nav-template");
                quickviewImageSlider = quickviewImageSliderTemplate.content.firstElementChild;

                // Add the image navigation to UI
                quikviewPicture.append(quickviewImageSlider);


                /**
                 * Register Event Listeners
                 */

                quikviewPicture.addEventListener("mouseenter", event => {
                    // Auto show the image navigation when the mouse enterrs
                    quickviewImageSlider.classList.remove("hidden");
                });

                /**
                 * Auto hide image navigation  when mouse leaves
                 */
                let quickViewHideTimeout;
                quikviewPicture.addEventListener("mouseleave", event => {
                    clearTimeout(quickViewHideTimeout);
                    // Hide the quickview pictures navigation, after a few secs
                    quickViewHideTimeout = setTimeout(() => {
                        quickviewImageSlider.classList.add("hidden");
                    }, 1000);

                });


                /**
                 * Add Event listeners to navigations buttons
                 */
                quickviewImageSliderLeft = quickviewImageSlider.querySelector(".left-button");
                quickviewImageSliderRight = quickviewImageSlider.querySelector(".right-button");

                quickviewImageSliderLeft.addEventListener("click", quickviewPreviousImage);
                quickviewImageSliderRight.addEventListener("click", quickviewNextImage);


            } // More than 1 image ends here

        }  // Image related ends here

        let quickviewName = quickview.querySelector(".customer-review--customer-name");
        quickviewName.innerText = review.reviewer_name;

        let quickviewVerification = quickview.querySelector(".customer-review--customer-verification");

        if (!parseInt(review.verified_purchase)) {
            let verifiedTemplate = quickviewVerification.querySelector("#verified");
            quickviewVerification.appendChild(verifiedTemplate.content);
        }

        let quickviewDateCreated = quickview.querySelector(".customer-review--review-date");
        let dateCreated = new Date(review.created_at);
        quickviewDateCreated.innerText = dateCreated.toLocaleDateString();
        quickviewDateCreated.setAttribute("datetime", dateCreated.toUTCString());


        let quickviewStar = quickview.querySelector(".customer-review--stars");
        let startOnTempleate = quickviewStar.querySelector("#star-on-template");
        let startOffTempleate = quickviewStar.querySelector("#star-off-template");
        let reviewStar = parseInt(review.stars);
        // Printing starts
        for (let i = 1; i <= 5; i++) {
            if (i <= reviewStar) {
                quickviewStar.append(startOnTempleate.content.cloneNode(true));
            } else {
                quickviewStar.append(startOffTempleate.content.cloneNode(true));
            }
        }


        let quickviewMessage = quickview.querySelector(".customer-review--comment");
        quickviewMessage.innerText = review.message;

        let quickviewTitle = quickview.querySelector(".customer-review--title");
        quickviewTitle.innerText = review.title;

        /**
         * Add Event Listeners
         *
         */
        let quickviewCloseBUtton = quickview.querySelector(".quikview-review--close");

        quickviewCloseBUtton.addEventListener("click", event => {
            hiddeQuickview();
        });

        this.quickview = quickview;
    }


}