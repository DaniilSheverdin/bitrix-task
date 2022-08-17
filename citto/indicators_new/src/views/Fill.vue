<template>
  <div class="container-fluid flex-grow-1 d-flex flex-column">
    <div id="main-content">
      <h1>Заполнение данных</h1>

      <h2 class="font-weight-normal">Отделы</h2>

      <b-form-select
        v-model="selectedDep"
        class="custom-select rounded-pill col-lg-4 mb-4"
      >
        <b-form-select-option :value="null" disabled>Выберите отдел</b-form-select-option>
        <template v-for="dep in departments">
          <template v-if="dep.parent === 0">
            <b-form-select-option-group :label="dep.name" :key="dep.id">
              <b-form-select-option
                v-for="depChild in dep.children"
                :key="depChild.id"
                :value="depChild.id"
              >{{ depChild.name }}</b-form-select-option>
            </b-form-select-option-group>
          </template>

          <template v-else>
            <b-form-select-option
              :key="dep.id"
              :value="dep.id"
            >{{ dep.name }}</b-form-select-option>
          </template>
        </template>
      </b-form-select>

      <form ref="fillForm" @submit.prevent="submitHandler">
        <div class="overflow-auto border rounded max-80vh mb-5">
          <div class="row no-gutters py-1 bg-dark-10 min-em-60">
            <div class="col-auto px-1"><span class="font-size-smaller font-weight-bold">№</span></div>
            <div class="col-3 px-1">
              <span class="font-size-smaller font-weight-bold">Показатель</span>
            </div>
            <div class="col-2 px-1 text-center">
              <span class="font-size-smaller font-weight-bold">Месячный план</span>
            </div>
            <div class="col-2 px-1 text-center">
              <span class="font-size-smaller font-weight-bold">Годовой план</span>
            </div>
            <div class="col-2 px-1 text-center">
              <span class="font-size-smaller font-weight-bold">Факт</span>
            </div>
            <div class="col-2 px-1 text-center">
              <span class="font-size-smaller font-weight-bold">%</span>
            </div>
          </div>

          <div v-if="!indicatorsByDep.length && !isLoading" class="col-auto">
            <p class="font-italic p-3" >Показатели отсутствуют либо еще не загружены</p>
          </div>

          <b-card v-else-if="isLoading">
            <b-skeleton animation="fade" width="35%"></b-skeleton>
            <b-skeleton animation="fade" width="65%"></b-skeleton>
            <b-skeleton animation="fade" width="35%"></b-skeleton>
            <b-skeleton animation="fade" width="65%"></b-skeleton>
          </b-card>

          <div v-else class="d-flex flex-column min-em-60">
            <fill-table-row
              v-for="(item, index) in indicatorsByDep"
              :key="item.id"
              :index="index"
              :item="item"
            />
          </div>
        </div>

        <div class="p-3 text-center">
          <button class="btn btn-primary font-size-larger font-weight-light rounded-pill">
            Сохранить
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex';
import FillTableRow from '@/components/FillTableRow';

export default {
  name: 'Fill',
  components: {
    FillTableRow,
  },
  data: () => ({
    selectedDep: null,
  }),
  created() {
    this.getDepartmentsWithSelectList();
  },
  watch: {
    selectedDep(val) {
      this.getIndicatorsByDep({ dep_id: val });
    },
  },
  computed: {
    ...mapGetters([
      'isLoading',
      'departmentsWithSelect',
      'indicatorsByDep',
    ]),
    departments() {
      if (this.departmentsWithSelect) {
        const indicatorsArray = Object.values(this.$store.getters.departmentsWithSelect);

        const parents = indicatorsArray.filter(d => d.parent === 0);

        // eslint-disable-next-line no-return-assign,no-param-reassign
        parents.map(p => p.children = indicatorsArray.filter(dep => dep.parent === p.id));

        return parents;
      }
    },
  },
  methods: {
    ...mapActions([
      'getDepartmentsWithSelectList',
      'getIndicatorsByDep',
      'setIndicators',
      'clearIndicatorsByDep',
    ]),
    submitHandler() {
      const formData = new FormData(this.$refs.fillForm);
      const data = {};
      // eslint-disable-next-line no-restricted-syntax
      for (const [key, val] of formData.entries()) {
        Object.assign(data, { [key]: val });
      }

      this.setIndicators({ formData }).then(() => {
        this.$fire({
          text: 'Показатели успешно сохранены',
          type: 'success',
        });
      });
    },
  },
  destroyed() {
    this.clearIndicatorsByDep();
  },
};
</script>
