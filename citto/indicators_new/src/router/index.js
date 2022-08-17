import Vue from 'vue';
import VueRouter from 'vue-router';
import Main from '../views/Main';

Vue.use(VueRouter);

const routes = [
  {
    path: '/',
    name: 'main',
    component: Main,
  },
  {
    path: '/programs',
    name: 'programs',
    component: () => import('../views/Programs'),
  },
  {
    path: '/programs/:id',
    name: 'program',
    component: () => import('../views/Program'),
  },
  {
    path: '/departments',
    name: 'departments',
    component: () => import('../views/Departments'),
  },
  {
    path: '/departments/:id',
    name: 'department',
    component: () => import('../views/Department'),
  },
  {
    path: '/stats',
    name: 'stats',
    component: () => import('../views/Stats'),
  },
  {
    path: '/search-results',
    name: 'search',
    component: () => import('../views/SearchResults'),
  },
  {
    path: '/fill',
    name: 'fill',
    component: () => import('../views/Fill'),
  },
];

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  routes,
});

export default router;
