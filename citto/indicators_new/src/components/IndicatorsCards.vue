<template>
  <b-carousel
    id="main-content"
    class="main-carousel indicators-carousel d-flex flex-column flex-grow-1 h-100 pb-5"
    :interval="carouselIntervalInMs"
    controls
    indicators
    :style="fontSize"
  >
    <b-carousel-slide
      v-for="(itemsArr, index) in chunkedItems"
      :key="index"
      img-blank
    >
      <template slot="img">
        <div class="cards row mx-n1 h-100">
          <indicators-card
            v-for="item in itemsArr"
            :key="item.id"
            :item="item"
            :view-mode="viewMode(item.id)"
            :is-pinned="isPinned(item.id)"
          />
        </div>
      </template>
    </b-carousel-slide>
  </b-carousel>
</template>

<script>
import indicatorsItemsMixin from '@/mixins/indicators-items';
import IndicatorsCard from '@/components/IndicatorsCard';

export default {
  name: 'IndicatorsCards',
  props: {
    carouselIntervalInMs: Number,
  },
  components: {
    IndicatorsCard,
  },
  mixins: [indicatorsItemsMixin],
  computed: {
    chunkedItems() {
      const { items } = this;
      const sortedItems = items.sort((a, b) => b.is_pinned - a.is_pinned);

      const chunk = (arr, size) => Array.from({ length: Math.ceil(arr.length / size) },
        (v, i) => arr.slice(i * size, i * size + size));

      return chunk(sortedItems, 9);
    },
  },
};
</script>

<style>
.main-carousel .carousel-indicators {
  align-items: center;
  left: -1rem;
  width: 100vw;
  height: 34px;
  margin: 0;
  background-color: hsla(0, 0%, 30%, 0.75);
}

.main-carousel .carousel-control-prev,
.main-carousel .carousel-control-next {
  top: auto;
  width: 10%;
  height: 34px;
  color: #fff;
  z-index: 16;
}

.main-carousel.indicators-carousel .carousel-inner {
  overflow: visible;
}
</style>
