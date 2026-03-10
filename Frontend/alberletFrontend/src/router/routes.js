const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { 
        path: '', 
        name: 'home',
        component: () => import('pages/IndexPage.vue') 
      },
      { 
        path: 'alberlet/:id', 
        name: 'reszletek',
        component: () => import('pages/AlberletReszletek.vue') 
      }
    ]
  },

  // Mindig ez legyen az utolsó
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes
