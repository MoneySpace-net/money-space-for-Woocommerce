<?php

if ( ! defined( 'ABSPATH' ) ) exit;

$moneyspace_qr_time = get_post_meta($moneyspace_order_id, 'MNS_QR_TIME', true);
$moneyspace_tz = 'Asia/Bangkok';
$moneyspace_dt = new DateTime("now", new DateTimeZone($moneyspace_tz));


$moneyspace_auto_cancel = $moneyspace_payment_gateway_qr->settings['auto_cancel'];
$moneyspace_auto_check_result_time = ! empty( $moneyspace_payment_gateway_qr->settings['auto_check_result_time'] )
    ? (int) $moneyspace_payment_gateway_qr->settings['auto_check_result_time']
    : 2000;
$moneyspace_enable_auto_check_result = $moneyspace_payment_gateway_qr->settings['enable_auto_check_result'];

$moneyspace_order_key = $moneyspace_order ? (string) $moneyspace_order->get_order_key() : '';
$moneyspace_cancel_url = add_query_arg(
    'key',
    $moneyspace_order_key,
    trailingslashit(get_site_url()) . 'ms/cancel/' . $moneyspace_order_id
);
$moneyspace_check_url = add_query_arg(
    'key',
    $moneyspace_order_key,
    get_site_url() . MNS_CHECK_PAYMENT_STATUS . $moneyspace_order_id
);

?>
<div style="text-align: center;">
    <img type="image/jpeg" style="display: initial" src="data:image/png;base64,<?php echo esc_attr($moneyspace_image_qrprom); ?>"/>
    <br />
    <a id="qrnone1" href="data:image/png;base64,<?php echo esc_attr($moneyspace_image_qrprom); ?>" download="qrcode.png" target="_blank" rel="noopener">
        <?php echo esc_html('Download QR'); ?>
    </a>
</div>

<?php

if(empty($moneyspace_auto_cancel)){
    $moneyspace_limit_time = 1200;
}else{
    $moneyspace_limit_time = $moneyspace_auto_cancel;
}
$moneyspace_dt->setTimestamp($moneyspace_qr_time + $moneyspace_limit_time);

wc_enqueue_js('
let timeZone = "Asia/Bangkok";
var autoCheckIntervalMs = ' . $moneyspace_auto_check_result_time . ';
var cancelUrl = ' . wp_json_encode($moneyspace_cancel_url) . ';
var checkUrl = ' . wp_json_encode($moneyspace_check_url) . ';
function startTimer(duration) {
    var countDownDate = new Date();
    countDownDate.setMinutes(countDownDate.getMinutes() + Math.round(duration/60000));
    var refreshId = setInterval(function () {
        var now = new Date().getTime();
        var distance = countDownDate - now;

        if (countDownDate.getTime() <=  now) {
            window.location = cancelUrl;
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
        fetch(checkUrl)
        .then(function(res) {
            return res.json();
        })
        .then(function(res) {
            if (res.status === "Pay Success") {
                clearInterval(refreshIdOfChechPayment);
                window.location = "'.wc_get_order($moneyspace_order_id)->get_checkout_order_received_url().'";
            }
        });        
    }, autoCheckIntervalMs);
}

var endDate = new Date(Date.parse("'.$moneyspace_dt->format('Y/m/d H:i').'")).getTime();
var startDate = new Date().getTime();
var resultDiffInMinutes = Math.round(endDate - startDate);
startTimer(resultDiffInMinutes); //display
var enableAutoCheckResult = "'.$moneyspace_enable_auto_check_result.'";
if (enableAutoCheckResult == "") {
    enableAutoCheckResult = "yes";
}

if (enableAutoCheckResult == "yes") {
    checkPayment(resultDiffInMinutes, '.$moneyspace_order_id.');
}
');

?>