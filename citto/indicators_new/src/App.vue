<template>
  <div id="app" class="d-flex flex-column">
    <main-header />

    <router-view/>
  </div>
</template>

<script>
import { mapActions } from 'vuex';
import MainHeader from '@/components/MainHeader';

export default {
  name: 'App',
  components: {
    MainHeader,
  },
  created() {
    this.getDirectories();
    this.getSettingsList();
  },
  mounted() {
    setTimeout(() => this.getIndicatorsList(), 500);
  },
  methods: {
    ...mapActions([
      'getDirectories',
      'getSettingsList',
      'getIndicatorsList',
    ]),
  },
};
</script>

<style>
#app {
  display: flex;
  flex-direction: column;
  height: 100%;
}
</style>
