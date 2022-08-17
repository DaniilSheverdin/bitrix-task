import Vue from 'vue';
import Vuex from 'vuex';

import departmentsModule from '@/store/modules/departments';
import indicatorsModule from '@/store/modules/indicators';
import programsModule from '@/store/modules/programs';
import settingsModule from '@/store/modules/settings';
import themesModule from '@/store/modules/themes';
import historyModule from '@/store/modules/history';
import directoriesModule from '@/store/modules/directories';

Vue.use(Vuex);

export default new Vuex.Store({
  modules: {
    departmentsModule,
    indicatorsModule,
    programsModule,
    settingsModule,
    themesModule,
    historyModule,
    directoriesModule,
  },
});
