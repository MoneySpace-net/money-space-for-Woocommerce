
var checkPaymentMethodINS = false;
if (document.getElementById('payment_method_moneyspace_installment') !== null) {
    checkPaymentMethodINS = document.getElementById('payment_method_moneyspace_installment').checked;
}

var appInstallment = new Vue({
    el: '#installment-form',
    data: {
        banks: [
            "KTC"= {
                months: [3, 4, 5, 6, 7, 8, 9, 10],
                lowerLimit: 300,
                extPercent: 0.8
              }
            , "BAY" = {
                months: [3, 4, 6, 9, 10],
                lowerLimit: 500,
                extPercent: 0.8
              }
            , "FCY" = {
                months: [3, 4, 6, 9, 10, 12, 18, 24, 36],
                lowerLimit: 300,
                extPercent: 1
              }
        ],
        listInstallment: [],
        amount: 0,
        fee: 'include'

    },
    methods: {
        getSettings: function(bankType) {
            return this.banks[bankType];
        },
        calculatePerMonth: function(monthValue, extPercent){
            return (this.fee === "include"
                ? this.amount / monthValue
                : (amount * (100 + extPercent * monthValue)) / (100 * monthValue)
                ).toFixed(2)
        },
        installmentOptions: function(bankType){
            const { months, lowerLimit, extPercent } = this.getSettings(bankType);
            return months.map(i => ({
                month: i,
                amount: this.calculatePerMonth(i, extPercent)
            }));
        }
    },
    computed: {
    },
    watch: {

    }
});

document.getElementsByName('payment_method').forEach(element => { 
    element.addEventListener('change', function() {
        if (this.checked && this.value == 'moneyspace_installment') {
            checkPaymentMethodINS = this.checked;
        } else {
            checkPaymentMethodINS = false;
        }

        appInstallment.$forceUpdate();
    });
});