<template>
  <q-page class="flex flex-center">
    <q-card style="width: 350px">
      <q-card-section class="bg-teal-10 text-white text-h6"
        >Dev Belépés</q-card-section
      >
      <q-card-section>
        <q-input v-model="username" label="Felhasználó" />
        <q-input
          v-model="password"
          label="Jelszó"
          type="password"
          class="q-mt-md"
        />
      </q-card-section>
      <q-card-actions align="right">
        <q-btn flat label="Belépés" color="primary" @click="handleLogin" />
      </q-card-actions>
    </q-card>
  </q-page>
</template>

<script setup>
import { ref } from "vue";
import { useAuthStore } from "../stores/authStore";
import { useRouter } from "vue-router";

const username = ref("");
const password = ref("");
const auth = useAuthStore();
const router = useRouter();

const handleLogin = () => {
  // Megpróbálunk belépni a store-on keresztül
  if (auth.login(username.value, password.value)) {
    // Sikeres belépés esetén irány az admin
    router.push("/admin");
  } else {
    // Használhatod a Quasar notify-t is az alert helyett, ha szebbet akarsz:
    // $q.notify({ color: 'negative', message: 'Hibás adatok!' })
    alert("Hibás adatok! (Próbáld: admin / adminpass)");
  }
};
</script>
