<template>
  <div>
    <button
      class="btn-icon icon-28 icon-options align-middle p-1"
      title="Настроить"
      v-b-modal:chooseIndicatorsModal
    ></button>

    <b-modal
      id="chooseIndicatorsModal"
      centered
      title="Выберите показатели для отображения"
      title-tag="h3"
      size="lg"
      ok-title="Добавить"
      cancel-title="Отмена"
      cancel-variant="outline-primary"
      @ok.prevent="handleOk"
    >
      <form>
        <template>
          <div class="accordion rounded" role="tablist">
            <b-card no-body class="overflow-hidden">
              <b-card-header header-tag="header" role="tab">
                <h4 class="mb-0 arr-down-after-black arr-fix" v-b-toggle.accordion-1>
                  Программы
                  <span
                    v-if="selectedPrograms.length"
                    class="badge badge-primary badge-pill">{{ selectedPrograms.length }}</span>
                </h4>
              </b-card-header>
              <b-collapse id="accordion-1" accordion="filter" role="tabpanel">
                <b-card-body>
                  <div v-for="program in programsTree" :key="program.id">
                    <div v-if="program.children.length">
                      <h4>{{ program.name }}</h4>
                      <div
                        v-for="progChild in program.children"
                        class="custom-control custom-checkbox mb-3"
                        :key="progChild.id"
                      >
                        <input
                          class="custom-control-input"
                          type="checkbox"
                          :id="`program-${progChild.id}`"
                          :value="progChild.id"
                          v-model="selectedPrograms"
                        />
                        <label
                          class="custom-control-label pt-1"
                          :for="`program-${progChild.id}`"
                        >{{ progChild.name }}</label
                        >
                      </div>
                    </div>

                    <div v-else class="custom-control custom-checkbox mb-3">
                      <input
                        class="custom-control-input"
                        type="checkbox"
                        :id="`program-${program.id}`"
                        :value="program.id"
                        v-model="selectedPrograms"
                      />
                      <label
                        class="custom-control-label pt-1"
                        :for="`program-${program.id}`"
                      >{{ program.name }}</label>
                    </div>
                  </div>
                </b-card-body>
              </b-collapse>
            </b-card>

            <b-card no-body class="overflow-hidden">
              <b-card-header header-tag="header" role="tab">
                <h4 class="mb-0 arr-down-after-black arr-fix" v-b-toggle.accordion-2>
                  Подразделения
                  <span
                    v-if="selectedDepartments.length"
                    class="badge badge-primary badge-pill">{{ selectedDepartments.length }}</span>
                </h4>
              </b-card-header>
              <b-collapse id="accordion-2" accordion="filter" role="tabpanel">
                <b-card-body>
                  <div v-for="dep in departmentsTree" :key="dep.id">
                    <div v-if="dep.children.length">
                      <div class="custom-control custom-checkbox">
                        <input
                          class="custom-control-input"
                          type="checkbox"
                          :id="`dep-${dep.id}`"
                          :value="dep.id"
                          v-model="selectedDepartments"
                        />
                        <label
                          class="custom-control-label pt-1 h4"
                          :for="`dep-${dep.id}`"
                        >{{ dep.name }}</label>
                      </div>

                      <div
                        v-for="depChild in dep.children"
                        class="custom-control custom-checkbox mb-3 pl-5"
                        :key="depChild.id"
                      >
                        <input
                          class="custom-control-input"
                          type="checkbox"
                          :id="`dep-${depChild.id}`"
                          :value="depChild.id"
                          v-model="selectedDepartments"
                        />
                        <label
                          class="custom-control-label pt-1"
                          :for="`dep-${depChild.id}`"
                        >{{ depChild.name }}</label>
                      </div>
                    </div>

                    <div v-else class="custom-control custom-checkbox mb-3">
                      <input
                        class="custom-control-input"
                        type="checkbox"
                        :id="`dep-${dep.id}`"
                        :value="dep.id"
                        v-model="selectedDepartments"
                      />
                      <label
                        class="custom-control-label pt-1"
                        :for="`dep-${dep.id}`"
                      >{{ dep.name }}</label>
                    </div>
                  </div>
                </b-card-body>
              </b-collapse>
            </b-card>

            <b-card no-body class="overflow-hidden">
              <b-card-header header-tag="header" role="tab">
                <h4 class="mb-0 arr-down-after-black arr-fix" v-b-toggle.accordion-3>
                  Тематика
                  <span
                    v-if="selectedThemes.length"
                    class="badge badge-primary badge-pill">{{ selectedThemes.length }}</span>
                </h4>
              </b-card-header>

              <b-collapse id="accordion-3" accordion="filter" role="tabpanel">
                <b-card-body>
                  <div class="form-check">
                    <div
                      v-for="theme in themesList"
                      :key="theme.id"
                      class="custom-control custom-checkbox mb-3"
                    >
                      <input
                        class="custom-control-input"
                        type="checkbox"
                        :id="`theme-${theme.id}`"
                        :value="theme.id"
                        v-model="selectedThemes"
                      />
                      <label
                        class="custom-control-label pt-1"
                        :for="`theme-${theme.id}`"
                      >{{ theme.name }}</label>
                    </div>
                  </div>
                </b-card-body>
              </b-collapse>
            </b-card>
          </div>
        </template>
      </form>
    </b-modal>
  </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex';

export default {
  name: 'FiltersModal',
  data: () => ({
    selectedPrograms: [],
    selectedDepartments: [],
    selectedThemes: [],
  }),
  mounted() {
    setTimeout(() => {
      if (this.checkedFilters.programs) {
        this.selectedPrograms = this.checkedFilters.programs;
      }

      if (this.checkedFilters.departments) {
        this.selectedDepartments = this.checkedFilters.departments;
      }

      if (this.checkedFilters.themes) {
        this.selectedThemes = this.checkedFilters.themes;
      }
    }, 1200);
  },
  computed: {
    ...mapGetters([
      'settings',
      'programsTree',
      'departmentsTree',
      'themesList',
    ]),
    checkedFilters() {
      if (this.settings) {
        return this.settings.show;
      }
      return [];
    },
  },
  methods: {
    ...mapActions([
      'getIndicatorsList',
      'setFilters',
    ]),
    handleOk() {
      this.submitFilters();
    },
    submitFilters() {
      this.setFilters({
        programs: this.selectedPrograms,
        departments: this.selectedDepartments,
        themes: this.selectedThemes,
      });

      this.$nextTick(() => {
        this.$bvModal.hide('chooseIndicatorsModal');
      });

      this.getIndicatorsList();
    },
  },
};
</script>

<style>
#chooseIndicatorsModal .btn {
  margin: 0.25rem;
  border-radius: 50rem;
}
</style>
