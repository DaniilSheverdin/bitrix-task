const indicatorsItemMixin = {
  props: {
    item: Object,
    isPinned: Boolean,
  },
  computed: {
    itemHistory() {
      if (!this.$store.getters.history) {
        return {};
      }

      const historyArr = Object.values(this.$store.getters.history);
      const dates = historyArr.map(h => h.date);
      const planArr = historyArr.map(h => h.target);
      const factArr = historyArr.map(h => h.value);
      const { lineColor } = historyArr[0];

      const invertColor = originalColor => {
        let color = originalColor;
        color = color.substring(1);
        color = parseInt(color, 16);
        // eslint-disable-next-line no-bitwise,operator-assignment
        color = 0xFFFFFF ^ color;
        color = color.toString(16);
        color = (`000000${color}`).slice(-6);
        color = `#${color}`;

        return color;
      };

      return {
        labels: dates,
        datasets: [
          {
            label: 'Плановое значение',
            borderColor: lineColor,
            backgroundColor: 'transparent',
            data: planArr,
          },
          {
            label: 'Фактическое значение',
            borderColor: invertColor(lineColor),
            backgroundColor: 'transparent',
            data: factArr,
          },
        ],
      };
    },
    isExpired() {
      return this.item.statuses.includes('expired');
    },
    colorClassByPercent() {
      const milestoneDate = new Date(this.item.milestone.split('.').reverse().join('-'));
      let percent = null;

      if (this.item.percent) {
        percent = +this.item.percent.replace(',', '.');
      }

      if (this.item.type !== 'stat') {
        if (percent < 30
          || (this.item.type === 'passport' && milestoneDate.getTime() < new Date())
        ) {
          return 'alert-danger';
        }

        if (percent >= 30 && percent < 80) {
          return 'alert-warning';
        }

        if (percent >= 80) {
          return 'alert-success';
        }
      }
    },
  },
  methods: {
    togglePin(item_id) {
      return this.isPinned
        ? this.$store.dispatch('unpinItem', { item_id })
        : this.$store.dispatch('pinItem', { item_id });
    },
    getHistory(xml_id) {
      this.$store.dispatch('getHistory', { xml_id });

      setTimeout(() => {
        this.$bvModal.show(`chartModal-${xml_id}`);
      }, 300);
    },
  },
};

export default indicatorsItemMixin;
