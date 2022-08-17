<template>
  <div>
    <div v-for="table in Object.keys(tables)" :key="table">
      <h2 v-if="getTableName(table)">{{ getTableName(table) }}</h2>

      <div class="overflow-auto border rounded max-80vh mb-5">
        <div class="row no-gutters py-1 bg-dark-10 min-em-60">
          <div class="col-auto px-1"><span class="font-size-smaller font-weight-bold">№</span></div>

          <div class="px-1" :class="table !== 'stat' ? 'col-3' : 'col-4'">
            <span class="font-size-smaller font-weight-bold">Показатель</span>
          </div>

          <div v-if="table !== 'stat'" class="col-2 px-1 text-center">
            <span class="font-size-smaller font-weight-bold">План</span>
          </div>

          <div class="col-2 px-1 text-center">
            <span class="font-size-smaller font-weight-bold">Факт</span>
          </div>

          <div v-if="table !== 'stat'" class="col-2 px-1 text-center">
            <span class="font-size-smaller font-weight-bold">%</span>
          </div>

          <div class="col px-1">
            <span class="font-size-smaller font-weight-bold">Комментарий</span>
          </div>
        </div>

        <div class="d-flex flex-column">
          <items-page-table-row
            v-for="(row, index) in tables[table]"
            :key="row.id"
            :item="row"
            :index="index"
            :is-pinned="isPinned(row.id)"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';
import ItemsPageTableRow from '@/components/ItemsPageTableRow';

export default {
  name: 'ItemsPageTable',
  components: {
    ItemsPageTableRow,
  },
  props: {
    items: Array,
  },
  computed: {
    ...mapGetters([
      'settings',
    ]),
    tables() {
      const types = [...new Set(this.items.map(item => item.type))];

      return types.reduce((acc, currVal) => {
        acc[currVal] = this.items.filter(item => item.type === currVal);

        return acc;
      }, {});
    },
  },
  methods: {
    getTableName(key) {
      if (key === 'passport') {
        return 'Региональные проекты';
      }

      if (key === 'stat') {
        return 'Статистика';
      }

      return '';
    },
    isPinned(id) {
      return Object.values(this.settings.pinned).includes(id);
    },
  },
};
</script>
