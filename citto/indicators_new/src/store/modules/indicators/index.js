import axios from '@/api/axios';
import { token } from '@/api/variables';

const indicatorsModule = {
  state: {
    isLoading: false,
    indicators: [],
    indicatorsByParam: [],
    statsIndicators: [],
    searchedIndicators: [],
    indicatorsByDep: [],
  },
  mutations: {
    START_LOADING(state) {
      state.isLoading = true;
    },
    STOP_LOADING(state) {
      state.isLoading = false;
    },
    GET_INDICATORS(state, payload) {
      state.indicators = payload;
    },
    GET_INDICATORS_BY_PARAM(state, payload) {
      state.indicatorsByParam = payload;
    },
    GET_STATS_INDICATORS(state, payload) {
      state.statsIndicators = payload;
    },
    GET_SEARCHED_INDICATORS(state, payload) {
      state.searchedIndicators = payload;
    },
    GET_INDICATORS_BY_DEP(state, payload) {
      state.indicatorsByDep = payload;
    },
    CLEAR_INDICATORS(state) {
      state.indicators = [];
    },
    CLEAR_INDICATORS_BY_PARAM(state) {
      state.indicatorsByParam = [];
    },
    CLEAR_SEARCHED_INDICATORS(state) {
      state.searchedIndicators = [];
    },
    CLEAR_INDICATORS_BY_DEP(state) {
      state.indicatorsByDep = [];
    },
  },
  actions: {
    async getIndicatorsList({ commit }) {
      commit('CLEAR_INDICATORS');
      commit('START_LOADING');

      try {
        const indicators = await axios.get(`/?get=list&token=${token}`);
        const data = indicators.data.result;

        commit('GET_INDICATORS', data);
        commit('STOP_LOADING');
      } catch (e) {
        console.error(e);
        commit('STOP_LOADING');
      }
    },

    async getIndicatorsListByParam({ commit }, { param_name, param_id_value }) {
      commit('START_LOADING');

      try {
        const response = await axios.get(`/?get=list&${param_name}=${param_id_value}&token=${token}`);
        const data = Object.values(response.data.result)
          .filter(item => {
            if (typeof item.filters[param_name] === 'object') {
              return item.filters[param_name].includes(parseInt(param_id_value, 10));
            }

            return item.filters[param_name] === parseInt(param_id_value, 10);
          });

        commit('GET_INDICATORS_BY_PARAM', data);
        commit('STOP_LOADING');
      } catch (e) {
        console.error(e);
        commit('STOP_LOADING');
      }
    },

    async getStatsIndicators({ commit }) {
      commit('START_LOADING');

      try {
        const response = await axios.get(`/?get=list&token=${token}`);
        const data = Object.values(response.data.result)
          .filter(item => item.type === 'stat');

        commit('GET_STATS_INDICATORS', data);
        commit('STOP_LOADING');
      } catch (e) {
        console.error(e);
        commit('STOP_LOADING');
      }
    },

    async getSearchedIndicators({ commit }, { search_str }) {
      commit('CLEAR_SEARCHED_INDICATORS');
      commit('START_LOADING');

      try {
        const response = await axios.get(`/?get=list&token=${token}`);
        const data = Object.values(response.data.result)
          .filter(item => item.name.toLowerCase().includes(search_str.toLowerCase()));

        commit('GET_SEARCHED_INDICATORS', data);
        commit('STOP_LOADING');
      } catch (e) {
        console.error(e);
        commit('STOP_LOADING');
      }
    },

    async getIndicatorsByDep({ commit }, { dep_id }) {
      commit('CLEAR_INDICATORS_BY_DEP');
      commit('START_LOADING');

      try {
        const response = await axios.get(`/?get=list&department=${dep_id}&token=${token}`);
        const data = Object.values(response.data.result);

        commit('GET_INDICATORS_BY_DEP', data);
        commit('STOP_LOADING');
      } catch (e) {
        console.error(e);
        commit('STOP_LOADING');
      }
    },

    // eslint-disable-next-line no-unused-vars
    async setIndicators({ commit }, { formData }) {
      await axios.post(
        `/?set=indicators&token=${token}`,
        formData,
      );
    },

    clearIndicatorsListByParam({ commit }) {
      commit('CLEAR_INDICATORS_BY_PARAM');
    },

    clearSearchedIndicators({ commit }) {
      commit('CLEAR_SEARCHED_INDICATORS');
    },

    clearIndicatorsByDep({ commit }) {
      commit('CLEAR_INDICATORS_BY_DEP');
    },
  },
  getters: {
    isLoading: state => state.isLoading,
    indicators: state => Object.values(state.indicators),
    indicatorsByParam: state => state.indicatorsByParam,
    indicatorsByDep: state => state.indicatorsByDep,
    statsIndicators: state => state.statsIndicators,
    searchedIndicators: state => state.searchedIndicators,
  },
};

export default indicatorsModule;
