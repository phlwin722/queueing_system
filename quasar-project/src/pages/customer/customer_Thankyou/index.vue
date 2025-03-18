<template>
    <div class="q-pa-md q-gutter-sm text-center">
      <div class="text-h5 text-primary font-weight-bold">
        Thank you for using our queueing system!
      </div>
  
      <div class="q-mt-md text-subtitle1 text-grey-7">
        <p>Your experience matters to us. Stay connected with us!</p>
      </div>
  
      <q-btn
        push
        color="primary"
        @click="goToPage"
        round
        icon="facebook"
        class="q-mt-md custom-btn"
      >
        Visit Our Facebook
      </q-btn>
  
      <div class="q-mt-lg text-grey-6">
        <p>VRTSystems Corporation</p>
      </div>
    </div>
  </template>
  
  <script>
  import { defineComponent } from 'vue';
  
  export default defineComponent({
    name: 'IndexPage',
  
    setup() {
      const goToPage = async () => {
        window.location = "https://www.facebook.com/vrtsystems";
      };
      return {
        goToPage,
      };
    },
  });
  </script>
  
  <style scoped>
  .q-pa-md {
    background-color: #f9f9f9; /* Slightly lighter background */
    border-radius: 15px; /* Softer rounded corners */
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15); /* Slightly larger shadow */
    padding: 2rem;
  }
  
  .text-h5 {
    font-size: 1.5rem; /* Increase font size */
    font-weight: 600; /* Slightly bolder text */
    color: #2c3e50; /* Rich color for the heading */
  }
  
  .q-btn {
    font-size: 1.2rem;
    padding: 16px 32px;
    text-transform: uppercase;
    letter-spacing: 1.5px; /* Slight spacing between letters */
    transition: all 0.3s ease;
    border-radius: 30px; /* Softer rounded button */
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1); /* Subtle button shadow */
  }
  
  .q-btn:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2); /* More pronounced shadow on hover */
  }
  
  .text-center {
    text-align: center;
  }
  
  .q-mt-md {
    margin-top: 24px;
  }
  
  .q-mt-lg {
    margin-top: 48px;
  }
  
  .custom-btn {
    background-color: #3b9ff3; /* Fresh blue background */
    color: white;
    border: none;
    font-weight: 500; /* Slightly lighter button text */
  }
  
  .custom-btn:hover {
    background-color: #1f77d1; /* Darker shade for hover */
    transform: translateY(-6px); /* Further hover lift effect */
  }
  
  .text-grey-6 {
    color: #7f8c8d;
    font-size: 1rem;
    font-weight: 300;
  }
  </style>
  