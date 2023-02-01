$(function () {
    const reviews = $('#ew_reviews');
    if (reviews.length > 0) {
        const shop_name = reviews.data('shopName');
        const product_id = reviews.data('productId');
        $.get("https://builder.emailwish.com/_shopify/embedShopifyReviews?shop_name=" + shop_name + "&product_id=" + product_id,
            function (data, status) {
                if (status === "success") {
                    reviews.html(data);
                }
            });
    }

    $(document).on("submit", "#ew_review_form", function (e) {
        e.preventDefault();
        const form_element = document.getElementById("ew_review_form");
        const fd = new FormData(form_element);
        $.ajax({
            url: "https://builder.emailwish.com/_shopify/storeFromReviewer",
            data: fd,
            cache: false,
            processData: false,
            contentType: false,
            type: 'POST',
            xhrFields: {
                withCredentials: true
            },
            success: function (data, status) {
                if (status === "success") {
                    $('#ew_review_result').html(data).show();
                    $('#ew_review_form').hide();
                }
                else{
                    $('#ew_review_result').html("Something went wrong! Please try again").show();
                }
            },
            error:function(){
                $('#ew_review_result').html("Something went wrong! Please try again").show();
            }
        });
    });
});