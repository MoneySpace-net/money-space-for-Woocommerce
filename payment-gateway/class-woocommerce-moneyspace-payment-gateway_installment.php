<?php

/**
 * WC wcCpg3 Gateway Class.
 * Built the wcCpg3 method.
 */
class MS_Payment_Gateway_INSTALLMENT extends WC_Payment_Gateway
{
    /**
     * Constructor for the gateway.
     *
     * @return void
     */
    public function __construct()
    {
        $this->domain = 'ms_payment_installment';
        global $woocommerce;
        $this->id = MS_ID_INSTALLMENT;
        $this->title = __($this->get_option('title'), $this->domain);
        $this->icon = apply_filters('woocommerce_custom_gateway_icon', MS_LOGO_INSTALLMENT, '');
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
        add_action('woocommerce_receipt_' . $this->id, array($this, 'paymentgateway_form'), 10, 1);
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'header_setting' => array(
                'title' => __('<h1><b> ' . MS_FORM_FIELD_HEADER_SETTING . ' </b></h1>', $this->domain),
                'type' => 'title'
            ),
            'enabled' => array(
                'title' => __(MS_FORM_FIELD_ENABLE, $this->domain),
                'type' => 'checkbox',
                'label' => __(MS_FORM_FIELD_ENABLE_LABEL, $this->domain),
                'default' => 'no'
            ),
            'title' => array(
                'title' => __('Title', $this->domain),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', $this->domain),
                'default' => __('ผ่อนชำระรายเดือน', $this->domain),
                'desc_tip' => true,
            ),
            'order_status_if_success' => array(
                'title' => __(MS_FORM_FIELD_SET_ORDER_STATUS, $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'wc-processing',
                'desc_tip' => true,
                'options' => wc_get_order_statuses()
            ),
            'description' => array(
                'title' => __(MS_FORM_FIELD_DESCRIPTION, $this->domain),
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
                'options' => ["include" => "ร้านค้ารับผิดชอบดอกเบี้ยรายเดือน"]
            ),
            'message2store_setting' => array(
                'title' => __(MESSAGE2STORE_HEADER, $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'Enable',
                'desc_tip' => true,
                'options' => [true => "Enable", false => "Disable"]
            ),
            'ktc_max_months_setting' => array(
                'title' => __("KTC ผ่อนสูงสุด", $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 3,
                'desc_tip' => true,
                'options' => [3 => "3 เดือน", 4 => "4 เดือน", 5 => "5 เดือน", 6 => "6 เดือน", 7 => "7 เดือน", 8 => "8 เดือน", 9 => "9 เดือน", 10 => "10 เดือน"]
            ),
            'bay_max_months_setting' => array(
                'title' => __("BAY ผ่อนสูงสุด", $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 3,
                'desc_tip' => true,
                'options' => [3 => "3 เดือน", 4 => "4 เดือน", 6 => "6 เดือน", 9 => "9 เดือน", 10 => "10 เดือน"]
            ),
            'fcy_max_months_setting' => array(
                'title' => __("FCY ผ่อนสูงสุด", $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 3,
                'desc_tip' => true,
                'options' => [3 => "3 เดือน", 4 => "4 เดือน", 6 => "6 เดือน", 9 => "9 เดือน", 10 => "10 เดือน", 12 => "12 เดือน (เฉพาะผู้ถือบัตรรับผิดชอบดอกเบี้ยรายเดือน)", 18 => "18 เดือน (เฉพาะผู้ถือบัตรรับผิดชอบดอกเบี้ยรายเดือน)", 24 => "24 เดือน (เฉพาะผู้ถือบัตรรับผิดชอบดอกเบี้ยรายเดือน)", 36 => "36 เดือน (เฉพาะผู้ถือบัตรรับผิดชอบดอกเบี้ยรายเดือน)"]
            ),
            'agreement_setting' => array(
                'title' => __("เงื่อนไข", $this->domain),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => "1",
                'desc_tip' => true,
                'options' => [
                    "1" => "ข้าพเจ้ายอมรับว่าไม่สามารถขอคืนเงิน หรือยกเลิกรายการได้",
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
            echo wpautop(wptexturize($this->instructions));
    }

    public function payment_fields()
    {
        $payment_gateway_id = MS_ID_INSTALLMENT;
        $payment_gateways = WC_Payment_Gateways::instance();
        $payment_gateway = $payment_gateways->payment_gateways()[$payment_gateway_id];
        $gateways = WC()->payment_gateways->get_available_payment_gateways();
        $ms_message2store = $gateways['moneyspace_installment']->settings['message2store_setting'];
        $ms_fee = $gateways['moneyspace_installment']->settings['fee_setting'];
        $ktc_max_months_setting = $gateways['moneyspace_installment']->settings['ktc_max_months_setting'];
        $bay_max_months_setting = $gateways['moneyspace_installment']->settings['bay_max_months_setting'];
        $fcy_max_months_setting = $gateways['moneyspace_installment']->settings['fcy_max_months_setting'];

        if (WC()->cart->total && WC()->cart->total != 0) {
            $amount_total = WC()->cart->total;
        } else {
            global $wp;
            $order_id = $wp->query_vars['order-pay'];
            $order = new WC_Order($order_id);
            $amount_total = round($order->get_total(), 2);
        }

        if ($ms_fee == "include") {
            $KTC = [3, 4, 5, 6, 7, 8, 9, 10];
            $BAY = [3, 4, 6, 9, 10];
            $FCY = [3, 4, 6, 9, 10];
        } else if ($ms_fee == "exclude") {
            $KTC = [3, 4, 5, 6, 7, 8, 9, 10];
            $BAY = [3, 4, 6, 9, 10];
            $FCY = [3, 4, 6, 9, 10, 12, 18, 24, 36];
        }

        if ($description = $this->get_description()) {
            echo wpautop(wptexturize($description));
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

        <?php if ($amount_total >= 3000.01) { ?>

        <?php if ($ms_fee == "include") { ?>
            <h3 style="text-decoration: underline;">เลือกการผ่อนชำระ</h3>
        <?php }
        if ($ms_fee == "exclude") { ?>
            <h3 style="text-decoration: underline;">เลือกการผ่อนชำระ</h3>
        <?php } ?>
        <br>

        <table id="banks" border="1">
            <tr id="tr_banks">
                <td id="td_banks">บัตรเคทีซี (KTC)</td>
                <td id="td_banks">บัตรกรุงศรีฯ วีซ่า , บัตรเซ็นทรัล , บัตรเทสโก้โลตัส</td>
                <td id="td_banks">บัตรกรุงศรีเฟิร์สช้อยส์ , บัตรโฮมโปร , บัตรเมกาโฮม</td>
            </tr>
            <tr>
                <td id="td_banks">
                    <label>
                        <input type="radio" name="selectbank" id="selectbank" value="KTC">
                        <img src="https://worldwallet.net/images/installment/KTC0.png">
                    </label>
                </td>
                <td id="td_banks">
                    <label>
                        <input type="radio" name="selectbank" id="selectbank" value="BAY">
                        <img src="https://worldwallet.net/images/installment/BAY33050.png">
                    </label>
                </td>
                <td id="td_banks">
                    <label>
                        <input type="radio" name="selectbank" id="selectbank" value="FCY">
                        <img src="https://worldwallet.net/images/installment/FCY.png">
                    </label>
                </td>
            </tr>
        </table>
        <br><br>
        <?php if ($ms_message2store != 0) { ?>
            <div id="custom_input">
                <p class="form-row form-row-wide">
                    <label for="message_card" class=""><?php _e("", $this->domain); ?></label>
                    <input type="text" class="" name="message_ins" id="message_card" placeholder="<?= MESSAGE2STORE ?>">
                </p>
            </div>
        <?php } ?>
    <?php } else if ($amount_total < 3000.01) { ?>
        <h5 style="color:red">จำนวนยอดเงินต้อง 3,000.01 บาทขึ้นไปถึงจะทำการผ่อนชำระได้</h5>
    <?php } ?>
        <?php
    }

    /* Process the payment and return the result. */
    function process_payment($order_id)
    {
        $order = wc_get_order($order_id);
        $order_amount = $order->get_total();

        $payment_gateway_id = MS_ID;

        $payment_gateways = WC_Payment_Gateways::instance();

        $payment_gateway = $payment_gateways->payment_gateways()[$payment_gateway_id];

        $gateways = WC()->payment_gateways->get_available_payment_gateways();
        $ms_order_select = $payment_gateway->settings['order_status_if_success'];
        $ms_secret_id = $payment_gateway->settings['secret_id'];
        $ms_secret_key = $payment_gateway->settings['secret_key'];

        $ms_template_payment = $payment_gateway->settings['ms_template_payment'];

        $ms_time = date("YmdHis");

        if (is_user_logged_in()) {
            if (strlen($_POST["message_ins"]) <= 150) {
                update_post_meta($order_id, 'MS_special_instructions_to_merchant', $_POST["message_ins"]);
                $MS_special_instructions_to_merchant = get_post_meta($order_id, 'MS_special_instructions_to_merchant', true);
                if (get_woocommerce_currency() == "THB") {
                    if ($order_amount >= 3000.01) {
                        if (isset($_POST["selectbank"])) {
                            $MS_PAYMENT_TYPE = get_post_meta($order_id, 'MS_PAYMENT_TYPE', true);

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
                                update_post_meta($order_id, 'MS_PAYMENT_TYPE', "Installment");
                                update_post_meta($order_id, 'MS_INSTALLMENT_BANK', $_POST["selectbank"]);

                                $MS_PAYMENT_TYPE = get_post_meta($order->id, 'MS_PAYMENT_TYPE', true);
                                $gateways = WC()->payment_gateways->get_available_payment_gateways();
                                $agreement_setting = $gateways['moneyspace_installment']->settings['agreement_setting'];

                                if (!$agreement_setting) {
                                    $agreement_setting = "1";
                                }

                                if ($_POST["selectbank"] == "KTC") {
                                    $max_months_setting = $gateways['moneyspace_installment']->settings['ktc_max_months_setting'];
                                    update_post_meta($order_id, 'MS_INSTALLMENT_MONTHS', $_POST["KTC_permonths"]);
                                } else if ($_POST["selectbank"] == "BAY") {
                                    $max_months_setting = $gateways['moneyspace_installment']->settings['bay_max_months_setting'];
                                    update_post_meta($order_id, 'MS_INSTALLMENT_MONTHS', $_POST["BAY_permonths"]);
                                } else if ($_POST["selectbank"] == "FCY") {
                                    $max_months_setting = $gateways['moneyspace_installment']->settings['fcy_max_months_setting'];
                                    update_post_meta($order_id, 'MS_INSTALLMENT_MONTHS', $_POST["FCY_permonths"]);
                                }

                                $order_firstname = "";
                                $order_lastname = "";
                                $order_email = "";
                                $order_phone = "";
                                $order_address = "";

                                if ($gateways['moneyspace']->settings['ms_firstname'] == "yes") {
                                    $order_firstname = $order->get_billing_first_name();
                                }

                                if ($gateways['moneyspace']->settings['ms_lastname'] == "yes") {
                                    $order_lastname = $order->get_billing_last_name();
                                }

                                if ($gateways['moneyspace']->settings['ms_email'] == "yes") {
                                    $order_email = $order->get_billing_email();
                                }

                                if ($gateways['moneyspace']->settings['ms_phone'] == "yes") {
                                    $order_phone = $order->get_billing_phone();
                                }

                                if ($gateways['moneyspace']->settings['ms_address'] == "yes") {
                                    $order_address = $order->get_billing_address_1() . " " . $order->get_billing_address_2() . " " . $order->get_billing_city() . " " . $order->get_billing_postcode();
                                }

                                $body_post = array("firstname" => $order_firstname, "lastname" => $order_lastname, "email" => $order_email, "phone" => $order_phone, "address" => $order_address, "amount" => $order_amount, "description" => $items_msg, "message" => $MS_special_instructions_to_merchant, "feeType" => "include", "customer_order_id" => $order_id . "MS" . $ms_time);

                                $ms_body = array(
                                    'secret_id' => $ms_secret_id,
                                    'secret_key' => $ms_secret_key,
                                    'firstname' => $body_post["firstname"],
                                    'lastname' => $body_post["lastname"],
                                    'email' => $body_post["email"],
                                    'phone' => $body_post["phone"],
                                    'amount' => round($body_post["amount"], 2),
                                    'description' => preg_replace( "/<br>|\n/", "", $body_post["description"] ),
                                    'address' => $body_post["address"],
                                    'message' => $body_post["message"],
                                    'feeType' => "include",
                                    'order_id' => $body_post["customer_order_id"],
                                    "payment_type" => "installment",
                                    'success_Url' => get_site_url() . "/process/payment/" . $order_id,
                                    'fail_Url' => get_site_url() . "/process/payment/" . $order_id,
                                    'cancel_Url' => get_site_url() . "/process/payment/" . $order_id,
                                    "agreement" => $agreement_setting,
                                    'bankType' => $_POST["selectbank"],
                                    'startTerm' => "3",
                                    'endTerm' => $max_months_setting
                                );

                                $response = wp_remote_post(MS_API_URL_CREATE, array(
                                        'method' => 'POST',
                                        'timeout' => 120,
                                        'body' => $ms_body
                                    )
                                );

                                if (is_wp_error($response)) {
                                    wc_add_notice(__("Error ms999", $this->domain), 'error');
                                } else {
                                    $data_status = json_decode($response["body"]);
                                    if ($data_status[0]->status == "success") {
                                        if (strlen($data_status[0]->mskey) > 9999) {
                                            wc_add_notice(__("Error ms100 : " . MS_NOTICE_CHECK_TRANSACTION . $data_status[0]->status, $this->domain), 'error');
                                        } else {
                                            $tranId = $data_status[0]->transaction_ID;
                                            $mskey = $data_status[0]->mskey;

                                            update_post_meta($order_id, 'MS_fee_installment', "include");
                                            update_post_meta($order_id, 'MS_orderid_installment', $body_post["customer_order_id"]);
                                            update_post_meta($order_id, 'MS_transaction_orderid', $body_post["customer_order_id"]);
                                            update_post_meta($order_id, 'MS_transaction', $tranId);
                                            update_post_meta($order_id, 'MS_PAYMENT_KEY', $mskey);

                                            return array(
                                                'result' => 'success',
                                                'redirect' => get_site_url() . "/mspaylink/" . $order_id
                                            );
                                        }
                                    } else {
                                        wc_add_notice(__($data_status[0]->status, $this->domain), 'error');
                                    }
                                }
                            }
                        } else {
                            wc_add_notice(__("กรุณาเลือกการผ่อนชำระ" . $_POST["selectbank"], $this->domain), 'error');
                        }
                    } else {
                        wc_add_notice(__("จำนวนยอดเงินต้อง 3,000.01 บาทขึ้นไปถึงจะทำการผ่อนชำระได้", $this->domain), 'error');
                    }
                } else {
                    wc_add_notice(__(MS_NOTICE_CURRENCY, $this->domain), 'error');
                }
            } else {
                wc_add_notice(__("Message to the store (150 characters maximum)", $this->domain), 'error');
            }
        } else {
            wc_add_notice(__("Please login !", $this->domain), 'error');
        }
    }

    public function getHash($data, $key)
    {
        return hash_hmac('sha256', $data, $key);
    }

    public function getTime()
    {
        return date("YmdHis");
    }

    public function paymentgateway_form($order_id)
    {
        $gateways = WC()->payment_gateways->get_available_payment_gateways();
        $ms_secret_id = $gateways['moneyspace']->settings['secret_id'];
        $ms_secret_key = $gateways['moneyspace']->settings['secret_key'];
        $ms_fee = $gateways['moneyspace_installment']->settings['fee_setting'];

        $MS_transaction = get_post_meta($order_id, 'MS_transaction', true);
        $MS_orderid_installment = get_post_meta($order_id, 'MS_orderid_installment', true);
        $MS_INSTALLMENT_BANK = get_post_meta($order_id, 'MS_INSTALLMENT_BANK', true);
        $MS_INSTALLMENT_MONTHS = get_post_meta($order_id, 'MS_INSTALLMENT_MONTHS', true);
        $MS_fee_installment = get_post_meta($order_id, 'MS_fee_installment', true);

        $order = wc_get_order($order_id);
        $order_amount = $order->get_total();

        if ($MS_fee_installment == "include") {
            $KTC = [3, 4, 5, 6, 7, 8, 9, 10];
            $BAY = [3, 4, 6, 9, 10];
            $FCY = [3, 4, 6, 9, 10];
        } else if ($MS_fee_installment == "exclude") {
            $KTC = [3, 4, 5, 6, 7, 8, 9, 10];
            $BAY = [3, 4, 6, 9, 10];
            $FCY = [3, 4, 6, 9, 10, 12, 18, 24, 36];
        }

        if ($MS_fee_installment == "include") {
            $ex_ktc_bay = $order_amount;
            $ex_fcy = $order_amount;
        } else if ($MS_fee_installment == "exclude") {
            $ex_ktc_bay = $order_amount / 100 * 0.8 * $MS_INSTALLMENT_MONTHS + $order_amount;
            $ex_fcy = $order_amount / 100 * 1 * $MS_INSTALLMENT_MONTHS + $order_amount;
        }
        ?>

        <h3 style="text-decoration: underline;">ยืนยันการชำระเงิน</h3>
        <p><h5>จำนวนเดือน : <?= $MS_INSTALLMENT_MONTHS . "  เดือน" ?></h5></p>
        <?php if ($MS_INSTALLMENT_BANK == "KTC" || $MS_INSTALLMENT_BANK == "BAY") { ?>
        <p><h5>จำนวนเงินชำระต่อเดือน
            : <?= round($ex_ktc_bay / $MS_INSTALLMENT_MONTHS, 2) . get_woocommerce_currency() . " / เดือน" ?></h5></p>
    <?php }
        if ($MS_INSTALLMENT_BANK == "FCY") { ?>
            <p><h5>จำนวนเงินชำระต่อเดือน
                : <?= round($ex_fcy / $MS_INSTALLMENT_MONTHS, 2) . get_woocommerce_currency() . " / เดือน" ?></h5></p>
        <?php } ?>
        <?php if ($MS_INSTALLMENT_BANK == "KTC") { ?>
        <div align="">
            <form id="mainform" method="post" action="https://www.moneyspace.net/ktccredit/payment/directpay">
                <div class="row">
                    <div class="col-12 col-sm-12 col-12 mt-3">
                        <input type="hidden" name="transactionID" value="<?= $MS_transaction ?>"/>
                        <input type="hidden" id="pay_type" name="pay_type" value=""/>
                        <input type="hidden" id="locale" name="locale" value=""/>
                        <input type="hidden" id="term" name="term" value="<?= $MS_INSTALLMENT_MONTHS ?>"/>
                        <input type="hidden" id="payForMonth" name="paymonth"
                               value="<?= round($ex_ktc_bay / $MS_INSTALLMENT_MONTHS, 2) ?>"/>
                        <input type="hidden" id="interest" name="interest" value="0.0"/>
                        <input type="hidden" id="bankType" name="bankType" value="<?= $MS_INSTALLMENT_BANK ?>"/>
                    </div>
                </div>
                <button class="button" type="submit">ชำระเงิน</button>
            </form>
        </div>
    <?php } ?>
        <?php if ($MS_INSTALLMENT_BANK == "BAY") { ?>
        <div align="">
            <form id="mainform" method="post" action="https://www.moneyspace.net/baycredit/pay">
                <div class="row">
                    <div class="col-12 col-sm-12 col-12 mt-3">
                        <input type="hidden" name="transactionID" value="<?= $MS_transaction ?>"/>
                        <input type="hidden" id="pay_type" name="pay_type" value=""/>
                        <input type="hidden" id="locale" name="locale" value=""/>
                        <input type="hidden" id="term" name="term" value="<?= $MS_INSTALLMENT_MONTHS ?>"/>
                        <input type="hidden" id="payForMonth" name="paymonth"
                               value="<?= round($ex_ktc_bay / $MS_INSTALLMENT_MONTHS, 2) ?>"/>
                        <input type="hidden" id="interest" name="interest" value="0.0"/>
                        <input type="hidden" id="bankType" name="bankType" value="<?= $MS_INSTALLMENT_BANK ?>"/>
                    </div>
                </div>
                <button class="button" type="submit">ชำระเงิน</button>
            </form>
        </div>
    <?php } ?>
        <?php if ($MS_INSTALLMENT_BANK == "FCY") { ?>
        <div align="">
            <form id="mainform" method="post" action="https://www.moneyspace.net/baycredit/pay">
                <div class="row">
                    <div class="col-12 col-sm-12 col-12 mt-3">
                        <input type="hidden" name="transactionID" value="<?= $MS_transaction ?>"/>
                        <input type="hidden" id="pay_type" name="pay_type" value=""/>
                        <input type="hidden" id="locale" name="locale" value=""/>
                        <input type="hidden" id="term" name="term" value="<?= $MS_INSTALLMENT_MONTHS ?>"/>
                        <input type="hidden" id="payForMonth" name="paymonth"
                               value="<?= round($ex_fcy / $MS_INSTALLMENT_MONTHS, 2) ?>"/>
                        <input type="hidden" id="interest" name="interest" value="0.0"/>
                        <input type="hidden" id="bankType" name="bankType" value="<?= $MS_INSTALLMENT_BANK ?>"/>
                    </div>
                </div>
                <button class="button" type="submit">ชำระเงิน</button>
            </form>
        </div>
    <?php } ?>
        <?php
    }

    protected function _process_external_payment($order)
    {
        // Redirect to payment page, where payment form will be printed
        return array(
            'result' => 'success',
            'redirect' => $order->get_checkout_payment_url(true)
        );
    }
}
