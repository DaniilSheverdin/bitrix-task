<template>
  <header>
    <div class="container-fluid pt-3">
      <div class="row align-items-center">
        <div class="col-6">
          <router-link :to="{ name: 'main' }">
            <img
              src="../assets/img/logo.svg"
              width="348"
              height="40"
              class="img-fluid"
              alt="Логотип ЦИТ"
            />
          </router-link>
        </div>

        <div class="col-6 text-right">
          <router-link
            :to="{ name: 'fill' }"
            class="btn btn-outline-primary rounded-pill arr-after text-nowrap"
          >Заполнить</router-link>
        </div>
      </div>
    </div>

    <div class="my-2">
      <nav>
        <ul class="nav nav-text w-100 px-3 border-bottom">
          <li class="nav-item">
            <router-link
              class="nav-link text-uppercase"
              :to="{name: 'main'}"
              exact
              exact-active-class="active"
            >Сводка</router-link>
          </li>
          <li class="nav-item">
            <router-link
              class="nav-link text-uppercase"
              :to="{name: 'programs'}"
              active-class="active"
            >Программы</router-link>
          </li>
          <li class="nav-item">
            <router-link
              class="nav-link text-uppercase"
              :to="{name: 'departments'}"
              active-class="active"
            >Подразделения</router-link>
          </li>
          <li class="nav-item">
            <router-link
              class="nav-link text-uppercase"
              :to="{name: 'stats'}"
              active-class="active"
            >Статистика</router-link>
          </li>
          <li class="nav-item">
            <button
              class="form-control icon-search-black border-0 rounded-0 p-0 h-100"
              type="button"
              v-b-modal:searchModal
            ></button>

            <search-modal />
          </li>
        </ul>
      </nav>
    </div>
  </header>
</template>

<script>
import SearchModal from '@/components/SearchModal';

export default {
  name: 'Header',
  components: {
    SearchModal,
  },
};
</script>
