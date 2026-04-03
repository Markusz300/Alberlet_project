<template>
    <q-page class="bg-teal-9 flex flex-center column">

        <div class="q-mb-xl text-center">
            <img src="icons/M&B logo.png" style="width: 250px; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));">
        </div>

        <div class="row q-col-gutter-xl justify-center items-stretch container-width">

            <div class="col-12 col-sm-5">
                <q-card class="choice-card cursor-pointer full-height flex flex-center q-pa-xl shadow-10"
                    @click="$router.push('/search')">
                    <q-card-section class="text-center">
                        <q-icon name="home" size="120px" color="teal-9" />
                        <div class="text-h4 text-weight-bolder text-teal-10 q-mt-md">Albérlet Kereső</div>
                        <div class="text-subtitle1 text-grey-7">Találd meg álmaid otthonát</div>
                    </q-card-section>
                </q-card>
            </div>

            <div class="col-12 col-sm-5">
                <q-card class="choice-card cursor-pointer full-height flex flex-center q-pa-xl shadow-10"
                    @click="$router.push('/create')">
                    <q-card-section class="text-center">
                        <q-icon name="add_business" size="120px" color="teal-9" />
                        <div class="text-h4 text-weight-bolder text-teal-10 q-mt-md">Hirdetés Feltöltése</div>
                        <div class="text-subtitle1 text-grey-7">Add bérbe ingatlanod nálunk</div>
                    </q-card-section>
                </q-card>
            </div>

        </div>


    </q-page>
</template>

<style scoped>
.container-width {
    max-width: 1000px;
    width: 100%;
}

.choice-card {
    border-radius: 24px;
    background: white;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 4px solid transparent;
}

/* Hover effekt: emelkedés és keret */
.choice-card:hover {
    transform: translateY(-15px) scale(1.02);
    border-color: #004d40;
    /* teal-10 */
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3) !important;
}

/* Mobilos finomítás */
@media (max-width: 600px) {
    .choice-card {
        padding: 20px !important;
    }
}
</style>