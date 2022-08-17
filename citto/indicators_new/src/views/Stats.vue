<template>
  <div class="container-fluid flex-grow-1 d-flex flex-column">
    <div id="controls">
      <h1 class="mb-2">Статистика</h1>

      <div class="d-flex align-items-center py-2">
        <div class="mr-auto">
          <download-excel
            class="btn-icon"
            :data="statsIndicators"
            :fields="tableFields"
            worksheet="Статистика"
            name="Показатели.xls"
          >
            <button
              class="btn-icon icon-28 icon-download border align-middle p-1"
              title="Скачать таблицу"
            ></button>
          </download-excel>
        </div>
      </div>
    </div>

    <div id="main-content" class="row">
      <div class="col-lg-9">
        <b-card v-if="isLoading">
          <b-skeleton animation="fade" width="35%"></b-skeleton>
          <b-skeleton animation="fade" width="65%"></b-skeleton>
          <b-skeleton animation="fade" width="35%"></b-skeleton>
          <b-skeleton animation="fade" width="65%"></b-skeleton>
        </b-card>

        <item-page-stats-table v-else :items="statsIndicators" />
      </div>
    </div>
  </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import ItemPageStatsTable from '@/components/ItemPageStatsTable';

export default {
  name: 'Stats',
  components: {
    ItemPageStatsTable,
  },
  created() {
    if (!this.settings) {
      this.getSettingsList();
    }

    if (!this.themesList) {
      this.getDirectories();
    }

    if (!this.statsIndicators.length) {
      this.getStatsIndicators();
    }
  },
  data: () => ({
    tableFields: {
      'Показатель': 'name',
      'Факт': 'plan',
      'Дата': 'date',
      'Автор': 'author',
      'Комментарий': 'comment',
    },
  }),
  computed: {
    ...mapGetters([
      'isLoading',
      'settings',
      'statsIndicators',
      'themesList',
    ]),
  },
  methods: {
    ...mapActions([
      'getSettingsList',
      'getDirectories',
      'getStatsIndicators',
    ]),
  },
};
</script>
