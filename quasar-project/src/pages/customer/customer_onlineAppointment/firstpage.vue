<template>
  <q-page class="flex flex-center flex-column">

    <div
      class="full-width q-pa-md q-pa-xs"
      style="
        width: 100%; 
        max-width: 1200px; 
        height: 500px;
        margin-top: 30px;
      "
    >
      <div class="row justify-center">
        <div class="col">
          <q-img
            :src="require('assets/appointment.png')"
            fit="cover"
            style="height: 430px; width: 100%;"

          />
        </div>
        <div class="col flex flex-center">
            <div class="column items-center">
              <p class="poppins-font" style= "font-size: 31px;">Set your appointment in online</p>
              <q-btn
                class=" "
                color="primary"
                label="Appointment now"
                icon-right="arrow_outward"/>
            </div>
        </div>
      </div>
    </div>

    <q-card 
      class="full-width rounded-borders q-pa-md q-pa-xs"
      style="
        width: 100%; 
        max-width: 1200px; 
        border: 1px solid black; 
        height: 370px;
        margin-top: 30px;
      "
    >
      <q-img
        alt="Quasar logo"
        src="~assets/quasar-logo-vertical.svg"
        style="width: 200px; height: 200px"
      />
    </q-card>

    <!-- footer -->
    <div
      class="full-width q-pa-md bg-grey-8 text-white flex flex-center"
      style="margin-top: 30px;"
    >
      <div
        class="full-width q-pa-md q-pa-xs bg-transparent"
        style="width: 100%; max-width: 1200px;"
      >
        <div class="row q-mb-md">
          <div class="col">d</div>
          <div class="col">as</div>
          <div class="col">
            <div>
              <div class="text-h6">
                CORPORATE OFFICE
              </div>
              <p class="q-mt-md" style="font-size: 13px;">
                B22, L5&7, GRACEVIllE 1 SUBD, MUZON-SOUTH, CITY OF SAN JOSE DEL MONTE BULACAN
              </p>
            </div>
            <div class="q-mt-md">
              <p class="text-h6">Follow us</p>
              <q-avatar 
                color="primary" 
                icon="facebook" 
                size="60px" 
                :style="{cursor: 'pointer'}"
              />
            </div>
          </div>
        </div>
        <q-separator />
        <div class="row q-mt-md">
          <div class="col">Contact us: vrtsystemtechnologycorp@gmail.com</div>
          <div class="col text-right">&copy; 2025 VRTSYSTEMS TECHNOLOGY CORP. All rights reserved.</div>
        </div>
      </div>
    </div>

  </q-page>

</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'IndexPage'
});
</script>

<style scoped>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
  /* Scoped styles for the component */
.poppins-font {
  font-family: 'Poppins', sans-serif;
}
</style>