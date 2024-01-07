import { getSetting } from '@woocommerce/settings';
import { decodeEntities } from '@wordpress/html-entities';
import { registerPaymentMethod  } from '@woocommerce/blocks-registry';

const settings = getSetting( 'moneyspace_data', {} );
const label = decodeEntities( settings.title );
/**
 * Label component
 *
 * @param {*} props Props from payment API.
 */
const Label = ( props ) => {
	const { PaymentMethodLabel } = props.components;
	return <PaymentMethodLabel text={ label } />;
};

/**
 * Content component
 */
const Content = () => {
	return decodeEntities( settings.description || '' );
};

const options = {
	name: 'moneyspace',
	label: <Label />,
	content: <Content />,
	edit:  <Content />,
	ariaLabel: label,
	paymentMethodId: 'moneyspace',
	canMakePayment: () => true,
	supports: {
		features: settings.supports,
	},
};

registerPaymentMethod( options );