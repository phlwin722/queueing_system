<template>
  <div class="q-pa-md example-row-equal-width">
    <div class="row">
      <q-form class="q-gutter-lg form-container" @submit.prevent="process">
        <div class="col">
        <div>
          <q-input
            v-model="formData.Waiting_time"
            type="number"
            filled
            label="Time"
            style="max-width: 200px"
            :error="formError.hasOwnProperty('Waiting_time')"
            :error-message="formError.Waiting_time"
            :min="1"
            :max="60"
          />
        </div>
      </div>
      <div class="col">
        <div>
          <q-select
            filled
            v-model="formData.Prepared"
            :options="listChoices"
            label="Time"
            :error="formError.hasOwnProperty('Prepared')"
            :error-message="formError.Prepared"
          />
        </div>
      </div>
      </q-form>
    </div>
    <div class="row" style="margin-top: 10px; justify-content: end;">
      <div class="col">
        <q-btn color="primary" icon="save" label="Save" @click="process" />
      </div>
    </div>
    <q-inner-loading
      :showing="isLoading"
      label="Please wait..."
      label-class="text-teal"
      label-style="font-size: 1.1em"
    />
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { $axios, $notify } from 'src/boot/app';

export default {
  setup() {
    const isLoading = ref(false);

    // Initialize formData with empty values
    const formData = ref({
      id: "",
      Waiting_time: "",
      Prepared: "",
    });
    const formError = ref({});

    // Process function for submitting the form data
    const process = async () => {
      isLoading.value = true;
      try {
         // Check if formData.id exists, if so update, else insert
        const endpoint = formData.value.id ? '/admin/waiting_Time-update' : '/admin/waiting_Time';
        const { data } = await $axios.post(endpoint, formData.value);
        formError.value = {}; // Reset form errors after successful submission

        if (data) {
          $notify('positive', 'done', data.message); // Notify success
        }
      } catch (error) {
        if (error.response.status === 422) {
          formError.value = error.response.data; // Handle form validation errors
        } else {
          console.log('Error', error);
        }
      } finally {
        isLoading.value = false;
      }
    };

    // Fetch the data from the backend when the component is mounted
    const fetchWaitingtime = async () => {
      try {
        const { data } = await $axios.post('/admin/waiting_Time-fetch');
        // Ensure that data.dataValue is available before trying to assign it to formData
        if (data && data.dataValue && data.dataValue.length > 0) {
          formData.value = data.dataValue[0]; // Assign the first object in dataValue to formData
        } else {
          console.log('No data available');
        }
      } catch (error) {
        console.log('Error fetching data:', error);
      }
    };

    const listChoices = ['Seconds', 'Minutes'];

    // Fetch the data when the component is mounted
    onMounted(() => {
      fetchWaitingtime();
    });

    return {
      formData,
      listChoices,
      formError,
      process,
      isLoading,
      fetchWaitingtime,
    };
  },
};
</script>
