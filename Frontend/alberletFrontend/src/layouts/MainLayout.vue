<template>
  <q-layout view="lHh Lpr lHh">
    <q-header elevated class="bg-teal-10">
      <q-toolbar>
        <div class="flex items-center cursor-pointer" @click="handleLogoClick">
          <img src="icons/M&B logo.png" alt="M&B logo" style="width: 150px">
          <q-toolbar-title class="text-bold q-ml-sm">
            M&B lakhatás
          </q-toolbar-title>
        </div>
      </q-toolbar>
    </q-header>

    <q-page-container>
      <router-view />
    </q-page-container>

    <q-footer class="bg-teal-10 text-white q-pa-md">
      <div class="row items-center justify-between container-maxWidth q-mx-auto">
        <div class="col-12 col-md-4 text-center text-md-left q-mb-md q-mb-md-none">
          <div class="text-h6 text-weight-bolder">M&B lakhatás</div>
          <div class="text-caption opacity-70">© 2026 Egyik jog sincs fenntartva</div>
        </div>

        <div class="col-12 col-md-4 text-center q-mb-md q-mb-md-none">
          <div class="row justify-center q-gutter-md">
            <a href="https://www.facebook.com/share/1Dco6Rb8wB/?mibextid=wwXIfr" target="_blank">
              <q-icon name="facebook" size="sm" class="cursor-pointer opacity-80 hover-opacity-100" />
            </a>
            <a href="https://www.instagram.com/mark.hvth?igsh=MTFoZnB4Ym16Z2VsMA%3D%3D&utm_source=qr" target="_blank">
              <q-icon name="camera_alt" size="sm" class="cursor-pointer opacity-80 hover-opacity-100" />
            </a>
            <a href="mailto:horvathmarkusz30@gmail.com">
              <q-icon name="mail" size="sm" class="cursor-pointer opacity-80 hover-opacity-100" />
            </a>
          </div>
        </div>

        <div class="col-12 col-md-4 text-center text-md-right">
          <div class="text-subtitle2">Ügyfélszolgálat</div>
          <div class="text-caption text-weight-light"><a href="tel:+36205003935">+36 20 500 3935</a></div>
        </div>
      </div>
    </q-footer>
  </q-layout>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/authStore';

const router = useRouter();
const auth = useAuthStore();

const handleLogoClick = () => {
  // 1. Töröljük az admin jogot a localStorage-ból
  localStorage.removeItem('isAdmin');
  
  // 2. Szólunk az authStore-nak is, hogy vége a dalnak
  if (auth.logout) {
    auth.logout();
  } else {
    auth.isLoggedIn = false;
  }

  // 3. Hazadobjuk a felhasználót
  router.push('/');

  // 4. (Opcionális) Frissítünk egyet az oldalon, hogy minden 
  // változó tutira kiürüljön a memóriából
  // window.location.reload(); 
};
</script>

<style scoped>
/* ... a stílusaid változatlanok ... */
.container-maxWidth {
  max-width: 1200px;
  width: 100%;
}
.opacity-70 { opacity: 0.7; }
.hover-opacity-100:hover {
  opacity: 1;
  transition: opacity 0.3s ease;
}
a { color: white; text-decoration: none; }
</style>