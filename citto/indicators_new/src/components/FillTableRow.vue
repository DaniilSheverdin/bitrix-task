<template>
  <div
    class="row no-gutters py-3 border-top"
    :class="{
      'alert-danger': percentExec < 30,
      'alert-warning': percentExec >= 30 && percentExec < 80,
      'alert-success': percentExec >= 80,
    }"
  >
    <input
      type="hidden"
      :name="`INDICATORS[${item.xml_id}][id]`"
      :value="item.id"
    >
    <input
      type="hidden"
      :name="`INDICATORS[${item.xml_id}][bi_id]`"
      :value="item.xml_id"
    >
    <input
      type="hidden"
      :name="`INDICATORS[${item.xml_id}][full_name]`"
      :value="item.name"
    >
    <input
      type="hidden"
      :name="`INDICATORS[${item.xml_id}][short_name]`"
      :value="item.short_name"
    >
    <input
      type="hidden"
      :name="`INDICATORS[${item.xml_id}][target_value]`"
      :value="item.plan"
    >
    <input
      type="hidden"
      :name="`INDICATORS[${item.xml_id}][monthly_target_value]`"
      :value="item.month_plan"
    >
    <input
      type="hidden"
      :name="`INDICATORS[${item.xml_id}][target_value_min]`"
      :value="item.plan_min"
    >
    <input
      type="hidden"
      :name="`INDICATORS[${item.xml_id}][percent_exec]`"
      :value="percentExec"
    >

    <div class="col-auto px-1">
      <span>{{ index + 1 }}.</span>
    </div>
    <div class="col-3 px-1">
      <h4
        class="font-condensed text-truncate popover-tip"
        type="button"
        :id="`popover-target-${item.id}`"
        v-html="item.name"
      ></h4>
      <b-popover
        :target="`popover-target-${item.id}`"
        triggers="hover focus"
        placement="top"
      >
        <p class="m-0" v-html="item.description || item.name"></p>
      </b-popover>

      <textarea
        class="form-control"
        rows="3"
        :name="`INDICATORS[${item.xml_id}][comment]`"
        v-model.trim="comment"
      ></textarea>
    </div>

    <div class="col-8">
      <div class="row no-gutters align-items-center text-center">
        <div class="col-3 px-1">
          <p class="m-0 text-uppercase">{{ item.month_plan || '-' }}</p>
        </div>
        <div class="col-3 px-1">
          <p class="m-0 text-uppercase">{{ itemPlan }}</p>
        </div>
        <div class="col-3 px-1">
          <p class="m-0 text-uppercase">
            <input
              type="tel"
              class="form-control rounded-pill"
              placeholder="Введите значение"
              v-model="fact"
              :name="`INDICATORS[${item.xml_id}][state_value]`"
            />
          </p>
        </div>
        <div class="col-3 px-1">
          <p class="m-0 text-uppercase">{{ percentExec }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FillTableRow',
  props: {
    item: {
      type: Object,
      required: false,
    },
    index: {
      type: Number,
      required: false,
    },
  },
  data() {
    return {
      fact: this.item.fact,
      comment: this.item.comment,
    };
  },
  watch: {
    fact(v) {
      this.fact = v.replace(/[^0-9.,]/gi, '');
    },
  },
  computed: {
    itemPlan() {
      if (!this.item.plan) {
        return '';
      }

      if (this.item.min_plan && this.item.plan) {
        return `${this.item.min_plan}-${this.item.plan}`;
      }

      return this.item.plan;
    },
    percentExec() {
      const { percent } = this.item;

      const fact = this.fact.replace(/ /g, '').replace(/,/g, '.');
      const plan = this.item.plan.replace(/ /g, '').replace(/,/g, '.');
      const minPlan = this.item.min_plan
        ? this.item.min_plan.replace(/ /g, '').replace(/,/g, '.')
        : '';
      const monthPlan = this.item.month_plan
        ? this.item.month_plan.replace(/ /g, '').replace(/,/g, '.')
        : '';
      const currentMonth = new Date().getMonth() + 1;

      if (this.item.type === 'passport') {
        return percent;
      }

      if (this.item.type === 'stat') {
        return '-';
      }

      if (!this.item.plan || !this.fact) {
        return 0;
      }

      if (monthPlan) {
        return Math.floor((parseFloat(fact) / (parseFloat(monthPlan) * currentMonth)) * 100);
      }

      if (minPlan) {
        if (fact < minPlan) {
          return Math.floor((parseFloat(fact) / parseFloat(minPlan)) * 100);
        }

        if (fact >= minPlan && fact <= plan) {
          return 100;
        }

        if (fact > plan) {
          return Math.floor((parseFloat(plan) / parseFloat(fact)) * 100);
        }
      }

      return Math.floor((parseFloat(fact) / parseFloat(plan)) * 100);
    },
  },
};
</script>
