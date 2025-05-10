<template>
  <q-page class="q-pa-md flex flex-center bg-grey-1 q-mt-md q-ml-md">
    <q-card flat bordered class="q-pa-lg q-gutter-md shadow-2" style="max-width: 600px; width: 100%">
      <!-- Back Button -->

          <q-btn
            label="Back to Home"
            color="primary"
            icon="arrow_back"
            to="/vrtsystem/onlineAppointment"
            flat
          />
      
      <!-- Title -->
      <q-card-section>
        <div class="row justify-center">
          <div class="text-h5 text-primary text-center">Contact Us</div>
        </div>
      </q-card-section>

      <!-- Description -->
      <q-card-section>
        <div class="text-caption text-center q-mb-md">
          We'd love to hear from you. Reach out to us through any of the following methods.
        </div>
      </q-card-section>

      <!-- Contact Info -->
      <q-card-section>
        <div class="q-mb-md row items-center">
          <q-icon name="phone" class="q-mr-sm text-primary" />
          <div><strong>Phone:</strong> (02) 1234-5678</div>
        </div>
        <div class="q-mb-md row items-center">
          <q-icon name="email" class="q-mr-sm text-primary" />
          <div><strong>Email:</strong> sample@vrtsystems.ph</div>
        </div>
        <div class="q-mb-md row items-center">
          <q-icon name="place" class="q-mr-sm text-primary" />
          <div><strong>Address:</strong> 123 VRT St., Tech City, PH</div>
        </div>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script setup>
import { ref } from 'vue';
</script>