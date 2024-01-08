const PaymentMethodLabel = ({components, title, icons, id}) => {
    if (!Array.isArray(icons)) {
        icons = [icons];
    }
    const {PaymentMethodLabel: Label, PaymentMethodIcons} = components;
    return (
        <div className={`wc-moneyspace-blocks-payment-method__label ${id}`}>
            <Label text={title}/>
            <PaymentMethodIcons icons={icons}/>
        </div>
    )
}
 
export default PaymentMethodLabel;