const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),  // ← csak itt van a layout
    children: [
      { path: '', component: () => import('pages/IndexPage.vue') },  // főoldal / lista
      { path: 'alberlet/:id', component: () => import('pages/AlberletReszletek.vue') },  // részletek oldal
      // ide jöhetnek később más oldalak is, pl. feltöltés
    ]
  },

  // 404 catch all
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue'),
  }
]

export default routes
