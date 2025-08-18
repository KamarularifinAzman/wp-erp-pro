import routes from './router/setting-routes';
import store  from './store/index';

const lodash = require('lodash');

window.erp_settings_vue_instance.$router.addRoutes( routes );

lodash.merge(
    window.erp_settings_vue_instance.$store.state,
    store.state
);
