<?php

namespace MoneySpace\Payments;

use WC_Payment_Gateway;
use WC_Order;
use MoneySpace\Mslogs;
use WC_Payment_Gateways;
use Exception;

/**
 * WC wcCpg3 Gateway Class.
 * Built the wcCpg3 method.
 */
class MNS_Payment_Gateway_INSTALLMENT extends WC_Payment_Gateway {

    public $domain;
    public $instructions;

    /**
     * Constructor for the gateway.
     *
     * @return void
     */
    public function __construct() {
        $this->domain = 'ms_payment_installment';
        global $woocommerce;
        $this->id             = MNS_ID_INSTALLMENT;
        $this->title = __($this->get_option( 'title' ), $this->domain);
        $this->icon = apply_filters('woocommerce_custom_gateway_icon', MNS_LOGO_INSTALLMENT, '');
        $this->method_title = __("Money Space for WooCommerce ( ผ่อนชำระรายเดือน )", $this->domain);
        $this->method_description = __("

        ไม่คิดคา่ธรรมเนียม :

        KTC เลือก ตัวเลขเดือน ได้ 3, 4, 5, 6, 7, 8, 9, 10
        BAY เลือกตัวเลขเดือนได้ 3, 4, 6, 9, 10
        FCY เลือก ตัวเลขเดือนได้ 3, 4, 6, 9, 10
        
        คิดคา่ธรรมเนียม ( ดอกเบี้ย : 0.8% , 1% ) :

        KTC เลือก ตัวเลขเดือน ได้ 3, 4, 5, 6, 7, 8, 9, 10
        BAY เลือกตัวเลขเดือนได้ 3, 4, 6, 9, 10
        FCY เลือก ตัวเลขเดือนได้ 3, 4, 6, 9, 10, 12, 18, 24, 36

        --------

        KTC : บัตรเคทีซี
        BAY : บัตรกรุงศรีฯ วีซ่า , บัตรเซ็นทรัล , บัตรเทสโก้โลตัส
        FCY : บัตรกรุงศรีเฟิร์สช้อยส์ , บัตรโฮมโปร , บัตรเมกาโฮม
        
        ขั้นต่ำของการรับชำระเงิน ของ KTC และ FCY 3000.01 บาท โดย ต่อเดือน ไม่ต่ำกว่า 300 บาท
        ขั้นต่ำของการรับชำระเงิน ของ BAY 3000.01 บาท โดย ต่อเดือนไม่ต่ำกว่า 500 บาท", $this->domain);


        $this->has_fields = true;

        $this->init_form_fields();
        $this->init_settings();


        $this->enabled = $this->get_option('enabled');
        $this->description = $this->get_option('description');


        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_thankyou_custom', array($this, 'thankyou_page'));
        add_action('woocommerce_receipt_' . $this->id, array($this,'paymentgateway_form'), 10, 1);
    }
    
    public function init_form_fields()
    {
        $this->form_fields = array(
            'header_setting' => array(
                'title' => __(MNS_FORM_FIELD_HEADER_SETTING, $this->domain), // '<h1><b> ' . MNS_FORM_FIELD_HEADER_SETTING . ' </b></h1>'
                'type' => 'title'
            ),
            'enabled' => array(
                'title' => __(MNS_FORM_FIELD_ENABLE, $this->domain),
                'type' => 'checkbox',
                'label' => __(MNS_FORM_FIELD_ENABLE_LABEL, $this->domain),
                'default' => 'no'
            ),
            'title' => array(
                'title' => __('Title', $this->domain),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', $this->domain),
                'default' => __(MNS_PAY_INS, $this->domain),
                'desc_tip' => true,
            ),
            'order_status_if_success' => array(
                'title' => __(MNS_FORM_FIELD_SET_ORDER_STATUS, $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'wc-completed',
                'desc_tip' => true,
                'options' => wc_get_order_statuses()
            ),
            'description' => array(
                'title' => __(MNS_FORM_FIELD_DESCRIPTION, $this->domain),
                'type' => 'textarea',
                'default' => __("", $this->domain),
                'desc_tip' => true
            ),
            'fee_setting' => array(
                'title' => __("เลือกผู้รับผิดชอบดอกเบี้ยรายเดือน", $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'include',
                'desc_tip' => true,
                'options' => ["include" => "ร้านค้ารับผิดชอบดอกเบี้ยรายเดือน" , "exclude" => " ผู้ถือบัตรรับผิดชอบดอกเบี้ยรายเดือน ( ดอกเบี้ย : 0.8% , 1% )"]
            ),
            // 'message2store_setting' => array(
            //     'title' => __(MNS_MESSAGE2STORE_HEADER, $this->domain),
            //     'type' => 'select',
            //     'class' => 'wc-enhanced-select',
            //     'default' => 'Enable',
            //     'desc_tip' => true,
            //     'options' => ["Enable" => "Enable" , "Disable" => "Disable"]
            // ),
            'ktc_max_months_setting' => array(
                'title' => __("KTC ผ่อนสูงสุด", $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'ten',
                'desc_tip' => true,
                'options' => [3 => "3 เดือน" , 4 => "4 เดือน" , 5 => "5 เดือน" , 6 => "6 เดือน" , 7 => "7 เดือน" , 8 => "8 เดือน" , 9 => "9 เดือน" , 10 => "10 เดือน"]
            ),
            'bay_max_months_setting' => array(
                'title' => __("BAY ผ่อนสูงสุด", $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'ten',
                'desc_tip' => true,
                'options' => [3 => "3 เดือน" , 4 => "4 เดือน" , 6 => "6 เดือน" , 9 => "9 เดือน" , 10 => "10 เดือน"]
            ),
            'fcy_max_months_setting' => array(
                'title' => __("FCY ผ่อนสูงสุด", $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'ten',
                'desc_tip' => true,
                'options' => [3 => "3 เดือน" , 4 => "4 เดือน" , 6 => "6 เดือน" , 9 => "9 เดือน" , 10 => "10 เดือน", 12 => "12 เดือน (เฉพาะผู้ถือบัตรรับผิดชอบดอกเบี้ยรายเดือน)", 18 => "18 เดือน (เฉพาะผู้ถือบัตรรับผิดชอบดอกเบี้ยรายเดือน)", 24 => "24 เดือน (เฉพาะผู้ถือบัตรรับผิดชอบดอกเบี้ยรายเดือน)", 36 => "36 เดือน (เฉพาะผู้ถือบัตรรับผิดชอบดอกเบี้ยรายเดือน)"]
            ),
            'agreement_setting' => array(
                'title' => __("เงื่อนไข", $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => '1',
                'desc_tip' => true,
                'options' => [
                 "1" => "ข้าพเจ้ายอมรับว่าไม่สามารถขอคืนเงิน หรือยกเลิกรายการได้" ,
                 "2" => "ข้าพเจ้ายอมรับว่าไม่สามารถขอคืนเงิน และเมื่อหากสินค้า / บริการ มีปัญหาจะรีบติดต่อกลับ ภายใน 7 วัน หรือ ปฏิบัติตามนโยบายการคืนเงินของร้านค้า",
                 "3" => "ข้าพเจ้ายอมรับว่่าไม่สามารถขอคืนเงิน และเมื่อหากสินค้า / บริการ มีปัญหาจะรีบติดต่อกลับ ภายใน 14 วัน หรือ ปฏิบัติตามนโยบายการคืนเงินของร้านค้า",
                 "4" => "ข้าพเจ้ายอมรับว่าไม่สามารถขอคืนเงิน และเมื่อหากสินค้า / บริการ มีปัญหาจะรีบติดต่อกลับ ภายใน 30 วัน หรือ ปฏิบัติตามนโยบายการคืนเงินของร้านค้า",
                 "5" => "ข้าพเจ้ายอมรับว่าไม่สามารถขอคืนเงิน และเมื่อหากสินค้า / บริการ มีปัญหาจะรีบติดต่อกลับ ภายใน 60 วัน หรือ ปฏิบัติตามนโยบายการคืนเงินของร้านค้า"]
            ),
            'ktc_enabled' => array(
                'title' => __(MNS_FORM_FIELD_KTC_ENABLE, $this->domain),
                'type' => 'checkbox',
                'label' => __(MNS_FORM_FIELD_ENABLE_LABEL, $this->domain),
                'default' => 'yes'
            ),
            'bay_enabled' => array(
                'title' => __(MNS_FORM_FIELD_BAY_ENABLE, $this->domain),
                'type' => 'checkbox',
                'label' => __(MNS_FORM_FIELD_ENABLE_LABEL, $this->domain),
                'default' => 'yes'
            ),
            'fcy_enabled' => array(
                'title' => __(MNS_FORM_FIELD_FCY_ENABLE, $this->domain),
                'type' => 'checkbox',
                'label' => __(MNS_FORM_FIELD_ENABLE_LABEL, $this->domain),
                'default' => 'yes'
            ),
        );
    }

    public function thankyou_page()
    {
        if ($this->instructions)
        _e(wpautop(wptexturize($this->instructions)));
    }

    public function payment_fields()
    {
        wc_add_notice(__("payment_fields", $this->domain), 'error');
        exit();
        $payment_gateway_id = MNS_ID_INSTALLMENT;
        $payment_gateways = WC_Payment_Gateways::instance();
        $payment_gateway = $payment_gateways->payment_gateways()[$payment_gateway_id];
        $gateways = WC()->payment_gateways->get_available_payment_gateways();
        $ms_message2store = $gateways['moneyspace_installment']->settings['message2store_setting'];
        $ms_fee = $gateways['moneyspace_installment']->settings['fee_setting'] ?? "include";
        $ktc_max_months_setting = $gateways['moneyspace_installment']->settings['ktc_max_months_setting']; 
        $bay_max_months_setting = $gateways['moneyspace_installment']->settings['bay_max_months_setting']; 
        $fcy_max_months_setting = $gateways['moneyspace_installment']->settings['fcy_max_months_setting']; 
        $ktc_enabled = $gateways['moneyspace_installment']->settings['ktc_enabled'] ?? "yes";
        $bay_enabled = $gateways['moneyspace_installment']->settings['bay_enabled'] ?? "yes";
        $fcy_enabled = $gateways['moneyspace_installment']->settings['fcy_enabled'] ?? "yes";

        
        if(WC()->cart->total && WC()->cart->total != 0){
            $amount_total = WC()->cart->total;
        } else {
            global $wp;
            $order_id = $wp->query_vars['order-pay'];
            $order = new WC_Order( $order_id );
            $amount_total = round($order->get_total(),2);
        }

        if ($ms_fee == "include"){
            $KTC = [ 3, 4, 5, 6, 7, 8, 9, 10];
            $BAY = [ 3, 4, 6, 9, 10];
            $FCY = [ 3, 4, 6, 9, 10];
        } else if ($ms_fee == "exclude") {
            $KTC = [ 3, 4, 5, 6, 7, 8, 9, 10];
            $BAY = [ 3, 4, 6, 9, 10];
            $FCY = [ 3, 4, 6, 9, 10, 12, 18, 24, 36];
        }

        if ($description = $this->get_description()) {
            _e(wpautop(wptexturize($description)));
        }
        ?>

        <style>
            #selectbank[type=radio] { 
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
            }
            #selectbank + img {
            cursor: pointer;
            }
            #selectbank:checked + img {
            outline: 2px solid #f00;
            outline: 2px solid #96588a;
            }
            .installment {
                 display: none;
            }
            #banks {
                border-collapse: collapse;
                border-radius: 1em;
                overflow: hidden;
            }
            #td_banks {
                padding: 1em;
                background: #ddd;
                border-bottom: 2px solid white; 
                text-align: center
            }
        </style>
        
        <script type="text/javascript">
            function KTC(){
                document.getElementById('KTC').style.display ='block';

                if (document.getElementById('BAY'))
                    document.getElementById('BAY').style.display ='none';
                if (document.getElementById('FCY'))
                    document.getElementById('FCY').style.display ='none';
                // document.getElementById('btbpay').disabled = false;

                bindOutline();
            }

            function BAY(){
                document.getElementById('BAY').style.display = 'block';
                if (document.getElementById('KTC'))
                    document.getElementById('KTC').style.display ='none';
                if (document.getElementById('FCY'))
                    document.getElementById('FCY').style.display ='none';
                // document.getElementById('btbpay').disabled = false;

                bindOutline();
            }

            function FCY(){
                document.getElementById('FCY').style.display = 'block';
                if (document.getElementById('KTC'))
                    document.getElementById('KTC').style.display ='none';
                if (document.getElementById('BAY'))
                    document.getElementById('BAY').style.display ='none';
                // document.getElementById('btbpay').disabled = false;

                bindOutline();
            }

            function bindOutline() {
                document.getElementsByName("selectbank").forEach(ele => { 
                    if (ele.checked == true) {
                        ele.style.outline = "2px solid #96588a";
                    } else {
                        ele.style.outline = "";
                    }
                });
            }

        </script>

        <?php if($amount_total >= 3000.01) { ?>
            <h3 style="text-decoration: underline;">เลือกการผ่อนชำระ</h3>
            <br>
            <?php if ($ktc_enabled == "yes") { ?>
                <div class="form-check">
                    <input class="form-check-input" type="radio" style="width: 0px" name="selectbank" id="selectbank-ktc" value="KTC" onclick="KTC();">
                    <label class="form-check-label" for="selectbank-ktc">
                    เคทีซี (KTC)
                    <img src="<?php _e(MNS_ROOT_URL . 'includes/images/installment/ktc-logo.png'); ?>">
                    </label>
                </div>
                <?php if ($ms_fee == "include") { ?>
                <div id="KTC" class="installment">
                    <select name="KTC_permonths" id="permonths" class="form-control">
                        <?php foreach($KTC as $months){ ?>
                            <?php if(round($amount_total / $months,2) >= 300 && $months <= $ktc_max_months_setting){ ?>
                            <option value="<?php _e($months); ?>">ผ่อน <?php _e($months); ?> เดือน ( <?php _e(number_format($amount_total / $months,2)); ?> บาท / เดือน )</option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>
                <?php } ?>
                <?php if ($ms_fee == "exclude") { ?>
                <div id="KTC" class="installment">
                    <select name="KTC_permonths" id="permonths" class="form-control">
                        <?php foreach($KTC as $months){ ?>
                            <?php if(round($amount_total / $months,2) >= 300 && $months <= $ktc_max_months_setting){ $ex_ktc_bay = $amount_total / 100 * 0.8 * $months + $amount_total; ?>
                            <option value="<?php _e($months); ?>">ผ่อน <?php _e($months); ?> เดือน ( <?php _e(number_format($ex_ktc_bay / $months,2)); ?> บาท / เดือน )</option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>
                <?php } ?>
                <br />
                <hr />
            <?php } ?>

            <?php if ($bay_enabled == "yes") { ?>
                <div class="form-check">
                    <input class="form-check-input" type="radio" style="width: 0px" name="selectbank" id="selectbank-bay" value="BAY" onclick="BAY();">
                    <label class="form-check-label" for="selectbank-bay">
                    กรุงศรีฯ วีซ่า , เซ็นทรัล , เทสโก้โลตัส
                    <img src="<?php _e(MNS_ROOT_URL . 'includes/images/installment/bay_central_lotus.png'); ?>">
                    </label>
                </div>
                <?php if ($ms_fee == "include") { ?>
                <div id="BAY" class="installment">
                    <select name="BAY_permonths" id="permonths" class="form-control">
                        <?php foreach($BAY as $months){ ?>
                            <?php if(round($amount_total / $months,2) >= 500 && $months <= $bay_max_months_setting){ ?>
                            <option value="<?php _e($months); ?>">ผ่อน <?php _e($months); ?> เดือน ( <?php _e(number_format($amount_total / $months,2)); ?> บาท / เดือน )</option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>
                <?php } ?>
                <?php if ($ms_fee == "exclude") { ?>
                <div id="BAY" class="installment">
                    <select name="BAY_permonths" id="permonths" class="form-control">
                        <?php foreach($BAY as $months){ ?>
                            <?php if(round($amount_total / $months,2) >= 500 && $months <= $bay_max_months_setting){ $ex_ktc_bay = $amount_total / 100 * 0.8 * $months + $amount_total;?>
                            <option value="<?php _e($months); ?>">ผ่อน <?php _e($months); ?> เดือน ( <?php _e(number_format($ex_ktc_bay / $months,2)); ?> บาท / เดือน )</option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>
                <?php } ?>
                <br />
                <hr />
            <?php } ?>

            <?php if ($fcy_enabled == "yes") { ?>
                <div class="form-check">
                    <input class="form-check-input" type="radio" style="width: 0px" name="selectbank" id="selectbank-fcy" value="FCY" onclick="FCY();">
                    <label class="form-check-label" for="selectbank-fcy">
                    กรุงศรีเฟิร์สช้อยส์ , โฮมโปร , เมกาโฮม
                    <img src="<?php _e(MNS_ROOT_URL . 'includes/images/installment/fcy-logo.png'); ?>">
                    </label>
                </div>
                <?php if ($ms_fee == "include") { ?>
                <div id="FCY" class="installment">
                    <select name="FCY_permonths" id="permonths" class="form-control">
                        <?php foreach($FCY as $months){ ?>
                            <?php if(round($amount_total / $months,2) >= 300 && $months <= $fcy_max_months_setting){ ?>
                            <option value="<?php _e($months); ?>">ผ่อน <?php _e($months); ?> เดือน ( <?php _e(number_format($amount_total / $months,2)); ?> บาท / เดือน )</option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>
                <?php } ?>
                <?php if ($ms_fee == "exclude") { ?>
                <div id="FCY" class="installment">
                    <select name="FCY_permonths" id="permonths" class="form-control">
                        <?php foreach($FCY as $months){ ?>
                            <?php if(round($amount_total / $months,2) >= 300 && $months <= $fcy_max_months_setting){ $ex_fcy = $amount_total / 100 * 1 * $months + $amount_total; ?>
                            <option value="<?php _e($months); ?>">ผ่อน <?php _e($months); ?> เดือน ( <?php _e(number_format($ex_fcy / $months,2)); ?> บาท / เดือน )</option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div> 
                <?php } ?>
                <br />
                <hr />
            <?php } ?>
            <br>

        <?php } else if ($amount_total < 3000.01){ ?>
            <h5 style="color:red; font-size: inherit;"><?php _e(MNS_INS_MESSAGE_WARNING); ?></h5>
        <?php } ?>
        <?php

    }

    /**
     * Process the payment and return the result.
     *
     * @param int $order_id
     * @return array
     */
    public function process_payment($order_id) {
        
        $order = wc_get_order($order_id);
        $order_amount = $order->get_total();
        $is_error = false;
        $items = $order->get_items();
        // wc_add_notice(__(json_encode($_POST), $this->domain), 'error');
        // exit();
        
        // $items_msg = set_item_message($items);
        // $return_url = get_site_url() . "/process/payment/" . $order_id;

        update_post_meta($order_id, 'MNS_special_instructions_to_merchant', sanitize_text_field($_POST["message_card"]));

        if (!is_user_logged_in() && !$is_error) {
            wc_add_notice(__("Please login !", $this->domain), 'error');
            $is_error = true;
        }

        if (strlen(sanitize_text_field($_POST["message_card"])) > 150 && !$is_error) {
            wc_add_notice(__("Message to the store (150 characters maximum)", $this->domain), 'error');
            $is_error = true;
        }

        if (get_woocommerce_currency() != "THB" && !$is_error) { 
            wc_add_notice(__(MNS_NOTICE_CURRENCY, $this->domain), 'error');
            $is_error = true;
        }

        if($order_amount < 3000.01 && !$is_error) { 
            wc_add_notice(__("จำนวนยอดเงินต้อง 3,000.01 บาทขึ้นไปถึงจะทำการผ่อนชำระได้", $this->domain), 'error');
            $is_error = true;
        }

        if(sanitize_text_field($_POST["selectbank"]) == "" && !$is_error) {
            wc_add_notice(__("กรุณาเลือกการผ่อนชำระ".sanitize_text_field($_POST["selectbank"]), $this->domain), 'error');
            $is_error = true;
        }

        if(!$is_error) {
            $MNS_PAYMENT_TYPE = get_post_meta($order_id, 'MNS_PAYMENT_TYPE', true);
            delete_post_meta($order_id, 'MNS_transaction');
            delete_post_meta($order_id, 'MNS_QR_URL');
            update_post_meta($order_id, 'MNS_PAYMENT_TYPE', "Installment");
            update_post_meta($order_id, 'MNS_INSTALLMENT_BANK', sanitize_text_field($_POST["selectbank"]));
            $endterm = "";
            $bankType = sanitize_text_field($_POST["selectbank"]);

            if ($bankType == "KTC"){
                $endterm = sanitize_text_field($_POST["KTC_permonths"] ?? $_POST["ktc_permonths"]);
            }

            if ($bankType == "BAY"){
                $endterm = sanitize_text_field($_POST["BAY_permonths"] ?? $_POST["pay_permonths"]);
            }

            if ($bankType == "FCY"){
                $endterm = sanitize_text_field($_POST["FCY_permonths"] ?? $_POST["fcy_permonths"]);
            }

            update_post_meta($order_id, 'MNS_INSTALLMENT_BANK_TYPE', $bankType);
            update_post_meta($order_id, 'MNS_INSTALLMENT_MONTHS', $endterm);

            return $this->_process_external_payment($order); // go to paymentgateway_form
        } else {
            wc_add_notice(__("Error : Message to the store (150 characters maximum)", $this->domain), 'error');
            // wc_add_notice(__("Error : Enter special instructions to merchant again", $this->domain), 'error');
            throw new Exception( __("Error : Message to the store (150 characters maximum)", $this->domain) );
        }
    }

    public function paymentgateway_form($order_id) {
       
        $payment_gateways = WC_Payment_Gateways::instance();
        $gateways = WC()->payment_gateways->get_available_payment_gateways();
        
        $payment_gateway = $payment_gateways->payment_gateways()[MNS_ID_INSTALLMENT];
        $ms_order_select = $payment_gateway->settings['order_status_if_success'];
        $ms_fee = $payment_gateway->settings['fee_setting'];
        $agreement_setting = $payment_gateway->settings['agreement_setting']; 

        $moneyspace_gw = $payment_gateways->payment_gateways()[MNS_ID];
        $ms_secret_id = $moneyspace_gw->settings['secret_id'];
        $ms_secret_key = $moneyspace_gw->settings['secret_key'];
        $ms_template_payment = $moneyspace_gw->settings['ms_template_payment'];
        $ms_time = date("YmdHis");
        $order = wc_get_order($order_id);
        $items = $order->get_items();

        
        $order_amount = $order->get_total();

        $items_msg = set_item_message($items);
        $return_url = get_site_url() . "/process/payment/" . $order_id;

        $error_list = array("wc-failed", "wc-cancelled", "wc-refunded");
        if (in_array($ms_order_select, $error_list)) {
            _e("Error : " . MNS_NOTICE_ERROR_CONTINUE);
            return;
        }

        $bankType = get_post_meta( $order_id, 'MNS_INSTALLMENT_BANK_TYPE', true);
        $endTerm = get_post_meta( $order_id, 'MNS_INSTALLMENT_MONTHS', true);
        $payment_data = array("secret_id" => $ms_secret_id
        , "secret_key" => $ms_secret_key
        , "firstname" => $order->get_billing_first_name()
        , "lastname" => $order->get_billing_last_name()
        , "email" => $order->get_billing_email()
        , "phone" => $order->get_billing_phone()
        , "amount" => round($order_amount, 2)
        , "description" => preg_replace( "/<br>|\n/", "", $items_msg)
        , "address" => $order->get_billing_address_1() . " " . $order->get_billing_address_2() . " " . $order->get_billing_city() . " " . $order->get_billing_postcode()
        // , "message" => $MNS_special_instructions_to_merchant
        , "feeType" => $ms_fee
        , "order_id" => $order_id . "MS" . $ms_time
        , "success_Url" => $return_url
        , "fail_Url" => $return_url
        , "cancel_Url" => $return_url
        , "agreement" => $agreement_setting 
        , "bankType" => $bankType
        , "startTerm" => $endTerm
        , "endTerm" => $endTerm);

        $response = wp_remote_post(MNS_API_URL_CREATE_INSTALLMENT, 
        array(
            'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
            'method' => 'POST',
            'timeout' => 120,
            'body' => json_encode($payment_data)
            )
        );
        (new Mslogs())->insert($response["body"], 4, 'Create Transaction Installment', date("Y-m-d H:i:s"), json_encode($payment_data));
        
        if (is_wp_error($response)) {
            wc_add_notice(__($response, $this->domain), 'error');
            return;
        }

        $json_tranId_status = json_decode($response["body"]);
                                    
        if ($json_tranId_status[0]->status == "NotFound" 
        || $json_tranId_status[0]->status == "The payment amount must be greater than 3000 baht." 
        || $json_tranId_status[0]->status == "Data Invalid"
        || $json_tranId_status[0]->status == "Payment amount must be less than 10000.00") {
            wc_add_notice(__($json_tranId_status[0]->status, $this->domain), 'error');
            return;
        }
        $json_tranId = json_decode($response["body"]);
        $tranId = $json_tranId[0]->transaction_ID;
        $hash_link = hash_hmac('sha256', $tranId . $ms_time, $ms_secret_key);
        $link = "https://www.moneyspace.net/merchantapi/makepayment/linkpaymentcard?transactionID=" . $tranId . "&timehash=" . $ms_time . "&secreteID=" . $ms_secret_id . "&hash=" . $hash_link;

        if ($payment_data['feeType'] == "include"){
            $KTC = [ 3, 4, 5, 6, 7, 8, 9, 10];
            $BAY = [ 3, 4, 6, 9, 10];
            $FCY = [ 3, 4, 6, 9, 10];

            $ex_ktc_bay = $order_amount;
            $ex_fcy = $order_amount;

        } else if ($payment_data['feeType'] == "exclude"){
            $KTC = [ 3, 4, 5, 6, 7, 8, 9, 10];
            $BAY = [ 3, 4, 6, 9, 10];
            $FCY = [ 3, 4, 6, 9, 10, 12, 18, 24, 36];

            $ex_ktc_bay = $order_amount / 100 * 0.8 * $endTerm + $order_amount;
            $ex_fcy = $order_amount / 100 * 1 * $endTerm + $order_amount;
        }

        ?>
        <h3 style="text-decoration: underline;">ยืนยันการชำระเงิน</h3>
        <p><h5>จำนวนเดือน : <?php _e($endTerm."  เดือน"); ?></h5></p>
        <?php if($bankType == "KTC" || $bankType == "BAY"){ ?>
        <p><h5>จำนวนเงินชำระต่อเดือน : <?php _e(round($ex_ktc_bay / $endTerm,2).get_woocommerce_currency()." / เดือน"); ?></h5></p>
        <?php }if($bankType == "FCY"){ ?>
        <p><h5>จำนวนเงินชำระต่อเดือน : <?php _e(round($ex_fcy / $endTerm,2).get_woocommerce_currency()." / เดือน"); ?></h5></p>
        <?php } ?>
        <?php 
        if($bankType == "KTC"){
            $actionUrl = "https://www.moneyspace.net/ktccredit/payment/directpay";
            $paymonth = round($ex_ktc_bay / $endTerm,2);
        }

        if($bankType == "BAY"){
            $actionUrl = "https://www.moneyspace.net/baycredit/pay";
            $paymonth = round($ex_ktc_bay / $endTerm,2);
        }

        if($bankType == "FCY"){
            $actionUrl = "https://www.moneyspace.net/baycredit/pay";
            $paymonth = round($ex_fcy / $endTerm,2);
        }
        ?>
        <div style="display: none;">
            <form  id="mainform"  method="post" action="<?php _e($actionUrl); ?>">
                    <div class="row">
                        <div class="col-12 col-sm-12 col-12 mt-3">
                            <input type="hidden" name="transactionID" value="<?php _e($tranId); ?>"/>
                            <input type="hidden" id="pay_type" name="pay_type" value=""/>
                            <input type="hidden" id="locale" name="locale" value=""/>
                             <input type="hidden" id="term" name="term" value="<?php _e($endTerm); ?>"/>
                            <input type="hidden" id="payForMonth" name="paymonth" value="<?php _e($paymonth); ?>"/>
                            <input type="hidden" id="interest" name="interest" value="0.0"/>
                            <input type="hidden" id="bankType" name="bankType" value="<?php _e($bankType); ?>"/>
                        </div>
                     </div>
                 <button class="button" type="submit">ชำระเงิน</button>
            </form>
        </div>
        <?php 
            wc_enqueue_js("document.getElementById('mainform').submit();");
    }

    public function getHash($data,$key) {
        return hash_hmac('sha256', $data, $key);
    }

    public function getTime() {
        return date("YmdHis");
    }
    
    protected function _process_external_payment($order) {
        // Redirect to payment page, where payment form will be printed
        return array(
            'result' => 'success',
            'redirect' => $order->get_checkout_payment_url(true)
        );
    }


	
}