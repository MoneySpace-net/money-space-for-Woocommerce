import { getSetting } from '@woocommerce/settings';
import { decodeEntities } from '@wordpress/html-entities';
import { registerPaymentMethod  } from '@woocommerce/blocks-registry';

const settings = getSetting( 'moneyspace_qrprom_data', {} );
console.log('settings mns : ', settings);
const label = decodeEntities( settings.title );
console.log('settings mns label : ', label);
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
	name: 'moneyspace_qrprom',
	label: <Label />,
	content: <Content />,
	edit:  <Content />,
	ariaLabel: label,
	canMakePayment: () => true,
	supports: {
		features: [],
	},
};

registerPaymentMethod( options );