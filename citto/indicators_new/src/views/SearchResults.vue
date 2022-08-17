<template>
  <div class="container-fluid flex-grow-1 d-flex flex-column">
    <div id="controls">
      <div class="d-flex align-items-center py-2">
        <div class="mr-auto">
          <download-excel
            class="btn-icon"
            :data="searchedIndicators"
            :fields="tableFields"
            worksheet="Программа"
            name="Показатели.xls"
          >
            <button
              class="btn-icon icon-28 icon-download border align-middle p-1"
              title="Скачать таблицу"
            ></button>
          </download-excel>
        </div>
        <div class="d-flex align-items-center">
          <select
            class="custom-select mr-1 py-1 pl-2 align-middle rounded-pill"
            v-model="showByExpiration"
          >
            <option value="all">Все</option>
            <option value="expired">Просроченные</option>
          </select>
        </div>
      </div>
    </div>

    <div id="main-content" class="row flex-row-reverse">
      <div class="col-lg-3">
        <div class="mb-2 p-3 border rounded">
          <h3 class="arr-down-after-black arr-fix mb-0" v-b-toggle.parameters type="button">Параметры</h3>
          <b-collapse id="parameters" class="my-3">
            <h4 class="arr-down-after-black arr-fix border-top pt-3" type="button" v-b-toggle.themes>Тематика</h4>
            <b-collapse id="themes" class="my-2">
              <div v-for="theme in themes" :key="theme.id">
                <b-form-checkbox
                  v-if="theme.items_length"
                  :id="`progtheme-${theme.id}`"
                  :value="theme.id"
                  v-model="selectedFilters"
                >
                  {{ theme.name }} ({{ theme.items_length }})
                </b-form-checkbox>
              </div>
            </b-collapse>
          </b-collapse>
        </div>
      </div>

      <div class="col-lg-9">
        <b-card v-if="isLoading">
          <b-skeleton animation="fade" width="35%"></b-skeleton>
          <b-skeleton animation="fade" width="65%"></b-skeleton>
          <b-skeleton animation="fade" width="35%"></b-skeleton>
          <b-skeleton animation="fade" width="65%"></b-skeleton>
        </b-card>

        <h2 v-else-if="!indicators.length">Ничего не найдено</h2>

        <items-page-table v-else :items="indicators" />
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex';
import ItemsPageTable from '@/components/ItemsPageTable';

export default {
  name: 'SearchResults',
  components: {
    ItemsPageTable,
  },
  data: () => ({
    selectedFilters: [],
    showByExpiration: 'all',
    tableFields: {
      'Показатель': 'name',
      'План': 'plan',
      'Факт': 'fact',
      'Процент': 'percent',
      'Дата': 'date',
      'Автор': 'author',
      'Комментарий': 'comment',
    },
  }),
  created() {
    this.getSearchedIndicators({ search_str: this.$route.query.search });

    if (!this.themesList) {
      return;
    }

    this.getDirectories();
  },
  computed: {
    ...mapGetters([
      'settings',
      'isLoading',
      'searchedIndicators',
      'themesList',
    ]),
    themes() {
      const indicatorsArray = Object.values(this.searchedIndicators);

      if (this.themesList) {
        return this.themesList.map(t => {
          t.items_length = indicatorsArray.filter(i => i.theme === t.id).length;
          return t;
        });
      }
    },
    indicators() {
      let indicatorsArray = this.searchedIndicators;
      const show = this.showByExpiration;

      indicatorsArray = show === 'expired'
        ? indicatorsArray.filter(i => i.statuses.includes('expired'))
        : indicatorsArray;

      return indicatorsArray.filter(i => (this.selectedFilters.length > 0
        ? this.selectedFilters.includes(i.theme)
        : this.searchedIndicators));
    },
  },
  methods: {
    ...mapActions([
      'getSearchedIndicators',
      'getDirectories',
      'getSettingsList',
      'clearSearchedIndicators',
    ]),
  },
  destroyed() {
    this.clearSearchedIndicators();
  },
};
</script>
