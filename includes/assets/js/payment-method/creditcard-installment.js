import { getSetting } from '@woocommerce/settings';
import { decodeEntities } from '@wordpress/html-entities';
import { registerPaymentMethod  } from '@woocommerce/blocks-registry';
import PaymentMethodLabel from './../components/PaymentMethodLabel';
import CreditCardInstallmentForm from './../components/CreditCardInstallmentForm';
import './styles.scss';

const id = "moneyspace_installment";

const settings = getSetting( `${id}_data`, {} );
const label = decodeEntities( settings.i18n.MNS_PAY_INS );

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
	content: <CreditCardInstallmentForm i18n={settings.i18n} msfee={settings.msfee} ccIns={settings.ccIns} />,
	edit:  <Content />,
	ariaLabel: label,
	paymentMethodId: id,
	canMakePayment: () => true,
	supports: {
		features: settings.supports,
	},
};

registerPaymentMethod( options );