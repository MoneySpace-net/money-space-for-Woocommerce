<?php

$MNS_QR_TIME = get_post_meta($order_id, 'MNS_QR_TIME', true);
$auto_cancel = $payment_gateway_qr->settings['auto_cancel'];

_e('<div style="text-align: center;">
    <embed type="image/jpeg" src="'.$image_qrprom.'" />
    </div>
');

if(empty($auto_cancel)){
    $limit_time = 1200;
}else{
    $limit_time = $auto_cancel;
}

wc_enqueue_js('function startTimer(duration) {
    var countDownDate = new Date();
    countDownDate.setMinutes(countDownDate.getMinutes() + Math.round(duration/60000));
    var refreshId = setInterval(function () {
        var now = new Date().getTime();
        var distance = countDownDate - now;

        if (countDownDate.getTime() <=  now) {
            window.location="'.(get_site_url() . "/ms/cancel/" . $order_id).'", true;
            clearInterval(refreshId);
        } else {
            // Time calculations for days, hours, minutes and seconds
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);
            if (document.getElementById("showTime") !== null) {
                document.getElementById("showTime").innerHTML = ("QR Code จะหมดอายุในอีก "+minutes + " นาที " + seconds + " วินาที ");
            }
            // display.innerHTML = ("QR Code จะหมดอายุในอีก "+minutes + " นาที " + seconds + " วินาที ");
        }
    }, 1000);
}

function checkPayment(duration, pid) {
    var countDownDate = new Date();
    countDownDate.setMinutes(countDownDate.getMinutes() + Math.round(duration/60000));

    var refreshIdOfChechPayment = setInterval(function() {
        var now = new Date().getTime();
        if (countDownDate.getTime() <=  now) {
            clearInterval(refreshIdOfChechPayment);
        }
        fetch("'.(get_site_url() . MNS_CHECK_PAYMENT_STATUS . $order_id).'")
        .then(function(res) {
            return res.json();
        })
        .then(function(res) {
            if (res.status === "completed") {
                clearInterval(refreshIdOfChechPayment);
                window.location = "'.wc_get_order($order_id)->get_checkout_order_received_url().'";
            }
        });        
    }, 2000);
}


var endDate = new Date(Date.parse("'.date('Y/m/d H:i', $MNS_QR_TIME + $limit_time).'")).getTime();
var startDate = new Date().getTime();
var resultDiffInMinutes = Math.round(endDate - startDate);
startTimer(resultDiffInMinutes); //display
checkPayment(resultDiffInMinutes, '.$order_id.');


');

?>