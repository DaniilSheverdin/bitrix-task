<template>
  <main class="d-flex flex-grow-1">
    <div class="container-fluid flex-grow-1 d-flex flex-column">
      <div id="main-content" class="mw-em-48 my-3">
        <items-page-list
          v-if="departmentsTree"
          :items="departmentsTree"
          route-name="department"
        />
      </div>
    </div>
  </main>
</template>

<script>
import { mapGetters, mapActions } from 'vuex';
import ItemsPageList from '@/components/ItemsPageList';

export default {
  name: 'Departments',
  components: { ItemsPageList },
  created() {
    if (this.departmentsTree) {
      return;
    }
    this.getDirectories();
  },
  computed: {
    ...mapGetters([
      'departmentsTree',
    ]),
  },
  methods: {
    ...mapActions([
      'getDirectories',
    ]),
  },
};
</script>
