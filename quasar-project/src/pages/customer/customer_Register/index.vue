<template>
  <q-layout>
    <div
      class="flex flex-center full-page column"
      style="height: 90vh; width: 100vw"
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
            dense
            class="q-mt-md"
            :options="categoriesList"
            option-label="name"
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
    const type_id = ref();
    const categoriesList = ref([]);
    const isLoading = ref(false);
    const formError = ref({});

    const route = useRoute(); // Use useRoute() correctly
    const token = ref(route.params.token); // Get token from URL
    console.log(token.value);
    const isUsedToken = ref(false);
    const processQrCode = async () => {
      try {
        const response = await $axios.post("/scan-qr", { token: token.value }); //  Correct way to send token

        if (response.data.success) {
          $notify("positive", "check", "Please register to join the queue.");
          console.log(isUsedToken.value);
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
        if (isUsedToken.value === true) {
          console.log(isUsedToken.value);
          $notify("negative", "error", "Invalid or already used QR code.");
        } else {
          console.log(email.value);
          name.value = name.value.charAt(0).toUpperCase() + name.value.slice(1);
          const response = await $axios.post("/customer-join", {
            token: token.value,
            name: name.value,
            email: email.value,
            email_status: email_status.value,
            type_id: type_id.value,
          });

          localStorage.setItem("customer_id" + token.value, response.data.id);
          localStorage.setItem(
            "queue_number" + token.value,
            response.data.queue_number
          );
          localStorage.setItem("customer_token" + token.value, token.value);
          window.location.href = "/customer-dashboard/" + token.value;
        }
      } catch (error) {
        if (error.response.status === 422) {
          formError.value = error.response.data;
        } else {
          console.log("error", error);
        }
      } finally {
        isLoading.value = false;
      }
    };

    const fetchCategories = async () => {
            try {
                const { data } = await $axios.post('/types/index');
                categoriesList.value = data.rows; // Ensure this matches the API response structure
            } catch (error) {
                console.error('Error fetching categories:', error);
            }
        };

    onMounted(processQrCode); // Runs on page load

    onMounted(() => {
      processQrCode
      fetchCategories()
  })

    return {
      name,
      email,
      email_status,
      joinQueue,
      formError,
      isLoading,
      token,
      type_id,
      categoriesList,
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
