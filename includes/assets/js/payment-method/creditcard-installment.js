import { getSetting } from '@woocommerce/settings';
import { decodeEntities } from '@wordpress/html-entities';
import { registerPaymentMethod  } from '@woocommerce/blocks-registry';
import PaymentMethodLabel from './../components/PaymentMethodLabel';
import './styles.scss';

const id = "moneyspace_installment";


const settings = getSetting( `${id}_data`, {} );
const label = decodeEntities( settings.title );

/**
 * Content component
 */
const Content = () => {
	return decodeEntities( settings.description || '' );
};

const options = {
	name: 'moneyspace_installment',
	label: <PaymentMethodLabel
            id={id}
            title={label}
            icons={settings.icons}/>,
	content: <Content />,
	edit:  <Content />,
	ariaLabel: label,
	canMakePayment: () => false,
	supports: {
		features: settings.supports,
	},
};

registerPaymentMethod( options );