let reviewScriptUrl = new URL(document.currentScript.getAttribute("src"));
const version_code_review="0.11";
const REVIEW_SHOP_NAME = reviewScriptUrl.searchParams.get("shop");
const REVIEW_CLIENT_ID = reviewScriptUrl.searchParams.get("client_uid");
// This field should had been a constant, but it would be filled from data- attribute latter
let REVIEW_PRODUCT_ID;


const SERVER_ROOT = "https://builder.emailwish.com/ew-front/review/"

const REVIEW_FETCH_API = "https://builder.emailwish.com/_shopify/embedShopifyReviews";
//const REVIEW_AGGREGATE_FETCH_API = "https://builder.emailwish.com/_shopify/embedShopifyReviews";
const REVIEW_SUBMIT_API = "https://builder.emailwish.com/_shopify/storeFromReviewer";
const REVIEW_DOM_STRING_URL = SERVER_ROOT + "all_reviews_dom.html";
const REVIEW_FETCH_PER_PAGE = 5;


/**
 * THe following are only related to summary review
 */

const REVIEW_AGGREGATE_FETCH_API = "https://builder.emailwish.com/_shopify/reviewStats";

// A url of the dom 
const SUMMARY_REVIEW_DOMStringURL = SERVER_ROOT + "review_summary_dom.html";
let reviewShadowRoot;


let rs_domPlaceHolder;
let rs_shadow;
let rs_options;


(function () {


    if (document.readyState === "complete") {
        initAllReviews();
        rs_init();

    } else {

        window.addEventListener("load", event => {
            initAllReviews();
            rs_init();

        })
    }


})();


function initAllReviews() {

    let reviewPlaceholder = document.querySelector("#ew-review");
    if (!reviewPlaceholder) {
        console.error("Please define a DIV tag where the reviews will be placed, with ID of \"ew-review\"  ");
        return;
    }

    REVIEW_PRODUCT_ID = reviewPlaceholder.dataset.product_id;

    //reviewPlaceholder.attachShadow({mode: "open"});
    reviewShadowRoot = reviewPlaceholder

    createReviewDOM()
        .then(dom => {
            reviewShadowRoot.appendChild(dom);
            addScripts();
        })


}
function createReviewDOM() {

    return new Promise((res, rej) => {

        fetch(REVIEW_DOM_STRING_URL)
            .then(result => {
                result.text()
                    .then(result => {
                        let tmp = document.createElement("div");
                        tmp.className="mb-50"
                        tmp.innerHTML = result;

                        res(tmp.firstElementChild.parentElement);
                    })
            })


    })


}


function addScripts() {

    console.log("Adding Scripts")

    let scriptSkelton = document.createElement("script");
    scriptSkelton.setAttribute("async", "");
    scriptSkelton.setAttribute("type", "text/javascript");

    let mainScript = scriptSkelton.cloneNode(true);
    mainScript.setAttribute("src", SERVER_ROOT + "res/script/main.js?v="+version_code_review);

    let quickviewScript = scriptSkelton.cloneNode(true);
    quickviewScript.setAttribute("src", SERVER_ROOT + "res/script/quickview.js?v="+version_code_review);

    document.head.append(mainScript);
    document.head.append(quickviewScript);
}


/**
 * #############################################################################
 * #############################################################################
 * #############################################################################
 * ###### The follwing functions are only related to review summary ############
 * #############################################################################
 * #############################################################################
 * #############################################################################
 */



function rs_init() {
    // Initialize

    rs_domPlaceHolder = document.querySelector("#ew-review-summary");
    if (!rs_domPlaceHolder) {
        console.error("Please define a DIV tag where the review summary will be placed, with ID of \"ew-review-summary\"  ");
        return;
    }

    rs_shadow = rs_domPlaceHolder;

    /**
     * Get the product id from the div data- attribute
     */
    let REVIEW_PRODUCT_ID = rs_domPlaceHolder.dataset.product_id;

    rs_options = {
        "server_root": SERVER_ROOT,
        "review_summary_fetch_url": REVIEW_AGGREGATE_FETCH_API,
        "DOMStringURL": SUMMARY_REVIEW_DOMStringURL,

        "client_uid": REVIEW_CLIENT_ID,
        "shop_name": REVIEW_SHOP_NAME,
        "product_id": REVIEW_PRODUCT_ID
    }


    // Initialize the imported module
    rs_initModule2(rs_options, rs_dom => {
        rs_shadow.append(rs_dom);
        let writeReviewButtons = rs_shadow.querySelectorAll(".write-review-button");
        writeReviewButtons.forEach(button => {
            button.addEventListener("click", event => {
                window.postMessage({"action":"write-ew-review"},"*");
            });
        })
        let summery_rate_5= rs_shadow.querySelectorAll(".ew-summery-rate-bar-5");
        summery_rate_5.forEach(e => {
            e.addEventListener("click", event => {
                window.postMessage({"action":"ew-summery-rate-bar","value":"5"},"*");
            });
        })
        let summery_rate_4= rs_shadow.querySelectorAll(".ew-summery-rate-bar-4");
        summery_rate_4.forEach(e => {
            e.addEventListener("click", event => {
                window.postMessage({"action":"ew-summery-rate-bar","value":"4"},"*");
            });
        })
        let summery_rate_3= rs_shadow.querySelectorAll(".ew-summery-rate-bar-3");
        summery_rate_3.forEach(e => {
            e.addEventListener("click", event => {
                window.postMessage({"action":"ew-summery-rate-bar","value":"3"},"*");
            });
        })
        let summery_rate_2= rs_shadow.querySelectorAll(".ew-summery-rate-bar-2");
        summery_rate_2.forEach(e => {
            e.addEventListener("click", event => {
                window.postMessage({"action":"ew-summery-rate-bar","value":"2"},"*");
            });
        })
        let summery_rate_1= rs_shadow.querySelectorAll(".ew-summery-rate-bar-1");
        summery_rate_1.forEach(e => {
            e.addEventListener("click", event => {
                window.postMessage({"action":"ew-summery-rate-bar","value":"1"},"*");
            });
        })
    });


}



