<template>
    <q-layout view="hHh lpR fFf">
      <!-- Header Section with Tabs -->
      <q-header elevated class="bg-white text-black flex flex-center" height-hint="98">
        <div style="width: 100%; max-width: 92%;">
          <q-toolbar>
            <q-toolbar-title>
            <div class="row ">
                <div class="col-2">
                    <q-img
                        class="q-mt-sm"
                        :src="require('assets/vrtlogoblack.png')"
                        :style="{ height: '40px', width: '200px', cursor: 'pointer' }"
                    />
                </div>
                <div class="col-4">
                    <!-- Tabs for Navigation with arrows -->
                    <q-tabs v-model="tabModel" align="left">
                        <q-route-tab
                            label="Page One"
                            @mouseenter="hoverTab = 'page1'"
                            @mouseleave="hoverTab = ''"
                        >
                        <template v-slot:prepend>
                            <q-icon
                            :name="hoverTab === 'page1' ? 'arrow_drop_down' : 'arrow_drop_down'"
                            class="tab-arrow"
                            />
                        </template>
                        </q-route-tab>
            
                        <q-route-tab
                            label="Page Two"
                            @mouseenter="hoverTab = 'page2'"
                            @mouseleave="hoverTab = ''"
                        >
                        <template v-slot:prepend>
                            <q-icon
                            :name="hoverTab === 'page2' ? 'arrow_drop_down' : 'arrow_drop_down'"
                            class="tab-arrow"
                            />
                        </template>
                        </q-route-tab>
            
                        <q-route-tab
                            label="Page Three"
                            @mouseenter="hoverTab = 'page3'"
                            @mouseleave="hoverTab = ''"
                        >
                        <template v-slot:prepend>
                            <q-icon
                            :name="hoverTab === 'page3' ? 'arrow_drop_down' : 'arrow_drop_down'"
                            class="tab-arrow"
                            />
                        </template>
                        </q-route-tab>
                    </q-tabs>
                </div>
            </div>
            </q-toolbar-title>
          </q-toolbar>
        </div>
      </q-header>
  
      <!-- Hover-based Content Display -->
      <div 
        v-show="hoverTab === 'page1'" 
        class="hover-tab-content q-mt-xl"
        :style="hoverStyle"
      >
        <q-card-section>
          <h3>Content for Page One</h3>
          <p>More details about page one.</p>
        </q-card-section>
      </div>
  
      <div 
        v-show="hoverTab === 'page2'" 
        class="hover-tab-content q-mt-xl"
        :style="hoverStyle"
      >
        <q-card-section>
          <h3>Content for Page Two</h3>
          <p>More details about page two.</p>
        </q-card-section>
      </div>
  
      <div 
        v-show="hoverTab === 'page3'" 
        class="hover-tab-content q-mt-xl"
        :style="hoverStyle"
      >
        <q-card-section>
          <h3>Content for Page Three</h3>
          <p>More details about page three.</p>
        </q-card-section>
      </div>
  
      <!-- Page Content Area -->
      <q-page-container :style="{ marginTop: hoverTab === '' ? '10px' : '10px', }">
        <!-- Default Route Content -->
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
  