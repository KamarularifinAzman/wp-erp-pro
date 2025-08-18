import AcPaymentGeneral from './../components/settings/payment/AcPaymentGeneral.vue';
import AcPaymentPaypal  from './../components/settings/payment/AcPaymentPaypal.vue';
import AcPaymentStripe  from './../components/settings/payment/AcPaymentStripe.vue';

const routes = [
    {
        path     : '/erp-ac',
        component: {
            render(c) {
                return c('router-view');
            }
        },
        children: [
            {
                path     : 'payment',
                name     : 'AcPayment',
                component: {
                    render(c) {
                        return c('router-view');
                    }
                },
                children: [
                    {
                        path     : 'general',
                        name     : 'AcPaymentGeneral',
                        component: AcPaymentGeneral,
                        alias    : '/'
                    },
                    {
                        path     : 'paypal',
                        name     : 'AcPaymentPaypal',
                        component: AcPaymentPaypal
                    },
                    {
                        path     : 'stripe',
                        name     : 'AcPaymentStripe',
                        component: AcPaymentStripe
                    }
                ]
            }
        ]
    }
];

export default routes;
