// function countDownSecond(obj) {
//
//     if (obj.second > 0) {
//         obj.second--;
//
//         setTimeout(function () {
//             if (obj.callback != '') {
//                 obj.callback(obj);
//             } else {
//                 showTime(obj);
//             }
//         }, 0);
//
//         setTimeout(function () {
//             countDownSecond(obj);
//             console.log(obj.second);
//         }, 10000);
//     }
// }
//
// function showTime(obj) {
//
//     var seconds = Math.floor(obj.second);
//     var minutes = Math.floor(seconds / 60);
//     var hours = Math.floor(minutes / 60);
//     var days = Math.floor(hours / 24);
//
//     hours %= 24;
//     minutes %= 60;
//     seconds %= 60;
//
//     var str_days = wrapperTagSpan(insertOneZero(days));
//     var str_hours = wrapperTagSpan(insertOneZero(hours));
//     var str_minutes = wrapperTagSpan(insertOneZero(minutes));
//     var str_seconds = wrapperTagSpan(insertOneZero(seconds));
//
//     if (jQuery('#' + obj.label_day)) {
//         if (obj.label_day_value) {
//             jQuery('#' + obj.label_day).text(obj.label_day_value);
//         } else {
//             jQuery('#' + obj.label_day).text('<?php echo __('Days'); ?>');
//         }
//     }
//     if (jQuery('#' + obj.label_hour)) {
//         if (obj.label_hour_value) {
//             jQuery('#' + obj.label_hour).text(obj.label_hour_value);
//         } else {
//             jQuery('#' + obj.label_hour).text('<?php echo __('Hours'); ?>');
//         }
//     }
//     if (jQuery('#' + obj.label_minute)) {
//         if (obj.label_minute_value) {
//             jQuery('#' + obj.label_minute).text(obj.label_minute_value);
//         } else {
//             jQuery('#' + obj.label_minute).text('<?php echo __('Minutes'); ?>');
//         }
//     }
//     if (jQuery('#' + obj.label_second)) {
//         if (obj.label_second_value) {
//             jQuery('#' + obj.label_second).text(obj.label_second_value);
//         } else {
//             jQuery('#' + obj.label_second).text('<?php echo __('Seconds'); ?>');
//         }
//     }
//
//     if (jQuery('#' + obj.id_day)) jQuery('#' + obj.id_day).text(str_days);
//     if (jQuery('#' + obj.id_hour)) jQuery('#' + obj.id_hour).text(str_hours);
//     if (jQuery('#' + obj.id_minute)) jQuery('#' + obj.id_minute).text(str_minutes);
//     if (jQuery('#' + obj.id_second)) jQuery('#' + obj.id_second).text(str_seconds);
//     // console.log(str_seconds);
//     // console.log(obj.id_second);
//     if (days <= 0) {
//         if (jQuery('#' + obj.label_day)) jQuery('#' + obj.label_day).text('');
//         if (jQuery('#' + obj.id_day)) jQuery('#' + obj.id_day).text('');
//     }
// }
//
// function insertOneZero(value) {
//     var result = '';
//
//     if (value < 10) {
//         result += '0' + value;
//     } else {
//         result += value;
//     }
//
//     return result;
// }
//
// function wrapperTagSpan(string) {
//     var result = '';
//
//     string.toString();
//
//     for (var i = 0; i < string.length; i++) {
//         result += "" + string.charAt(i) + "";
//     }
//
//     return result;
// }
