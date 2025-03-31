<template>
  <div class="bg-primary" style="height: 30vh; position: relative;">

  <div class="q-py-md flex flex-center" style="position: relative;">
    <q-card class="q-pa-md q-mt-xl text-center shadow-3 bg-white rounded-card" bordered>
      
      <q-card-section class="column items-center">
        <!-- Responsive Logo -->
        <q-img
          src="~assets/vrtlogoblack.webp"
          alt="Logo"
          fit="full"
          :style="{ width: $q.screen.lt.md ? '180px' : '200px' }"
          class="q-mb-md"
        /> 

        <div class="custom-border text-h6 text-secondary text-weight-bold q-pa-md q-mb-md">
          <q-icon name="check_circle" color="green" size="32px" class="check-icon" />
          Thank you for using our <br /> Queuing System
          <q-icon name="mood" size="md" class="q-ml-xs" />
        </div>

      </q-card-section>

      <q-card-section>
        <p class="text-subtitle1 text-grey-8">
          Your experience matters to us. Stay connected with us!
        </p>
      </q-card-section>

      <!-- Facebook Button with Logo -->
      <q-card-section>
        <q-btn
          dense
          color="blue-8"
          @click="goToPage"
          size="md"
          class="custom-btn"
          glossy
        >
          Visit Our Facebook
        </q-btn>
      </q-card-section>

      <q-card-section class=" text-grey-7 q-mt-md">
        <p class="text-weight-medium">VRTSystems Corporation</p>
      </q-card-section>
      
    </q-card>
  </div>
  </div>
</template>

<script>
import { defineComponent } from "vue";

export default defineComponent({
  name: "IndexPage",

  setup() {
    const goToPage = () => {
      window.open("https://www.facebook.com/fil.labs.2025", "_blank");
    };
    return {
      goToPage,
    };
  },
});
</script>

<style scoped>

/* Custom Button Styling */
.custom-btn {
  width: 225px;
  border-radius: 8px;
  font-size: 16px;
}

/* Responsive Adjustments */
@media (max-width: 600px) {
  .custom-btn {
    width: 180px;
    font-size: 14px;
  }
}

/* Rounded Card */
.rounded-card {
  width: 90vw;
  max-width: 400px;
  border-radius: 16px;
}

.custom-border {
  border: 1.5px solid #1976d2; /* Custom border color */
  padding: 20px;
  border-radius: 8px;
  position: relative; /* Needed for absolute positioning */
  text-align: center;
}

.check-icon {
  position: absolute;
  top: -18px; /* Moves the icon above the border */
  left: 50%; /* Centers the icon */
  transform: translateX(-50%); /* Ensures perfect centering */
  background-color: white; /* Makes it stand out */
  border-radius: 50%;
  padding: 2px;
}

</style>
