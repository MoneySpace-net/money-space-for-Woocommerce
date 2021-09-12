<?php
// wp_enqueue_script("bootstrap-4", MNS_ROOT_URL ."/includes/libs/bootstrap-4.6.0-dist/js/bootstrap.bundle.min.js", array(), "4.0.0", true);
// wp_enqueue_style("bootstrap-4", MNS_ROOT_URL ."/includes/libs/bootstrap-4.6.0-dist/css/bootstrap.min.css", array(), "4.0.0", "" );
// wp_enqueue_style("mns-01", MNS_ROOT_URL ."/includes/themes/mns-01/css/mns-01.css", array(), "1.0.0", "" );

?>
<div class="container" id="installment-form">
    <div class="card">
        <div class="card-body">
            <div>
                <input type="hidden" id="mspay" name="mspay">
                <div class="form-group">
                    <input type="radio" name="bankType" id="bankTypeKTC" value="KTC">
                    <label for="bankTypeKTC">บัตรเคทีซี (KTC)</label>
                    <img alt="" src="<?php esc_html_e(MNS_ROOT_URL .'/includes/images/installment/KTC0.png'); ?>">
                </div>
                <div class="form-group">
                    <input type="radio" name="bankType" id="bankTypeBAY" value="BAY">
                    <label for="bankTypeBAY">บัตรกรุงศรีฯ วีซ่า , บัตรเซ็นทรัล , บัตรเทสโก้โลตัส</label>
                    <img alt=""  src="<?php esc_html_e(MNS_ROOT_URL .'includes/images/installment/BAY33050.png'); ?>">
                </div>
                <div class="form-group">
                    <input type="radio" name="bankType" id="bankTypeFCY" value="FCY">
                    <label for="bankTypeFCY">บัตรกรุงศรีเฟิร์สช้อยส์ , บัตรโฮมโปร , บัตรเมกาโฮม</label>
                    <img alt="" src="<?php esc_html_e(MNS_ROOT_URL .'includes/images/installment/FCY.png'); ?>">
                    
                </div>
            </div>
        </div>
    </div>
</div>
<script id="bootstrap-4-js" src="<?php esc_html_e(MNS_ROOT_URL ."includes/libs/bootstrap-4.6.0-dist/js/bootstrap.bundle.min.js"); ?>"></script>
<script id="vue-2-js" src="<?php esc_html_e(MNS_ROOT_URL ."includes/libs/vue@2/dist/vue.js"); ?>"></script>
<script id="moneyspace-util-js" src="<?php esc_html_e(MNS_ROOT_URL ."includes/libs/moneyspace/moneyspace_util.js"); ?>"></script>
<script id="installment-js" src="<?php esc_html_e(MNS_ROOT_URL ."includes/js/installment.js"); ?>"></script>