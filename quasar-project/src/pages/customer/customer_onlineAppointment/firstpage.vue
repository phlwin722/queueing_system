<template>
  <q-page class="q-px-md q-pt-md q-pb-xs">
    <!-- Responsive Tabs -->
    <!-- <div class="q-mb-md">
      <q-tabs align="right" inline-label class="q-tabs--responsive scroll">
        <q-route-tab to="/page1" icon="visibility" label="View Appointments" class="text-secondary"/>
        <q-separator vertical inset />
        <q-route-tab to="/page2" icon="account_balance" label="Branches" class="text-secondary"/>
        <q-separator vertical inset />
        <q-route-tab to="/page3" icon="category" label="Services" class="text-secondary"/>
      </q-tabs>
    </div> -->
    
    <!-- Main Container -->
    <div class="row q-col-gutter-md q-mb-lg">
      <!-- Image Section -->
      <div class="col-12 col-sm-6" flat bordered>
        <q-img
          :src="require('assets/appointment.jpg')"
          alt="Appointment Image"
          fit="cover"
          class="full-width"
          style="max-height: 400px;"
        />
      </div>

      <!-- Text and Button Section -->
      <div class="col-12 col-sm-6 q-pa-lg text-center text-sm-left" flat bordered>
        <div class="q-mt-xl">
          <p class="text-h4 text-primary q-mb-sm">
            Set your Appointment Online
          </p>
          <p class="text-subtitle1 text-secondary poppins-font q-mb-md" style="font-weight: 600;">
            Hassle-Free Online Booking
          </p>
          <div class="q-mt-md flex flex-center flex-sm-start">
            <q-btn
              color="warning"
              label="Appointment Now"
              icon-right="arrow_outward"
              :to="'/book-appointment'"
              class="full-width full-sm-width"
              style="max-width: 250px; font-size: 16px; padding: 12px 24px; border-radius: 8px; transition: background-color 0.3s ease-in-out;"
            />
          </div>
        </div>
      </div>

    </div>

    

    <!-- QUICK GUIDE SECTION -->
    <div class="q-mb-lg q-mx-xs">
      <q-card class="q-py-md q-px-sm bg-white" flat bordered>
        <div class="text-h5 text-center text-secondary q-mb-md">Quick Guide</div>

        <q-carousel
          animated
          v-model="slide"
          arrows
          infinite
          navigation
          control-color="secondary"
        >
          <q-carousel-slide :name="1" img-src="~assets/step1.png" />
          <q-carousel-slide :name="2" img-src="~assets/Step2.png" />
          <q-carousel-slide :name="3" img-src="~assets/Step3.png" />
          <q-carousel-slide :name="4" img-src="~assets/Step4.png" />
        </q-carousel>

        <q-stepper
          v-model="slide"
          flat
          alternative-labels
          vertical
          color="primary"
          class="q-mt-md"
        >
          <q-step :name="1" title="Appointment Options">Select the appointment type</q-step>
          <q-step :name="2" title="Select Bank Branch">Choose the branch where you want to book your appointment.</q-step>
          <q-step :name="3" title="Choose Schedule">Pick a date and time that suits your schedule.</q-step>
          <q-step :name="4" title="Confirm Submission">Review your details and submit your appointment.</q-step>
        </q-stepper>
      </q-card>
    </div>

    <!-- FREQUENTLY ASKED QUESTIONS SECTION -->
    <div class="q-mb-xl q-mx-xs">
      <q-card class="q-pa-md bg-white" flat bordered>
        <div class="text-h5 text-center text-secondary q-mb-md">Frequently Asked Questions</div>

        <!-- Example FAQ Entries -->
        <q-expansion-item icon="help" label="How do I book an appointment?" class="text-secondary" expand-separator>
          <div class="q-ml-xl text-negative">
          Click on the 'Appointment Now' button, choose your bank and date, and confirm your booking.
          </div>
        </q-expansion-item>

        <q-expansion-item icon="schedule" label="Can I reschedule my appointment?" class="text-secondary" expand-separator>
          <div class="q-ml-xl text-negative">
          Yes! Use the 'Edit Appointment' option and select your new preferred schedule.
          </div>
        </q-expansion-item>

        <q-expansion-item icon="cancel" label="How do I cancel an appointment?" class="text-secondary" expand-separator>
          <div class="q-ml-xl text-negative">
          Simply go to 'Cancel Appointment' and follow the instructions.
          </div>
        </q-expansion-item>
      </q-card>
    </div>


    
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

  .text-h4 {
    font-size: 22px;
  }

  .q-btn {
    font-size: 15px !important;
    padding: 10px 16px !important;
  }
}

</style>
