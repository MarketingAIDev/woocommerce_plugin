/**
 * Constant Values
 */

"use strict";


let lastFechedPage = 0;
let isReviewFetchInProgress = false;
let reviewFilter_star = "0"
let loadMoreClicked=false;

let reviewHeader;
let emptyReviewMessageWrapper;

let allReviews;
// Container for customer reviews
let customerReviewList;
let loadMoreReviewsButton;
let filterStarSelect;

// Start Review write button
let writeReviewButtons = reviewShadowRoot.querySelectorAll(".write-review-button");


//let wizardNavigatorFill;

//Array of all the review wizards that are found 
let reviewWizards;


let last;

/**
 * Quickview
 */

let quickviewOverlay;


(function () {

    if (document.readyState == "complete") {
        initReviewMain();
    } else {
        window.addEventListener('load', event => {
            initReviewMain();
        })
    }

})()


function initReviewMain() {
    /**
     *  Get reference of elements on  page
     */


    customerReviewList = reviewShadowRoot.querySelector(".customers-review");


    reviewHeader = reviewShadowRoot.querySelector(".review-header");
    allReviews = reviewShadowRoot.querySelector(".all-reviews");
    customerReviewList = allReviews.querySelector(".customers-review-list");
    loadMoreReviewsButton = reviewShadowRoot.querySelector("#load-more-review-button");
    filterStarSelect = reviewShadowRoot.querySelector("#review-header--filters_select");
    emptyReviewMessageWrapper = reviewShadowRoot.querySelector(".customers-review--noreview ");


    quickviewOverlay = reviewShadowRoot.querySelector(".quickview-overlay");


    reviewWizards = reviewShadowRoot.querySelectorAll(".review-wizard");


    loadMoreReviewsButton.addEventListener("click", event => {
        loadMoreClicked=true;
        fetchReviews(++lastFechedPage);
    });
    filterStarSelect.addEventListener("change", event => {
        reviewFilter_star=event.target.value;
        fetchReviews();
    });

    let fetchReviewAgregate = () => {

        let fetch_url;

        fetch_url = new URL(REVIEW_AGGREGATE_FETCH_API);
        fetch_url.searchParams.append("shop_name", REVIEW_SHOP_NAME);
        fetch_url.searchParams.append("product_id", REVIEW_PRODUCT_ID);
        fetch_url.searchParams.append("client_uid", REVIEW_CLIENT_ID);


        let agregateLoaderAjax = new XMLHttpRequest();
        agregateLoaderAjax.open("GET", fetch_url);
        agregateLoaderAjax.responseType = "json";
        agregateLoaderAjax.onload = event => {
            if (agregateLoaderAjax.status === 200) {

                let reviewAgregateObject = agregateLoaderAjax.response;
                // If there is any review

                if (reviewAgregateObject.total_reviews > 0) {

                    fetchReviews();
                    /**
                     *
                     * Build the review summary
                     *
                     *  */

                    let reviewSummary = reviewShadowRoot.querySelector(".review-header--summary");
                    let reviewStars = reviewSummary.querySelector(".review-header--summary--stars");
                    let starOnTemplate = reviewStars.querySelector(".star-on-template");
                    let starOffTemplate = reviewStars.querySelector(".star-off-template");

                    let reviewCount = reviewSummary.querySelector(".review-header--summary--count");

                    // Add starts to the header
                    for (let i = 1; i <= 5; i++) {
                        let star;
                        if (i <= reviewAgregateObject.average_score) {
                            star = starOnTemplate.cloneNode(true).content;
                        } else {
                            star = starOffTemplate.cloneNode(true).content;
                        }
                        reviewStars.appendChild(star);
                    }
                    // Update the total reviews counter
                    reviewCount.innerText = reviewAgregateObject.total_reviews + " Reviews";


                    reviewHeader.classList.remove("hidden");
                    emptyReviewMessageWrapper.classList.add("hidden");

                } else if (reviewAgregateObject.total_reviews < 1) {

                    reviewHeader.classList.add("hidden");
                    emptyReviewMessageWrapper.classList.remove("hidden");
                    return;
                }


            }
        }

        agregateLoaderAjax.send();

    }

    let fetchReviews = page => {

        // Make the function synchrinous
        if (isReviewFetchInProgress) {
            return;
        }
        isReviewFetchInProgress = true;

        let review_fetch_url = new URL(REVIEW_FETCH_API);
        review_fetch_url.searchParams.append("shop_name", REVIEW_SHOP_NAME);
        review_fetch_url.searchParams.append("client_uid", REVIEW_CLIENT_ID);
        review_fetch_url.searchParams.append("product_id", REVIEW_PRODUCT_ID);
        review_fetch_url.searchParams.append("per_page", REVIEW_FETCH_PER_PAGE.toString());
        review_fetch_url.searchParams.append("page", page);
        review_fetch_url.searchParams.append("filter_stars", reviewFilter_star);

        let reviews_fetch_ajax = new XMLHttpRequest();
        reviews_fetch_ajax.open("GET", review_fetch_url.toString());
        reviews_fetch_ajax.onload = () => {
            if (reviews_fetch_ajax.status === 200) {
                if(!loadMoreClicked){
                    customerReviewList.innerHTML = '';
                    loadMoreClicked=false
                }
                let responseObject = JSON.parse(reviews_fetch_ajax.responseText);
                let responseItems = responseObject.items;
                let reviewItems = responseItems.data;

                // Check if there exist any review

                if (responseItems.total < 1) {
                    reviewHeader.classList.add("hidden");
                    emptyReviewMessageWrapper.classList.remove("hidden");
                    return;
                } else {
                    reviewHeader.classList.remove("hidden");
                    emptyReviewMessageWrapper.classList.add("hidden");
                }

                // Check if this is the last page
                if (responseItems.current_page.toString() === responseItems.last_page.toString()) {
                    // Hide the "Load More" button
                    loadMoreReviewsButton.classList.add("hidden");
                } else {
                    // Show the "Load More" button
                    loadMoreReviewsButton.classList.remove("hidden");
                }


                lastFechedPage = responseItems.current_page;


                let customerReviewTemplate = allReviews.querySelector("#customer-review-template");
                let customerReviewTemplateContent = customerReviewTemplate.content;

                reviewItems.forEach(review => {

                    let customerReviewClone = customerReviewTemplateContent.firstElementChild.cloneNode(true);

                    if (review.images.length > 0) {

                        let customerReviewClonImagePlaceholder = customerReviewClone.querySelector(".customer-review--customer-picture--placeholder");
                        let customerReviewClonImageTemplate = customerReviewClone.querySelector(".customer-review--customer-picture--template");

                        let customerReviewClonImage = customerReviewClonImageTemplate.content.firstElementChild;
                        customerReviewClonImage.setAttribute("src", review.images[0].full_path);
                        /**
                         * IMages load lately, so need to reposition   the reviews
                         */
                        customerReviewClonImage.addEventListener("load", event => {
                            updateCustomerReviewBoxSize();
                        });


                        /**Register Events for the review */
                        customerReviewClonImage.addEventListener("click", event => {
                            previewQuickviewReview(review);
                        });


                        customerReviewClonImagePlaceholder.append(customerReviewClonImage);
                    }


                    let customerReviewCloneName = customerReviewClone.querySelector(".customer-review--customer-name");
                    customerReviewCloneName.innerText = review.reviewer_name;

                    let customerReviewCloneVerification = customerReviewClone.querySelector(".customer-review--customer-verification");

                    if (parseInt(review.verified_purchase)) {
                        let verifiedTemplate = customerReviewCloneVerification.querySelector("#verified");
                        customerReviewCloneVerification.appendChild(verifiedTemplate.content);
                    }


                    let customerReviewCloneDateCreated = customerReviewClone.querySelector(".customer-review--review-date");
                    let dateCreated = new Date(review.created_at);
                    customerReviewCloneDateCreated.innerText = dateCreated.toLocaleDateString();
                    customerReviewCloneDateCreated.setAttribute("datetime", dateCreated.toUTCString());


                    let customerReviewCloneStar = customerReviewClone.querySelector(".customer-review--stars");
                    let startOnTempleate = customerReviewCloneStar.querySelector("#star-on-template");
                    let startOffTempleate = customerReviewCloneStar.querySelector("#star-off-template");
                    let reviewStar = parseInt(review.stars);
                    for (let i = 1; i <= 5; i++) {
                        if (i <= reviewStar) {
                            customerReviewCloneStar.append(startOnTempleate.content.cloneNode(true));
                        } else {
                            customerReviewCloneStar.append(startOffTempleate.content.cloneNode(true));
                        }
                    }


                    let customerReviewCloneMessage = customerReviewClone.querySelector(".customer-review--comment");
                    customerReviewCloneMessage.innerText = review.message;

                    let titleReviewCloneMessage = customerReviewClone.querySelector(".customer-review--title");
                    titleReviewCloneMessage.innerText = review.title;

                    customerReviewList.appendChild(customerReviewClone);


                });
                // append to customerReviewList
                updateCustomerReviewBoxSize();

            }
        }

        reviews_fetch_ajax.onreadystatechange = () => {
            if (reviews_fetch_ajax.readyState == 4) {
                console.log("Review fetch completed : ", reviews_fetch_ajax.readyState);
                isReviewFetchInProgress = false;
            }

        }
        reviews_fetch_ajax.send();
    }


    reviewWizards.forEach(reviewWizard => {


        let wizardSlides = reviewWizard.querySelector(".slides");
        let wizardSlidesCount = wizardSlides.childElementCount;


        let wizardBackButton = reviewWizard.querySelector(".slide-nav-back");
        let wizardSkipButton = reviewWizard.querySelector(".slide-nav-skip");

        let wizardCloseButton = reviewWizard.querySelector(".review-wizard-close-btn");

        // let wizardNavigatorFill = reviewWizard.querySelector(".review-progress-filled");


        /**
         * Alerts
         */

        let moveToPreviousSlide = () => {
            // set current visisble slide to (activeSlideIdp-1)
            let activeSlide = parseInt(reviewWizard.dataset.activeSlideId);
            let newActiveSlide = activeSlide - 1;

            setActiveSlide(newActiveSlide);
        }
        let moveToNextSlide = () => {

            let activeSlideId = parseInt(reviewWizard.dataset.activeSlideId);
            setActiveSlide(activeSlideId + 1);
        }

        wizardBackButton.addEventListener("click", event => {
            moveToPreviousSlide();
        });

        wizardSkipButton.addEventListener("click", event => {
            moveToNextSlide();
        });


        function writeReview() {
            let wizardOverlay = reviewShadowRoot.querySelector(".ew-review-wizard-overlay");
            wizardOverlay.classList.remove("hidden");
            reviewWizard.classList.remove("hidden");


            //
            window.document.documentElement.style.overflow = "hidden";


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

        writeReviewButtons.forEach(button => {
            button.addEventListener("click", event => {
                writeReview()
            });
        })

        window.addEventListener("message", (event) => {
            if (event.data.action === "write-ew-review")
                writeReview()
            if (event.data.action === "ew-summery-rate-bar"){
                reviewFilter_star=event.data.value;
                filterStarSelect.value=event.data.value
                fetchReviews()
            }
        }, false);


        wizardCloseButton.addEventListener("click", event => {
            closeReviewWizard();
        });

        /**
         * Get reference to wizard forms
         */

        let ratingForm = reviewWizard.querySelector("#rating-form");
        let picturesForm = reviewWizard.querySelector("#pictures-form");

        // Asks user to upload a photo
        let imageChooserAsk = picturesForm.querySelector(".slide-options-image-chooser-askuser");
        let choosePictureButton = imageChooserAsk.querySelector("#choose-picture-button");

        let imageChooser = picturesForm.querySelector(".slide-options-image-chooser");

        let pictureInput = imageChooser.querySelector("#pictureInput");
        let appendPictureButton = imageChooser.querySelector("#append-picture-button");

        let selectedImagesList = imageChooser.querySelector(".slide-options-image-list");
        let selectedImageTemplate = imageChooser.querySelector("#slide-options-image-template");


        let messageForm = reviewWizard.querySelector("#message-form");
        let aboutReviewerForm = reviewWizard.querySelector("#about-reviewer-form");
        let thankYouForm = reviewWizard.querySelector("#thank-you-form");


        /**
         * Globally accesible form values
         */

        let userRating = undefined;
        let allSelectedPictures = []

        let userMessageTitle = undefined
        let userMessage = undefined;

        let userFname = undefined;
        let userLname = undefined;
        let userEmail = undefined;


        /**
         *
         * Register Event Listeners for Slide Forms
         *
         */
        ratingForm.addEventListener("submit", event => {
            event.preventDefault();
            let submitter = event.submitter;

            let rating = parseInt(submitter.dataset.rating_value);
            if (rating >= 1 && rating <= 5) {
                userRating = rating;
                // Show the next slide
                moveToNextSlide();
            } else {
                throw("Un expected error occured ");
            }


        });

        picturesForm.addEventListener("submit", event => {
            event.preventDefault();

            if (allSelectedPictures.length > 0) {
                moveToNextSlide();
            } else {
                showWizardErrorAlert("Please Choose at least 1 picture or skip the process");
            }

        });


        choosePictureButton.addEventListener("click", event => {

            imageChooserAsk.classList.add("hidden");
            imageChooser.classList.remove("hidden");

            pictureInput.click();

        });

        appendPictureButton.addEventListener("click", event => {
            event.preventDefault();

            // Check if the already selacted images are not more than 10.
            if (allSelectedPictures.length == 10) {
                showWizardErrorAlert("Maximum files to add is only 10");
                return;
            }

            pictureInput.click();
        });

        pictureInput.addEventListener("change", event => {

            let selectedFiles = Array.from(pictureInput.files);
            let selectedFilesSize = selectedFiles.length;
            let alreadySelectedFilesSize = allSelectedPictures.length;

            // Check if number of already selected images and currently selected images do not exceed 10

            if ((selectedFilesSize + alreadySelectedFilesSize) > 10) {

                showWizardErrorAlert(`You can only add ${10 - alreadySelectedFilesSize} more image`);
                return;
            }
            selectedFiles.forEach(file => {
                if (!file.type.includes("image/")) {
                    showWizardErrorAlert("Please choose only Images");
                    return;
                }

                let picture_id = Math.round(Math.random() * 1000)
                let selectedFile = {
                    id: picture_id,
                    file: file
                }

                allSelectedPictures.push(selectedFile);


                // Make UI for the selected file.

                let selectedFileImage = selectedImageTemplate.content.firstElementChild.cloneNode(true);

                let selectedFileImageRemove = selectedFileImage.querySelector(".slide-options-image-remove");
                let selectedFileImageImg = selectedFileImage.querySelector(".slide-options-image-img");

                selectedFileImageRemove.dataset.picture_id = picture_id;
                selectedFileImageRemove.addEventListener("click", event => {

                    // Remove Target from
                    let target = event.target;
                    let picture_id = parseInt(target.dataset.picture_id);

                    console.log("Removing file with Id : ", picture_id);

                    // Remove from UI
                    let targetImage = target.closest(".slide-options-image");
                    targetImage.remove();

                    // Remove from Array
                    for (let i = 0; i < allSelectedPictures.length; i++) {
                        if (allSelectedPictures[i].id === picture_id) {
                            allSelectedPictures.splice(i, 1);
                        }
                    }

                });

                selectedFileImageImg.setAttribute("src", URL.createObjectURL(file));

                selectedImagesList.append(selectedFileImage, appendPictureButton);
            });


            appendPictureButton.scrollIntoView();
        });

        messageForm.addEventListener("submit", event => {
            event.preventDefault();
            let target = event.target;
            let title = target.elements["title"].value;
            let message = target.elements["message"].value;

            /**
             * Check if Message title and message are present
             */
            if (title.length < 1) {
                showWizardErrorAlert("Please Enter your message title");
                return;
            }

            if (message.length < 1) {
                showWizardErrorAlert("Please Enter your message");
                return;
            }

            userMessageTitle = title;
            userMessage = message;

            /**
             * Navigate to the next slide
             */
            moveToNextSlide();

        });

        aboutReviewerForm.addEventListener("submit", event => {

            event.preventDefault();

            let target = event.target;

            let lname = target.elements["lname"].value;
            let fname = target.elements["fname"].value;
            let email = target.elements["email"].value;

            /**
             * Check if first name,last name and email is provided
             */

            if (fname.length < 1) {
                showWizardErrorAlert("Please enter first name");
                return;
            }


            if (lname.length < 1) {
                showWizardErrorAlert("Please enter  last name");
                return;
            }


            if (email.length < 1) {

                showWizardErrorAlert("Please enter your email");
                return;
            }


            userFname = fname;
            userLname = lname;
            userEmail = email;

            sendForm();


        });

        thankYouForm.addEventListener("submit", event => {
            event.preventDefault();
            closeReviewWizard();

            window.location.reload();

        });

        /**
         * Register event listeer for misc elements
         */


        let setActiveSlide = (slideId) => {

            // hide previously active slide,
            let activeSlideId = parseInt(reviewWizard.dataset.activeSlideId);

            if (activeSlideId) {
                let activeSlide = wizardSlides.querySelector(`[data-slide-num='${activeSlideId}']`);
                activeSlide.classList.add("hidden");
            }

            let nextActiveSlide = wizardSlides.querySelector(`[data-slide-num='${slideId}']`);
            nextActiveSlide.classList.remove("hidden");

            reviewWizard.dataset.activeSlideId = slideId;

            /**
             *  Hide the back button, if set to the first slide
             */
            if (slideId == 1) {
                wizardBackButton.classList.add("hidden");
            } else {
                wizardBackButton.classList.remove("hidden");
            }

            /**
             * Hide Skip button if it is not supported
             */
            let isSlideSkippabele = eval(nextActiveSlide.dataset.slide_show_skip);

            if (isSlideSkippabele) {
                wizardSkipButton.classList.remove("hidden");
            } else {
                wizardSkipButton.classList.add("hidden");
            }

        }


        let closeReviewWizard = () => {

            let wizardOverlay = reviewShadowRoot.querySelector(".ew-review-wizard-overlay");
            wizardOverlay.classList.add("hidden");

            reviewWizard.classList.add("hidden");
            window.document.documentElement.style.overflow = "auto";


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

        let showWizardErrorAlert = messageText => {
            showWizardAlert("ERROR", messageText);
        }

        let showWizardInfoAlert = messageText => {
            showWizardAlert("INFO", messageText);
        }

        let showWizardAlert = (type, messageText) => {

            let wizardAlertList = reviewWizard.querySelector(".wizard-alert-list");
            let alertTemplate = wizardAlertList.querySelector(".wizard-alert-template");
            let alert = alertTemplate.content.firstElementChild.cloneNode(true);

            // Set appropriate classList depending in an argument
            switch (type) {
                case "ERROR" : {
                    alert.classList.add("error-alert");
                    break;
                }

                case "INFO" : {
                    alert.classList.add("info-alert");
                    break;
                }

                default : {
                    throw(`${type} is not a supported error type`);
                }
            }

            // Set Message
            let alertMessage = alert.querySelector(".wizard-alert-message");
            alertMessage.innerText = messageText;

            // Add Event Listener for close button
            let alertClose = alert.querySelector(".wizard-alert-close");
            alertClose.addEventListener("click", event => {
                let theAlert = event.target.closest(".wizard-alert");
                theAlert.remove();
            });

            // Add the cloned message alert to container
            wizardAlertList.appendChild(alert);

            // Remove the alert after specific timeout
            setTimeout(() => {
                alert.remove();
            }, 3000);

        }

        /**
         *
         * Submit customers review form
         *
         */

        let alredySending = false;
        let sendForm = (isRetry = false) => {

            if (alredySending) {
                console.log('Another op in prog');
                return;
            }

            alredySending = true;


            /**
             * Used to show progress via animation and upload error message, during upload
             */

            let SubmittingAnimationWrapper;
            let submittingProgressFill;


            let submitingDiv;
            let errorMessageDiv;
            let errorMessage;
            let retryButton;


            SubmittingAnimationWrapper = reviewWizard.querySelector("#submitting-animation");
            submittingProgressFill = SubmittingAnimationWrapper.querySelector(".submitting-progress-fill");

            submitingDiv = SubmittingAnimationWrapper.querySelector("#submitting-animation-uploading");
            errorMessageDiv = SubmittingAnimationWrapper.querySelector("#submitting-animation-error");
            errorMessage = errorMessageDiv.querySelector(".submitting-error-message");

            retryButton = errorMessageDiv.querySelector("#submit-animation-retry-btn");
            retryButton.addEventListener("click", event => {
                sendForm(true);
            });


            let formData = new FormData();

            if (userFname != undefined && userLname != undefined) {
                formData.append("reviewer_name", userFname + " " + userLname);
            }

            if (userEmail != undefined) {
                formData.append("reviewer_email", userEmail);
            }
            if (userRating != undefined) {
                formData.append("stars", userRating);
            }
            if (userMessageTitle != undefined) {
                formData.append("title", userMessageTitle);
            }
            if (userMessage != undefined) {
                formData.append("message", userMessage);
            }

            for (let i = 0; i < allSelectedPictures.length; i++) {
                formData.append(`images[${i}]`, allSelectedPictures[i].file);
            }

            /**
             * Constant Values
             */
            formData.append("shop_name", REVIEW_SHOP_NAME);
            formData.append("shopify_product_id", REVIEW_PRODUCT_ID);


            let sendReviewAjax = new XMLHttpRequest();


            sendReviewAjax.open("POST", REVIEW_SUBMIT_API);

            sendReviewAjax.onloadstart = () => {

                if (!isRetry) {
                    moveToNextSlide();
                }
            }

            sendReviewAjax.onload = event => {
                if (sendReviewAjax.status == 200) {
                    // Stop animation
                    moveToNextSlide();
                } else {
                    showWizardErrorAlert("Error Submitting Form");
                }
            }

            /**
             *  Upload related events
             */


            sendReviewAjax.upload.onloadstart = event => {

                // Reset progress
                submittingProgressFill.style.width = 0 + "%";

                // Hide the error message and show progress
                submitingDiv.classList.remove("hidden");
                errorMessageDiv.classList.add("hidden");
            }
            sendReviewAjax.upload.onprogress = event => {
                let uploadedPercent = Math.round((event.loaded / event.total) * 100)
                submittingProgressFill.style.width = uploadedPercent + "%";
            }

            sendReviewAjax.upload.onerror = event => {

                submitingDiv.classList.add("hidden");
                errorMessageDiv.classList.remove("hidden");
            }

            sendReviewAjax.onerror = event => {
                submitingDiv.classList.add("hidden");
                errorMessageDiv.classList.remove("hidden");
                errorMessage.innerText = "A network connection error has occured while submitting your review - JS";
            }

            sendReviewAjax.ontimeout = event => {
                submitingDiv.classList.add("hidden");
                errorMessageDiv.classList.remove("hidden");
                errorMessage.innerText = "Timeout while submitting your review - JS";

            }

            sendReviewAjax.onreadystatechange = event => {
                if (sendReviewAjax.readyState == 4) {
                    alredySending = false;
                }
            }

            sendReviewAjax.send(formData);
        }


        /**
         * Calling important functions
         */

        //Show the first slide
        setActiveSlide(1);

    });

    /**
     * Call important functions on page load
     */
    fetchReviewAgregate();

}


// Update the review dispaly  size   when page size gets resized
window.addEventListener("resize", event => {
    updateCustomerReviewBoxSize();
})

let updateCustomerReviewBoxSize = () => {


    let boxMinWidth = 200;

    let boxLeftMargin = 8;
    let boxRightMargin = 8;

    let boxHorizontalMargin = boxLeftMargin + boxRightMargin;

    let boxActualWidth = boxMinWidth + boxHorizontalMargin;


    let columnsPerRow = parseInt(customerReviewList.clientWidth / boxActualWidth);
    if (columnsPerRow < 1) {
        columnsPerRow = 1;
    }

    let unoccupiedSpace = customerReviewList.clientWidth % boxActualWidth;

    let expandedBoxWidth = (boxActualWidth + (unoccupiedSpace / columnsPerRow)) - boxHorizontalMargin;


    // Update all box width to expanded width
    let customerReviews = customerReviewList.querySelectorAll(".customer-review");
    customerReviews.forEach(review => {
        review.style.width = expandedBoxWidth + "px";
    });


    // Build a table array of elemts
    let allRows = [];
    let customerReviewsClone = Array.from(customerReviews);
    while (customerReviewsClone.length > 0) {
        let rowArray = customerReviewsClone.splice(0, columnsPerRow);
        allRows.push(rowArray);
    }


    // Build column references
    let cloumns = [];
    for (let i = 0; i < columnsPerRow; i++) {
        cloumns.push({
            lastTopPosition: 0
        });
    }

    // Update locatin for all items
    allRows.forEach((row, i) => {
        let lastLeftPosition = 0;
        row.forEach((element, columnIndex) => {

            // indicates column
            element.style.left = lastLeftPosition + "px";
            element.style.top = cloumns[columnIndex].lastTopPosition + "px";

            lastLeftPosition += expandedBoxWidth + boxHorizontalMargin;
            cloumns[columnIndex].lastTopPosition += element.offsetHeight + 16;

        });

    });


    // Update the customerReviewList Height to the longest column
    let longestColumn;
    cloumns.forEach(column => {
        if (!longestColumn) {
            longestColumn = column;
        }
        if (column.lastTopPosition > longestColumn.lastTopPosition) {
            longestColumn = column;
        }
    });

    customerReviewList.style.height = longestColumn.lastTopPosition + "px";

}
