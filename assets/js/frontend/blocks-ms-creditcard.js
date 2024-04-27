(()=>{"use strict";const e=window.React,t=window.wc.wcSettings,c=window.wp.htmlEntities,a=window.wc.wcBlocksRegistry,n=window.wp.element,r=(window.wp.i18n,t=>{const c={ccNo:"",ccName:"",ccExpMonth:"",ccExpYear:"",ccCVV:"",cardYear:"",minCardYear:(new Date).getFullYear(),dirty:!1};var a=!1,r=[];const o=[1,2,3,4,5,6,7,8,9,10,11,12],[l,i]=(0,n.useState)(c),{onPaymentSetup:s,onPaymentProcessing:d,onCheckoutValidationBeforeProcessing:m}=t.eventRegistration,{i18n:p}=t;null!==document.getElementById("radio-control-wc-payment-method-options-moneyspace")&&(a=document.getElementById("radio-control-wc-payment-method-options-moneyspace").checked),(({formData:e,onPaymentSetup:t})=>{(0,n.useEffect)((()=>t((()=>e))),[e])})({formData:l,onPaymentSetup:s}),(({formData:e,onPaymentProcessing:t})=>{(0,n.useEffect)((()=>t((()=>({type:"success",meta:{paymentMethodData:{cardNumber:e.ccNo.replaceAll(" ",""),cardHolder:e.ccName,cardExpDate:e.ccExpMonth,cardExpDateYear:e.ccExpYear,cardCVV:e.ccCVV}}})))),[e])})({formData:l,onPaymentProcessing:d}),(({formData:e,onCheckoutValidationBeforeProcessing:t},c)=>{(0,n.useEffect)((()=>t((()=>0==e.dirty?(i({...e,dirty:!0}),{errorMessage:"Please fill in Pay by Card 3D secured section before placing your order."}):!(Object.keys(c).length>0)||{errorMessage:"Please check Pay by Card 3D secured section before placing your order."}))),[e])})({formData:l,onCheckoutValidationBeforeProcessing:m},r);const C=e=>"ccNo"==e&&l[e].replaceAll(" ","").length<16&&1==l.dirty||"ccCVV"==e&&l[e].length<3&&1==l.dirty||""==l[e]&&1==l.dirty?"has-error":"",E=(e,t)=>""==l[e]&&1==l.dirty?(r[e]=t,u(t)):"",u=t=>(0,e.createElement)("div",{class:"wc-block-components-validation-error",role:"alert"},(0,e.createElement)("p",null,t)),N=e=>{for(var t=e.replace(/\s+/g,"").replace(/[^0-9]/gi,"").match(/\d{4,16}/g),c=t&&t[0]||"",a=[],n=0,r=c.length;n<r;n+=4)a.push(c.substring(n,n+4));return a.length?a.join(" "):e},y=e=>t=>{"ccNo"==e?/^[0-9]*$/.test(t.target.value.toString().replaceAll(" ",""))&&i({...l,[e]:N(t.target.value),dirty:!0}):i("ccName"==e?{...l,[e]:t.target.value.toUpperCase(),dirty:!0}:{...l,[e]:t.target.value,dirty:!0})};return(0,e.createElement)("div",{className:"wc-block-components-credit-card-form"},(0,e.createElement)("div",{className:`wc-block-components-text-input wc-block-components-credit-form is-active ${C("ccNo")}`},(0,e.createElement)("input",{type:"text",value:l.ccNo,onChange:y("ccNo"),id:"txtCardNumber",name:"cardNumber",onKeyDown:e=>/^[0-9]*$/.test(e.key)||[8,67,86,88].includes(e.keyCode)?l.ccNo.replaceAll(" ","").length>=16&&![8,67,86,88].includes(e.keyCode)?e.preventDefault():void 0:e.preventDefault(),placeholder:"0000 0000 0000 0000"}),(0,e.createElement)("label",{for:"creditCard"},p.MNS_CC_NO," *"),E("ccNo",p.MNS_CC_WARN_CC_NO_1),(_="ccNo",g=p.MNS_CC_WARN_CC_NO_2,l[_].replaceAll(" ","").length<16&&l[_].replaceAll(" ","").length>0&&1==l.dirty?(r[_]=g,u(g)):"")),(0,e.createElement)("div",{className:`wc-block-components-text-input wc-block-components-credit-form is-active ${C("ccName")}`},(0,e.createElement)("input",{type:"text",value:l.ccName,onChange:y("ccName"),id:"txtHolder",name:"cardHolder",keypress:"checkCardName",placeholder:"TONY ELSDEN"}),(0,e.createElement)("label",{for:"cardHolder"},p.MNS_CC_NAME," *"),E("ccName",p.MNS_CC_WARN_CC_NAME)),(0,e.createElement)("div",{className:`wc-block-components-text-input is-active ${C("ccExpMonth")}`},(0,e.createElement)("select",{value:l.ccExpMonth,onChange:y("ccExpMonth"),id:"txtExpDate",name:"cardExpDate"},(0,e.createElement)("option",{value:"",disabled:!0,selected:!0},p.MNS_MONTH),o.map((t=>(0,e.createElement)("option",{value:t,disabled:t<(c.cardYear===c.minCardYear?(new Date).getMonth()+1:1)},t<10?"0"+t:t)))),(0,e.createElement)("label",{for:"ccExpMonth"},p.MNS_CC_EXP_MONTH," *"),E("ccExpMonth",p.MNS_CC_WARN_CC_EXP_MONTH)),(0,e.createElement)("div",{className:`wc-block-components-text-input is-active ${C("ccExpYear")}`},(0,e.createElement)("select",{value:l.ccExpYear,onChange:y("ccExpYear"),id:"ccExpYear",name:"cardExpDateYear"},(0,e.createElement)("option",{value:"",disabled:!0,selected:!0},p.MNS_YEAR),o.map(((t,c)=>(0,e.createElement)("option",{value:c+l.minCardYear},c+l.minCardYear)))),(0,e.createElement)("label",{for:"ccExpYear"},p.MNS_CC_EXP_YEAR," *"),E("ccExpYear",p.MNS_CC_WARN_CC_EXP_YEAR)),(0,e.createElement)("div",{className:`wc-block-components-text-input wc-block-components-credit-form is-active ${C("ccCVV")}`},(0,e.createElement)("input",{type:"password",value:l.ccCVV,onChange:y("ccCVV"),id:"txtCVV",name:"cardCVV",maxLength:3,onKeyDown:e=>{if(!/^[0-9]*$/.test(e.key)&&![8,67,86,88].includes(e.keyCode))return e.preventDefault()},placeholder:"000",required:!(0!=l.ccCVV.length||!a)}),(0,e.createElement)("label",{for:"cardCVV"},p.MNS_CC_CVV," *"),E("ccCVV",p.MNS_CC_WARN_CVV_1),((e,t)=>l[e].length<3&&l[e].length>0&&1==l.dirty?(r[e]=t,u(t)):"")("ccCVV",p.MNS_CC_WARN_CVV_2)));var _,g}),o="moneyspace",l=(0,t.getSetting)(`${o}_data`,{}),i=(0,c.decodeEntities)(l.title),s=l.ms_template_payment,d=()=>(0,c.decodeEntities)(l.description||""),m={name:o,label:(0,e.createElement)((({components:t,title:c,icons:a,id:n})=>{Array.isArray(a)||(a=[a]);const{PaymentMethodLabel:r,PaymentMethodIcons:o}=t;return(0,e.createElement)("div",{className:`wc-moneyspace-blocks-payment-method__label ${n}`},(0,e.createElement)(r,{text:c}),(0,e.createElement)(o,{icons:a}))}),{id:o,title:i,icons:l.icons}),content:1==s?(0,e.createElement)(r,{i18n:l.i18n}):(0,e.createElement)(d,null),edit:(0,e.createElement)(d,null),ariaLabel:i,paymentMethodId:o,canMakePayment:()=>!0,supports:{features:l.supports}};(0,a.registerPaymentMethod)(m)})();