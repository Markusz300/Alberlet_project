const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      // --- PUBLIKUS OLDALAK ---
      { 
        path: '', 
        name: 'home',
        component: () => import('pages/IndexPage.vue') 
      },
      { 
        path: 'search', 
        name: 'search',
        component: () => import('pages/SearchPage.vue') 
      },
      { 
        path: 'create', 
        name: 'create',
        component: () => import('pages/CreatePage.vue') 
      },
      { 
        path: 'alberlet/:id', 
        name: 'reszletek',
        component: () => import('pages/AlberletReszletek.vue') 
      },

      // --- ADMIN / AUTH OLDALAK ---
      { 
        path: 'login', 
        name: 'login',
        component: () => import('pages/LoginView.vue') 
      },
      { 
  path: 'admin', 
  name: 'admin',
  component: () => import('pages/AdminView.vue'),
  beforeEnter: (to, from, next) => {
    const isAdmin = localStorage.getItem('isAdmin');
    
    // Csak akkor engedjük be, ha a tárolt érték konkrétan a "true" szöveg
    if (isAdmin === 'true') {
      next();
    } else {
      next('/login'); // Minden más esetben irány a login
    }
  }
},
{
  path: 'admin/edit/:id',
  name: 'edit',
  component: () => import('pages/EditAlberletView.vue'),
  beforeEnter: (to, from, next) => {
    if (localStorage.getItem('isAdmin') === 'true') {
      next();
    } else {
      next('/login');
    }
  }
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