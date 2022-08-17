import axios from '@/api/axios';
import { token } from '@/api/variables';

const programsModule = {
  state: {
    programs: [],
    program: null,
  },
  mutations: {
    GET_PROGRAMS_LIST(state, payload) {
      state.programs = payload;
    },
    GET_PROGRAM(state, payload) {
      state.program = payload;
    },
  },
  actions: {
    async getProgramsList({ commit }) {
      const response = await axios.get(`/?get=programs&token=${token}`);
      const data = response.data.result;

      commit('GET_PROGRAMS_LIST', data);
    },
    // eslint-disable-next-line no-unused-vars
    async getProgram({ commit }, { program_id }) {
      const response = await axios.get(`/?get=programs&token=${token}`);
      const data = Object.values(response.data.result);
      const children = data.filter(p => p.parent > 0);
      const parents = data.filter(p => p.parent === 0);

      children.map(p => p.parent = data.find(prog => prog.id === p.parent));

      const program = [...parents, ...children].find(prog => prog.id === parseInt(program_id, 10));

      commit('GET_PROGRAM', program);
    },
  },
  getters: {
    programs: state => state.programs,
    program: state => state.program,
  },
};

export default programsModule;
