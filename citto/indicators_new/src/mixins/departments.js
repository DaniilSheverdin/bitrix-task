const departmentsMixin = {
  computed: {
    departments() {
      const depsList = Object.values(this.$store.getters.directories.department);
      const parents = depsList.filter(d => d.parent === 0);

      // eslint-disable-next-line no-return-assign,no-param-reassign
      parents.map(p => p.children = depsList.filter(dep => dep.parent === p.id));

      return parents;
    },
  },
};

export default departmentsMixin;
