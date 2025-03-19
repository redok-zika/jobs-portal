import { createRouter, createWebHistory } from 'vue-router'
import JobList from './components/JobList.vue'
import JobDetail from './components/JobDetail.vue'

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'job-list',
      component: JobList
    },
    {
      path: '/jobs/:id',
      name: 'job-detail',
      component: JobDetail
    }
  ]
})
