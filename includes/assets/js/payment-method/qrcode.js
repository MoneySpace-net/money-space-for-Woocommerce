import { getSetting } from '@woocommerce/settings';
import { decodeEntities } from '@wordpress/html-entities';
import { registerPaymentMethod  } from '@woocommerce/blocks-registry';
import './styles.scss';

const id = "moneyspace_qrprom";
const settings = getSetting( `${id}_data`, {} );
const label = decodeEntities( settings.title );

/**
 * Content component
 */
const Content = () => {
	return decodeEntities( settings.description || '' );
};

const options = {
	name: id,
	label: label,  // Use simple string label
	content: <Content />,
	edit: <Content />,
	ariaLabel: label,
	canMakePayment: () => true,
	supports: {
		features: settings.supports,
	},
};

registerPaymentMethod( options );