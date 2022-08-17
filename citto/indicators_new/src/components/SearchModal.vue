<template>
  <b-modal
    id="searchModal"
    centered
    title="Поиск"
    title-tag="h3"
    size="lg"
    ok-title="Искать"
    cancel-title="Отмена"
    cancel-variant="outline-primary"
    @ok.prevent="submitSearch"
  >
    <form @submit.prevent="submitSearch">
      <div class="input-group input-group-lg">
        <input
          v-model.trim="searchStr"
          type="text"
          class="form-control"
          aria-describedby="searchIcon"
        >
        <div class="search-btn-wrapper input-group-append">
          <button class="search-btn form-control icon-search-black rounded-0"></button>
        </div>
      </div>
    </form>
  </b-modal>
</template>

<script>
import { mapActions } from 'vuex';

export default {
  name: 'SearchModal',
  data: () => ({
    searchStr: '',
  }),
  methods: {
    ...mapActions([
      'getSearchedIndicators',
    ]),
    submitSearch() {
      this.$nextTick(() => {
        this.$bvModal.hide('searchModal');
      });

      const { path } = this.$route;

      return path === '/search-results'
        ? this.getSearchedIndicators({ search_str: this.searchStr })
        : this.$router.push({ name: 'search', query: { 'search': this.searchStr } });
    },
  },
};
</script>

<style>
#searchModal .btn {
  margin: 0.25rem;
  border-radius: 50rem;
}

.search-btn-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
}

.search-btn-wrapper .search-btn {
  height: 100%;
  padding: 0;
  border: 1px solid #ced4da;
  border-left: 0;
}
</style>
