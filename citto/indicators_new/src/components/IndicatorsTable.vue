<template>
  <div class="d-flex flex-column" :style="fontSize">
    <indicators-table-row
      v-for="item in items"
      :item="item"
      :key="item.id"
      :view-mode="viewMode(item.id)"
      :is-pinned="isPinned(item.id)"
    />
  </div>
</template>

<script>
import IndicatorsTableRow from '@/components/IndicatorsTableRow';
import indicatorsItemsMixin from '@/mixins/indicators-items';

export default {
  name: 'IndicatorsTable',
  components: {
    IndicatorsTableRow,
  },
  mixins: [indicatorsItemsMixin],
};
</script>
