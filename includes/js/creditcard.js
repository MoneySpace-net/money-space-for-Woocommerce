"use strict";

var checkPaymentMethodCC = false;
if (document.getElementById('payment_method_moneyspace') !== null) {
    checkPaymentMethodCC = document.getElementById('payment_method_moneyspace').checked;
}

var appCreditCard = new Vue({
    el: '#credit-card-form',
    data: {
        cardNumber: "",
        cardHolder: "",
        expDate: "",
        expDateYear: "",
        cardCVV: "",
        cardYear: "",
        minCardYear: new Date().getFullYear()
    },
    methods: {
        checkCardName: function(event) {
            if (!/^[a-zA-Z\s]+$/.test(event.key)) {
                return event.preventDefault();
            }
        },
        checkCardNumber: function(event) {
            if (!/^[0-9]*$/.test(event.key)) {
                return event.preventDefault();
            }

            if (this.cardNumber.replaceAll(" ", "").length > 16) {
                return event.preventDefault();
            }
        },
        checkCVV: function(event) {
            if (!/^[0-9]*$/.test(event.key)) {
                return event.preventDefault();
            }

            if (this.cardCVV.replaceAll(" ", "").length > 2) {
                return event.preventDefault();
            }
        },
        cc_format: function(value) {
            var v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '')
            var matches = v.match(/\d{4,16}/g);
            var match = matches && matches[0] || ''
            var parts = []
        
            for (var i=0, len=match.length; i<len; i+=4) {
                parts.push(match.substring(i, i+4))
            }
        
            if (parts.length) {
                return parts.join(' ')
            } else {
                return value
            }
        },
        validateCardNumber: function() {
            return this.cardNumber.trim().length == 0 && checkPaymentMethodCC ? true: false;
        },
        validateCardHolder: function() {
            return this.cardHolder.trim().length == 0 && checkPaymentMethodCC ? true: false;
        },
        validateCardExpDate: function() {
            return this.expDate.length == 0 && checkPaymentMethodCC ? true: false;
        },
        validateCardExpYear: function() {
            return this.expDateYear.length == 0 && checkPaymentMethodCC ? true: false;
        },
        validateCardCVV: function() {
            return this.cardCVV.length == 0 && checkPaymentMethodCC ? true: false;
        },
    },
    computed: {
        minCardMonth () {
            if (this.cardYear === this.minCardYear) return new Date().getMonth() + 1;
            return 1;
        },
        mspay_message () {
            if (this.cardNumber.replaceAll(" ", "").length === 16
                && (this.cardHolder.trim().length > 0)
                && (this.expDateYear > 0)
                && (this.expDate > 0)
                && (this.cardCVV > 0)) {

                // return Moneyspace_util.addEncryptedData(this.cardHolder.trim(), this.cardNumber, this.expDate, this.expDateYear, this.cardCVV, t);
            }

            return;
        },
        
    },
    watch: {
        cardNumber: function(value) {
            this.cardNumber = this.cc_format(value);
        },
        cardHolder: function(value) {
            this.cardHolder = value.toUpperCase();
        }
    }
})

document.getElementsByName('payment_method').forEach(element => { 
    element.addEventListener('change', function() {
        if (this.checked && this.value == 'moneyspace') {
            checkPaymentMethodCC = this.checked;
        } else {
            checkPaymentMethodCC = false;
        }

        appCreditCard.$forceUpdate();
    });
});
// function addEncryptedData() {
//     const encrypted = CryptoJS.AES.encrypt(
//       JSON.stringify(
//         `${cardHolderNameValue}|${cardNumberValue}|${month}|${year}|${cvv}`
//       ),
//       String(t),
//       { format: CryptoJSAesJson }
//     ).toString();
//     setFormMsPay(encrypted);
// };

// function ccFormat(value){
//     const v = value.replace(/\s+/g, "").replace(/[^0-9]/gi, "");
//     const matches = v.match(/\d{4,16}/g);
//     const match = (matches && matches[0]) || "";
//     const parts = [];
//     let i;
//     for (i = 0; i < match.length; i += 4) {
//       parts.push(match.substring(i, i + 4));
//     }
//     if (parts.length) {
//       return parts.join(" ");
//     }
//     return value;
// };

// function checkCardName(event) {
//     if (event.target.value === "" || /^[a-zA-Z\s]+$/.test(event.target.value)) {
//       setCardHolderNameValue(event.target.value.toUpperCase());
//       setErrCardHolderName(false);
//     } else {
//       setErrCardHolderName(true);
//     }
//   };

// function checkCardNumber(event)  {
//     if (event.target.value === "" || /^[0-9 ]+$/.test(event.target.value)) {
//       setCardNumberValue(ccFormat(event.target.value));
//       setErrCardNumber(false);
//     } else {
//       setErrCardNumber(true);
//     }
//   };

// function checkMonth(event)  {
//     if (event.target.value === "" || /^[0-9\b]+$/.test(event.target.value)) {
//       setMonth(event.target.value);
//       setErrMonth(false);
//     } else {
//       setErrMonth(true);
//     }
//   };

// function checkYear(event)  {
//     if (event.target.value === "" || /^[0-9\b]+$/.test(event.target.value)) {
//       setYear(event.target.value);
//       setErrYear(false);
//     } else {
//       setErrYear(true);
//     }
//   };

// function checkCvv(event)  {
//     if (event.target.value === "" || /^[0-9\b]+$/.test(event.target.value)) {
//       setCvv(event.target.value);
//       setErrCvv(false);
//     } else {
//       setErrCvv(true);
//     }
//   };