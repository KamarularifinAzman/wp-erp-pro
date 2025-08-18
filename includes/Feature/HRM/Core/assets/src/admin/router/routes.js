
import HRRemoteWork  from './../components/settings/remote-work/HRRemoteWork.vue';
import HRDigestEmail from './../components/settings/hr-digest-email/DigestEmail.vue';

const routes = [
    {
        path     : '/erp-hr',
        component: {
            render(c) {
                return c('router-view');
            }
        },
        children: [
            {
                path     : 'remote_work',
                name     : 'HRRemoteWork',
                component: HRRemoteWork
            }
        ]
    },

    {
        path     : '/erp-email',
        component: {
            render(c) {
                return c('router-view');
            }
        },
        children : [
            {
                path     : 'hrm_digest_email',
                name     : 'HRDigestEmail',
                component: HRDigestEmail
            },
        ]
    },
];

export default routes;
