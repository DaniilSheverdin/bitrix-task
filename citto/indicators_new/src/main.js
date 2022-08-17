import Vue from 'vue';
import { BootstrapVue } from 'bootstrap-vue';
import JsonExcel from 'vue-json-excel';
import VueSimpleAlert from 'vue-simple-alert';
import App from './App';
import router from './router';
import store from './store';

import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-vue/dist/bootstrap-vue.min.css';
import './assets/css/avz-reboot.css';
import './assets/css/main.css';

Vue.use(BootstrapVue);
Vue.use(VueSimpleAlert);
Vue.component('download-excel', JsonExcel);

Vue.config.productionTip = false;

new Vue({
  router,
  store,
  render: h => h(App),
}).$mount('#app');
