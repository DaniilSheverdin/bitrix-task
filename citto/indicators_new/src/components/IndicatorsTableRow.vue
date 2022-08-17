<template>
  <div
    class="mb-2"
    :class="{ 'order-first': isPinned }"
  >
    <div class="d-flex flex-column rounded" :class="colorClassByPercent">
      <div class="d-flex align-items-center px-2 py-1 bg-dark-10 rounded-top">
        <h5 v-if="item.program === 0" class="m-0 text-truncate">
          <a href="#" class="text-body">Без программы</a>
        </h5>

        <h5 v-else-if="item.program_info" class="m-0 text-truncate">
          <router-link
            :to="{ name: 'program', params: { id: item.program } }"
            class="text-body"
          >{{ item.program_info.name }}</router-link>
        </h5>

        <h5 v-else class="m-0 text-truncate">
          Загружается...
        </h5>

        <button
          class="btn-icon icon-16 icon-pin ml-auto"
          title="Закрепить/Открепить"
          :class="{ active: isPinned }"
          @click="togglePin(item.id)"
        ></button>
      </div>

      <div class="row no-gutters py-1">
        <div class="col-lg-3 px-2">
          <div class="d-flex align-items-center">
            <h3
              class="m-0 pr-3 mr-auto font-condensed text-truncate"
              type="button"
              :id="`popover-target-${item.id}`"
            >
              {{ item.short_name }}
            </h3>
            <b-popover
              :target="`popover-target-${item.id}`"
              triggers="hover focus"
              placement="bottom"
            >
              <p class="m-0" v-html="item.description || item.name"></p>
            </b-popover>

            <button
              v-if="item.percent"
              title="Динамика"
              class="d-block btn btn-icon icon-28 icon-chart-before my-0 p-1 rounded-0"
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

            <b-dropdown
              v-if="item.type !== 'stat'"
              toggle-tag="div"
              variant="link"
              size="sm"
              right
              no-caret
              class="format-dropdown-btn p-2"
            >
              <template #button-content>
                <button
                  class="btn-icon btn-dots px-1 font-size-larger py-0"
                  title="Формат вывода"
                  type="button"
                ></button>
              </template>
              <b-dropdown-item
                v-if="item.type === 'passport'"
                :href="`/lpa/?page=detail_view&id=${item.npa}`"
                target="_blank"
              >
                НПА
              </b-dropdown-item>
              <b-dropdown-item
                v-if="item.type === 'passport'"
                :href="`/workgroups/group/${item.project.id}/tasks/`"
                target="_blank"
              >План проекта</b-dropdown-item>
              <b-dropdown-header tag="h4">Формат вывода</b-dropdown-header>
              <b-dropdown-divider></b-dropdown-divider>
              <b-dropdown-form>
                <b-form-group>
                  <b-form-radio
                    class="text-nowrap"
                    name="output-format"
                    value="full"
                    v-model="cardViewMode"
                  >Полностью</b-form-radio>
                  <b-form-radio
                    class="text-nowrap"
                    name="output-format"
                    value="plan-fact"
                    v-model="cardViewMode"
                  >План&nbsp;|&nbsp;Факт</b-form-radio>
                  <b-form-radio
                    class="text-nowrap"
                    name="output-format"
                    value="percent"
                    v-model="cardViewMode"
                  >Процент</b-form-radio>
                </b-form-group>
              </b-dropdown-form>
            </b-dropdown>
          </div>

          <p v-if="item.department" class="font-size-smaller mb-3">
            <router-link
              :to="{name: 'department', params: { id: item.department }}"
              v-if="item.department_info"
            >{{ item.department_info.name }}</router-link>
          </p>
        </div>

        <div class="col-lg-6 px-2">
          <div v-if="item.type !== 'stat'" class="card-info row align-items-center text-center">
            <div
              class="col"
              v-if="viewMode === 'full' || viewMode === 'plan-fact'"
            >
              <h5 class="text-uppercase">План</h5>
              <p class="m-0 text-uppercase font-weight-bold text-nowrap">{{ item.plan || 0 }}</p>
            </div>

            <div
              class="col"
              v-if="viewMode === 'full' || viewMode === 'plan-fact'"
            >
              <h5 class="text-uppercase">Факт</h5>
              <p class="m-0 text-uppercase font-weight-bold text-nowrap">{{ item.fact || 0 }}</p>
            </div>

            <div
              class="col"
              v-if="viewMode === 'full' || viewMode === 'percent'"
            >
              <span class="display-4">{{ item.percent || 0 }}%</span>
            </div>
          </div>

          <div v-else class="card-info row align-items-center text-center">
            <div class="col">
              <h5 class="text-uppercase">Факт</h5>
              <p class="m-0 text-uppercase font-weight-bold text-nowrap">{{ item.plan || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="col-lg-3 px-2">
          <div class="font-size-smaller pb-1">
            <p v-if="item.author || item.date" class="mb-1 icon-refresh-before">
              <b v-if="item.date">{{ item.date }}</b> <span v-if="item.author">&minus; {{ item.author }}</span>
            </p>

            <p v-if="item.comment" class="mb-1 icon-comment-before">{{ item.comment }}</p>

            <p v-if="item.milestone" class="icon-checkpoint-before" title="Следующая веха">
              {{ item.milestone }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import indicatorsItemMixin from '@/mixins/indicators-item';
import IndicatorChart from '@/components/IndicatorChart';

export default {
  name: 'IndicatorsTableRow',
  components: {
    IndicatorChart,
  },
  mixins: [indicatorsItemMixin],
  props: {
    viewMode: String,
  },
  data() {
    return {
      cardViewMode: this.viewMode,
    };
  },
  watch: {
    cardViewMode(val) {
      this.$store.dispatch('setItemView', { id: this.item.id, view: val });
    },
  },
};
</script>
