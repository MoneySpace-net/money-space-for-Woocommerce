<?php

define('MONEYSPACE_DESCRIPTION', 'Integrates WooCommerce with MoneySpace Payment Gateway module. ');
define('MONEYSPACE_DESCRIPTION_QR', '
	QR Code PromptPay   คือ  วิธีการชำระเงินรูปแบบใหม่ เพิ่มความสะดวกสบายให้กับลูกค้า เพียงสแกน QR Code และชำระเงินค่าสินค้า  / บริการ  ด้วยแอพพลิเคชั่น Mobile Banking ของลูกค้า

ธนาคารที่ให้บริการชำระเงินผ่าน QR Payment บน Mobile Banking  คือ
- ธนาคารไทยพาณิชย์ (SCB)
- ธนาคารกสิกรไทย (KBANK)
- ธนาคารกรุงไทย (KTB)
- ธนาคารกรุงเทพ (BBL)
- ธนาคารกรุงศรีอยุธยา (BAY)
- ธนาคารทหารไทย (TMB)
- ธนาคารธนชาติ (TBANK)
- ธนาคารออมสิน (GSB)

เงื่อนไขการให้บริการชำระผ่าน QR Code PromptPay 

- เมื่อ QR Payment ถูกสร้างแล้ว ลูกค้าจะมีเวลา 15 (หรือ 45 นาที) ในการชำระเงิน
- ไม่สามารถชำระเงินค่าเปลี่ยนแปลงรายละเอียด สินค้า หรือ บริการอื่นๆ เพิ่มภายหลังด้วย QR Payment ได้
- ลูกค้าจะต้องเปิดบัญชีและสมัครบริการ Mobile Banking กับธนาคารที่ให้บริการ QR Payment แล้วเท่านั้น
- รองรับเฉพาะรายการสกลุเงินไทยบาท');
define('MONEYSPACE_TOTAL_PAYMENT_ORDER', 'ยอดสุทธิที่ต้องชำระ');
define('MONEYSPACE_VAT_PAYMENT_ORDER', 'ภาษีมูลค่าเพิ่ม');
define('MONEYSPACE_THANK_PAYMENT_ORDER_1', 'ขอบคุณที่เลือกใช้บริการผ่านมันนี่ สเปซ');
define('MONEYSPACE_THANK_PAYMENT_ORDER_2', 'Moneysp* แสดงบนใบแจ้งยอดบัญชีธนาคารของคุณในฐานะผู้รับเงิน');
define('MONEYSPACE_TRANSACTION_PAYMENT_ORDER', 'Transaction');
define('MONEYSPACE_NOTICE_CURRENCY', 'ชำระเงินเฉพาะสกุล THB เท่านั้น');
define('MONEYSPACE_NOTICE_CHECK_TRANSACTION', 'เกิดข้อผิดพลาดกรุณากดชำระเงินอีกครั้ง');
define('MONEYSPACE_NOTICE_ERROR_SETUP', 'ไม่สามารถชำระเงินได้ กรุณาตั้งค่าระบบใหม่อีกครั้ง');
define('MONEYSPACE_NOTICE_ERROR_PAYMENT', 'ไม่สามารถชำระเงินได้');
define('MONEYSPACE_NOTICE_ERROR_CONTINUE', 'การตั้งค่าของท่านไม่สามารถดำเนินการต่อได้ กรุณาตั้งค่าระบบใหม่อีกครั้ง');
define('MONEYSPACE_ORDER_PENDING', 'อยู่ระหว่างดำเนินการ');
define('MONEYSPACE_FORM_FIELD_HEADER_SETTING', 'ตั้งค่า');
define('MONEYSPACE_FORM_FIELD_ENABLE', 'เปิดใช้งาน / ปิดการใช้งาน');
define('MONEYSPACE_FORM_FIELD_ENABLE_LABEL', 'เปิดใช้งาน');
define('MONEYSPACE_FORM_FIELD_DESCRIPTION', 'รายละเอียด');
define('MONEYSPACE_FORM_FIELD_HEADER_SETTING_MS', 'Moneyspace');
define('MONEYSPACE_FORM_FIELD_MS_VAT', 'ภาษีมูลค่าเพิ่ม');
define('MONEYSPACE_FORM_FIELD_SET_ORDER_STATUS', 'เลือกสถานะเมื่อชำระเงินสำเร็จ');
define('MONEYSPACE_FORM_FIELD_TEMPLATE', 'เลือกรูปแบบ');
define('MONEYSPACE_FORM_FIELD_SET_QRNONE_TIMEOUT', 'ตั้งเวลาหมดอายุ');
define('MONEYSPACE_FORM_FIELD_ENABLE_AUTO_CHECK_QR', 'เปิดการเช็ค QR Code อัตโนมัติ');
define('MONEYSPACE_FORM_FIELD_HEADER_SETTING_UI', 'กรอกข้อมูลผู้ซื้อในรหัสการทำธุรกรรม');
define('MONEYSPACE_FORM_FIELD_MS_FIRSTNAME', 'ชื่อจริง');
define('MONEYSPACE_FORM_FIELD_MS_LASTNAME', 'นามสกุล');
define('MONEYSPACE_FORM_FIELD_MS_EMAIL', 'อีเมลล์เพื่อรับใบเสร็จจากทางร้าน');
define('MONEYSPACE_FORM_FIELD_MS_PHONE', 'เบอร์โทรศัพท์');
define('MONEYSPACE_FORM_FIELD_MS_ADDRESS', 'ที่อยู่');
define('MONEYSPACE_FORM_FIELD_MS_CUSTOMER', 'ลูกค้า');
define('MONEYSPACE_FORM_FIELD_MS_LABEL', 'กรอก');
define('MONEYSPACE_FORM_FIELD_KTC_ENABLE', 'KTC เปิดใช้งาน / ปิดการใช้งาน');
define('MONEYSPACE_FORM_FIELD_BAY_ENABLE', 'BAY เปิดใช้งาน / ปิดการใช้งาน');
define('MONEYSPACE_FORM_FIELD_FCY_ENABLE', 'FCY เปิดใช้งาน / ปิดการใช้งาน');
define('MONEYSPACE_FORM_FIELD_HEADER_SETTING_CHECK_CONNECTION', 'ทดสอบเชื่อมต่อ moneyspace gateway');

define('MONEYSPACE_YOUR_DOMAIN', 'โดเมนของท่าน');
define('MONEYSPACE_YOUR_WEBHOOK', 'Webhook ของท่าน');
define('MONEYSPACE_HEAD_DOMAIN_WEBHOOK', 'โดเมน และ Webhook ของท่าน');
define('MONEYSPACE_DOMAIN_WEBHOOK', 'กรุณานำ โดเมน และ Webhook ไปสร้าง secret_id , secret_key ที่เมนู Webhook ของ Money space หลังเข้าสู่ระบบ');

define('MONEYSPACE_PAYMENT_TYPE', 'ประเภทการชำระเงิน');
define('MONEYSPACE_TYPE_PAYMENT_CARD', 'ชำระเงินผ่านบัตรเครดิต');
define('MONEYSPACE_TYPE_PAYMENT_QR', '  คิวอาร์ โค้ด พร้อมเพย์');
define('MONEYSPACE_TYPE_PAYMENT_INSTALLMENT', 'ผ่อนชำระรายเดือน');

define('MONEYSPACE_SETTING_LINK', 'ตั้งค่า');

define('MONEYSPACE_FEE_HEADER', 'ผู้รับผิดชอบค่าธรรมเนียม');
define('MONEYSPACE_FEE_INCLUDE', 'ร้านค้ารับผิดชอบค่าธรรมเนียมบัตรเครดิต /เดบิต');
define('MONEYSPACE_FEE_EXCLUDE', 'ผู้ซื้อรับผิดชอบค่าธรรมเนียมบัตรเครดิต /เดบิต');

define('MONEYSPACE_MESSAGE2STORE_HEADER', 'กล่องข้อความถึงร้านค้า');
define('MONEYSPACE_MESSAGE2STORE', '(ไม่บังคับ) ฝากข้อความถึงผู้ขาย');
define('MONEYSPACE_MESSAGE', 'หมายเหตุ');

define('MONEYSPACE_STOCKSETTING_HEAD', 'ตั้งค่าตัดสต็อก');
define('MONEYSPACE_STOCKSETTING_ENABLE', 'เปิด');
define('MONEYSPACE_STOCKSETTING_DISABLE', 'ปิด');

define('MONEYSPACE_QR_SCAN', 'กรุณาชำระเงินด้วยการสแกน QR CODE ด้านล่างผ่าน MOBILE BANKING หลังจากสแกนให้รอสักครู่ด้วยครับ หรือ กดปุ่มยืนยันการชำระเงิน');
define('MONEYSPACE_QR_SCANSUCCESS', 'ชำระเงินเรียบร้อยแล้ว กำลังเปลี่ยนหน้าเว็บ...');
define('MONEYSPACE_QR_CONFIRM', 'ยืนยันการชำระเงิน');
define('MONEYSPACE_QR_NEWCREATE', 'สร้าง QR Code ใหม่');
define('MONEYSPACE_NOTICE_ERROR_LOAD_QR', 'ไม่สามารถโหลด QR CODE ได้');

define('MONEYSPACE_PAY_INS', 'ผ่อนชำระรายเดือน');
define('MONEYSPACE_PAY_QR', 'จ่ายบิลด้วยคิวอาร์โค้ด');

define('MONEYSPACE_INS_MESSAGE_WARNING', 'จำนวนยอดเงินต้อง 3,000.01 บาทขึ้นไปถึงจะทำการผ่อนชำระได้');

define('MONEYSPACE_ADMIN_SETTING_CC_DESC', 'รายละเอียดการจ่ายเครดิตการ์ด');
define('MONEYSPACE_ADMIN_SETTING_STATUS_AFTER_PAY', "สถานะหลังชำระเงิน <br>'Pending payment', <br>'Processing', <br>'On hold', <br>'Completed', <br>'Cancelled', <br>'Refunded', <br>'Failed'");

define('MONEYSPACE_CC_NAME', 'ชื่อผู้ถือบัตร');
define('MONEYSPACE_CC_NO', 'หมายเลขบัตรเครดิต');
define('MONEYSPACE_CC_EXP', 'วันหมดอายุ');
define('MONEYSPACE_CC_EXP_MONTH', 'เดือนหมดอายุ');
define('MONEYSPACE_CC_EXP_YEAR', 'ปีหมดอายุ');
define('MONEYSPACE_CC_CVV', 'รหัสป้องกัน');
define('MONEYSPACE_CC_REMEMBER', 'บันทึกข้อมูลบัตรสำหรับการสั่งซื้อรอบถัดไป');
define('MONEYSPACE_MONTH', 'เดือน');
define('MONEYSPACE_YEAR', 'ปี');
define('MONEYSPACE_BAHT', 'บาท');
define('MONEYSPACE_INS', 'ผ่อน');

define('MONEYSPACE_CC_WARN_CC_NO_1', 'กรุณากรอกหมายเลขบัตรเครดิต');
define('MONEYSPACE_CC_WARN_CC_NO_2', 'กรุณาตรวจสอบเลขบัตรเครดิต');
define('MONEYSPACE_CC_WARN_CC_NAME', 'กรุณากรอกชื่อผู้ถือบัตร');
define('MONEYSPACE_CC_WARN_CC_EXP_MONTH', 'กรุณากรอกเดือนหมดอายุ');
define('MONEYSPACE_CC_WARN_CC_EXP_YEAR', 'กรุณากรอกปีหมดอายุ');
define('MONEYSPACE_CC_WARN_CVV_1', 'กรุณากรอกรหัสป้องกัน');
define('MONEYSPACE_CC_WARN_CVV_2', 'กรุณาตรวจสอบรหัสป้องกัน');

define('MONEYSPACE_CC_INS_TITLE', 'เลือกการผ่อนชำระ');
define('MONEYSPACE_CC_INS_MONTH', 'จำนวนเดือนผ่อนชำระ');

define('MONEYSPACE_CC_INS_KTC', 'บัตรเคทีซี');
define('MONEYSPACE_CC_INS_BAY', 'บัตรกรุงศรีฯ วีซ่า , บัตรเซ็นทรัล , บัตรเทสโก้โลตัส');
define('MONEYSPACE_CC_INS_FCY', 'บัตรกรุงศรีเฟิร์สช้อยส์ , บัตรโฮมโปร , บัตรเมกาโฮม');

define('MONEYSPACE_ORDER_STATUS_COMPLETED', 'ชำระเงินเรียบร้อยแล้ว');

define('MONEYSPACE_FORM_FIELD_AUTO_CHECK_QR_RESULT_TIME', 'ตั้งค่าเวลาเช็คผล QR Code อัตโนมัติ');
define('MONEYSPACE_FORM_FIELD_SECONDS', 'วินาที');