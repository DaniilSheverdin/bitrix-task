const programsMixin = {
  computed: {
    programs() {
      const programsList = Object.values(this.$store.getters.directories.programsTree);
      const parents = programsList.filter(p => p.parent === 0);

      // eslint-disable-next-line no-return-assign,no-param-reassign
      parents.map(p => p.children = programsList.filter(prog => prog.parent === p.id));

      return parents;
    },
  },
};

export default programsMixin;
