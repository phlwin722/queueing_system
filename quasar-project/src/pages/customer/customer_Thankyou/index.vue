<template>
  <div class="bg-primary" style="height: 30vh; position: relative;">
    <div class="q-py-md flex flex-center" style="position: relative;">
      <q-card class="q-px-md q-mt-xl text-center shadow-3 bg-white rounded-card" bordered>
        <q-card-section class="column items-center">
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

        <!-- Survey Form -->
        <q-card-section class="text-center">
          <p class="text-h6 text-primary">
            Your experience matters to us! Please provide feedback to improve our service.
          </p>
        </q-card-section>

        <q-form class="q-gutter-md q-px-md q-pb-md flex flex-center column shadow-1" style="max-width: 500px; margin: 0 auto;">
          <p>Survey form</p>
          <q-input label="Your Name" filled dense class="full-width" />

          <q-select
            :options="[1, 2, 3, 4, 5]"
            label="Rate our queuing system (1-5)"
            filled
            dense
            class="full-width"
          />

          <!-- Ease of Use -->
          <q-card-section class="column bg-accent full-width rounded-borders">
            <p class="text-weight-medium">Was the system easy to use?</p>
            <div class="row justify-center">
              <q-radio val="Yes" label="Yes" class="q-mx-sm" />
              <q-radio val="No" label="No" class="q-mx-sm" />
            </div>
          </q-card-section>

          <!-- Waiting Time Satisfaction -->
          <q-select
            :options="['Very Satisfied', 'Satisfied', 'Neutral', 'Dissatisfied', 'Very Dissatisfied']"
            label="How satisfied are you with the waiting time?"
            filled
            dense
            class="full-width"
          />

          <!-- Suggestions -->
          <q-input type="textarea" label="Suggestions for Improvement" filled dense class="full-width" />

          <div class="flex flex-center">
            <q-btn label="Submit Feedback" type="submit" color="primary" class="custom-btn" />
          </div>
        </q-form>

        <q-separator class="q-my-md" />

        <!-- Facebook Button -->
        <q-card-section class="column items-center">
          Connect with us
          <q-img
            src="~assets/facebook-logo1.png"
            alt="Facebook"
            width="30px"
            class="cursor-pointer q-mt-md"
            @click="goToPage"
          />
        </q-card-section>

        <q-card-section class="text-grey-7 q-mt-md">
          <p class="text-weight-medium">VRTSystems Corporation</p>
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref } from "vue";

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
  width: 150px;
  border-radius: 8px;
  font-size: 13px;
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
  border: 1.5px solid #1976d2;
  padding: 20px;
  border-radius: 8px;
  position: relative;
  text-align: center;
}

.check-icon {
  position: absolute;
  top: -18px;
  left: 50%;
  transform: translateX(-50%);
  background-color: white;
  border-radius: 50%;
  padding: 2px;
}

.survey-container {
  max-width: 500px;
  width: 100%;
  border-radius: 12px;
  border: 1px solid #ccc;
  background-color: #ffffff;
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
}

.full-width {
  width: 100%;
}
</style>