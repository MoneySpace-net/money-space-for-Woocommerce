import { getSetting } from '@woocommerce/settings';
import { decodeEntities } from '@wordpress/html-entities';
import { registerPaymentMethod  } from '@woocommerce/blocks-registry';
import {__} from '@wordpress/i18n';

const id = "moneyspace_installment";

const getData = (key) => {
    const data = getSetting(key);
    return (key, defaultValue = null) => {
        if (!data.hasOwnProperty(key)) {
            data[key] = defaultValue;
        }
        return data[key];
    };
}

const data = getData(`${id}_data`);
const settings = getSetting( `${id}_data`, {} );
const label = decodeEntities( settings.title );
console.log('data', data('icon'));
const PaymentMethodLabel = ({components, title, icons, id}) => {
    if (!Array.isArray(icons)) {
        icons = [icons];
    }
    const {PaymentMethodLabel: Label, PaymentMethodIcons} = components;
    return (
        <div className={`wc-blocks-payment-method__label ${id}`}>
            <Label text={title}/>
            {/* <PaymentMethodIcons icons={icons}/> */}
        </div>
    )
};


/**
 * Content component
 */
const Content = () => {
	return decodeEntities( settings.description || '' );
};

const options = {
	name: 'moneyspace_installment',
	label: <PaymentMethodLabel
		id='moneyspace_installment'
		title={data('title')}
		icons={data('icons')}/>,
	content: <Content />,
	edit:  <Content />,
	ariaLabel: label,
    icon: data('icons'),
	canMakePayment: () => true,
	supports: {
		features: settings.supports,
	},
};

registerPaymentMethod( options );