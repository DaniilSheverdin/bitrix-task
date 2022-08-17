import axios from '@/api/axios';
import { token } from '@/api/variables';

const historyModule = {
  state: {
    history: null,
  },
  mutations: {
    GET_HISTORY(state, payload) {
      state.history = payload;
    },
    CLEAR_HISTORY(state) {
      state.history = null;
    },
  },
  actions: {
    async getHistory({ commit }, { xml_id }) {
      const response = await axios.get(`/?get=history&bi_id=${xml_id}&token=${token}`);
      const data = response.data.result;
      commit('GET_HISTORY', data);
    },
    clearHistory({ commit }) {
      commit('CLEAR_HISTORY');
    },
  },
  getters: {
    history: state => state.history,
  },
};

export default historyModule;
