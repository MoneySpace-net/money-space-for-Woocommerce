<?php

define('MS_DESCRIPTION', 'Integrates WooCommerce with MoneySpace Payment Gateway module. ');
define('MS_DESCRIPTION_QR', '
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
define('MS_TOTAL_PAYMENT_ORDER', 'ยอดสุทธิที่ต้องชำระ');
define('MS_VAT_PAYMENT_ORDER', 'ภาษีมูลค่าเพิ่ม');
define('MS_THANK_PAYMENT_ORDER_1', 'ขอบคุณที่เลือกใช้บริการผ่านมันนี่ สเปซ');
define('MS_THANK_PAYMENT_ORDER_2', 'Moneysp* แสดงบนใบแจ้งยอดบัญชีธนาคารของคุณในฐานะผู้รับเงิน');
define('MS_TRANSACTION_PAYMENT_ORDER', 'Transaction');
define('MS_NOTICE_CURRENCY', 'ชำระเงินเฉพาะสกุล THB เท่านั้น');
define('MS_NOTICE_CHECK_TRANSACTION', 'เกิดข้อผิดพลาดกรุณากดชำระเงินอีกครั้ง');
define('MS_NOTICE_ERROR_SETUP', 'ไม่สามารถชำระเงินได้ กรุณาตั้งค่าระบบใหม่อีกครั้ง');
define('MS_NOTICE_ERROR_PAYMENT', 'ไม่สามารถชำระเงินได้');
define('MS_NOTICE_ERROR_CONTINUE', 'การตั้งค่าของท่านไม่สามารถดำเนินการต่อได้ กรุณาตั้งค่าระบบใหม่อีกครั้ง');
define('MS_ORDER_PENDING', 'อยู่ระหว่างดำเนินการ');
define('MS_FORM_FIELD_HEADER_SETTING', 'ตั้งค่า');
define('MS_FORM_FIELD_ENABLE', 'เปิดใช้งาน / ปิดการใช้งาน');
define('MS_FORM_FIELD_ENABLE_LABEL', 'เปิดใช้งาน');
define('MS_FORM_FIELD_DESCRIPTION', 'รายละเอียด');
define('MS_FORM_FIELD_HEADER_SETTING_MS', 'Moneyspace');
define('MS_FORM_FIELD_MS_VAT', 'ภาษีมูลค่าเพิ่ม');
define('MS_FORM_FIELD_SET_ORDER_STATUS', 'เลือกสถานะเมื่อชำระเงินสำเร็จ');
define('MS_FORM_FIELD_HEADER_SETTING_UI', 'กรอกข้อมูลผู้ซื้อในรหัสการทำธุรกรรม');
define('MS_FORM_FIELD_MS_FIRSTNAME', 'ชื่อจริง');
define('MS_FORM_FIELD_MS_LASTNAME', 'นามสกุล');
define('MS_FORM_FIELD_MS_EMAIL', 'อีเมลล์เพื่อรับใบเสร็จจากทางร้าน');
define('MS_FORM_FIELD_MS_PHONE', 'เบอร์โทรศัพท์');
define('MS_FORM_FIELD_MS_ADDRESS', 'ที่อยู่');
define('MS_FORM_FIELD_MS_CUSTOMER', 'ลูกค้า');
define('MS_FORM_FIELD_MS_LABEL', 'กรอก');

define('YOUR_DOMAIN', 'โดเมนของท่าน');
define('YOUR_WEBHOOK', 'Webhook ของท่าน');
define('HEAD_DOMAIN_WEBHOOK', 'โดเมน และ Webhook ของท่าน');
define('DOMAIN_WEBHOOK', 'กรุณานำ โดเมน และ Webhook ไปสร้าง secret_id , secret_key ที่เมนู Webhook ของ Money space หลังเข้าสู่ระบบ');

define('PAYMENT_TYPE', 'ประเภทการชำระเงิน');
define('TYPE_PAYMENT_CARD', 'ชำระเงินผ่านบัตรเครดิต');
define('TYPE_PAYMENT_QR', '  คิวอาร์ โค้ด พร้อมเพย์');

define('SETTING_LINK', 'ตั้งค่า');

define('FEE_HEADER', 'ผู้รับผิดชอบค่าธรรมเนียม');
define('FEE_INCLUDE', 'ร้านค้ารับผิดชอบค่าธรรมเนียมบัตรเครดิต /เดบิต');
define('FEE_EXCLUDE', 'ผู้ซื้อรับผิดชอบค่าธรรมเนียมบัตรเครดิต /เดบิต');

define('MESSAGE2STORE_HEADER', 'กล่องข้อความถึงร้านค้า');
define('MESSAGE2STORE', 'ข้อความถึงร้านค้า');

define('STOCKSETTING_HEAD', 'ตั้งค่าตัดสต็อก');
define('STOCKSETTING_ENABLE', 'เปิด');
define('STOCKSETTING_DISABLE', 'ปิด');

define('QR_SCAN', 'กรุณาชำระเงินด้วยการสแกน QR CODE ด้านล่างผ่าน MOBILE BANKING หลังจากสแกนให้รอสักครู่ด้วยครับ หรือ กดปุ่มยืนยันการชำระเงิน');
define('QR_SCANSUCCESS', 'ชำระเงินเรียบร้อยแล้ว กำลังเปลี่ยนหน้าเว็บ...');
define('QR_CONFIRM', 'ยืนยันการชำระเงิน');
define('QR_NEWCREATE', 'สร้าง QR Code ใหม่');
