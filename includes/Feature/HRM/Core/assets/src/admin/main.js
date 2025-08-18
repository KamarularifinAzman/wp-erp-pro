import routes from './router/routes';
import store from './store/index';

const lodash = require('lodash');

if ( typeof window !== 'undefined' ) {

    window.erp_settings_vue_instance.$router.addRoutes( routes );

    lodash.merge(
        window.erp_settings_vue_instance.$store.state,
        store.state
    );
}

