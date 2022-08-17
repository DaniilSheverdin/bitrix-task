import axios from '@/api/axios';
import { token } from '@/api/variables';

const settingsModule = {
  state: {
    settings: null,
  },
  mutations: {
    GET_SETTINGS_LIST(state, payload) {
      state.settings = payload;
    },
    UPDATE_SETTINGS(state, payload) {
      state.settings = payload;
    },
  },
  actions: {
    async getSettingsList({ commit }) {
      const response = await axios.get(`/?get=settings&token=${token}`);
      const data = response.data.result;

      commit('GET_SETTINGS_LIST', data);
    },
    // eslint-disable-next-line no-unused-vars
    async pinItem({ commit, dispatch }, { item_id }) {
      const response = await axios.post(`/?set=pin&id=${item_id}&token=${token}`);
      const data = response.data.result;
      // dispatch('getIndicatorsList', null, { root: true });

      commit('UPDATE_SETTINGS', data);
    },
    // eslint-disable-next-line no-unused-vars
    async unpinItem({ commit, dispatch }, { item_id }) {
      const response = await axios.post(`/?set=unpin&id=${item_id}&token=${token}`);
      const data = response.data.result;
      // dispatch('getIndicatorsList', null, { root: true });

      commit('UPDATE_SETTINGS', data);
    },
    async setItemView({ commit }, { id, view }) {
      const response = await axios.post(`/?set=view&id=${id}&value=${view}&token=${token}`);
      const data = response.data.result;

      commit('UPDATE_SETTINGS', data);
    },
    async setFilters({ commit }, { departments, programs, themes }) {
      const response = await axios
        .post(`/?set=show&value[themes]=${themes}&value[programs]=${programs}&value[departments]=${departments}&token=${token}`);
      const data = response.data.result;

      commit('UPDATE_SETTINGS', data);
    },
  },
  getters: {
    settings: state => state.settings,
  },
};

export default settingsModule;
