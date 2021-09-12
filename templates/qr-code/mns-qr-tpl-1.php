<?php
wp_enqueue_script("bootstrap-4", MNS_ROOT_URL ."/includes/libs/bootstrap-4.6.0-dist/js/bootstrap.bundle.min.js", array(), "4.0.0", true);
wp_enqueue_script("vue-3", MNS_ROOT_URL ."/includes/libs/vue@3.0.11/dist/vue.global.js", array(), "3.0.11", true);

wp_enqueue_style("bootstrap-4", MNS_ROOT_URL ."/includes/libs/bootstrap-4.6.0-dist/css/bootstrap.min.css", array(), "4.0.0", "" );
wp_enqueue_style("mns-01", MNS_ROOT_URL ."/includes/themes/mns-01/css/mns-01.css", array(), "1.0.0", "" );

?>
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="card-group justify-content-between">
                <h3><?php esc_html_e(MNS_PAY_QR); ?></h3>
                <div class="d-flex">
                    <img class="mns-title-logo" src="<?php esc_html_e(MNS_ROOT_URL); ?>includes/themes/mns-01/images/logo.png" alt="logo">
                    <div class="mns-title-data">
                        <div class="mns-title-subtitle">powered by </div>
                        <div class="mns-title-title">money space</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form action="" class="container" method="post">
                <div class="row">
                    <div class="col-md-6 text-center">
                        <img class="mx-auto d-block" src="https://www.moneyspace.net/merchantapi/makepayment/showqr?topic=00020101021230830016A000000677010112011501055600681276602180000002105240014900318000020210524193518530376454073001.005802TH5910GBPrimePay6304CBFA&amp;locale=en&amp;successUrl=http%3A%2F%2F127.0.0.1%2Fprocess%2Fpayment%2F130&amp;transactionId=MSTRF18000000193518&amp;amount=3001.00&amp;description=Belt++3001%E0%B8%BF+%28+1+qty+%29+&amp;channel=0&amp;currency=THB&amp;note=&amp;type=linkpaymentcard&amp;firstname=Ekapop&amp;lastname=Boonrote&amp;phone=0636695193&amp;address=Pruksa+64%2F2+House+No.+51%2F45+Village+No.+5+Khlong+sam++Khlong+luang+12120&amp;email=bus.ekapop%40gmail.com&amp;message=&amp;payment_id=193518&amp;gateway=qrnone&amp;lang=E&amp;user_merchant=5853&amp;agreement=1&amp;fee=include&amp;bblFormActionUrl=https%3A%2F%2Fipay.bangkokbank.com%2Fb2c%2Feng%2FdPayment%2FpayComp.jsp&amp;bahtCurrencyCode=764&amp;store_name=Peepo&amp;store_logo=&amp;orderRef5=TEST+BUS" alt="QR Code">
                        <a href="https://a.moneyspace.net/Qrbase64?qr_url=https://www.moneyspace.net/merchantapi/makepayment/showqr?topic=00020101021230830016A000000677010112011501055600681276602180000002105240015360318000020210524193520530376454073001.005802TH5910GBPrimePay6304AFBA&amp;locale=en&amp;successUrl=http%3A%2F%2F127.0.0.1%2Fprocess%2Fpayment%2F130&amp;transactionId=MSTRF18000000193520&amp;amount=3001.00&amp;description=Belt++3001%E0%B8%BF+%28+1+qty+%29+&amp;channel=0&amp;currency=THB&amp;note=&amp;type=linkpaymentcard&amp;firstname=Ekapop&amp;lastname=Boonrote&amp;phone=0636695193&amp;address=Pruksa+64%2F2+House+No.+51%2F45+Village+No.+5+Khlong+sam++Khlong+luang+12120&amp;email=bus.ekapop%40gmail.com&amp;message=&amp;payment_id=193520&amp;gateway=qrnone&amp;lang=E&amp;user_merchant=5853&amp;agreement=1&amp;fee=include&amp;bblFormActionUrl=https%3A%2F%2Fipay.bangkokbank.com%2Fb2c%2Feng%2FdPayment%2FpayComp.jsp&amp;bahtCurrencyCode=764&amp;store_name=Peepo&amp;store_logo=&amp;orderRef5=TEST+BUS" download="QRCODE.jpg" class="jss22"><b class="jss25">(ดาวน์โหลด QR CODE)</b></a>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex">
                                <img src="https://a.moneyspace.net/static/img/qr/openapp.png" class="mns-icon" alt="">
                                เปิดแอปพลิเคชั่นธนาคารบนมือถือ
                            </li>
                            <li class="list-group-item d-flex">
                                <img src="https://a.moneyspace.net/static/img/qr/bill.png" class="mns-icon" alt="">
                                เลือกเมนู "จ่ายบิล"
                            </li>
                            <li class="list-group-item d-flex">
                                <img src="https://a.moneyspace.net/static/img/qr/scan.png" class="mns-icon" alt="">
                                สแกน QR CODE ในหน้านี้
                            </li>
                            <li class="list-group-item d-flex">
                                <img src="https://a.moneyspace.net/static/img/qr/success.png" class="mns-icon" alt="">
                                ชำระเงินสำเร็จ
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="row">
                    
                </div>
            </form>
        </div>
    </div>
</div>