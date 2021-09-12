<?php
/**
 * WC wcCpg3 Gateway Class.
 * Built the wcCpg3 method.
 */
class MNS_Payment_Gateway_INSTALLMENT extends WC_Payment_Gateway {
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
                'title' => __('<h1><b> ' . MNS_FORM_FIELD_HEADER_SETTING . ' </b></h1>', $this->domain),
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
                'default' => __('ผ่อนชำระรายเดือน', $this->domain),
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => __(MNS_FORM_FIELD_DESCRIPTION, $this->domain),
                'type' => 'textarea',
                'default' => __("", $this->domain),
                'desc_tip' => true,
            ),
            'fee_setting' => array(
                'title' => __("เลือกผู้รับผิดชอบดอกเบี้ยรายเดือน", $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'include',
                'desc_tip' => true,
                'options' => ["include" => "ร้านค้ารับผิดชอบดอกเบี้ยรายเดือน" , "exclude" => " ผู้ถือบัตรรับผิดชอบดอกเบี้ยรายเดือน ( ดอกเบี้ย : 0.8% , 1% )"]
            ),
            'message2store_setting' => array(
                'title' => __(MNS_MESSAGE2STORE_HEADER, $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'Enable',
                'desc_tip' => true,
                'options' => ["Enable" => "Enable" , "Disable" => "Disable"]
            ),
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
        );


    }

    public function thankyou_page()
    {
        if ($this->instructions)
        esc_html_e(wpautop(wptexturize($this->instructions)));
    }

    public function payment_fields()
    {
        $payment_gateway_id = MNS_ID_INSTALLMENT;
        $payment_gateways = WC_Payment_Gateways::instance();
        $payment_gateway = $payment_gateways->payment_gateways()[$payment_gateway_id];
        $gateways = WC()->payment_gateways->get_available_payment_gateways();
        $ms_message2store = $gateways['moneyspace_installment']->settings['message2store_setting'];
        $ms_fee = $gateways['moneyspace_installment']->settings['fee_setting'];
        $ktc_max_months_setting = $gateways['moneyspace_installment']->settings['ktc_max_months_setting']; 
        $bay_max_months_setting = $gateways['moneyspace_installment']->settings['bay_max_months_setting']; 
        $fcy_max_months_setting = $gateways['moneyspace_installment']->settings['fcy_max_months_setting']; 

        

        if(WC()->cart->total && WC()->cart->total != 0){
            $amount_total = WC()->cart->total;
        }else{
            global $wp;
            $order_id = $wp->query_vars['order-pay'];
            $order = new WC_Order( $order_id );
            $amount_total = round($order->get_total(),2);
        }

        

        if ($ms_fee == "include"){
            $KTC = [ 3, 4, 5, 6, 7, 8, 9, 10];
            $BAY = [ 3, 4, 6, 9, 10];
            $FCY = [ 3, 4, 6, 9, 10];
        }else if ($ms_fee == "exclude"){
            $KTC = [ 3, 4, 5, 6, 7, 8, 9, 10];
            $BAY = [ 3, 4, 6, 9, 10];
            $FCY = [ 3, 4, 6, 9, 10, 12, 18, 24, 36];
        }

        if ($description = $this->get_description()) {
            esc_html_e(wpautop(wptexturize($description)));
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

                document.getElementById('BAY').style.display ='none';
                document.getElementById('FCY').style.display ='none';
                document.getElementById('btbpay').disabled = false;
            }

            function BAY(){
                document.getElementById('BAY').style.display = 'block';

                document.getElementById('KTC').style.display ='none';
                document.getElementById('FCY').style.display ='none';
                document.getElementById('btbpay').disabled = false;
            }

            function FCY(){
                document.getElementById('FCY').style.display = 'block';

                document.getElementById('KTC').style.display ='none';
                document.getElementById('BAY').style.display ='none';
                document.getElementById('btbpay').disabled = false;
            }

        </script>

        <?php if($amount_total >= 3000.01){ ?>

        <?php if($ms_fee == "include"){ ?>    
        <h3 style="text-decoration: underline;">เลือกการผ่อนชำระ</h3>
        <?php } if($ms_fee == "exclude"){ ?>  
        <h3 style="text-decoration: underline;">เลือกการผ่อนชำระ</h3>
        <?php } ?>
        <br>

        <table id="banks" border = "1">
        <tr id="tr_banks">
            <td id="td_banks">บัตรเคทีซี (KTC)</td>
            <td id="td_banks">บัตรกรุงศรีฯ วีซ่า , บัตรเซ็นทรัล , บัตรเทสโก้โลตัส</td>
            <td id="td_banks">บัตรกรุงศรีเฟิร์สช้อยส์ , บัตรโฮมโปร , บัตรเมกาโฮม</td>
        </tr>
         
        <tr>
        <?php if($ms_fee == "include"){ ?>
            <td id="td_banks">
            <label>
                <input type="radio" name="selectbank" id="selectbank" value="KTC" onclick="KTC();">
                <img src="<?php esc_html_e(MNS_ROOT_URL . 'includes/images/installment/KTC08.png'); ?>">
            </label>
            </td>
        <?php } if($ms_fee == "exclude"){ ?>   
            <td id="td_banks">
            <label>
                <input type="radio" name="selectbank" id="selectbank" value="KTC" onclick="KTC();">
                <img src="<?php esc_html_e(MNS_ROOT_URL .  'includes/images/installment/KTC08.png'); ?>">
            </label>
            </td>
        <?php } ?>
            <td id="td_banks">
            <label>
                <input type="radio" name="selectbank" id="selectbank" value="BAY" onclick="BAY();">
                <img src="<?php esc_html_e(MNS_ROOT_URL . 'includes/images/installment/BAY33050.png'); ?>">
            </label>
            </td>
            <td id="td_banks">
            <label>
                <input type="radio" name="selectbank" id="selectbank" value="FCY" onclick="FCY();">
                <img src="<?php esc_html_e(MNS_ROOT_URL . 'includes/images/installment/FCY.png'); ?>">
            </label>
            </td>
         </tr>
      </table>
        
        
        

        <br><br>
        
        <?php if($ms_fee == "include"){ ?> <!-------------------------------------------------------------------- include -------------------------------------------------------------------->
           
        <div id="KTC" class="installment">
            <select name="KTC_permonths" id="permonths">
                <?php foreach($KTC as $months){ ?>
                    <?php if(round($amount_total / $months,2) >= 300 && $months <= $ktc_max_months_setting){ ?>
                    <option value="<?php esc_html_e($months); ?>">ผ่อน <?php esc_html_e($months); ?> เดือน ดอกเบี้ย 0 % ( <?php esc_html_e(number_format($amount_total / $months,2)); ?> บาท / เดือน )</option>
                    <?php } ?>
                <?php } ?>
            </select>
            <br><br>
        </div>

        <div id="BAY" class="installment">
            <select name="BAY_permonths" id="permonths">
                <?php foreach($BAY as $months){ ?>
                    <?php if(round($amount_total / $months,2) >= 500 && $months <= $bay_max_months_setting){ ?>
                    <option value="<?php esc_html_e($months); ?>">ผ่อน <?php esc_html_e($months); ?> เดือน ดอกเบี้ย 0 % ( <?php esc_html_e(number_format($amount_total / $months,2)); ?> บาท / เดือน )</option>
                    <?php } ?>
                <?php } ?>
            </select>
            <br><br>
        </div>

        <div id="FCY" class="installment">
            <select name="FCY_permonths" id="permonths">
                <?php foreach($FCY as $months){ ?>
                    <?php if(round($amount_total / $months,2) >= 300 && $months <= $fcy_max_months_setting){ ?>
                    <option value="<?php esc_html_e($months); ?>">ผ่อน <?php esc_html_e($months); ?> เดือน ดอกเบี้ย 0 % ( <?php esc_html_e(number_format($amount_total / $months,2)); ?> บาท / เดือน )</option>
                    <?php } ?>
                <?php } ?>
            </select>
            <br><br>
        </div>

        <?php } if($ms_fee == "exclude"){ ?>   <!-------------------------------------------------------------------- exclude -------------------------------------------------------------------->

         <div id="KTC" class="installment">
            <select name="KTC_permonths" id="permonths">
                <?php foreach($KTC as $months){ ?>
                    <?php if(round($amount_total / $months,2) >= 300 && $months <= $ktc_max_months_setting){ $ex_ktc_bay = $amount_total / 100 * 0.8 * $months + $amount_total; ?>
                    <option value="<?php esc_html_e($months); ?>">ผ่อน <?php esc_html_e($months); ?> เดือน ดอกเบี้ย 0.8 % ( <?php esc_html_e(number_format($ex_ktc_bay / $months,2)); ?> บาท / เดือน )</option>
                    <?php } ?>
                <?php } ?>
            </select>
            <br><br>
        </div>

        <div id="BAY" class="installment">
            <select name="BAY_permonths" id="permonths">
                <?php foreach($BAY as $months){ ?>
                    <?php if(round($amount_total / $months,2) >= 500 && $months <= $bay_max_months_setting){ $ex_ktc_bay = $amount_total / 100 * 0.8 * $months + $amount_total;?>
                    <option value="<?php esc_html_e($months); ?>">ผ่อน <?php esc_html_e($months); ?> เดือน ดอกเบี้ย 0.8 % ( <?php esc_html_e(number_format($ex_ktc_bay / $months,2)); ?> บาท / เดือน )</option>
                    <?php } ?>
                <?php } ?>
            </select>
            <br><br>
        </div>

        <div id="FCY" class="installment">
            <select name="FCY_permonths" id="permonths">
                <?php foreach($FCY as $months){ ?>
                    <?php if(round($amount_total / $months,2) >= 300 && $months <= $fcy_max_months_setting){ $ex_fcy = $amount_total / 100 * 1 * $months + $amount_total; ?>
                    <option value="<?php esc_html_e($months); ?>">ผ่อน <?php esc_html_e($months); ?> เดือน ดอกเบี้ย 1 % ( <?php esc_html_e(number_format($ex_fcy / $months,2)); ?> บาท / เดือน )</option>
                    <?php } ?>
                <?php } ?>
            </select>
            <br><br>
        </div>

        <?php } ?>


        <?php if ($ms_message2store == "Enable"){ ?>

        <div id="custom_input">
            <p class="form-row form-row-wide">
                <label for="message_card" class=""><?php _e("", $this->domain); ?></label>
                <input type="text" class="" name="message_card" id="message_card" placeholder="<?php esc_html_e(MESSAGE2STORE); ?>">
            </p>
        </div>

        <?php } ?>

        <?php if ($ms_message2store != "Enable"){ ?>


        <input type="text" class="" name="message_card" id="message_card" placeholder="<?php esc_html_e(MESSAGE2STORE); ?>" value="" hidden>


        <?php } ?>

        <?php }else if($amount_total < 3000.01){ ?>

        <h5 style="color:red">จำนวนยอดเงินต้อง 3,000.01 บาทขึ้นไปถึงจะทำการผ่อนชำระได้</h5>

        <?php } ?>
        
        <?php

    }


    /* Process the payment and return the result. */
	function process_payment ($order_id) {



        $order = wc_get_order($order_id);
        $order_amount = $order->get_total();
        update_post_meta($order_id, 'MNS_special_instructions_to_merchant', sanitize_text_field($_POST["message_card"]));

    if (is_user_logged_in()) {
        if (strlen(sanitize_text_field($_POST["message_card"])) <= 150) {

            if (get_woocommerce_currency() == "THB") {


                if($order_amount >= 3000.01){

                    if(sanitize_text_field($_POST["selectbank"]) != ""){

                        $MNS_PAYMENT_TYPE = get_post_meta($order_id, 'MNS_PAYMENT_TYPE', true);

                
            
                            delete_post_meta($order_id, 'MNS_transaction');
                            delete_post_meta($order_id, 'MS_QR_URL');
                            update_post_meta($order_id, 'MNS_PAYMENT_TYPE', "Installment");
                            update_post_meta($order_id, 'MS_INSTALLMENT_BANK', sanitize_text_field($_POST["selectbank"]));

                            $Endterm = "";

                            if (sanitize_text_field($_POST["selectbank"]) == "KTC"){
                                $Endterm = sanitize_text_field($_POST["KTC_permonths"]);
                                update_post_meta($order_id, 'MS_INSTALLMENT_MONTHS', sanitize_text_field($_POST["KTC_permonths"]));
                            }else if (sanitize_text_field($_POST["selectbank"]) == "BAY"){
                                $Endterm = sanitize_text_field($_POST["BAY_permonths"]);
                                update_post_meta($order_id, 'MS_INSTALLMENT_MONTHS', sanitize_text_field($_POST["BAY_permonths"]));
                            }else if (sanitize_text_field($_POST["selectbank"]) == "FCY"){
                                $Endterm = sanitize_text_field($_POST["FCY_permonths"]);
                                update_post_meta($order_id, 'MS_INSTALLMENT_MONTHS', sanitize_text_field($_POST["FCY_permonths"]));
                            }


                            $gateways = WC()->payment_gateways->get_available_payment_gateways();
                            $ms_order_select = $gateways['moneyspace']->settings['order_status_if_success'];
                            $ms_secret_id = $gateways['moneyspace']->settings['secret_id'];
                            $ms_secret_key = $gateways['moneyspace']->settings['secret_key'];
                            $ms_fee = $gateways['moneyspace_installment']->settings['fee_setting'];
                            $agreement_setting = $gateways['moneyspace_installment']->settings['agreement_setting']; 

                            $ms_time = date("YmdHis");

                            $items_order = new WC_Order($order_id);
                            $items = $order->get_items();

                            $items_msg = "";
                            $i = 1;

                            foreach ($items as $item) {

                                $product = wc_get_product($item['product_id']);
            
                                if (count($items) > 1) {
                                    $items_msg .= $i . ". " . $product->get_name() . "  " . $product->get_price() . "฿" . " ( " . $item['quantity'] . " qty ) ";
                                    $i++;
                                } else {
                                    $items_msg .= $product->get_name() . "  " . $product->get_price() . "฿" . " ( " . $item['quantity'] . " qty ) ";
                                }
            
                            }
                            
                            if ($ms_order_select == "wc-failed" || $ms_order_select == "wc-cancelled" || $ms_order_select == "wc-refunded") {


                                wc_add_notice(__(MS_NOTICE_ERROR_CONTINUE, $this->domain), 'error');
            
            
                            } else {

                                $url = "https://www.moneyspace.net/merchantapi/v2/createinstallment/obj";

                                $payment_data = array();
                                $payment_data["secreteID"] = $ms_secret_id;
                                $payment_data['firstname'] = $order->get_billing_first_name();
                                $payment_data['lastname'] = $order->get_billing_last_name();
                                $payment_data['email'] = $order->get_billing_email();
                                $payment_data['phone'] = $order->get_billing_phone();
                                $payment_data['amount'] = $order_amount;
                                $payment_data['currency'] = 'THB';
                                $payment_data['description'] = $items_msg;
                                $payment_data['address'] = $order->get_billing_address_1() . " " . $order->get_billing_address_2() . " " . $order->get_billing_city() . " " . $order->get_billing_postcode();
                                $payment_data['message'] = sanitize_text_field($_POST["message_card"]);
                                $payment_data['feeType'] = $ms_fee;
                                $payment_data['timeHash'] = $ms_time;
                                $payment_data['customer_order_id'] = $ms_time;
                                $payment_data['gatewayType'] = "card";
                                $payment_data['successUrl'] = $this->get_return_url($order);
                                $payment_data['failUrl'] = $this->get_return_url($order);
                                $payment_data['cancelUrl'] = $this->get_return_url($order);
                                $payment_data['agreement'] = $agreement_setting;
                                $payment_data['bankType'] = sanitize_text_field($_POST["selectbank"]);
                                $payment_data['startTerm'] = '3';
                                $payment_data['endTerm'] = $Endterm;
                                $payment_data['bgColor'] = "#43a047";
                                $payment_data['txtColor'] = "#FFFFFF";

                                $data = $payment_data["firstname"] . $payment_data["lastname"] . $payment_data["email"] . $payment_data["phone"] . $payment_data["amount"] . $payment_data['currency'] . $payment_data["description"] . $payment_data["address"] . $payment_data["message"] . $payment_data["feeType"] . $payment_data['timeHash'] . $payment_data['customer_order_id'] . $payment_data['gatewayType'] . $payment_data['successUrl'] . $payment_data['failUrl'] . $payment_data['cancelUrl'];
		                        $dataHash = hash_hmac('sha256',$data,$ms_secret_key);
                                $payment_data["hash"] = $dataHash;

                                $response = wp_remote_post($url, array(
                                    'method' => 'POST',
                                    'timeout' => 120,
                                    'body' => $payment_data
                                    )
                                );
                                if (is_wp_error($response)) {
    
                                    wc_add_notice(__($response, $this->domain), 'error');
    
                                } else {

                                    $json_tranId_status = json_decode($response["body"]);

                                    if ($json_tranId_status[0]->status == "NotFound" 
                                    || $json_tranId_status[0]->status == "The payment amount must be greater than 3000 baht." 
                                    || $json_tranId_status[0]->status == "Data Invalid"
                                    || $json_tranId_status[0]->status == "Payment amount must be less than 10000.00") {

                                        wc_add_notice(__($json_tranId_status[0]->status, $this->domain), 'error');
        
                                    } else {

                                        $urlpayment = "Transaction ID";
                                        $json_tranId = json_decode($response["body"]);
                                        $tranId = $json_tranId[0]->$urlpayment;

                                        $hash_link = hash_hmac('sha256', $tranId . $ms_time, $ms_secret_key);

                                        $link = "https://www.moneyspace.net/merchantapi/makepayment/linkpaymentcard?transactionID=" . $tranId . "&timehash=" . $ms_time . "&secreteID=" . $ms_secret_id . "&hash=" . $hash_link;

                                        update_post_meta($order_id, 'MNS_transaction', $tranId);

                                        update_post_meta($order_id, 'MS_fee_installment', $payment_data['feeType']);

                                        update_post_meta($order_id, 'MS_orderid_installment', $payment_data['customer_order_id']);

                                        $order = wc_get_order($order_id);
                                        
                                        return $this->_process_external_payment($order);
                                    }
                                }
                            }
                        }else{
                            wc_add_notice(__("กรุณาเลือกการผ่อนชำระ".sanitize_text_field($_POST["selectbank"]), $this->domain), 'error');
                        }
                    }else{
                        wc_add_notice(__("จำนวนยอดเงินต้อง 3,000.01 บาทขึ้นไปถึงจะทำการผ่อนชำระได้", $this->domain), 'error');
                    }
                }else{
                    wc_add_notice(__(MS_NOTICE_CURRENCY, $this->domain), 'error');
                }
            }else{
                wc_add_notice(__("Message to the store (150 characters maximum)", $this->domain), 'error');
            }
        }else{
            wc_add_notice(__("Please login !", $this->domain), 'error');
        }
    }


    public function getHash($data,$key) {
        return hash_hmac('sha256', $data, $key);
    }

    public function getTime() {
        return date("YmdHis");
    }



    public function paymentgateway_form($order_id) {

        $gateways = WC()->payment_gateways->get_available_payment_gateways();
        $ms_secret_id = $gateways['moneyspace']->settings['secret_id'];
        $ms_secret_key = $gateways['moneyspace']->settings['secret_key'];
        $ms_fee = $gateways['moneyspace_installment']->settings['fee_setting'];    



        $MNS_transaction = get_post_meta($order_id, 'MNS_transaction', true);
        $MS_orderid_installment = get_post_meta($order_id, 'MS_orderid_installment', true);
        $MS_INSTALLMENT_BANK = get_post_meta($order_id, 'MS_INSTALLMENT_BANK', true);
        $MS_INSTALLMENT_MONTHS = get_post_meta($order_id, 'MS_INSTALLMENT_MONTHS', true);
        $MS_fee_installment = get_post_meta($order_id, 'MS_fee_installment', true);

        $order = wc_get_order($order_id);
        $order_amount = $order->get_total();


        if ($MS_fee_installment == "include"){
            $KTC = [ 3, 4, 5, 6, 7, 8, 9, 10];
            $BAY = [ 3, 4, 6, 9, 10];
            $FCY = [ 3, 4, 6, 9, 10];
        }else if ($MS_fee_installment == "exclude"){
            $KTC = [ 3, 4, 5, 6, 7, 8, 9, 10];
            $BAY = [ 3, 4, 6, 9, 10];
            $FCY = [ 3, 4, 6, 9, 10, 12, 18, 24, 36];
        }

        if($MS_fee_installment == "include"){
            $ex_ktc_bay = $order_amount;
            $ex_fcy = $order_amount;
        }else if($MS_fee_installment == "exclude"){
            $ex_ktc_bay = $order_amount / 100 * 0.8 * $MS_INSTALLMENT_MONTHS + $order_amount;
            $ex_fcy = $order_amount / 100 * 1 * $MS_INSTALLMENT_MONTHS + $order_amount;
        }

        ?>
        

        <h3 style="text-decoration: underline;">ยืนยันการชำระเงิน</h3>

        <p><h5>จำนวนเดือน : <?php esc_html_e($MS_INSTALLMENT_MONTHS."  เดือน"); ?></h5></p>

        <?php if($MS_INSTALLMENT_BANK == "KTC" || $MS_INSTALLMENT_BANK == "BAY"){ ?>

        <p><h5>จำนวนเงินชำระต่อเดือน : <?php esc_html_e(round($ex_ktc_bay / $MS_INSTALLMENT_MONTHS,2).get_woocommerce_currency()." / เดือน"); ?></h5></p>

        <?php }if($MS_INSTALLMENT_BANK == "FCY"){ ?>

        <p><h5>จำนวนเงินชำระต่อเดือน : <?php esc_html_e(round($ex_fcy / $MS_INSTALLMENT_MONTHS,2).get_woocommerce_currency()." / เดือน"); ?></h5></p>

        <?php } ?>


        <?php 
        if($MS_INSTALLMENT_BANK == "KTC"){
            $actionUrl = "https://www.moneyspace.net/ktccredit/payment/directpay";
            $paymonth = round($ex_ktc_bay / $MS_INSTALLMENT_MONTHS,2);
        }

        if($MS_INSTALLMENT_BANK == "BAY"){
            $actionUrl = "https://www.moneyspace.net/baycredit/pay";
            $paymonth = round($ex_ktc_bay / $MS_INSTALLMENT_MONTHS,2);
        }

        if($MS_INSTALLMENT_BANK == "FCY"){
            $actionUrl = "https://www.moneyspace.net/baycredit/pay";
            $paymonth = round($ex_fcy / $MS_INSTALLMENT_MONTHS,2);
        }
        ?>
        
        <div style="display: none;">
            <form  id="mainform"  method="post" action="<?php esc_html_e($actionUrl); ?>">
                    <div class="row">
                        <div class="col-12 col-sm-12 col-12 mt-3">
                            <input type="hidden" name="transactionID" value="<?php esc_html_e($MNS_transaction); ?>"/>
                            <input type="hidden" id="pay_type" name="pay_type" value=""/>
                            <input type="hidden" id="locale" name="locale" value=""/>
                             <input type="hidden" id="term" name="term" value="<?php esc_html_e($MS_INSTALLMENT_MONTHS); ?>"/>
                            <input type="hidden" id="payForMonth" name="paymonth" value="<?php esc_html_e($paymonth); ?>"/>
                            <input type="hidden" id="interest" name="interest" value="0.0"/>
                            <input type="hidden" id="bankType" name="bankType" value="<?php esc_html_e($MS_INSTALLMENT_BANK); ?>"/>
                        </div>
                     </div>
                 <button class="button" type="submit">ชำระเงิน</button>
            </form>
        </div>
        <?php wc_enqueue_js("
        document.getElementById('mainform').submit();
        "); ?>
    <?php


    }
    
    protected function _process_external_payment($order) {
        // Redirect to payment page, where payment form will be printed
        return array(
            'result' => 'success',
            'redirect' => $order->get_checkout_payment_url(true)
        );
    }


	
}