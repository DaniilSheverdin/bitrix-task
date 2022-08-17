import axios from '@/api/axios';
import { token } from '@/api/variables';

const departmentsModule = {
  state: {
    departments: [],
    department: null,
    departmentsWithSelect: null,
  },
  mutations: {
    GET_DEPARTMENTS_LIST(state, payload) {
      state.departments = payload;
    },
    GET_DEPARTMENT(state, payload) {
      state.department = payload;
    },
    GET_DEPARTMENTS_WITH_SELECT_LIST(state, payload) {
      state.departmentsWithSelect = payload;
    },
  },
  actions: {
    async getDepartmentsList({ commit }) {
      const response = await axios.get(`/?get=departments&token=${token}`);
      const data = response.data.result;
      commit('GET_DEPARTMENTS_LIST', data);
    },
    async getDepartment({ commit }, { dep_id }) {
      const response = await axios.get(`/?get=departments&token=${token}`);
      const data = Object.values(response.data.result);
      const children = data.filter(p => p.parent > 0);
      const parents = data.filter(p => p.parent === 0);

      children.map(p => p.parent = data.find(prog => prog.id === p.parent));

      const department = [...parents, ...children].find(prog => prog.id === parseInt(dep_id, 10));

      commit('GET_DEPARTMENT', department);
    },
    async getDepartmentsWithSelectList({ commit }) {
      const response = await axios.get(`/?get=departments&token=${token}`);
      const data = response.data.result;

      commit('GET_DEPARTMENTS_WITH_SELECT_LIST', data);
    },
  },
  getters: {
    departments: state => state.departments,
    department: state => state.department,
    departmentsWithSelect: state => state.departmentsWithSelect,
  },
};

export default departmentsModule;
