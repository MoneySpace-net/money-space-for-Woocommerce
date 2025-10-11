import { getSetting } from '@woocommerce/settings';
import { decodeEntities } from '@wordpress/html-entities';
import { registerPaymentMethod  } from '@woocommerce/blocks-registry';
import { useMemo } from '@wordpress/element';
import CreditCardForm from './../components/CreditCardForm';
import PaymentMethodLabel from '../components/PaymentMethodLabel';
import './styles.scss';

const id = 'moneyspace';
const settings = getSetting( `${id}_data`, {} );
const label = decodeEntities( settings.title );
const template_payment = settings.ms_template_payment;

const Label = ( props ) => {
    return <PaymentMethodLabel
        components={ props.components }
        title={ label }
        icons={ settings.icons }
        id={ id }
    />
}

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
	label: <Label />,
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