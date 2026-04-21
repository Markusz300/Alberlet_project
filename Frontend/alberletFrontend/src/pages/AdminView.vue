<template>
  <q-page class="q-pa-md">
    <div class="text-h4 q-mb-md text-weight-bold">Adminisztrációs felület</div>

    <q-tabs v-model="tab" class="text-teal" active-color="teal" indicator-color="teal" align="left">
      <q-tab name="pending" icon="hourglass_empty" label="Jóváhagyásra vár" />
      <q-tab name="active" icon="check_circle" label="Aktív hirdetések" />
    </q-tabs>

    <q-separator />

    <q-tab-panels v-model="tab" animated class="bg-grey-1">
      <q-tab-panel name="pending">
        <q-list bordered separator class="bg-white rounded-borders shadow-1">
          <q-item v-for="albi in pendingAlbis" :key="albi.id" class="q-py-md">
            <q-item-section avatar>
              <q-img :src="store.formatImageUrl(albi.kepek)" style="width: 80px; height: 60px"
                class="rounded-borders shadow-1" img-class="object-cover">
                <template v-slot:error>
                  <div class="absolute-full flex flex-center bg-grey-3 text-grey-7">Nincs kép</div>
                </template>
              </q-img>
            </q-item-section>

            <q-item-section>
              <q-item-label class="text-weight-bold text-subtitle1">{{ albi.cim }}</q-item-label>
              <q-item-label caption class="text-grey-8">{{ albi.ar }} Ft · {{ albi.meret }} m²</q-item-label>
            </q-item-section>

            <q-item-section side>
              <div class="row q-gutter-xs">
                <q-btn icon="check" color="green" round unelevated size="sm"
                  @click="setStatus(albi.id, 1)"><q-tooltip>Jóváhagyás</q-tooltip></q-btn>
                <q-btn icon="edit" color="blue" round unelevated size="sm"
                  @click="goToEdit(albi.id)"><q-tooltip>Szerkesztés</q-tooltip></q-btn>
                <q-btn icon="delete" color="red" round unelevated size="sm"
                  @click="deleteAlbi(albi.id)"><q-tooltip>Törlés</q-tooltip></q-btn>
              </div>
            </q-item-section>
          </q-item>
          <div v-if="!pendingAlbis.length" class="text-center q-pa-xl text-grey-6">Nincs jóváhagyásra váró hirdetés.
          </div>
        </q-list>
      </q-tab-panel>

      <q-tab-panel name="active">
        <q-list bordered separator class="bg-white rounded-borders shadow-1">
          <q-item v-for="albi in activeAlbis" :key="albi.id" class="q-py-md">
            <q-item-section avatar>
              <q-img :src="store.formatImageUrl(albi.kepek)" style="width: 80px; height: 60px"
                class="rounded-borders shadow-1">
                <template v-slot:error>
                  <div class="absolute-full flex flex-center bg-grey-3 text-grey-7">Nincs kép</div>
                </template>
              </q-img>
            </q-item-section>

            <q-item-section>
              <q-item-label class="text-weight-bold text-subtitle1">{{ albi.cim }}</q-item-label>
              <q-item-label caption class="text-teal text-weight-bold">{{ albi.ar }} Ft · Aktív</q-item-label>
            </q-item-section>

            <q-item-section side>
              <div class="row q-gutter-xs">
                <q-btn icon="edit" color="blue" round unelevated size="sm"
                  @click="goToEdit(albi.id)"><q-tooltip>Szerkesztés</q-tooltip></q-btn>
                <q-btn icon="block" color="orange" round unelevated size="sm"
                  @click="setStatus(albi.id, 0)"><q-tooltip>Hirdetés elrejtése</q-tooltip></q-btn>
                <q-btn icon="delete" color="red" round unelevated size="sm"
                  @click="deleteAlbi(albi.id)"><q-tooltip>Törlés</q-tooltip></q-btn>
              </div>
            </q-item-section>
          </q-item>
          <div v-if="!activeAlbis.length" class="text-center q-pa-xl text-grey-6">Nincs aktív hirdetés a rendszerben.
          </div>
        </q-list>
      </q-tab-panel>
    </q-tab-panels>
  </q-page>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAlberletStore } from '../stores/alberletStore';
import { useQuasar } from 'quasar';

const store = useAlberletStore();
const router = useRouter();
const $q = useQuasar();
const tab = ref('pending');

// Szűrt listák - az ESLint ezeket szereti, mert a template-ben használjuk őket
const pendingAlbis = computed(() => store.alberletek.filter(a => a.aktiv == 0 || a.aktiv === false));
const activeAlbis = computed(() => store.alberletek.filter(a => a.aktiv == 1 || a.aktiv === true));

const setStatus = async (id, status) => {
  try {
    const success = await store.updateStatus(id, status);
    if (success) {
      await store.fetchAllForAdmin(); // Mindig az adminos lekérést hívjuk újra!
      $q.notify({
        color: status === 1 ? 'positive' : 'warning',
        message: status === 1 ? 'Hirdetés aktiválva!' : 'Hirdetés elrejtve!'
      });
    }
  } catch (err) {
    console.error(err);
  }
};

const deleteAlbi = (id) => {
  // Ellenőrizzük, hogy a $q létezik-e és van-e rajta dialog
  if (!$q.dialog) {
    console.error("A Quasar Dialog plugin nincs telepítve!");
    // Ha sürgős és nem megy a plugin, használd a sima böngészős confirm-ot:
    if (confirm("Biztosan törölni akarod?")) {
      performDelete(id);
    }
    return;
  }

  $q.dialog({
    title: 'Megerősítés',
    message: 'Biztosan véglegesen törölni akarod ezt a hirdetést?',
    cancel: { color: 'grey-7', flat: true, label: 'Mégse' },
    ok: { color: 'red', label: 'Törlés' },
    persistent: true
  }).onOk(() => {
    performDelete(id);
  });
};

// Külön szedtem a tényleges törlést, hogy átláthatóbb legyen
const performDelete = async (id) => {
  try {
    const success = await store.deleteAlberlet(id);
    if (success) {
      $q.notify({ color: 'positive', message: 'Hirdetés törölve!' });
      await store.fetchAllForAdmin();
    }
  } catch (err) {
    console.error("Törlési hiba:", err);
  }
};

const goToEdit = (id) => {
  router.push(`/admin/edit/${id}`);
};

onMounted(async () => {
  await store.fetchAllForAdmin();
});
</script>
