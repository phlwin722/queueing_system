<template>
  <div class="bg-primary" style="height: 30vh; position: relative;">
    <div class="q-py-md flex flex-center" style="position: relative;">
      <q-card class="q-px-md q-mt-xl text-center shadow-3 bg-white rounded-card" bordered>
        <q-card-section class="column items-center">
          <q-img src="~assets/vrtlogoblack.webp" alt="Logo" fit="full" class="q-mb-md" :style="{ width: $q.screen.lt.md ? '180px' : '200px' }" />
          <div class="custom-border text-h6 text-secondary text-weight-bold q-pa-md q-mb-md">
            <q-icon name="check_circle" color="green" size="32px" class="check-icon" />
            Thank you for using our <br /> Queuing System
            <q-icon name="mood" size="md" class="q-ml-xs" />
          </div>
        </q-card-section>

        <!-- Survey Form -->
        <q-card-section class="text-center">
          <p class="text-h6 text-primary">Your experience matters to us! Please provide feedback to improve our service.</p>
        </q-card-section>

        <q-form @submit="submitSurvey" class="q-gutter-md q-px-md q-pb-md flex flex-center column shadow-1" style="max-width: 500px; margin: 0 auto;">
          <div class="text-secondary q-mb-sm">
              Survey form
            </div>
          <q-input v-model="survey.name" label="Your Name" filled dense class="full-width" hint="Optional"/>

        <q-card-section class="bg-accent full-width rounded-borders">
          <div class="text-secondary q-mb-sm text-left">
            Rate our Queuing System
          </div>
          <q-rating v-model="survey.rating" :max="5" size="2.5em" color="yellow-14" icon="star_border" icon-selected="star"/>
        </q-card-section>

          <q-card-section class="column bg-accent full-width rounded-borders">
            <div class="text-secondary q-mb-sm text-left">
              Was the system easy to use?
            </div>
            <div class="row justify-center">
              <q-radio v-model="survey.ease_of_use" val="Yes" label="Yes" class="q-mx-sm" />
              <q-radio v-model="survey.ease_of_use" val="No" label="No" class="q-mx-sm" />
            </div>
          </q-card-section>

          <!-- <q-select v-model="survey.waiting_time_satisfaction" :options="['Very Satisfied', 'Satisfied', 'Neutral', 'Dissatisfied', 'Very Dissatisfied']" 
            label="How satisfied are you with the waiting time?" filled dense class="full-width" required /> -->
            <q-card-section class="bg-accent full-width rounded-borders">
          <div class="text-secondary q-mb-sm text-left">
            How satisfied are you with the waiting time?
          </div>
          <q-rating v-model="survey.waiting_time_satisfaction" :max="5" size="2.5em" color="yellow-14" :icon="icons"/>
        </q-card-section>

          <q-input v-model="survey.suggestions" type="textarea" label="Suggestions for Improvement" filled dense class="full-width" />

          <div class="flex flex-center">
            <q-btn label="Submit Feedback" type="submit" color="primary" class="custom-btn" :loading="loading" />
          </div>
        </q-form>

        <q-separator class="q-my-md" />

        <q-card-section class="column items-center">
          Connect with us
          <q-img src="~assets/facebook-logo1.png" alt="Facebook" width="30px" class="cursor-pointer q-mt-md" @click="goToPage" />
        </q-card-section>

        <q-card-section class="text-grey-7 q-mt-md">
          <p class="text-weight-medium">VRTSystems Corporation</p>
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>

<script>
import { ref } from "vue";
import { $axios, $notify } from "boot/app";


export default {

  setup() {
    const survey = ref({
      name: "",
      rating: null,
      ease_of_use: "",
      waiting_time_satisfaction: "",
      suggestions: "",
    });

    const loading = ref(false);

    const icons = [
      "sentiment_very_dissatisfied",
      "sentiment_dissatisfied",
      "sentiment_neutral",
      "sentiment_satisfied",
      "sentiment_very_satisfied"
    ];

    const submitSurvey = async () => {
      loading.value = true;
      try {
        await $axios.post("/survey", survey.value);
        $notify("positive", "check", "Survey submitted successfully!" );
        survey.value = { name: "", rating: null, ease_of_use: "", waiting_time_satisfaction: "", suggestions: "" };
      } catch (error) {
        $notify("negative", "error", "Failed to submit survey. Try again!");
      }
      loading.value = false;
    };

    const goToPage = () => {
      window.open("https://www.facebook.com/fil.labs.2025", "_blank");
    };

    return {
      survey,
      loading,
      submitSurvey,
      goToPage,
      icons,
    };
  },
};
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