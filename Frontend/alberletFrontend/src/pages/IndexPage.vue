<template>
  <q-page class="bg-grey-1 flex flex-center q-pa-lg">
    <div class="full-width text-center" style="max-width: 1100px;">

      <header class="q-mb-xl">
        <img src="icons/M&B logo.png" class="app-logo" alt="M&B Logo">
        <div class="text-subtitle1 text-grey-7 q-mt-sm font-premium">Prémium Ingatlankezelés</div>
      </header>

      <div class="row q-col-gutter-xl justify-center">

        <div class="col-12 col-sm-6">
          <div class="karty-alap vilagos-kartya" @click="$router.push('/search')">
            <q-icon name="search" size="56px" class="q-mb-md ikon-zold" />
            <div class="text-h5 text-weight-bold">Bérlés</div>
            <p>Böngésszen prémium lakáskínálatunkban egyszerűen.</p>
            <div class="gomb-szoveg-zold">Keresés indítása →</div>
          </div>
        </div>

        <div class="col-12 col-sm-6">
          <div class="karty-alap zold-kartya" @click="$router.push('/create')">
            <q-icon name="add_business" size="56px" class="q-mb-md ikon-feher" />
            <div class="text-h5 text-weight-bold">Hirdetés</div>
            <p>Adja bérbe ingatlanát gyorsan és megbízhatóan.</p>
            <div class="gomb-szoveg-feher">Új hirdetés feladása →</div>
          </div>
        </div>

      </div>

      <footer class="q-mt-xl">
        <div class="text-caption text-grey-5 uppercase spacing-2">
          Established 2026 • M&B Management
        </div>
      </footer>

    </div>
  </q-page>
</template>

<style scoped>
/* LOGÓ SZÍNE ÉS MÉRETE */
.app-logo {
  width: 220px;
  filter: brightness(0) opacity(0.8); /* Feketévé teszi a fehér logót */
}

/* ALAP KÁRTYA BEÁLLÍTÁSOK (Minden kártyára vonatkozik) */
.karty-alap {
  border-radius: 20px;
  padding: 50px 30px;
  height: 350px;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  transition: all 0.3s ease-in-out;
}

/* LEBEGÉS (HOVER) EFFEKT */
.karty-alap:hover {
  transform: translateY(-10px);
  box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}

/* VILÁGOS KÁRTYA STÍLUSA */
.vilagos-kartya {
  background: linear-gradient(135deg, #e7ffe5, #e6ffe3); /* Elegáns zöld átmenet */
  color: black;
}
.ikon-zold { color: #000000; }
.gomb-szoveg-zold { 
  color: #000000; 
  margin-top: 20px; 
  font-weight: bold; 
}

/* ZÖLD KÁRTYA STÍLUSA (A sötét helyett) */
.zold-kartya {
  background: linear-gradient(135deg, #96ab96, #0e3d11); /* Elegáns zöld átmenet */
  color: white;
}
.zold-kartya p { color: #e8f5e9; } /* Világosabb szöveg a zöldön */
.ikon-feher { color: white; }
.gomb-szoveg-feher { 
  color: white; 
  margin-top: 20px; 
  font-weight: bold;
  text-decoration: underline; 
}

/* SZÖVEG FORMÁZÁS */
p {
  font-size: 1.1rem;
  margin-top: 10px;
  max-width: 250px;
  line-height: 1.4;
}

.spacing-2 { letter-spacing: 2px; }

/* MOBIL NÉZET */
@media (max-width: 600px) {
  .karty-alap { height: 300px; }
  .app-logo { width: 180px; }
}
</style>