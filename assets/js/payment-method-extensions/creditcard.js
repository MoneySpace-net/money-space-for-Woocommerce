import { registerPaymentMethod  } from '@woocommerce/blocks-registry';

const options = {
	name: 'moneyspace_creditcard_payment_method',
	content: <div>A React node</div>,
	edit: <div>A React node</div>,
	canMakePayment: () => true,
	// paymentMethodId: 'new_payment_method',
	supports: {
		features: [],
	},
};

registerPaymentMethod( options );

// const ExpressPaymentMethod = (props) => {
//     return <PayPalPaymentMethod
//         context={'express_checkout'}
//         isExpress={true}
//         paymentMethodId='paymentplugins_ppcp_express'
//         {...props}/>;
// }

// const MoneySpaceCreditCardPaymentMethod = (
//     {
//         isExpress = false,
//         context,
//         billing,
//         shippingData,
//         eventRegistration,
//         emitResponse,
//         onError,
//         onClick,
//         onClose,
//         onSubmit,
//         activePaymentMethod,
//         paymentMethodId,
//         ...props
//     }) => {
//     const [error, setError] = useState(false);
//     const queryParams = getSetting('paypalQueryParams');
//     const vault = queryParams.vault === 'true';
//     const {billingData} = billing;
//     const {
//         onPaymentProcessing,
//         onCheckoutAfterProcessingWithError,
//         onCheckoutValidationBeforeProcessing
//     } = eventRegistration;
//     const {responseTypes, noticeContexts} = emitResponse;
//     const [buttonsContainer, setButtonsContainer] = useState();

//     useBreakpointWidth({width: 375, node: buttonsContainer});

//     if (!isExpress) {
//         onError = useCallback((error) => {
//             setError(error?.message ? error.message : error);
//         }, []);
//     }

//     const setButtonContainerRef = useCallback(el => {
//         setButtonsContainer(el?.parentElement?.parentElement);
//     }, []);

//     const {paymentData, setPaymentData} = useProcessPayment({
//         onSubmit,
//         billingData,
//         shippingData,
//         onPaymentProcessing,
//         responseTypes,
//         activePaymentMethod,
//         paymentMethodId
//     });

//     useProcessPaymentFailure({
//         event: onCheckoutAfterProcessingWithError,
//         responseTypes,
//         messageContext: isExpress ? noticeContexts.EXPRESS_PAYMENTS : null,
//         setPaymentData
//     });

//     useValidateCheckout({
//         isExpress,
//         onCheckoutValidationBeforeProcessing,
//         paymentData
//     })

//     const paypal = useLoadPayPalScript(queryParams);

//     const {getOptions} = usePayPalOptions({
//         isExpress,
//         paypal,
//         vault,
//         intent: queryParams.intent,
//         buttonStyles: data('buttons'),
//         billing,
//         shippingData,
//         eventRegistration,
//         setError: onError,
//         setPaymentData,
//         onClick,
//         onClose
//     });
//     const sources = usePayPalFundingSources({
//         data,
//         paypal,
//         context,
//         vault
//     });
//     const cancelPayment = e => {
//         e.preventDefault();
//         setPaymentData(null);
//     }

//     if (!isExpress && paymentData) {
//         return (
//             <>
//                 <div className={'wc-ppcp-order-review__message'}>
//                     {__('Your PayPal payment method is ready to be processed. Please review your order details then click Place Order',
//                         'pymntpl-paypal-woocommerce')}
//                 </div>
//                 <a href={'#'} onClick={cancelPayment} className={'wc-ppcp-cancel__payment'}>{__('Cancel', 'pymntpl-paypal-woocommerce')}</a>
//             </>
//         );
//     }
//     if (paypal && sources) {
//         const Button = paypal.Buttons.driver("react", {React, ReactDOM});
//         const BUTTONS = sources.map(source => {
//             const options = getOptions(source);
//             const button = paypal.Buttons(options);
//             return button.isEligible() ? <Button key={source} {...options}/> : null;
//         });
//         return (
//             <>
//                 {!isExpress && <ErrorMessage msg={error}/>}
//                 <div className='wc-ppcp-paypal__buttons' ref={setButtonContainerRef}>
//                     {BUTTONS}
//                 </div>
//             </>
//         );
//     }
//     return null;
// }

// const PaymentMethodLabel = ({components, title, icons, id}) => {
//     if (!Array.isArray(icons)) {
//         icons = [icons];
//     }
//     const {PaymentMethodLabel: Label, PaymentMethodIcons} = components;
//     return (
//         <div className={`wc-ppcp-blocks-payment-method__label ${id}`}>
//             <Label text={title}/>
//             <PaymentMethodIcons icons={icons}/>
//         </div>
//     )
// };

// registerPaymentMethod({
// 	name: 'moneyspace-creditcard',
// 	label: <PaymentMethodLabel
// 		id='moneyspace-creditcard'
// 		title={data('title')}
// 		icons={data('icons')}/>,
// 	ariaLabel: 'MoneySpaceCreditCard',
// 	canMakePayment: () => true,
// 	content: <MoneySpaceCreditCardPaymentMethod context={'checkout'} paymentMethodId={'moneyspace-creditcard'}/>,
// 	edit: <MoneySpaceCreditCardPaymentMethod context={'checkout'} paymentMethodId={'moneyspace-creditcard'}/>,
// 	supports: {
// 		showSavedCards: false,
// 		showSaveOption: false,
// 		features: data('features')
// 	}
// });