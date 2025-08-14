import { getSetting } from '@woocommerce/settings';
import { decodeEntities } from '@wordpress/html-entities';
import { registerPaymentMethod  } from '@woocommerce/blocks-registry';
import { useMemo } from '@wordpress/element';
import CreditCardForm from './../components/CreditCardForm';
import './styles.scss';

const id = 'moneyspace';
const settings = getSetting( `${id}_data`, {} );
const label = decodeEntities( settings.title );
const template_payment = settings.ms_template_payment;

/**
 * Content component - This receives WooCommerce blocks context
 */
const Content = (props) => {
    // This component receives props from WooCommerce blocks including:
    // - billing data
    // - cart data  
    // - eventRegistration
    // - etc.
    
    const formProps = useMemo(() => ({
        i18n: settings.i18n,
        // Pass through all WooCommerce blocks context
        ...props
    }), [props]);

    return template_payment == 1 ? <CreditCardForm {...formProps} /> : decodeEntities( settings.description || '' );
};

const options = {
	name: id,
	label: label,
	content: <Content />,
	edit: <Content />,
	ariaLabel: label,
	paymentMethodId: id,
	canMakePayment: () => true,
	supports: {
		features: settings.supports,
	},
};

registerPaymentMethod( options );