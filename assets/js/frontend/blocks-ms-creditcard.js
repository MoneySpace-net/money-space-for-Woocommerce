(()=>{"use strict";const e=window.React,t=window.wc.wcSettings,a=window.wp.htmlEntities,c=window.wc.wcBlocksRegistry,o=window.wp.element,r=window.wp.i18n,n="moneyspace",l=(0,t.getSetting)(`${n}_data`,{}),i=(0,a.decodeEntities)(l.title),d={name:n,label:(0,e.createElement)((({components:t,title:a,icons:c,id:o})=>{Array.isArray(c)||(c=[c]);const{PaymentMethodLabel:r,PaymentMethodIcons:n}=t;return(0,e.createElement)("div",{className:`wc-moneyspace-blocks-payment-method__label ${o}`},(0,e.createElement)(r,{text:a}),(0,e.createElement)(n,{icons:c}))}),{id:n,title:i,icons:l.icons}),content:(0,e.createElement)((t=>{const a={ccNo:"",ccName:"",ccExpMonth:"",ccExpYear:"",ccCVV:"",cardYear:"",minCardYear:(new Date).getFullYear()};console.log("props",t);const{isComplete:c}=t.checkoutStatus,{ValidationInputError:n}=t.components,{onCheckoutValidationBeforeProcessing:l}=t.eventRegistration;var i=!1;null!==document.getElementById("radio-control-wc-payment-method-options-moneyspace")&&(i=document.getElementById("radio-control-wc-payment-method-options-moneyspace").checked);const d=e=>(console.log("formData[fieldName]",typeof m[e]),""!=m[e]?"is-active":"has-error"),s=(t,a)=>""==m[t]?(0,e.createElement)("div",{class:"wc-block-components-validation-error",role:"alert"},(0,e.createElement)("p",null,a)):"";console.log("ValidationInputError",n);const[m,p]=(0,o.useState)(a),u=[1,2,3,4,5,6,7,8,9,10,11,12];(({formData:e,onCheckoutValidationBeforeProcessing:t})=>{(0,o.useEffect)((()=>t((()=>{console.log("!formData?.ccNo",e,!e?.ccNo);var t=!1,a=[];return console.log("arrMessage",a),e?.ccNo||(a=[(0,r.__)("Please fill in Card Number before placing your order.","moneyspace-woocommerce"),"<br/>",...a],t=!0),e?.ccName||(a=[(0,r.__)("Please fill in Card Holder before placing your order.","moneyspace-woocommerce"),"<br/>",...a],t=!0),e?.ccExpMonth||(a=[(0,r.__)("Please fill in Exp Month before placing your order.","moneyspace-woocommerce"),"<br/>",...a],t=!0),e?.ccExpYear||(a=[(0,r.__)("Please fill in Exp Year before placing your order.","moneyspace-woocommerce"),"<br/>",...a],t=!0),e?.ccCVV||(a=[(0,r.__)("Please fill in CVV before placing your order.","moneyspace-woocommerce"),"<br/>",...a],t=!0),!t||{errorMessage:String.prototype.concat(...a)}}))),[e])})({formData:m,onCheckoutValidationBeforeProcessing:l});const E=e=>{for(var t=e.replace(/\s+/g,"").replace(/[^0-9]/gi,"").match(/\d{4,16}/g),a=t&&t[0]||"",c=[],o=0,r=a.length;o<r;o+=4)c.push(a.substring(o,o+4));return c.length?c.join(" "):e},g=e=>t=>{p("ccNo"==e?{...m,[e]:E(t.target.value)}:"ccName"==e?{...m,[e]:t.target.value.toUpperCase()}:{...m,[e]:t.target.value})};return(0,e.createElement)("div",{className:"wc-block-components-credit-card-form"},(0,e.createElement)("div",{className:`wc-block-components-text-input ${d("ccNo")}`},(0,e.createElement)("input",{type:"text",value:m.ccNo,onChange:g("ccNo"),id:"txtCardNumber",name:"cardNumber",required:"validateCardNumber()",onKeyDown:e=>/^[0-9]*$/.test(e.key)||8==e.keyCode?m.ccNo.replaceAll(" ","").length>=16&&8!=e.keyCode?e.preventDefault():void 0:e.preventDefault(),placeholder:"0000 0000 0000 0000"}),(0,e.createElement)("label",{for:"creditCard"},"Card Number *"),s("ccNo","Please fill in Card Number before placing your order.")),(0,e.createElement)("div",{className:`wc-block-components-text-input ${d("ccName")}`},(0,e.createElement)("input",{type:"text",value:m.ccName,onChange:g("ccName"),id:"txtHolder",name:"cardHolder",required:"validateCardHolder()",keypress:"checkCardName",placeholder:"TONY ELSDEN"}),(0,e.createElement)("label",{for:"cardHolder"},"Card Holder *"),s("ccName","Please fill in Card Holder before placing your order.")),(0,e.createElement)("div",{className:`wc-block-components-text-input ${d("ccExpMonth")}`},(0,e.createElement)("select",{value:m.ccExpMonth,onChange:g("ccExpMonth"),id:"txtExpDate",name:"cardExpDate",required:"validateCardExpDate()"},(0,e.createElement)("option",{value:"",disabled:!0,selected:!0},"Month"),u.map((t=>(0,e.createElement)("option",{value:t,disabled:t<(a.cardYear===a.minCardYear?(new Date).getMonth()+1:1)},t<10?"0"+t:t)))),(0,e.createElement)("label",{for:"ccExpMonth"},"Exp Month *"),s("ccExpMonth","Please fill in Exp Month before placing your order.")),(0,e.createElement)("div",{className:`wc-block-components-text-input ${d("ccExpYear")}`},(0,e.createElement)("select",{value:m.ccExpYear,onChange:g("ccExpYear"),id:"ccExpYear",name:"ccExpYear",required:"validateCardExpYear()"},(0,e.createElement)("option",{value:"",disabled:!0,selected:!0},"Month"),u.map(((t,a)=>(0,e.createElement)("option",{value:a+m.minCardYear},a+m.minCardYear)))),(0,e.createElement)("label",{for:"ccExpYear"},"Exp Year *"),s("ccExpYear","Please fill in Card Exp Year before placing your order.")),(0,e.createElement)("div",{className:`wc-block-components-text-input ${d("ccCVV")}`},(0,e.createElement)("input",{type:"password",value:m.ccCVV,onChange:g("ccCVV"),id:"txtCVV",name:"cardCVV",maxLength:3,onKeyDown:e=>{if(!/^[0-9]*$/.test(e.key)&&8!=e.keyCode)return e.preventDefault()},placeholder:"000",required:!(0!=m.ccCVV.length||!i)}),(0,e.createElement)("label",{for:"cardCVV"},"CVV *"),s("ccCVV","Please fill in CVV before placing your order.")))}),null),edit:(0,e.createElement)((()=>(0,a.decodeEntities)(l.description||"")),null),ariaLabel:i,paymentMethodId:n,canMakePayment:()=>!0,supports:{features:l.supports}};(0,c.registerPaymentMethod)(d)})();