<template>
    <q-page class="q-pa-md flex flex-center bg-grey-1">
      <q-card flat bordered class="q-pa-lg q-gutter-md q-mt-sm" style="max-width: 600px; width: 100%">
        <!-- Back Button -->
        
        <q-btn
            label="Back to Home"
            color="primary"
            icon="arrow_back"
            to="/vrtsystem/onlineAppointment"
            flat
            class="text-right"
        />
        
  
        <!-- Title -->
        <div class="text-h5 text-primary">Contact Us</div>
        <div class="text-caption q-mb-md">
          We'd love to hear from you. Reach out to us through any of the following methods.
        </div>
  
        <!-- Contact Info Card -->
        <q-card flat bordered>
          <q-card-section>
            <div class="q-mb-sm row items-center">
              <q-icon name="phone" class="q-mr-sm" />
              <div><strong>Phone:</strong> (02) 1234-5678</div>
            </div>
            <div class="q-mb-sm row items-center">
              <q-icon name="email" class="q-mr-sm" />
              <div><strong>Email:</strong> sample@vrtsystems.ph</div>
            </div>
            <div class="q-mb-sm row items-center">
              <q-icon name="place" class="q-mr-sm" />
              <div><strong>Address:</strong> 123 VRT St., Tech City, PH</div>
            </div>
          </q-card-section>
        </q-card>
  
        <!-- Message Form -->
        <div class="text-subtitle1">Send us a message</div>
  
        <q-form @submit.prevent="submitForm" class="q-gutter-md q-mt-sm">
          <q-input filled v-model="name" label="Your Name" required />
          <q-input filled v-model="email" type="email" label="Email" required />
          <q-input filled v-model="message" type="textarea" label="Message" required />
          <div class="row justify-end">
            <q-btn label="Send Message" type="submit" color="primary" />
          </div>
        </q-form>
      </q-card>
    </q-page>
  </template>
  
  
  <script setup>
  import { ref } from 'vue';
  
  const name = ref('');
  const email = ref('');
  const message = ref('');
  
  const submitForm = () => {
    console.log('Message sent:', {
      name: name.value,
      email: email.value,
      message: message.value,
    });
  
    name.value = '';
    email.value = '';
    message.value = '';
  };
  </script>
  