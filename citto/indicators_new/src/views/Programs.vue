<template>
  <main class="d-flex flex-grow-1">
    <div class="container-fluid flex-grow-1 d-flex flex-column">
      <div id="main-content" class="mw-em-48 my-3">
        <items-page-list :items="programsTree" route-name="program" />
      </div>
    </div>
  </main>
</template>

<script>
import { mapGetters, mapActions } from 'vuex';
import ItemsPageList from '@/components/ItemsPageList';

export default {
  name: 'Programs',
  components: {
    ItemsPageList,
  },
  created() {
    if (this.programsTree) {
      return;
    }

    this.getDirectories();
  },
  computed: {
    ...mapGetters([
      'programsTree',
    ]),
  },
  methods: {
    ...mapActions([
      'getDirectories',
    ]),
  },
};
</script>
