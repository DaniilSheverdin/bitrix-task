<template>
  <div>
    <div v-for="item in items" :key="item.name">
      <h2 class="font-weight-normal">
        <router-link
          :to="{ name: routeName, params: { id: item.id } }"
          class="text-body arr-after"
        >{{ item.name }}
        </router-link>
      </h2>

      <ul v-if="item.children">
        <li v-for="itemChild in item.children" :key="itemChild.id" class="mb-3">
          <router-link
            :to="{ name: routeName, params: { id: itemChild.id } }"
            class="arr-after"
          >
            {{ itemChild.name }}
          </router-link>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ItemsPageList',
  props: {
    items: {
      type: Array,
      required: true,
    },
    routeName: {
      type: String,
    },
  },
};
</script>
