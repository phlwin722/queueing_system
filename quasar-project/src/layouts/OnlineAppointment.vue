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
          cursor: 'pointer',
          transition: 'transform 0.3s ease, box-shadow 0.3s ease',
        }"
        @click="goToIndex"
        class="q-ml-sm"
      />
      <q-space />
      
      </q-toolbar>
        
    </q-header
    <!-- Page Content Area -->
    <q-page-container :style="!$route.meta.noPadding ? 'padding-left: 5%; padding-right: 5%;' : ''">
  <router-view />
</q-page-container>

  <q-footer class="bg-secondary text-grey-4 q-pt-md" :class="$q.screen.lt.sm ? 'footer-float-mobile' : ''">
  <q-container>
    <div class="row justify-around items-start">
      <!-- Column 1: About -->
      <div class="col-12 col-sm-4 q-mb-md q-pl-sm">
        <div class="text-weight-bold text-info q-mb-sm">About VRT</div>
        <div class="text-caption">
          VRTSystems Technologies Corporation is a Filipino-owned corporation involved in test systems integration, process automation, test program development, technical consulting, and equipment trading.
        </div>
      </div>

      <!-- Column 2: Quick Links -->
      <div class="col-12 col-sm-3 q-mb-md q-pl-sm">
        <div class="text-weight-bold text-info q-mb-sm">Quick Links</div>
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
      <div class="col-12 col-sm-3 q-mb-md q-pl-sm">
        <div class="text-weight-bold text-info q-mb-sm">Contact</div>
        <div class="text-caption row items-center">
          <q-icon name="phone" size="16px" class="q-mr-xs" />
          (02) 1234-5678
        </div>
        <div class="text-caption row items-center q-mt-xs">
          <q-icon name="email" size="16px" class="q-mr-xs" />
          sample@vrtsystems.ph
        </div>
      </div>

      <!-- Bottom Line -->
      <div class="col-12">
        <div class="text-caption text-grey-5 text-center text-sm-right">
          © 2025 VRTSystems Technologies Corporation. All rights reserved.
        </div>
      </div>
    </div>
  </q-container>
</q-footer>

  </q-layout>
</template>

<script>
import { defineComponent, ref } from 'vue'
import { useRouter } from 'vue-router'

export default defineComponent({
  setup() {
    const router = useRouter()

    const goToIndex = () => {
      router.push('/vrtsystem/onlineAppointment') // or router.push({ name: 'index' }) if using named routes
    }

    const tabModel = ref(null)
    const hoverTab = ref('')
    const hoverStyle = ref({
      position: 'absolute',
      top: '0',
      left: '0',
      width: '100%',
      zIndex: 10,
      background: 'white',
      padding: '20px',
      transition: 'transform 0.3s ease',
    })

    return {
      tabModel,
      hoverTab,
      hoverStyle,
      goToIndex, // expose the method
    }
  }
})
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

.footer-float-mobile {
  bottom: 0;
  left: 0;
  width: 100%;
  z-index: 1000;
  box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1); /* Optional shadow */
}

</style>
