import { mapGetters } from 'vuex';

const indicatorsItemsMixin = {
  props: {
    items: Array,
    fontSize: String,
  },
  methods: {
    viewMode(id) {
      if (this.settings.view.all !== 'user') {
        return this.settings.view.all;
      }

      return this.settings.view.hasOwnProperty(id) ? this.settings.view[id] : 'full';
    },
    isPinned(id) {
      return Object.values(this.settings.pinned).includes(id);
    },
  },
  computed: {
    ...mapGetters([
      'settings',
    ]),
  },
};

export default indicatorsItemsMixin;
