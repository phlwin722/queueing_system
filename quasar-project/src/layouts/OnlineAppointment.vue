<template>
    <q-layout view="hHh lpR lfr">
      <!-- Header Section with Tabs -->
      <q-header>
        <q-toolbar>
        <q-img
          src="~assets/vrtlogowhite1.png"
          alt="Logo"
          fit="full"
          :style="{
            maxWidth: $q.screen.lt.sm ? '100px' : '160px',
            cursor: 'pointer', // Fix cursor property
            transition: 'transform 0.3s ease, box-shadow 0.3s ease',
          }"
          @click="goonDashboard"
          class="q-ml-sm"
        />

        <q-space />
        
        </q-toolbar>
        
        
      </q-header>
  
      <!-- Page Content Area -->
      <q-page-container>
      <router-view />
    </q-page-container>

    <!-- Footer Section -->
    <q-footer class="bg-secondary text-grey-4 q-pa-md">
  <div class="row q-col-gutter-md wrap full-width items-start">

    <!-- Column 1: About -->
    <div class="col-12 col-sm-4">
      <div class="text-weight-bold text-grey-3 q-mb-sm">About VRT</div>
      <div class="text-caption">
        VRTSystems Technologies Corporation is a Filipino-owned corporation involved in test systems integration, process automation, test program development, technical consulting, and equipment trading.
      </div>
<!--       <q-img
        src="~assets/vrtlogowhite.webp"
        alt="VRT Logo"
        style="width: 125px; height: auto;"
      /> -->
    </div>

    <!-- Column 2: Quick Links -->
    <div class="col-12 col-sm-4">
      <div class="text-weight-bold text-grey-3 q-mb-sm">Quick Links</div>
      <q-list dense class="text-caption">
        <q-item clickable v-ripple to="/book-appointment">
          <q-item-section>Book Appointment</q-item-section>
        </q-item>
        <q-item clickable v-ripple to="/contact">
          <q-item-section>Contact Us</q-item-section>
        </q-item>
        <q-item clickable v-ripple to="/privacy">
          <q-item-section>Privacy Policy</q-item-section>
        </q-item>
      </q-list>
    </div>

    <!-- Column 3: Contact Info -->
    <div class="col-12 col-sm-4">
      <div class="text-weight-bold text-grey-3 q-mb-sm">Contact</div>
      <div class="text-caption">
        <q-icon name="phone" size="16px" class="q-mr-xs" />
        (02) 1234-5678 //sample
      </div>
      <div class="text-caption q-mt-xs">
        <q-icon name="email" size="16px" class="q-mr-xs" />
          sample@vrtsystems.ph
      </div>
      <div class="q-mt-sm">
        <q-btn flat round size="sm" icon="mdi-facebook" class="text-grey-4 q-mr-xs" />
        <q-btn flat round size="sm" icon="mdi-twitter" class="text-grey-4 q-mr-xs" />
        <q-btn flat round size="sm" icon="mdi-instagram" class="text-grey-4" />
      </div>
    </div>
    
    <!-- Footer Bottom Row -->
    <div class="col-12 text-center text-caption text-grey-5">
      © 2025 VRTSystems Technologies Corporation. All rights reserved.
    </div>

  </div>
</q-footer>

    </q-layout>
  </template>
  
  <script>

  import { defineComponent, ref } from 'vue';
 
  
  export default defineComponent({
    setup() {
      const tabModel = ref(null); // Used to control the active tab
      const hoverTab = ref(''); // Track which tab is being hovered
  
      const hoverStyle = ref({
        position: 'absolute', // Position the hover content absolutely
        top: '0', // Start at the top of the screen
        left: '0', // Align to the left
        width: '100%', // Full width
        zIndex: 10, // Ensure it is above other content
        background: 'white', // Optional: Add background color to hover content
        padding: '20px', // Optional: Padding for spacing
        transition: 'transform 0.3s ease', // Smooth transition effect
      });
  
      return {
        tabModel, // Bind this to q-tabs' v-model to manage the active tab
        hoverTab, // Track hovered tab for hover content
        hoverStyle, // Style for hover-based content
      };
    },
  });
  </script>
  
  <style scoped>
  /* Styling the tab arrows */
  .tab-arrow {
    margin-right: 8px; /* space between arrow and label */
    transition: transform 0.3s ease; /* smooth transition for rotation */
  }
  
  /* Optional styling to make sure tab arrows are positioned well */
  .q-route-tab .q-icon {
    vertical-align: middle;
  }
  
  /* Optional: Smooth transition of content */
  .hover-tab-content {
    padding: 20px;
    transition: opacity 0.3s ease;
  }
  </style>
  