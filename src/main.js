import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import App from './App.vue';
import EmployeeList from './pages/EmployeeList.vue';
import DeviceList from './pages/DeviceList.vue';
import Assignments from './pages/Assignments.vue';
import Accounts from './pages/Accounts.vue';


const routes = [
  { path: '/', component: EmployeeList },
  { path: '/devices', component: DeviceList },
  { path: '/assignments', component: Assignments },
  { path: '/accounts', component: Accounts }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

createApp(App).use(router).mount('#app');
