<template>
  <div
    class="d-flex flex-column min-em-60"
    :class="{ 'order-first': isPinned }"
  >
    <div class="row no-gutters py-3 border-top" :class="colorClassByPercent">
      <div class="col-auto px-1">
        <span class="font-size-larger">{{ index + 1 }}.</span>
      </div>

      <div class="px-1" :class="item.type !== 'stat' ? 'col-3' : 'col-4'">
        <h3
          class="m-0 font-condensed text-truncate popover-tip"
          type="button"
          :id="`popover-target-${item.id}`"
          v-html="item.short_name"
        ></h3>
        <b-popover
          :target="`popover-target-${item.id}`"
          triggers="hover focus"
          placement="bottom"
        >
          <p class="m-0" v-html="item.description || item.name"></p>
        </b-popover>

        <button
          v-if="item.percent > 0"
          title="Динамика"
          class="d-block btn btn-icon icon-28 icon-chart-before my-0"
          @click="getHistory(item.xml_id)"
        ></button>

        <b-modal
          :id="`chartModal-${item.xml_id}`"
          hide-footer
          centered
          size="md"
          modal-class="modal-chart"
        >
          <indicator-chart :chartdata="itemHistory" :options="{
            responsive: true,
            maintainAspectRatio: true
          }" />
        </b-modal>
      </div>

      <div v-if="item.type !== 'stat'" class="col-2 px-1 text-center">
        <p class="m-0 text-uppercase">{{ item.plan }}</p>
      </div>

      <div class="col-2 px-1 text-center">
        <p class="m-0 text-uppercase">{{ item.fact || 0 }}</p>
      </div>

      <div v-if="item.type !== 'stat'"  class="col-2 px-1 text-center">
        <p class="m-0 text-uppercase">{{ item.percent || 0 }} %</p>
      </div>

      <div class="col px-1">
        <div class="font-size-smaller pr-4">
          <p v-if="item.author || item.date" class="mb-1 icon-refresh-before">
            <b v-if="item.date">{{ item.date }}</b> <span v-if="item.author">&minus; {{ item.author }}</span>
          </p>

          <p v-if="item.comment" class="mb-1 icon-comment-before">{{ item.comment }}</p>
        </div>

        <button
          class="btn-icon icon-16 icon-pin position-absolute top-0 right-0 mr-2"
          :class="{ 'active': isPinned }"
          title="Закрепить в сводке"
          @click="togglePin(item.id)"
        ></button>
      </div>
    </div>
  </div>
</template>

<script>
import IndicatorChart from '@/components/IndicatorChart';
import indicatorsItemMixin from '@/mixins/indicators-item';

export default {
  name: 'ItemsPageTableRow',
  components: {
    IndicatorChart,
  },
  mixins: [indicatorsItemMixin],
  props: {
    index: Number,
  },
};
</script>
