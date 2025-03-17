<template>
  <div>
    <q-card>
      <q-form @submit.prevent="process">
        <q-input
          v-model="formData.Waiting_time"
          mask="##:##"
          fill-mask="0"
          label="Enter Time (MM:SS)"
          :error="formError.hasOwnProperty('Waiting_time')"
          :error-message="formError.Waiting_time"
          hint="Format: MM:SS"
          outlined
        />
        <q-btn color="primary" label="save" icon="save" @click="process" />
      </q-form>
    </q-card>
  </div>
</template>

<script>
import { ref, onMounted } from "vue";
import { $axios, $notify } from "src/boot/app";

export default {
  setup() {
    const isLoading = ref(false);
    const formData = ref({
      id: "", // Store ID if it exists
      Waiting_time: "",
    });
    const timeData = ref(null);
    const formError = ref({});

    // Fetch saved time
    const fetchWaitingtime = async () => {
      try {
        const { data } = await $axios.post("/admin/waiting_Time-fetch");
        console.log("Fetched Data:", data);
        if (data && data.time) {
          formData.value = {
            id: data.id || "", // Store ID if available
            Waiting_time: data.time,
          };
          timeData.value = data.time;
        }
      } catch (error) {
        console.error("Error fetching data:", error);
      }
    };

    const process = async () => {
      isLoading.value = true;
      try {
        const endpoint = "/admin/waiting_Time"; // Always use the same endpoint

        const { data } = await $axios.post(endpoint, formData.value);
        formError.value = {}; // Reset form errors

        if (data) {
          $notify("positive", "done", data.message);
          fetchWaitingtime(); // Refresh data after insert/update
        }
      } catch (error) {
        if (error.response.status === 422) {
          formError.value = error.response.data; // Handle validation errors
        } else {
          console.error("Error", error);
        }
      } finally {
        isLoading.value = false;
      }
    };

    onMounted(() => {
      fetchWaitingtime();
    });

    return {
      formData,
      timeData,
      process,
      isLoading,
      formError,
    };
  },
};
</script>
