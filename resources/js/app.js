/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

import Vue from 'vue';
import Vuetify from 'vuetify';
Vue.use(Vuetify);



/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('device-component', require('./components/DeviceComponent.vue').default);
Vue.component('user-control-component', require('./components/UserControlComponent.vue').default);
Vue.component('edit-parameters', require('./components/EditParametersComponent.vue').default);
Vue.component('edit-card-component', require('./components/EditCardComponent.vue').default);
Vue.component('add-tempcard-row-component', require('./components/AddTempCardRowComponent.vue').default);
Vue.component('attach-objects-component', require('./components/AttachObjectsComponent.vue').default);
Vue.component('chart-component', require('./components/ChartComponent.vue').default);
Vue.component('application-settings-component', require('./components/ApplicationSettingsComponent.vue').default);
Vue.component('settings-component', require('./components/SettingsComponent.vue').default);
Vue.component('device-settings-component', require('./components/DeviceSettingsComponent.vue').default);
Vue.component('district-settings-component', require('./components/DistrictSettingsComponent.vue').default);

/* --------  AUDIT CONTROL ------------ */
Vue.component('audit-question-component', require('./components/Audit/AuditQuestionComponent.vue').default);
Vue.component('audit-append-root', require('./components/Audit/AuditAppendRoot.vue').default);

/* --------  MONITOR ANALYTICS ------------ */
Vue.component('monitor-index-component', require('./components/Analytics/MonitorIndexComponent.vue').default);
Vue.component('monitor-district-component', require('./components/Analytics/MonitorDistrictComponent.vue').default);
Vue.component('monitor-table-component', require('./components/Analytics/MonitorTableComponent.vue').default);

/* --------  AUDIT ANALYTICS ------------ */
Vue.component('audit-index-component', require('./components/Analytics/AuditIndexComponent.vue').default);
Vue.component('audit-district-component', require('./components/Analytics/AuditDistrictComponent.vue').default);
Vue.component('audit-table-component', require('./components/Analytics/AuditTableComponent.vue').default);

/* --------  CONSUMPTION ANALYTICS ------------ */
Vue.component('consumption-index-component', require('./components/Analytics/ConsumptionIndexComponent.vue').default);
Vue.component('consumption-district-component', require('./components/Analytics/ConsumptionDistrictComponent.vue').default);
Vue.component('consumption-table-component', require('./components/Analytics/ConsumptionTableComponent.vue').default);

Vue.component('kpi-component', require('./components/Analytics/KPIComponent.vue').default);
Vue.component('alert-history-component', require('./components/AlertHistoryComponent.vue').default);
Vue.component('date-picker-component', require('./components/DatePickerComponent.vue').default);
Vue.component('users-actions-component', require('./components/UsersActionsComponent.vue').default);

Vue.component('logist-component', require('./components/Logist/LogistComponent.vue').default);
Vue.component('logist-day-component', require('./components/Logist/LogistDayComponent.vue').default);
Vue.component('logist-control-component', require('./components/Logist/LogistControlComponent.vue').default);
Vue.component('logist-chart-component', require('./components/Logist/LogistChartComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
    vuetify: new Vuetify(),
    data: () => ({
        drawer: null,
      }),
});
