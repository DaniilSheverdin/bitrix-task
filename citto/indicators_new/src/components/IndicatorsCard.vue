<template>
  <div
    class="col-lg-6 col-xl-4 p-1"
    :class="{ 'order-first': isPinned }"
  >
    <div
      class="d-flex flex-column d-flex flex-column h-100 rounded"
      :class="colorClassByPercent"
    >
      <div class="d-flex align-items-center mb-1 px-2 py-1 bg-dark-10 rounded-top">
        <h5 v-if="item.type === 'stat'" class="m-0 text-truncate">
          <a href="#" class="text-body">Статистика</a>
        </h5>

        <h5 v-if="item.program_info" class="m-0 text-truncate">
          <router-link
            :to="{ name: 'program', params: { id: item.program } }"
            class="text-body"
          >{{ item.program_info.name }}</router-link>
        </h5>

        <button
          class="btn-icon icon-16 icon-pin ml-auto"
          :class="{ active: isPinned }"
          @click="togglePin(item.id)"
          title="Закрепить/Открепить"
        ></button>
      </div>

      <div class="d-flex flex-column px-2 flex-grow-1">
        <div class="d-flex align-items-center">
          <h3
            class="m-0 pr-3 mr-auto font-condensed text-truncate popover-tip"
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
            class="format-dropdown-btn p-1"
          >
            <template #button-content>
              <button
                class="btn-icon btn-dots font-size-larger py-0"
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

        <div class="d-flex flex-column flex-grow-1">
          <p v-if="item.department" class="font-size-smaller mb-2">
            <router-link
              :to="{name: 'department', params: { id: item.department }}"
              v-if="item.department_info"
            >{{ item.department_info.name }}</router-link>
          </p>

          <div class="d-flex flex-grow-1 overflow-hidden">
            <div class="card-info row align-items-center text-center flex-grow-1">
              <div
                class="col"
                v-if="item.type !== 'stat' && (viewMode === 'full' || viewMode === 'plan-fact')"
              >
                <h5 class="text-uppercase">План</h5>
                <p class="m-0 text-uppercase font-weight-bold text-nowrap">{{ item.plan || 0 }}</p>
              </div>

              <div
                class="col"
                v-if="item.type === 'stat' || (viewMode === 'full' || viewMode === 'plan-fact')"
              >
                <h5 class="text-uppercase">Факт</h5>
                <p class="m-0 text-uppercase font-weight-bold text-nowrap">{{ item.fact || 0 }}</p>
              </div>

              <div
                class="col"
                v-if="item.type !== 'stat' && (viewMode === 'full' || viewMode === 'percent')"
              >
                <span class="display-4">{{ item.percent || 0 }}%</span>
              </div>
            </div>
          </div>

          <div class="font-size-smaller">
            <p v-if="item.author || item.date" class="mb-1 icon-refresh-before">
              <b v-if="item.date">{{ item.date }}</b> <span v-if="item.author">&minus; {{ item.author }}</span>
            </p>

            <p v-if="item.comment" class="mb-1 icon-comment-before">{{ item.comment }}</p>

            <p v-if="item.milestone" class="mb-0 icon-checkpoint-before" title="Следующая веха">
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
  name: 'IndicatorsCard',
  components: { IndicatorChart },
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

<style>
.modal-chart .modal-md {
  max-width: 38rem;
}
</style>
