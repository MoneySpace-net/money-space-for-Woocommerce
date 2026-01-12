import { getSetting } from '@woocommerce/settings';
import { decodeEntities } from '@wordpress/html-entities';
import { registerPaymentMethod  } from '@woocommerce/blocks-registry';
import { memo, useMemo } from '@wordpress/element';
import CreditCardInstallmentForm from './../components/CreditCardInstallmentForm';
import PaymentMethodLabel from '../components/PaymentMethodLabel';
import './styles.scss';

const id = "moneyspace_installment";

const settings = getSetting( `${id}_data`, {} );
const label = decodeEntities( settings.i18n.MNS_PAY_INS );
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
    
    // Debug logging removed for production
    
    const formProps = useMemo(() => ({
        i18n: settings.i18n,
        msfee: settings.msfee,
        ccIns: settings.ccIns,
        // Pass through all WooCommerce blocks context
        ...props
    }), [props]);

    return <CreditCardInstallmentForm {...formProps} />;
};

/**
 * Edit component (for admin/editor)
 */
const Edit = () => {
	return decodeEntities( settings.description || '' );
};

const options = {
	name: 'moneyspace_installment',
	label: <Label />,
	content: <Content />,
	edit: <Edit />,
	ariaLabel: label,
	paymentMethodId: id,
	canMakePayment: () => true,
	supports: {
		features: settings.supports,
	},
};

registerPaymentMethod( options );