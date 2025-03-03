<template>
    <div class="row justify-center">
      <div class="" style="max-width: 500px">
        <h2>Welcome</h2>
        <q-form @submit="onSubmit" @reset="onReset" class="q-gutter-md">
          <q-input
            clearable 
            filled
            v-model="phoneNo"
            type="tel"
            label="Phone"
            mask="(###) ### - ####"
            unmasked-value
            hint="Phone: (09#) ### - ####"
            lazy-rules
            :rules="[(val) => (val && val.length > 0) || 'Please type something']"
          />
  
          <q-select
            clearable
            style="width: 350px;"
            v-model="windowType"
            :options="options"
            label="Window type" />
  
          <div>
           <q-btn
              class="absolute"
              label="Proceed"
              @click="proceedDialog = true"
              color="primary" />
          </div>
        </q-form>
  
        <q-dialog v-model="proceedDialog">
        <q-card>
          <q-card-section>
            <div class="text-h6">Confirmataion</div>
          </q-card-section>
  
          <q-card-section class="q-pt-none">
            Are you sure you want to proceed?
          </q-card-section>
  
          <q-card-actions align="right">
            <q-btn flat label="Cancel" color="primary" v-close-popup />
            <q-btn flat label="OK" color="primary" v-close-popup />
          </q-card-actions>
        </q-card>
      </q-dialog>
      </div>
    </div>
  </template>
  
  <script>
  import { ref } from "vue";
  
  export default {
    setup() {
      const accept = ref(false);
      const windowType = ref ()
      const phoneNo = ref ()
      const proceedDialog = ref(false)
  
      return {
        accept,
        windowType,
        phoneNo,
        proceedDialog,
  
        options: [
          "Regular Deposit",
          "Withdrawal",
          "Bills Payment",
          "Check Encashment",
          "Foreign Exchange ",
          "Remittance",
          "Special Assistance (Senior Citizen / PWD) / Pregnant",
        ],
      };
    },
  };
  </script>