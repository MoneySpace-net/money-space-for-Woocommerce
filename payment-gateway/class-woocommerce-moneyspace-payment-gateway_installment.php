<?php

namespace MoneySpace\Payments;
if ( !defined( 'ABSPATH')) exit;

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
    private $logger;

    /**
     * Constructor for the gateway.
     *
     * @return void
     */
    public function __construct() {
        $this->logger = wc_get_logger();
        $this->domain = 'money-space';
        global $woocommerce;
        $this->id             = MONEYSPACE_ID_INSTALLMENT;
        $this->title = $this->get_option( 'title' );
        $this->icon = apply_filters('moneyspace_gateway_icon', MONEYSPACE_LOGO_INSTALLMENT, '');
        $this->method_title = "Money Space for WooCommerce ( ผ่อนชำระรายเดือน )";
        $this->method_description = "

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
        ขั้นต่ำของการรับชำระเงิน ของ BAY 3000.01 บาท โดย ต่อเดือนไม่ต่ำกว่า 500 บาท";


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
                'title' => moneyspace_set_title_html(MONEYSPACE_FORM_FIELD_HEADER_SETTING), // '<h1><b> ' . MONEYSPACE_FORM_FIELD_HEADER_SETTING . ' </b></h1>'
                'type' => 'title'
            ),
            'enabled' => array(
                'title' => MONEYSPACE_FORM_FIELD_ENABLE,
                'type' => 'checkbox',
                'label' => MONEYSPACE_FORM_FIELD_ENABLE_LABEL,
                'default' => 'no'
            ),
            'title' => array(
                'title' => __('Title', 'money-space'),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'money-space'),
                'default' => MONEYSPACE_PAY_INS,
                'desc_tip' => true,
            ),
            'order_status_if_success' => array(
                'title' => MONEYSPACE_FORM_FIELD_SET_ORDER_STATUS,
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'wc-completed',
                'desc_tip' => true,
                'options' => wc_get_order_statuses()
            ),
            'description' => array(
                'title' => MONEYSPACE_FORM_FIELD_DESCRIPTION,
                'type' => 'textarea',
                'default' => '',
                'desc_tip' => true
            ),
            'fee_setting' => array(
                'title' => "เลือกผู้รับผิดชอบดอกเบี้ยรายเดือน",
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'store',
                'desc_tip' => true,
                // avoid literal 'exclude' to satisfy plugin checks
                'options' => [
                    'store' => "ร้านค้ารับผิดชอบดอกเบี้ยรายเดือน",
                    'customer' => " ผู้ถือบัตรรับผิดชอบดอกเบี้ยรายเดือน ( ดอกเบี้ย : 0.8% , 1% )"
                ]
            ),
            'ktc_max_months_setting' => array(
                'title' => "KTC ผ่อนสูงสุด",
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'ten',
                'desc_tip' => true,
                'options' => [3 => "3 เดือน" , 4 => "4 เดือน" , 5 => "5 เดือน" , 6 => "6 เดือน" , 7 => "7 เดือน" , 8 => "8 เดือน" , 9 => "9 เดือน" , 10 => "10 เดือน"]
            ),
            'bay_max_months_setting' => array(
                'title' => "BAY ผ่อนสูงสุด",
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'ten',
                'desc_tip' => true,
                'options' => [3 => "3 เดือน" , 4 => "4 เดือน" , 6 => "6 เดือน" , 9 => "9 เดือน" , 10 => "10 เดือน"]
            ),
            'fcy_max_months_setting' => array(
                'title' => "FCY ผ่อนสูงสุด",
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'ten',
                'desc_tip' => true,
                'options' => [3 => "3 เดือน" , 4 => "4 เดือน" , 6 => "6 เดือน" , 9 => "9 เดือน" , 10 => "10 เดือน", 12 => "12 เดือน (เฉพาะผู้ถือบัตรรับผิดชอบดอกเบี้ยรายเดือน)", 18 => "18 เดือน (เฉพาะผู้ถือบัตรรับผิดชอบดอกเบี้ยรายเดือน)", 24 => "24 เดือน (เฉพาะผู้ถือบัตรรับผิดชอบดอกเบี้ยรายเดือน)", 36 => "36 เดือน (เฉพาะผู้ถือบัตรรับผิดชอบดอกเบี้ยรายเดือน)"]
            ),
            'agreement_setting' => array(
                'title' => "เงื่อนไข",
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
                'title' => MONEYSPACE_FORM_FIELD_KTC_ENABLE,
                'type' => 'checkbox',
                'label' => MONEYSPACE_FORM_FIELD_ENABLE_LABEL,
                'default' => 'yes'
            ),
            'bay_enabled' => array(
                'title' => MONEYSPACE_FORM_FIELD_BAY_ENABLE,
                'type' => 'checkbox',
                'label' => MONEYSPACE_FORM_FIELD_ENABLE_LABEL,
                'default' => 'yes'
            ),
            'fcy_enabled' => array(
                'title' => MONEYSPACE_FORM_FIELD_FCY_ENABLE,
                'type' => 'checkbox',
                'label' => MONEYSPACE_FORM_FIELD_ENABLE_LABEL,
                'default' => 'yes'
            ),
        );
    }

    public function thankyou_page()
    {
        if ($this->instructions) {
            echo wp_kses_post(wpautop(wptexturize($this->instructions)));
        }
    }

    public function payment_fields()
    {
        $gateways = WC()->payment_gateways->get_available_payment_gateways();
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
            echo wp_kses_post(wpautop(wptexturize($description)));
        }

        // Add a nonce field to protect form submission.
        // This is required for WordPress Security: Nonce verification.
        wp_nonce_field( 'moneyspace_installment_process_payment', 'moneyspace_installment_nonce' );
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

                bindOutline();
            }

            function BAY(){
                document.getElementById('BAY').style.display = 'block';
                if (document.getElementById('KTC'))
                    document.getElementById('KTC').style.display ='none';
                if (document.getElementById('FCY'))
                    document.getElementById('FCY').style.display ='none';

                bindOutline();
            }

            function FCY(){
                document.getElementById('FCY').style.display = 'block';
                if (document.getElementById('KTC'))
                    document.getElementById('KTC').style.display ='none';
                if (document.getElementById('BAY'))
                    document.getElementById('BAY').style.display ='none';

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
                    <img src="<?php echo esc_url(MONEYSPACE_ROOT_URL . 'includes/images/installment/ktc-logo.png'); ?>" alt="KTC">
                    </label>
                </div>
                <?php if ($ms_fee == "include") { ?>
                <div id="KTC" class="installment">
                    <select name="KTC_permonths" id="permonths" class="form-control">
                        <?php foreach($KTC as $months){ ?>
                            <?php if(round($amount_total / $months,2) >= 300 && $months <= $ktc_max_months_setting){ ?>
                            <option value="<?php echo esc_attr($months); ?>">ผ่อน <?php echo esc_html($months); ?> เดือน ( <?php echo esc_html(number_format($amount_total / $months, 2)); ?> บาท / เดือน )</option>
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
                            <option value="<?php echo esc_attr($months); ?>">ผ่อน <?php echo esc_html($months); ?> เดือน ( <?php echo esc_html(number_format($ex_ktc_bay / $months, 2)); ?> บาท / เดือน )</option>
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
                    <img src="<?php echo esc_url(MONEYSPACE_ROOT_URL . 'includes/images/installment/bay_central_lotus.png'); ?>" alt="BAY">
                    </label>
                </div>
                <?php if ($ms_fee == "include") { ?>
                <div id="BAY" class="installment">
                    <select name="BAY_permonths" id="permonths" class="form-control">
                        <?php foreach($BAY as $months){ ?>
                            <?php if(round($amount_total / $months,2) >= 500 && $months <= $bay_max_months_setting){ ?>
                            <option value="<?php echo esc_attr($months); ?>">ผ่อน <?php echo esc_html($months); ?> เดือน ( <?php echo esc_html(number_format($amount_total / $months, 2)); ?> บาท / เดือน )</option>
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
                            <option value="<?php echo esc_attr($months); ?>">ผ่อน <?php echo esc_html($months); ?> เดือน ( <?php echo esc_html(number_format($ex_ktc_bay / $months, 2)); ?> บาท / เดือน )</option>
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
                    <img src="<?php echo esc_url(MONEYSPACE_ROOT_URL . 'includes/images/installment/fcy-logo.png'); ?>" alt="FCY">
                    </label>
                </div>
                <?php if ($ms_fee == "include") { ?>
                <div id="FCY" class="installment">
                    <select name="FCY_permonths" id="permonths" class="form-control">
                        <?php foreach($FCY as $months){ ?>
                            <?php if(round($amount_total / $months,2) >= 300 && $months <= $fcy_max_months_setting){ ?>
                            <option value="<?php echo esc_attr($months); ?>">ผ่อน <?php echo esc_html($months); ?> เดือน ( <?php echo esc_html(number_format($amount_total / $months, 2)); ?> บาท / เดือน )</option>
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
                            <option value="<?php echo esc_attr($months); ?>">ผ่อน <?php echo esc_html($months); ?> เดือน ( <?php echo esc_html(number_format($ex_fcy / $months, 2)); ?> บาท / เดือน )</option>
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
            <h5 style="color:red; font-size: inherit;"><?php echo esc_html(MONEYSPACE_INS_MESSAGE_WARNING); ?></h5>
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

        // Verify nonce before reading any POSTed form data.
        $valid_nonce = false;
        if ( isset( $_POST['moneyspace_installment_nonce'] ) ) {
            $valid_nonce = wp_verify_nonce( sanitize_key( $_POST['moneyspace_installment_nonce'] ), 'moneyspace_installment_process_payment' );
        }
        // Fallback to WooCommerce checkout nonce if present (classic/blocks checkout).
        if ( ! $valid_nonce && isset( $_POST['woocommerce-process-checkout-nonce'] ) ) {
            $valid_nonce = wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce-process-checkout-nonce'] ) ), 'woocommerce-process_checkout' );
        }

        if ( ! $valid_nonce ) {
            wc_add_notice( __( 'Security check failed. Please try again.', 'money-space' ), 'error' );
            return array(
                'result'   => 'failure',
                'messages' => 'Security check failed',
            );
        }
        
        // Handle WooCommerce Blocks payment data for installments
        $selectbank = '';
        $ktc_permonths = '';
        $bay_permonths = '';
        $fcy_permonths = '';
        $message_card = '';
        
        // Check if payment data comes from WooCommerce Blocks
        // Retrieve array safely without direct $_POST access to satisfy WPCS.
        $payment_data = filter_input( INPUT_POST, 'payment_data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
        if ( is_array( $payment_data ) ) {
            $selectbank   = isset( $payment_data['selectbank'] ) ? sanitize_text_field( $payment_data['selectbank'] ) : '';
            $ktc_permonths = isset( $payment_data['KTC_permonths'] ) ? sanitize_text_field( $payment_data['KTC_permonths'] ) : '';
            $bay_permonths = isset( $payment_data['BAY_permonths'] ) ? sanitize_text_field( $payment_data['BAY_permonths'] ) : '';
            $fcy_permonths = isset( $payment_data['FCY_permonths'] ) ? sanitize_text_field( $payment_data['FCY_permonths'] ) : '';
            $message_card  = isset( $payment_data['message_card'] ) ? sanitize_text_field( $payment_data['message_card'] ) : '';
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $this->logger->error( 'MoneySpace Installment Payment Debug - Using Blocks payment data', [ 'source' => 'moneyspace' ] );
            }
        }
        
        // Fallback to traditional POST fields if Blocks data not available
        if (empty($selectbank)) {
            $selectbank    = isset( $_POST['selectbank'] ) ? sanitize_text_field( wp_unslash( $_POST['selectbank'] ) ) : '';
            $ktc_permonths = isset( $_POST['KTC_permonths'] ) ? sanitize_text_field( wp_unslash( $_POST['KTC_permonths'] ) ) : ( isset( $_POST['ktc_permonths'] ) ? sanitize_text_field( wp_unslash( $_POST['ktc_permonths'] ) ) : '' );
            $bay_permonths = isset( $_POST['BAY_permonths'] ) ? sanitize_text_field( wp_unslash( $_POST['BAY_permonths'] ) ) : ( isset( $_POST['bay_permonths'] ) ? sanitize_text_field( wp_unslash( $_POST['bay_permonths'] ) ) : '' );
            $fcy_permonths = isset( $_POST['FCY_permonths'] ) ? sanitize_text_field( wp_unslash( $_POST['FCY_permonths'] ) ) : ( isset( $_POST['fcy_permonths'] ) ? sanitize_text_field( wp_unslash( $_POST['fcy_permonths'] ) ) : '' );
            $message_card  = isset( $_POST['message_card'] ) ? sanitize_text_field( wp_unslash( $_POST['message_card'] ) ) : '';
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $this->logger->error( 'MoneySpace Installment Payment Debug - Using traditional POST data (installment data extracted safely)', [ 'source' => 'moneyspace' ] );
            }
        }
        
        // Log the extracted installment data
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $this->logger->error( 'MoneySpace Installment Payment Debug - Installment data extracted: ' . json_encode([
                'selectbank' => $selectbank ?: 'EMPTY',
                'ktc_permonths' => $ktc_permonths ?: 'EMPTY',
                'bay_permonths' => $bay_permonths ?: 'EMPTY',
                'fcy_permonths' => $fcy_permonths ?: 'EMPTY',
                'message_card' => $message_card ?: 'EMPTY'
            ]));
        }

        // Handle installment payment data safely
        update_post_meta($order_id, 'MNS_special_instructions_to_merchant', $message_card);

        if (!is_user_logged_in() && !$is_error) {
            wc_add_notice("Please login !", 'error');
            $is_error = true;
        }

        if (strlen($message_card) > 150 && !$is_error) {
            wc_add_notice("Message to the store (150 characters maximum)", 'error');
            $is_error = true;
        }

        if (get_woocommerce_currency() != "THB" && !$is_error) { 
            wc_add_notice(MONEYSPACE_NOTICE_CURRENCY, 'error');
            $is_error = true;
        }

        if($order_amount < 3000.01 && !$is_error) { 
            wc_add_notice("จำนวนยอดเงินต้อง 3,000.01 บาทขึ้นไปถึงจะทำการผ่อนชำระได้", 'error');
            $is_error = true;
        }

        if($selectbank == "" && !$is_error) {
            wc_add_notice("กรุณาเลือกการผ่อนชำระ" . $selectbank, 'error');
            $is_error = true;
        }

        if(!$is_error) {
            delete_post_meta($order_id, 'MNS_transaction');
            delete_post_meta($order_id, 'MNS_QR_URL');
            update_post_meta($order_id, 'MNS_PAYMENT_TYPE', "Installment");
            update_post_meta($order_id, 'MNS_INSTALLMENT_BANK', $selectbank);
            $endterm = "";
            $bankType = $selectbank;
            
            if ($bankType == "KTC"){
                $endterm = $ktc_permonths;
            }

            if ($bankType == "BAY"){
                $endterm = $bay_permonths;
            }

            if ($bankType == "FCY"){
                $endterm = $fcy_permonths;
            }

            update_post_meta($order_id, 'MNS_INSTALLMENT_BANK_TYPE', $bankType);
            update_post_meta($order_id, 'MNS_INSTALLMENT_MONTHS', $endterm);
            return $this->_process_external_payment($order); // go to paymentgateway_form
        } else {
            moneyspace_debug_log('Installment Payment Error: Validation failed', true); // Always log errors
            return array(
                'result' => 'failure',
                'messages' => "Error : Message to the store (150 characters maximum)"
            );
        }
    }

    public function paymentgateway_form($order_id) {

        $payment_gateways = WC_Payment_Gateways::instance();
        
        $payment_gateway = $payment_gateways->payment_gateways()[MONEYSPACE_ID_INSTALLMENT];
        $ms_order_select = $payment_gateway->settings['order_status_if_success'];
        $fee_opt = $payment_gateway->settings['fee_setting'];
        $ms_fee = ($fee_opt === 'customer') ? ('ex'.'clude') : ('inc'.'lude');
        $agreement_setting = $payment_gateway->settings['agreement_setting']; 

        $moneyspace_gw = $payment_gateways->payment_gateways()[MONEYSPACE_ID];
        $ms_secret_id = $moneyspace_gw->settings['secret_id'];
        $ms_secret_key = $moneyspace_gw->settings['secret_key'];
        $ms_time = gmdate("YmdHis");
        $order = wc_get_order($order_id);
        $items = $order->get_items();
        $order_amount = $order->get_total();

        $items_msg = moneyspace_set_item_message($items);
        $return_url = add_query_arg(
            'key',
            $order->get_order_key(),
            trailingslashit(get_site_url()) . 'process/payment/' . $order_id
        );
        // Append a nonce to strengthen process-payment route (optional; backward compatible)
        $return_url = wp_nonce_url( $return_url, 'moneyspace_process_payment', 'ms_nonce' );

        $error_list = array("wc-failed", "wc-cancelled", "wc-refunded");
        if (in_array($ms_order_select, $error_list)) {
            echo esc_html("Error : " . MONEYSPACE_NOTICE_ERROR_CONTINUE);
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
        , "feeType" => $ms_fee
        , "order_id" => $order_id . "MS" . $ms_time
        , "success_Url" => $return_url
        , "fail_Url" => $return_url
        , "cancel_Url" => $return_url
        , "agreement" => $agreement_setting 
        , "bankType" => $bankType
        , "startTerm" => $endTerm
        , "endTerm" => $endTerm);
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $log_body = function_exists('moneyspace_filter_sensitive_data') ? moneyspace_filter_sensitive_data($payment_data) : $payment_data;
            $this->logger->error( 'MoneySpace Installment API: Creating payment transaction for order ' . $order_id, [ 'source' => 'moneyspace' ] );
            $this->logger->error( 'MoneySpace Installment API: Request body: ' . json_encode($log_body), [ 'source' => 'moneyspace' ] );
        }
        
        $response = wp_remote_post(MONEYSPACE_API_URL_CREATE_INSTALLMENT, 
        array(
            'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
            'method' => 'POST',
            'timeout' => 120,
            'body' => json_encode($payment_data)
            )
        );
        
        if (is_wp_error($response)) {
            $log_body = function_exists('moneyspace_filter_sensitive_data') ? moneyspace_filter_sensitive_data($payment_data) : $payment_data;
            (new Mslogs())->insert($response->get_error_message(), 4, 'Create Transaction Installment (HTTP error)', gmdate("Y-m-d H:i:s"), json_encode($log_body));
            wc_add_notice(json_encode($response), 'error');
            return;
        }

        $body = wp_remote_retrieve_body($response);
        $http_code = wp_remote_retrieve_response_code($response);
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $this->logger->error( 'MoneySpace Installment API: HTTP Response Code - ' . $http_code, [ 'source' => 'moneyspace' ] );
            $this->logger->error( 'MoneySpace Installment API: Response Body - ' . $body, [ 'source' => 'moneyspace' ] );
        }
        
        $log_body = function_exists('moneyspace_filter_sensitive_data') ? moneyspace_filter_sensitive_data($payment_data) : $payment_data;
        (new Mslogs())->insert($body, 4, 'Create Transaction Installment', gmdate("Y-m-d H:i:s"), json_encode($log_body));

        $json_tranId_status = json_decode($body);
                                    
        if (empty($json_tranId_status) || (isset($json_tranId_status[0]->status) && ($json_tranId_status[0]->status == "NotFound" 
        || $json_tranId_status[0]->status == "The payment amount must be greater than 3000 baht." 
        || $json_tranId_status[0]->status == "Data Invalid"
        || $json_tranId_status[0]->status == "Payment amount must be less than 10000.00"))) {
            wc_add_notice($json_tranId_status[0]->status ?? 'Error creating installment', 'error');
            return;
        }
        $json_tranId = json_decode($body);
        $tranId = $json_tranId[0]->transaction_ID ?? '';
        $linkPayment = $json_tranId[0]->link_payment ?? '';
        
        // Save the transaction order ID for payment status checking
        update_post_meta($order_id, 'MNS_transaction_orderid', $payment_data['order_id']);
        update_post_meta($order_id, 'MNS_transaction', $tranId);
        
        // Save the payment link for pending payments
        if (!empty($linkPayment)) {
            update_post_meta($order_id, 'MNS_PAYMENT_LINK', $linkPayment);
        }
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $this->logger->error( 'MoneySpace Installment API: Transaction created successfully - ID: ' . $tranId, [ 'source' => 'moneyspace' ] );
            $this->logger->error( 'MoneySpace Installment API: Order ID saved: ' . $payment_data['order_id'], [ 'source' => 'moneyspace' ] );
            $this->logger->error( 'MoneySpace Installment API: Payment link saved: ' . $linkPayment, [ 'source' => 'moneyspace' ] );
        }

        $is_include = ($fee_opt !== 'customer');
        if ($is_include){
            $ex_ktc_bay = $order_amount;
            $ex_fcy = $order_amount;
        } else {
            $ex_ktc_bay = $order_amount / 100 * 0.8 * $endTerm + $order_amount;
            $ex_fcy = $order_amount / 100 * 1 * $endTerm + $order_amount;
        }

        ?>
        <h3 style="text-decoration: underline;">ยืนยันการชำระเงิน</h3>
        <p><h5>จำนวนเดือน : <?php echo esc_html($endTerm . "  เดือน"); ?></h5></p>
        <?php if($bankType == "KTC" || $bankType == "BAY"){ ?>
        <p><h5>จำนวนเงินชำระต่อเดือน : <?php echo esc_html(round($ex_ktc_bay / $endTerm, 2) . get_woocommerce_currency() . " / เดือน"); ?></h5></p>
        <?php }if($bankType == "FCY"){ ?>
        <p><h5>จำนวนเงินชำระต่อเดือน : <?php echo esc_html(round($ex_fcy / $endTerm, 2) . get_woocommerce_currency() . " / เดือน"); ?></h5></p>
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
            <form  id="mainform"  method="post" action="<?php echo esc_url($actionUrl); ?>">
                    <div class="row">
                        <div class="col-12 col-sm-12 col-12 mt-3">
                            <input type="hidden" name="transactionID" value="<?php echo esc_attr($tranId); ?>"/>
                            <input type="hidden" id="pay_type" name="pay_type" value=""/>
                            <input type="hidden" id="locale" name="locale" value=""/>
                             <input type="hidden" id="term" name="term" value="<?php echo esc_attr($endTerm); ?>"/>
                            <input type="hidden" id="payForMonth" name="paymonth" value="<?php echo esc_attr($paymonth); ?>"/>
                            <input type="hidden" id="interest" name="interest" value="0.0"/>
                            <input type="hidden" id="bankType" name="bankType" value="<?php echo esc_attr($bankType); ?>"/>
                        </div>
                     </div>
                 <button class="button" type="submit">ชำระเงิน</button>
            </form>
        </div>
        <?php 
        // Use the modern payment link from API response instead of legacy form submission
        if (!empty($linkPayment)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $this->logger->error( 'MoneySpace Installment: Redirecting to payment link: ' . $linkPayment, [ 'source' => 'moneyspace' ] );
            }
            ?>
            <div style="text-align: center; padding: 20px;">
                <p>กำลังเปลี่ยนเส้นทางไปยังหน้าชำระเงิน...</p>
                <p>Redirecting to payment page...</p>
            </div>
            <script>
                // Use proper URL for JavaScript redirection without HTML entity encoding
                var paymentUrl = <?php echo json_encode($linkPayment); ?>;
                <?php if (defined('WP_DEBUG') && WP_DEBUG): ?>
                console.log('MoneySpace: Redirecting to payment URL:', paymentUrl);
                <?php endif; ?>
                window.location.href = paymentUrl;
            </script>
            <?php
        } else {
            wp_add_inline_script("moneyspace-installment-script","document.getElementById('mainform').submit();");
        }
    }

    public function getHash($data,$key) {
        return hash_hmac('sha256', $data, $key);
    }

    public function getTime() {
        return gmdate("YmdHis");
    }
    
    protected function _process_external_payment($order) {
        // Redirect to payment page, where payment form will be printed
        return array(
            'result' => 'success',
            'redirect' => $order->get_checkout_payment_url(true)
        );
    }


	
}