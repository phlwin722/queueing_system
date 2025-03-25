<template>
  <q-layout>
    <div
      class="flex flex-center full-page column"
      style="height: 110vh; width: 100vw"
    >
      <q-img
        src="../../../assets/vrtlogowhite1.png"
        alt="Logo"
        fit="full"
        :style="{ maxWidth: $q.screen.lt.sm ? '250px' : '300px' }"
        class="q-mb-xl"
      />

      <q-card
        class="q-pa-md shadow-2 bg-accent"
        style="width: 350px; max-width: 90vw"
      >
        <q-card-section>
          <div class="text-h6 text-center">Join the Queue</div>
        </q-card-section>

        <q-card-section>
          <q-input
            v-model="name"
            label="Full Name"
            :error="formError.hasOwnProperty('name')"
            :error-message="formError.name"
            outlined
            dense
          />
          <q-input
            v-model="email"
            label="Email address"
            :error="formError.hasOwnProperty('email')"
            :error-message="formError.email"
            outlined
            dense
            class="q-mt-md"
          />
          <q-select
            v-model="type_id"
            label="Service Available"
            transition-show="flip-up"
            transition-hide="flip-down"
            outlined
            emit-value
            map-options
            @update:modelValue="fecthCurrencty"  
            :error="formError.hasOwnProperty('type_id')"
            :error-message="formError.type_id"
            dense
            class="q-mt-md"
            :options="categoriesList"
            option-label="name"
            option-value="id"
          />
          <q-select
            v-if="currentCiesList && currentCiesList.length > 0"
            v-model="currencySelected"
            label="Currency Available"
            transition-show="flip-up"
            transition-hide="flip-down"
            outlined
            emit-value
            map-options
            dense
            class="q-mt-md"
            :options="currentCiesList"
            option-label="name"
            :error="formError.hasOwnProperty('currency')"
            :error-message="formError.currency"
            option-value="id"
          />
        </q-card-section>

        <q-card-actions align="center">
          <q-btn label="Proceed" color="primary" @click="joinQueue" />
        </q-card-actions>
        <q-inner-loading
          :showing="isLoading"
          label="Please wait..."
          label-class="text-teal"
          label-style="font-size: 1.1em"
        />
      </q-card>
    </div>
  </q-layout>
</template>

<script>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { $axios, $notify } from "boot/app";

export default {
  setup() {
    const name = ref("");
    const email = ref("");
    const email_status = ref("pending");
    const type_id = ref(null);
    const currencySelected = ref ();
    const categoryForeignExchange = ref();
    const categoriesList = ref([]);
    const currentCiesList = ref ([]);
    const isLoading = ref(false);
    const formError = ref({});

    const route = useRoute(); // Use useRoute() correctly
    const token = ref(route.params.token); // Get token from URL
    const isUsedToken = ref(false);

    const processQrCode = async () => {
      try {
        const response = await $axios.post("/scan-qr", { token: token.value });
        if (response.data.success) {
          $notify("positive", "check", "Please register to join the queue.");
        } else {
          isUsedToken.value = true;
          $notify("negative", "error", "Invalid or already used QR code.");
        }
      } catch (error) {
        isUsedToken.value = true;
        $notify("negative", "error", "Invalid or already used QR code.");
      }
    };

    const joinQueue = async () => {
      isLoading.value = true;
      try {
        // Check if the category is 'Foreign Exchange' and validate the currency selection
        if (categoryForeignExchange.value === 1) {
          if (currencySelected.value == null) {
            formError.value.currency = "Currency field is required";
            return;
          }
        }

        if (isUsedToken.value) {
          $notify("negative", "error", "Invalid or already used QR code.");
        } else {
          console.log(email.value);
          name.value = name.value
          .split(' ')
          .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
          .join(' ');
    
          const response = await $axios.post("/customer-join", {
            token: token.value,
            name: name.value,
            email: email.value,
            email_status: email_status.value,
            type_id: type_id.value,
            currency: currencySelected.value
          });
          window.location.href = "/customer-dashboard/" + token.value;
        }
      } catch (error) {
        if (error.response.status === 422) {
          formError.value = error.response.data;
        }
      } finally {
        isLoading.value = false;
      }
    };

    const fetchCategories = async () => {
      try {
        const { data } = await $axios.post("/types/index");
        categoriesList.value = data.rows;
        console.log("Categories loaded:", categoriesList.value); // Check categories
      } catch (error) {
        console.error("Error fetching categories:", error);
      }
    };

    const fecthCurrencty = async (selectedValue) => {
      try {
        categoryForeignExchange.value = selectedValue
        if (selectedValue === 1) {
          const { data } = await $axios.post('/currency/showData');

          // Map the response to create the list of currencies with name and id
          currentCiesList.value = data.rows.map(row => {
            return {
              id: row.id,  // Ensure each currency has an id
              name: `${row.currency_symbol} ${row.currency_name}    (${row.value})`, // Format name
            };
          });
        } else {
          currentCiesList.value = [];  // Clear the currentCiesList if selectedValue is not 1
          currencySelected.value = '';
        }
      } catch (error) {
        if (error.response.status === 422) {
          console.log(error.message)
        }
      }
    };


    onMounted(() => {
      processQrCode();
      fetchCategories();
    });

    return {
      name,
      email,
      email_status,
      joinQueue,
      formError,
      isLoading,
      token,
      currencySelected,
      type_id,
      categoriesList,
      fecthCurrencty,
      currentCiesList,
    };
  },
};
</script>

<style lang="scss" scoped>
.full-page {
  height: 100vh;
  width: 100vw;
  background: linear-gradient(to bottom, $primary 50%, white 50%);
}
</style>
