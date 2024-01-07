
// import { sprintf, __ } from '@wordpress/i18n';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { decodeEntities } from '@wordpress/html-entities';
import { getSetting } from '@woocommerce/settings';

const settings = getSetting( 'moneyspace_test_data', {} );

// const defaultLabel = __(
// 	'moneyspace_test Payments',
// 	'woo-gutenberg-products-block'
// );

const label = decodeEntities( settings.title ); // || defaultLabel;
/**
 * Content component
 */
const Content = () => {
	return ''; // decodeEntities( settings.description || '' );
};
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
 * Dummy payment method config object.
 */
// const Dummy = {
// 	name: "moneyspace_test",
// 	label: <Label />,
// 	content: <Content />,
// 	edit: <Content />,
// 	canMakePayment: () => true,
// 	ariaLabel: label,
// 	supports: {
// 		features: settings.supports,
// 	},
// };

// registerPaymentMethod( Dummy );


const options = {
	name: 'moneyspace_test',
	label: <Label />,
	content: <Content />,
	edit:  <Content />,
	ariaLabel: label,
	paymentMethodId: 'moneyspace_test',
	canMakePayment: () => true,
	supports: {
		features: settings.supports,
	},
};

registerPaymentMethod( options );
