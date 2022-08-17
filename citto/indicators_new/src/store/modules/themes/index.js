import axios from '@/api/axios';
import { token } from '@/api/variables';

const themesModule = {
  state: {
    themes: [],
  },
  mutations: {
    GET_THEMES_LIST(state, payload) {
      state.themes = payload;
    },
  },
  actions: {
    async getThemesList({ commit }) {
      const response = await axios.get(`/?get=themes&token=${token}`);
      const data = response.data.result;
      commit('GET_THEMES_LIST', data);
    },
  },
  getters: {
    themes: state => state.themes,
  },
};

export default themesModule;
