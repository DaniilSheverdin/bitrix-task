<template>
  <div class="d-flex flex-grow-1 flex-column">
    <main v-if="programsList" class="h-100">
      <div class="container-fluid h-100 d-flex flex-column flex-grow-1">
        <div class="d-flex flex-wrap align-items-center" id="controls">
          <div class="mr-auto py-2 text-nowrap">
            <span class="mr-2">
              <button
                class="btn-icon icon-28 icon-view-cards border align-middle p-1"
                :class="{ active: currentViewMode === 'cards' }"
                title="Карточки"
                @click="switchToCards"
              ></button>
              <button
                class="btn-icon icon-28 icon-view-table border align-middle p-1 ml-1"
                :class="{ active: currentViewMode === 'table' }"
                title="Таблица"
                @click="switchToTable"
              ></button>
            </span>

            <span class="mx-2">
              <button
                class="btn-icon icon-28 icon-font-reduce border align-middle p-1"
                :class="{ active: !biggerFontSize }"
                title="Уменьшить масштаб"
                @click="biggerFontSize = false"
              ></button>

              <button
                class="btn-icon icon-28 icon-font-enlarge border align-middle p-1 ml-1"
                :class="{ active: biggerFontSize }"
                title="Увеличить масштаб"
                @click="biggerFontSize = true"
              ></button>
            </span>

            <download-excel
              class="btn-icon mx-2"
              :data="indicators.flat()"
              :fields="tableFields"
              worksheet="Сводка"
              name="Показатели.xls"
            >
              <button
                class="btn-icon icon-28 icon-download border align-middle p-1"
                title="Скачать таблицу"
              ></button>
            </download-excel>

            <span class="d-inline-flex align-items-center pr-2 border rounded-pill align-middle ml-2">
              <label
                class="btn-icon icon-28 icon-timer m-0 p-1"
                for="timer-val"
                title="Время смены слайдов"
              ></label>
              <input
                type="tel"
                size="4"
                id="timer-val"
                class="position-relative z-1 p-0 pr-4 border-0 bg-transparent text-center"
                v-model="carouselInterval"
              />
              <span class="position-absolute right-0 mr-2 z-0 text-secondary">сек</span>
            </span>
          </div>

          <div class="d-flex align-items-center">
            <select
              class="custom-select mr-1 py-1 pl-2 align-middle rounded-pill"
              v-model="showByExpiration"
            >
              <option value="all">Все</option>
              <option value="expired">Просроченные</option>
            </select>

            <filters-modal />

            <b-dropdown
              toggle-tag="div"
              variant="link"
              size="sm"
              right
              no-caret
              class="format-dropdown-btn p-1"
            >
              <template #button-content>
                <button
                  class="btn-icon btn-dots font-size-larger py-0"
                  title="Формат вывода"
                  type="button"
                ></button>
              </template>
              <b-dropdown-header tag="h4">Формат вывода</b-dropdown-header>
              <b-dropdown-form>
                <b-form-group>
                  <b-form-radio
                    name="some-radios"
                    value="user"
                    v-model="allCardsViewMode"
                  >Пользовательский</b-form-radio>
                  <b-form-radio
                    name="some-radios"
                    value="full"
                    v-model="allCardsViewMode"
                  >Полностью</b-form-radio>
                  <b-form-radio
                    name="some-radios"
                    value="plan-fact"
                    v-model="allCardsViewMode"
                  >План | Факт</b-form-radio>
                  <b-form-radio
                    name="some-radios"
                    value="percent"
                    v-model="allCardsViewMode"
                  >Процент</b-form-radio>
                </b-form-group>
              </b-dropdown-form>
            </b-dropdown>
          </div>
        </div>

        <div
          v-if="isLoading"
          class="d-flex flex-column justify-content-center flex-grow-1"
        >
          <indicators-cards-preloader />
        </div>

        <div v-else-if="!indicators.length" class="row">
          <indicators-cards-no-items />
        </div>

        <keep-alive
          v-else
          include="IndicatorsTable"
        >
          <component
            :is="viewMode"
            :items="indicators"
            :carouselIntervalInMs="carouselIntervalInMs"
            :font-size="fontSize"
          />
        </keep-alive>
      </div>
    </main>
  </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import FiltersModal from '@/components/FiltersModal';
