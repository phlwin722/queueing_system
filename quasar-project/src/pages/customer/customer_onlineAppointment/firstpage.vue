<template>
  <q-page class="q-pa-md bg-accent">
    <!-- Responsive Tabs -->
    <div class="q-mb-md">
      <q-tabs align="right" inline-label class="q-tabs--responsive scroll">
        <q-route-tab to="/page1" icon="visibility" label="View Appointments" />
        <q-separator vertical inset />
        <q-route-tab to="/page2" icon="account_balance" label="Branches" />
        <q-separator vertical inset />
        <q-route-tab to="/page3" icon="category" label="Services" />
      </q-tabs>
    </div>
    

    <!-- Main Container -->
    <div class="row q-col-gutter-md q-gutter-y-md q-pa-md q-ml-xs">
      <!-- Image Section -->
      <q-card class="col-12 col-sm-6 q-pa-none" flat bordered>
        <q-img
          :src="require('assets/appointment.jpg')"
          alt="Appointment Image"
          fit="cover"
          class="full-width"
          style="max-height: 400px;"
        />
      </q-card>

      <!-- Text and Button Section -->
      <q-card class="col-12 col-sm-6 q-pa-md text-center text-sm-left" flat bordered>
        <h3 class="text-left">
          Set your Appointment Online
        </h3>
        <p class="text-h6 text-secondary poppins-font" style="font-weight: 600;">
          Hassle-Free Online Booking
        </p>
        <q-btn
          color="warning"
          label="Appointment Now"
          icon-right="arrow_outward"
          :to="'/book-appointment'"
          class="q-mt-md full-width full-width-xs"
          style="max-width: 250px; font-size: 16px; padding: 12px 24px; border-radius: 8px; transition: background-color 0.3s ease-in-out;"
        />
      </q-card>
    </div>

    <!-- Quick Guide Section -->
<div class="q-my-xl ">
  <q-card class="q-py-md q-px-lg bg-white" flat bordered>
    <div class="text-h5 text-center q-mb-md">Quick Guide</div>

    <q-carousel
      animated
      v-model="slide"
      arrows
      navigation
      infinite
    >
      <q-carousel-slide :name="1" img-src="https://cdn.quasar.dev/img/mountains.jpg" />
      <q-carousel-slide :name="2" img-src="https://cdn.quasar.dev/img/parallax1.jpg" />
      <q-carousel-slide :name="3" img-src="https://cdn.quasar.dev/img/parallax2.jpg" />
    </q-carousel>

    <q-stepper
      v-model="slide"
      flat
      alternative-labels
      color="primary"
      class="q-mt-md"
    >
      <q-step :name="1" title="Choose Branch">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</q-step>
      <q-step :name="2" title="Pick Schedule">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</q-step>
      <q-step :name="3" title="Confirm Booking">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</q-step>
    </q-stepper>
  </q-card>
</div>


    <!-- Footer Section -->
    <q-footer class="bg-secondary text-grey-4 q-pa-sm fixed-bottom">
      <div class="row items-center justify-between full-width">
        <span class="text-caption">
          © 2025 VRTSystems Technologies Corporation.
        </span>
      </div>
    </q-footer>
  </q-page>
</template>

<script>
import { defineComponent, ref } from 'vue';

export default defineComponent({
  name: 'IndexPage',
  setup() {
    const slide = ref(1); // Start at step 1
    return { slide };
  }
});
</script>

<style scoped>
@media (max-width: 600px) {
  .q-tabs {
    flex-wrap: wrap;
  }

  .q-carousel {
    height: 300px !important;
  }

  .text-subtitle1 {
    font-size: 16px;
  }

  .text-caption {
    font-size: 14px;
  }
}

</style>






    <!-- <q-card 
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
    </q-card> -->

    <!-- footer -->
    <!-- <div
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
    </div> -->