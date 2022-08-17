import axios from '@/api/axios';
import { token } from '@/api/variables';

const makeTree = stateProp => {
  const list = Object.values(stateProp);
  const parents = list.filter(p => p.parent === 0);

  parents.map(p => p.children = list.filter(l => l.parent === p.id));

  return parents;
};

const directoriesModule = {
  state: {
    directories: null,
  },
  mutations: {
    GET_DIRECTORIES(state, payload) {
      state.directories = payload;
    },
  },
  actions: {
    async getDirectories({ commit }) {
      const response = await axios.get(`/?get=directory&token=${token}`);
      const data = response.data.result;

      commit('GET_DIRECTORIES', data);
    },
  },
  getters: {
    programsTree: state => {
      if (state.directories) {
        return makeTree(state.directories.program);
      }
    },
    departmentsTree: state => {
      if (state.directories) {
        return makeTree(state.directories.department);
      }
    },
    programsList: state => {
      if (state.directories) {
        return Object.values(state.directories.program);
      }
    },
    departmentsList: state => {
      if (state.directories) {
        return Object.values(state.directories.department);
      }
    },
    themesList: state => {
      if (state.directories) {
        return Object.values(state.directories.theme);
      }
    },
  },
};

export default directoriesModule;
