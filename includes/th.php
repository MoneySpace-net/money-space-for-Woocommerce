<?php

define('MNS_DESCRIPTION', 'Integrates WooCommerce with MoneySpace Payment Gateway module. ');
define('MNS_DESCRIPTION_QR', '
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
define('MNS_TOTAL_PAYMENT_ORDER', 'ยอดสุทธิที่ต้องชำระ');
define('MNS_VAT_PAYMENT_ORDER', 'ภาษีมูลค่าเพิ่ม');
define('MNS_THANK_PAYMENT_ORDER_1', 'ขอบคุณที่เลือกใช้บริการผ่านมันนี่ สเปซ');
define('MNS_THANK_PAYMENT_ORDER_2', 'Moneysp* แสดงบนใบแจ้งยอดบัญชีธนาคารของคุณในฐานะผู้รับเงิน');
define('MNS_TRANSACTION_PAYMENT_ORDER', 'Transaction');
define('MNS_NOTICE_CURRENCY', 'ชำระเงินเฉพาะสกุล THB เท่านั้น');
define('MNS_NOTICE_CHECK_TRANSACTION', 'เกิดข้อผิดพลาดกรุณากดชำระเงินอีกครั้ง');
define('MNS_NOTICE_ERROR_SETUP', 'ไม่สามารถชำระเงินได้ กรุณาตั้งค่าระบบใหม่อีกครั้ง');
define('MNS_NOTICE_ERROR_PAYMENT', 'ไม่สามารถชำระเงินได้');
define('MNS_NOTICE_ERROR_CONTINUE', 'การตั้งค่าของท่านไม่สามารถดำเนินการต่อได้ กรุณาตั้งค่าระบบใหม่อีกครั้ง');
define('MNS_ORDER_PENDING', 'อยู่ระหว่างดำเนินการ');
define('MNS_FORM_FIELD_HEADER_SETTING', 'ตั้งค่า');
define('MNS_FORM_FIELD_ENABLE', 'เปิดใช้งาน / ปิดการใช้งาน');
define('MNS_FORM_FIELD_ENABLE_LABEL', 'เปิดใช้งาน');
define('MNS_FORM_FIELD_DESCRIPTION', 'รายละเอียด');
define('MNS_FORM_FIELD_HEADER_SETTING_MS', 'Moneyspace');
define('MNS_FORM_FIELD_MS_VAT', 'ภาษีมูลค่าเพิ่ม');
define('MNS_FORM_FIELD_SET_ORDER_STATUS', 'เลือกสถานะเมื่อชำระเงินสำเร็จ');
define('MNS_FORM_FIELD_HEADER_SETTING_UI', 'กรอกข้อมูลผู้ซื้อในรหัสการทำธุรกรรม');
define('MNS_FORM_FIELD_MS_FIRSTNAME', 'ชื่อจริง');
define('MNS_FORM_FIELD_MS_LASTNAME', 'นามสกุล');
define('MNS_FORM_FIELD_MS_EMAIL', 'อีเมลล์เพื่อรับใบเสร็จจากทางร้าน');
define('MNS_FORM_FIELD_MS_PHONE', 'เบอร์โทรศัพท์');
define('MNS_FORM_FIELD_MS_ADDRESS', 'ที่อยู่');
define('MNS_FORM_FIELD_MS_CUSTOMER', 'ลูกค้า');
define('MNS_FORM_FIELD_MS_LABEL', 'กรอก');

define('MNS_YOUR_DOMAIN', 'โดเมนของท่าน');
define('MNS_YOUR_WEBHOOK', 'Webhook ของท่าน');
define('MNS_HEAD_DOMAIN_WEBHOOK', 'โดเมน และ Webhook ของท่าน');
define('MNS_DOMAIN_WEBHOOK', 'กรุณานำ โดเมน และ Webhook ไปสร้าง secret_id , secret_key ที่เมนู Webhook ของ Money space หลังเข้าสู่ระบบ');

define('MNS_PAYMENT_TYPE', 'ประเภทการชำระเงิน');
define('MNS_TYPE_PAYMENT_CARD', 'ชำระเงินผ่านบัตรเครดิต');
define('MNS_TYPE_PAYMENT_QR', '  คิวอาร์ โค้ด พร้อมเพย์');
define('MNS_TYPE_PAYMENT_INSTALLMENT', 'ผ่อนชำระรายเดือน');

define('MNS_SETTING_LINK', 'ตั้งค่า');

define('MNS_FEE_HEADER', 'ผู้รับผิดชอบค่าธรรมเนียม');
define('MNS_FEE_INCLUDE', 'ร้านค้ารับผิดชอบค่าธรรมเนียมบัตรเครดิต /เดบิต');
define('MNS_FEE_EXCLUDE', 'ผู้ซื้อรับผิดชอบค่าธรรมเนียมบัตรเครดิต /เดบิต');

define('MNS_MESSAGE2STORE_HEADER', 'กล่องข้อความถึงร้านค้า');
define('MNS_MESSAGE2STORE', '(ไม่บังคับ) ฝากข้อความถึงผู้ขาย');
define('MNS_MESSAGE', 'หมายเหตุ');

define('MNS_STOCKSETTING_HEAD', 'ตั้งค่าตัดสต็อก');
define('MNS_STOCKSETTING_ENABLE', 'เปิด');
define('MNS_STOCKSETTING_DISABLE', 'ปิด');

define('MNS_QR_SCAN', 'กรุณาชำระเงินด้วยการสแกน QR CODE ด้านล่างผ่าน MOBILE BANKING หลังจากสแกนให้รอสักครู่ด้วยครับ หรือ กดปุ่มยืนยันการชำระเงิน');
define('MNS_QR_SCANSUCCESS', 'ชำระเงินเรียบร้อยแล้ว กำลังเปลี่ยนหน้าเว็บ...');
define('MNS_QR_CONFIRM', 'ยืนยันการชำระเงิน');
define('MNS_QR_NEWCREATE', 'สร้าง QR Code ใหม่');

define('MNS_PAY_INS', 'ผ่อนชำระรายเดือน');
define('MNS_PAY_QR', 'จ่ายบิลด้วยคิวอาร์โค้ด');
