import { getSetting } from '@woocommerce/settings';
import { decodeEntities } from '@wordpress/html-entities';
import { registerPaymentMethod  } from '@woocommerce/blocks-registry';
import { memo, useMemo } from '@wordpress/element';
import CreditCardForm from './../components/CreditCardForm';
import './styles.scss';

const id = 'moneyspace';
const settings = getSetting( `${id}_data`, {} );
const label = decodeEntities( settings.title );
const template_payment = settings.ms_template_payment;

/**
 * Content component
 */
const Content = () => {
	return decodeEntities( settings.description || '' );
};

// Memoize the credit card form
const MemoizedCreditCardForm = memo(() => {
    const formProps = useMemo(() => ({
        i18n: settings.i18n
    }), [settings.i18n]);

    return <CreditCardForm {...formProps} />;
});

const options = {
	name: id,
	label: label,
	content: template_payment == 1 ? <MemoizedCreditCardForm /> : <Content />,
	edit: <Content />,
	ariaLabel: label,
	paymentMethodId: id,
	canMakePayment: () => true,
	supports: {
		features: settings.supports,
	},
};

registerPaymentMethod( options );