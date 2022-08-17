<template>
  <div class="container-fluid flex-grow-1 d-flex flex-column">
    <div id="controls" v-if="department">
      <h4 v-if="department.parent" class="my-2 text-secondary">{{ department.parent.name }}</h4>

      <h1 class="mb-2">{{ department.name }}</h1>

      <div class="d-flex align-items-center py-2">
        <div class="mr-auto">
          <download-excel
            class="btn-icon"
            :data="indicators"
            :fields="tableFields"
            worksheet="Подразделение"
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
          <b-collapse visible id="parameters" class="my-3">
            <h4 class="arr-down-after-black arr-fix border-top pt-3" type="button" v-b-toggle.themes>Тематика</h4>
            <b-collapse visible id="themes" class="my-2">
              <div v-for="theme in themes" :key="theme.id">
                <b-form-checkbox
                  v-if="theme.items_length"
                  :id="`deptheme-${theme.id}`"
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

        <items-page-table :items="indicators" :settings="settings" />
      </div>
    </div>
  </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import ItemsPageTable from '@/components/ItemsPageTable';

export default {
  name: 'Department',
  components: {
    ItemsPageTable,
  },
  created() {
    this.getDepartment({ dep_id: this.$route.params.id });

    this.getIndicatorsListByParam({
      param_name: 'departments',
      param_id_value: this.$route.params.id,
    });

    if (this.themesList) {
      return;
    }

    this.getDirectories();
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
  computed: {
    ...mapGetters([
      'isLoading',
      'settings',
      'department',
      'themesList',
    ]),
    themes() {
      const indicatorsArray = Object.values(this.indicatorsNotFiltered);

      if (this.themesList) {
        return this.themesList.map(t => {
          t.items_length = indicatorsArray.filter(i => i.theme === t.id).length;
          return t;
        });
      }
    },
    indicatorsNotFiltered() {
      return Object.values(this.$store.getters.indicatorsByParam).flat();
    },
    indicators() {
      let indicatorsArray = this.$store.getters.indicatorsByParam;
      const show = this.showByExpiration;

      indicatorsArray = show === 'expired'
        ? indicatorsArray.filter(i => i.statuses.includes('expired'))
        : indicatorsArray;

      return indicatorsArray.filter(i => (this.selectedFilters.length > 0
        ? this.selectedFilters.includes(i.theme)
        : this.$store.getters.indicatorsByParam));
    },
  },
  methods: {
    ...mapActions([
      'getSettingsList',
      'getDepartment',
      'getDirectories',
      'getIndicatorsListByParam',
      'clearIndicatorsListByParam',
    ]),
  },
  destroyed() {
    this.clearIndicatorsListByParam();
  },
};
</script>

<style>
.icon-refresh-before,
.icon-comment-before,
.icon-checkpoint-before,
.icon-chart-before {
  padding-left: 1.5rem;
}
</style>