/***
 *
 * The Second Module
 *
 */


function fetchReviewSummary(url) {

    return new Promise((resolve, reject) => {
        fetch(url)
            .then(result => {
                result.json()
                    .then(result => {
                        //reviewSummary = result;
                        resolve(result)
                    })
            })

    })

}

function fetchSummaryReviewString(url) {

    return new Promise((resolve, reject) => {
        fetch(url)
            .then(result => {
                result.text()
                    .then(result => {
                        //reviewSummaryDOMString = result;
                        resolve(result);
                    });
            })

    });

}


function makeDOM(reviewSummary, textString) {

    let dom = document.createElement("div");
    dom.style.width="100%"
    dom.innerHTML = textString;


    /**
     * Related to stars
     */
    // let starsPlaceholder = dom.querySelector("#stars");
    //
    // let starOffTemplate = dom.querySelector("#star-off-template");
    // let starOnTemplate = dom.querySelector("#star-on-template");
    //
    // let starOff = starOffTemplate.content.firstElementChild;
    // let startOn = starOnTemplate.content.firstElementChild;
    //
    //
    // for (let i = 1; i <= 5; i++) {
    //     if (i < reviewSummary.average_score) {
    //         starsPlaceholder.appendChild(startOn.cloneNode(true));
    //     } else {
    //         starsPlaceholder.appendChild(starOff.cloneNode(true));
    //     }
    // }
    //
    //
    let averageScoreLabel = dom.querySelector("#average-score-label");
    let revireCountLabel = dom.querySelector("#review-count-label");

    averageScoreLabel.innerText = reviewSummary.average_score.toFixed(1);
    revireCountLabel.innerText = reviewSummary.total_reviews;


    /**
     *
     * Related to bar indicators
     *
     */

    if(reviewSummary.total_reviews===0)reviewSummary.total_reviews=1;
    let percent5 = (reviewSummary.stars_5 / reviewSummary.total_reviews) * 100;
    let percent4 = (reviewSummary.stars_4 / reviewSummary.total_reviews) * 100;
    let percent3 = (reviewSummary.stars_3 / reviewSummary.total_reviews) * 100;
    let percent2 = (reviewSummary.stars_2 / reviewSummary.total_reviews) * 100;
    let percent1 = (reviewSummary.stars_1 / reviewSummary.total_reviews) * 100;

    /**
     * Related to Bars
     */

    let bar5 = dom.querySelector("#bar5");
    let bar4 = dom.querySelector("#bar4");
    let bar3 = dom.querySelector("#bar3");
    let bar2 = dom.querySelector("#bar2");
    let bar1 = dom.querySelector("#bar1");


    bar5.style.width = percent5 + '%';
    bar4.style.width = percent4 + '%';
    bar3.style.width = percent3 + '%';
    bar2.style.width = percent2 + '%';
    bar1.style.width = percent1 + '%';

    /**
     * Related to Bar Label
     */

    let barLabel5 = dom.querySelector("#bar-label5");
    let barLabel4 = dom.querySelector("#bar-label4");
    let barLabel3 = dom.querySelector("#bar-label3");
    let barLabel2 = dom.querySelector("#bar-label2");
    let barLabel1 = dom.querySelector("#bar-label1");

    barLabel5.innerText = reviewSummary.stars_5;
    barLabel4.innerText = reviewSummary.stars_4;
    barLabel3.innerText = reviewSummary.stars_3;
    barLabel2.innerText = reviewSummary.stars_2;
    barLabel1.innerText = reviewSummary.stars_1;


    let reviewStars = dom.querySelector(".rating-star-wrapper");
    let starOnTemplate = reviewStars.querySelector(".star-on-template");
    let starOffTemplate = reviewStars.querySelector(".star-off-template");

    // Add starts to the header
    for (let i = 1; i <= 5; i++) {
        let star;
        if (i <= reviewSummary.average_score) {
            star = starOnTemplate.cloneNode(true).content;
        } else {
            star = starOffTemplate.cloneNode(true).content;
        }
        reviewStars.appendChild(star);
    }

    return dom;

}

function rs_initModule2(options, callback) {

    let summary_fetch_url = options.review_summary_fetch_url;
    let client_uid = options.client_uid;
    let shop_name = options.shop_name;
    let product_id = options.product_id;

    let url = new URL(summary_fetch_url);

    url.searchParams.append("client_uid", client_uid);
    url.searchParams.append("shop_name", shop_name);
    url.searchParams.append("product_id", product_id);

    console.log(url.toString());


    let DOMStringURL = options.DOMStringURL;


    let promise1 = fetchReviewSummary(url);
    let promise2 = fetchSummaryReviewString(DOMStringURL);

    // Wait until all promisses fulfill
    Promise.all([promise1, promise2]).then(result => {

        let reviewSummary = result[0];
        let reviewDOMString = result[1];

        let dom = makeDOM(reviewSummary, reviewDOMString);

        let writeReviewButtons = document.querySelectorAll(".write-review-button");
        writeReviewButtons.forEach(button => {
            button.addEventListener("click", event => {

                console.log("dsad")

            });
        })


        callback(dom);
    })


}