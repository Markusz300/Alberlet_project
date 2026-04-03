const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      // 1. A legelső választó oldal (a zöld háttérrel és a két kártyával)
      { 
        path: '', 
        name: 'home',
        component: () => import('pages/IndexPage.vue') 
      },
      
      // 2. Az albérlet kereső oldal (a régi főoldalad, amit átneveztél SearchPage.vue-ra)
      { 
        path: 'search', 
        name: 'search',
        component: () => import('pages/SearchPage.vue') 
      },

      // 3. Az új hirdetés feltöltése oldal
      { 
        path: 'create', 
        name: 'create',
        component: () => import('pages/CreatePage.vue') 
      },

      // 4. A részletek oldal (az ID alapján)
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
