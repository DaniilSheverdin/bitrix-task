<template>
  <div class="overflow-auto border rounded max-80vh mb-5">
    <div class="row no-gutters py-1 bg-dark-10 min-em-60">
      <div class="col-2 px-1"><span class="font-size-smaller font-weight-bold">№</span></div>

      <div class="col-4 px-1">
        <span class="font-size-smaller font-weight-bold">Показатель</span>
      </div>

      <div class="col-2 px-1 text-center">
        <span class="font-size-smaller font-weight-bold">Факт</span>
      </div>

      <div class="col px-1">
        <span class="font-size-smaller font-weight-bold">Комментарий</span>
      </div>
    </div>

    <div class="d-flex flex-column">
      <item-page-stats-table-row
        v-for="(item, index) in items"
        :key="item.id"
        :row="item"
        :index="index"
        :is-pinned="isPinned(item.id)"
      />
    </div>
  </div>
</template>

<script>
import ItemPageStatsTableRow from '@/components/ItemPageStatsTableRow';
import { mapActions, mapGetters } from 'vuex';

export default {
  name: 'ItemPageStatsTable',
  components: {
    ItemPageStatsTableRow,
  },
  created() {
    if (this.settings) {
      return;
    }

    this.getSettingsList();
  },
  props: {
    items: Array,
  },
  computed: {
    ...mapGetters([
      'settings',
    ]),
  },
  methods: {
    ...mapActions([
      'getSettingsList',
    ]),
    isPinned(id) {
      if (this.settings) {
        return Object.values(this.settings.pinned).includes(id);
      }
    },
  },
};
</script>
