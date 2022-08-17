<template>
  <div class="d-flex flex-column min-em-60" :class="{ 'order-first': isPinned }">
    <div class="row no-gutters py-3 border-top btn-white">
      <div class="col-2 px-1">
        <span class="font-size-larger">{{ index + 1 }}.</span>
      </div>

      <div class="col-4 px-1">
        <h3
          class="m-0 font-condensed text-truncate popover-tip"
          type="button"
          :id="`popover-target-${row.id}`"
          v-html="row.short_name"
        ></h3>
        <b-popover
          :target="`popover-target-${row.id}`"
          triggers="hover focus"
          placement="bottom"
        >
          <p class="m-0" v-html="row.description || row.name"></p>
        </b-popover>
      </div>

      <div class="col-2 text-center">
        <p class="m-0 text-uppercase">{{ row.fact }}</p>
      </div>

      <div class="col-4 px-1">
        <div class="font-size-smaller pr-4">
          <p v-if="row.author" class="mb-1 icon-refresh-before">
            <b>{{ row.date }}</b> &minus; {{ row.author }}
          </p>
          <p v-if="row.comment" class="mb-1 icon-comment-before">{{ row.comment }}</p>
        </div>

        <button
          class="btn-icon icon-16 icon-pin position-absolute top-0 right-0 mr-2"
          :class="{ 'active': isPinned }"
          title="Закрепить в сводке"
          @click="togglePin(row.id)"
        ></button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ItemPageStatsTableRow',
  props: ['row', 'index', 'isPinned'],
  methods: {
    togglePin(item_id) {
      return this.isPinned
        ? this.$store.dispatch('unpinItem', { item_id })
        : this.$store.dispatch('pinItem', { item_id });
    },
  },
};
</script>
