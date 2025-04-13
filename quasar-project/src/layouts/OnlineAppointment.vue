<template>
    <q-layout view="hHh lpR fFf">
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
      <q-page-container style="padding-bottom: 20px;">
      <router-view />
    </q-page-container>
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
  