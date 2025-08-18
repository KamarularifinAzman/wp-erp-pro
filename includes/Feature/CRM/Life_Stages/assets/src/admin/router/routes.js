
import LifeStage from '../components/settings/LifeStage.vue';

const routes = [
    {
        path: '/erp-crm',
        component: {
            render(c) {
                return c('router-view');
            }
        },
        children: [
            {
                path: 'crm_life_stages',
                name: 'LifeStage',
                component: LifeStage
            },
        ]
    },
];

export default routes;
