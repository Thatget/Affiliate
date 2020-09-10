var span_change_product = false;
var max_qty_value, min_ppl_value;
var class_validate_deal_qty = '';
require(['jquery'],function($){
    $(window).load(function() {
        var self = this;
        $("#page_dailydeal_price").change(function () {
            var product_price = $('#page_product_price')[0].value;
            var deal_price = $('#page_dailydeal_price')[0].value;
            var one_page_discount = product_price / 100;
            var page_discount = (product_price - deal_price) / one_page_discount;
            var page_discount = Math.round(page_discount * 100) / 100;

            if (typeof page_discount == 'number') {
                $('#page_discount')[0].value = page_discount;
            }
        });
        $("#page_discount").change(function () {
            var product_price = $('#page_product_price')[0].value;
            var percent_price = $('#page_discount')[0].value;
            var one_percent_price = product_price / 100;
            var deal_price = product_price - percent_price * one_percent_price;
            deal_price = Math.round(deal_price * 100) / 100;
            if (typeof deal_price == 'number') {
                $('#page_dailydeal_price')[0].value = deal_price;
            }
            // if (typeof percent_price != 'number') {
            //     alert("Discount Percent must be number!");
            //     $('#page_discount')[0].value = '';
            //     $('#page_dailydeal_price')[0].value = '';
            // }
            if (percent_price > 100) {
                alert("Discount Percent must be less than 100!");
                $('#page_discount')[0].value = '';
                $('#page_dailydeal_price')[0].value = '';
            }
        });
        
        // $("#page_deal_qty").change(function () {
        //     var product_qty = $("#page_product_qty")[0].value;
        //     var deal_qty = $("#page_deal_qty")[0].value;
        //     var deal_qty = parseFloat(deal_qty);
        //     if (typeof deal_qty == 'number') {
        //         if (deal_qty < 0) {
        //             $('#page_deal_qty')[0].value = 0;
        //         }
        //
        //         if (deal_qty > product_qty) {
        //             $('#page_deal_qty')[0].value = product_qty;
        //         }
        //     } else {
        //         alert("Deal Qty invalid");
        //     }
        // })
    });
});