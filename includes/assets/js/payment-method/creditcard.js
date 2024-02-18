import { getSetting } from '@woocommerce/settings';
import { decodeEntities } from '@wordpress/html-entities';
import { registerPaymentMethod  } from '@woocommerce/blocks-registry';
import PaymentMethodLabel from './../components/PaymentMethodLabel';
import CreditCardForm from './../components/CreditCardForm';
import './styles.scss';

const id = 'moneyspace';
const settings = getSetting( `${id}_data`, {} );
const label = decodeEntities( settings.title );
const template_payment = settings.ms_template_payment;
 console.log('settings cc', settings);

/**
 * Content component
 */
const Content = () => {
	return decodeEntities( settings.description || '' );
};

const options = {
	name: id,
	label: <PaymentMethodLabel
			id={id}
			title={label}
			icons={settings.icons}/>,
	content: template_payment == 1 ? <CreditCardForm i18n={settings.i18n} /> : <Content />,
	edit:  <Content />,
	ariaLabel: label,
	paymentMethodId: id,
	canMakePayment: () => true,
	supports: {
		features: settings.supports,
	},
};

registerPaymentMethod( options );