import {useEffect} from '@wordpress/element';
import {__} from '@wordpress/i18n';

export const useValidateCheckout = (
    {
        formData,
        onCheckoutValidationBeforeProcessing
    }) => {
    useEffect(() => {
        const unsubscribe = onCheckoutValidationBeforeProcessing(() => {
            console.log('!formData?.ccNo', formData, !formData?.ccNo);
            var isError = false;
            var arrMessage = [];
            // validate that the order has been created.
            if (!formData?.ccNo) {
                arrMessage = [__('Please fill in Card Number before placing your order.', 'moneyspace-woocommerce'), "<br/>", ...arrMessage];
                isError = true;
                // return {
                //     errorMessage: __('Please fill in Card Number before placing your order.', 'moneyspace-woocommerce')
                // }
            }

            if (!formData?.ccName) {
                arrMessage = [__('Please fill in Card Holder before placing your order.', 'moneyspace-woocommerce'), "<br/>", ...arrMessage];
                isError = true;
                // return {
                //     errorMessage: __('Please fill in Card Holder before placing your order.', 'moneyspace-woocommerce')
                // }
            }

            if (!formData?.ccExpMonth) {
                arrMessage = [__('Please fill in Exp Month before placing your order.', 'moneyspace-woocommerce'), "<br/>", ...arrMessage];
                isError = true;
                // return {
                //     errorMessage: __('Please fill in Exp Month before placing your order.', 'moneyspace-woocommerce')
                // }
            }

            if (!formData?.ccExpYear) {
                arrMessage = [__('Please fill in Exp Year before placing your order.', 'moneyspace-woocommerce'), "<br/>", ...arrMessage];
                isError = true;
                // return {
                //     errorMessage: __('Please fill in Exp Year before placing your order.', 'moneyspace-woocommerce')
                // }
            }

            if (!formData?.ccCVV) {
                arrMessage = [__('Please fill in CVV before placing your order.', 'moneyspace-woocommerce'), "<br/>", ...arrMessage];
                isError = true;
                // return {
                //     errorMessage: __('Please fill in CVV before placing your order.', 'moneyspace-woocommerce')
                // }
            }

            if (isError) {
                return {
                    errorMessage: String.prototype.concat(...arrMessage)
                }
            }
            return true;
        });
        return unsubscribe;
    }, [formData]);
}