import IndicatorsCardsPreloader from '@/components/IndicatorsCardsPreloader';
import IndicatorsCardsNoItems from '@/components/IndicatorsCardsNoItems';
import IndicatorsCards from '@/components/IndicatorsCards';
import IndicatorsTable from '@/components/IndicatorsTable';

export default {
  name: 'Home',
  components: {
    FiltersModal,
    IndicatorsCardsPreloader,
    IndicatorsCardsNoItems,
    IndicatorsCards,
    IndicatorsTable,
  },
  mounted() {
    if (this.indicators) {
      return;
    }

    this.getIndicatorsList();
  },
  data: () => ({
    viewMode: IndicatorsCards,
    biggerFontSize: false,
    carouselInterval: 30,
    showByExpiration: 'all',
    allCardsViewModeVal: '',
    tableFields: {
      'Программа': 'program_info.name',
      'Название': 'name',
      'Группа': 'department_info.name',
      'План': 'plan',
      'Факт': 'fact',
      'Процент': 'percent',
      'Дата': 'date',
      'Автор': 'author',
      'Комментарий': 'comment',
    },
  }),
  watch: {
    allCardsViewModeVal(val) {
      this.setItemView({ id: 'all', view: val });
    },
  },
  computed: {
    ...mapGetters([
      'isLoading',
      'programsList',
      'departmentsList',
      'themesList',
      'settings',
    ]),
    allCardsViewMode: {
      get() {
        return this.settings ? this.settings.view.all : 'user';
      },
      set(v) {
        this.allCardsViewModeVal = v;
      },
    },
    fontSize() {
      return this.biggerFontSize ? 'font-size: 125%' : '';
    },
    currentViewMode() {
      return this.viewMode === IndicatorsCards ? 'cards' : 'table';
    },
    carouselIntervalInMs() {
      return this.carouselInterval * 1000;
    },
    indicators() {
      const indicatorsArray = this.$store.getters.indicators;
      const show = this.showByExpiration;
      const isShown = (department, program, theme) => {
        const { departments, programs, themes } = this.settings.show;

        if (!departments.length && !programs.length && !themes.length) {
          return true;
        }

        return departments.includes(department)
          || programs.includes(program)
          || themes.includes(theme);
      };

      const isPinned = id => Object.values(this.settings.pinned).includes(id);

      let shownIndicators = indicatorsArray
        .map(item => {
          item.program_info = this.programsList.find(program => program.id === item.program);
          item.department_info = this.departmentsList.find(dep => dep.id === item.department);
          item.is_pinned = isPinned(item.id);

          return item;
        })
        .filter(item => isShown(item.department, item.program, item.theme) || item.is_pinned);

      shownIndicators = show === 'expired'
        ? shownIndicators.filter(i => i.statuses.includes('expired'))
        : shownIndicators;

      return shownIndicators;
    },
  },
  methods: {
    ...mapActions([
      'getIndicatorsList',
      'setItemView',
      'getDirectories',
    ]),
    switchToCards() {
      if (this.viewMode === IndicatorsCards) {
        return false;
      }

      this.viewMode = IndicatorsCards;
    },
    switchToTable() {
      if (this.viewMode === IndicatorsTable) {
        return false;
      }

      this.viewMode = IndicatorsTable;
    },
  },
};
</script>

<style>
.format-dropdown-btn .btn {
  padding: 0;
}

.icon-chart-before {
  background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="%23666"><line stroke="%2319A0E6" stroke-width="1.6" x1="1" y1="9" x2="12.5" y2="1"/><polygon fill="%2319A0E6" points="14,0 12.3,4.5 9.5,0"/><rect x="1.5" y="11" width="3" height="3"/><rect x="6.5" y="8" width="3" height="6"/><rect x="11.5" y="6" width="3" height="8"/></svg>');
  background-repeat: no-repeat;
}

.icon-chart-before::before {
  display: none !important;
}
</style>
