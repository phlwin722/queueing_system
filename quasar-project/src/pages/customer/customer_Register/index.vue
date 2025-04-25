<template>
  <div class="bg-primary background-container"></div>
  <div class="column flex flex-center q-pa-md content-container">
    <q-img
      src="../../../assets/vrtlogowhite1.png"
      alt="Logo"
      fit="full"
      :style="{
        maxWidth: $q.screen.lt.sm ? '170px' : '180px',
        marginTop: '-450px',
      }"
      style="margin-top: 30px; margin-bottom: 30px"
    />

    <q-card class="q-pa-lg shadow-3 login-card">
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
          v-model="selectId"
          :label="
            isServiceAvailable ? 'Service Available' : 'No Service Available'
          "
          transition-show="flip-up"
          transition-hide="flip-down"
          outlined
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
        >
          <template v-slot:option="scope">
            <q-item v-bind="scope.itemProps">
              <q-item-section avatar>
                <span :class="['fi', scope.opt.flag]"></span>
              </q-item-section>
              <q-item-section>
                <q-item-label
                  >{{ scope.opt.symbol }} - {{ scope.opt.name }}</q-item-label
                >
                <q-item-label caption
                  >Buy: {{ scope.opt.buy_value }} | Sell:
                  {{ scope.opt.sell_value }}</q-item-label
                >
              </q-item-section>
            </q-item>
          </template>

          <template v-slot:selected-item="scope">
            <div class="flex items-center">
              <span
                :class="['fi', scope.opt.flag]"
                style="margin-right: 8px"
              ></span>
              {{ scope.opt.symbol }} - {{ scope.opt.name }}
            </div>
          </template>
        </q-select>
        <q-checkbox
          v-model="customModel"
          color="green"
          label="Priority services"
          true-value="yes"
          false-value="no"
        />

        <q-select
          v-if="customModel === 'yes'"
          v-model="prioritySelected"
          label="Priority Service"
          transition-show="flip-up"
          transition-hide="flip-down"
          outlined
          emit-value
          map-options
          dense
          class="q-mt-md"
          :options="categoriesPriority"
          :error="formError.hasOwnProperty('priority')"
          :error-message="formError.priority"
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
</template>

<script>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import { $axios, $notify } from "boot/app";

export default {
  setup() {
    const name = ref("");
    const email = ref("");
    const email_status = ref("sending_customer");
    const type_id = ref(null);
    const currencySelected = ref();
    const categoryForeignExchange = ref();
    const categoriesList = ref([]);
    const prioritySelected = ref();
    const currentCiesList = ref([]);
    const isLoading = ref(false);
    const formError = ref({});
    const customModel = ref("no");
    const branch_id = ref();
    const route = useRoute(); // Use useRoute() correctly
    const token = ref(route.params.token); // Get token from URL
    const isUsedToken = ref(false);
    const selectId = ref(null);
    const processQrCode = async () => {
      try {
        const temp = token.value;
        const temp1 = temp.substring(12); // Extract everything after the 12th character
        const branch_id = parseInt(temp1, 10);
        const response = await $axios.post("/scan-qr", {
          token: token.value,
          branch_id: branch_id,
        });
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
      formError.value = [];
      try {
        // Check if the category is 'Foreign Exchange' and validate the currency selection
        if (categoryForeignExchange.value === 1) {
          if (currencySelected.value == null) {
            formError.value.currency = "Currency field is required";
            return;
          }
        }

        if (customModel.value == "yes") {
          if (prioritySelected.value == null) {
            formError.value.priority = "Priority service field is required";
            return;
          }
        }
        if (isUsedToken.value) {
          $notify("negative", "error", "Invalid or already used QR code.");
        } else {
          console.log(email.value);
          name.value = name.value
            .split(" ")
            .map(
              (word) =>
                word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
            )
            .join(" ");
          type_id.value = selectId.value.id;
          const response = await $axios.post("/customer-join", {
            token: token.value,
            name: name.value,
            email: email.value,
            email_status: email_status.value,
            type_id: type_id.value,
            currency: currencySelected.value,
            priority_service: prioritySelected.value,
          });
          window.location.href = "/customer-dashboard/" + token.value;
        }
      } catch (error) {
        if (error.response.status === 422) {
          formError.value = error.response.data;
        } else if (error.response.status === 400) {
          $notify(
            "negative",
            "error",
            "No tellers assigned to this service type"
          );
        } else if (error.response.status === 500) {
          $notify(
            "negative",
            "error",
            "No tellers assigned to this service type"
          );
        } else if (error.response.status === 500) {
          $notify(
            "negative",
            "error",
            "No tellers assigned to this service type"
          );
        }
      } finally {
        isLoading.value = false;
      }
    };
    const isServiceAvailable = ref(false);
    const fetchCategories = async () => {
      try {
        const temp = token.value;
        const temp1 = temp.substring(12); // Extract everything after the 12th character
        const branch_id = parseInt(temp1, 10); // Convert to integer (base 10)
        console.log(branch_id);
        const { data } = await $axios.post("/types/filteredTypes", {
          branch_id: branch_id,
        });
        const OnlineTellers = data.rows;
        const WindowsInBranch = data.types;

        // Filter OnlineTellers that exist in WindowsInBranch by matching IDs
        const NewObject = OnlineTellers.filter((teller) =>
          WindowsInBranch.some((window) => window.id === teller.id)
        );

        console.log(NewObject);

        const seenNames = new Set();
        const uniqueRows = NewObject.filter((row) => {
          if (seenNames.has(row.name)) {
            return false;
          } else {
            seenNames.add(row.name);
            return true;
          }
        });

        console.log(uniqueRows);
        const filteredRows = uniqueRows.filter(
          (row) => row.type_id !== null && row.name !== "Online Appointment"
        );

        if (filteredRows.length === 0) {
          isServiceAvailable.value = false;
        } else {
          isServiceAvailable.value = true;
        }
        // Log filtered type_id values

        // Assign only valid rows to categoriesList.value
        categoriesList.value = filteredRows;
      } catch (error) {
        console.error("Error fetching categories:", error);
      }
    };

    const fecthCurrencty = async (selectedValue) => {
      try {
        if (selectedValue.name === "Foreign Exchange") {
          const { data } = await $axios.post("/currency/showData");

          currentCiesList.value = data.rows
            .map((row) => ({
              id: row.id,
              name: row.currency_name,
              symbol: row.currency_symbol,
              flag: row.flag,
              buy_value: row.buy_value,
              sell_value: row.sell_value,
            }))
            .sort((a, b) => a.name.localeCompare(b.name)); // Sort alphabetically by name
        } else {
          currentCiesList.value = [];
          currencySelected.value = "";
        }
      } catch (error) {
        if (error.response.status === 422) {
          console.log(error.message);
        }
      }
    };

    onMounted(() => {
      processQrCode();
      fetchCategories();
    });

    return {
      customModel,
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
      prioritySelected,
      isServiceAvailable,
      selectId,
      categoriesPriority: [
        "Elderly Customers",
        "Pregnant Women",
        "People with Disabilities",
        "Premium Customers",
        "Parents with Young Children",
      ],
    };
  },
};
</script>

<style scoped>
/* Background */
.background-container {
  position: absolute;
  width: 100%;
  min-height: 45vh;
  z-index: -1;
}

/* Content layout */
.content-container {
  min-height: 80vh;
  width: 100%;
  padding: 2rem;
}

/* Logo */
.logo {
  max-width: 350px;
  width: 80%;
  margin-bottom: 1rem;
}

/* Login Card */
.login-card {
  width: 100%;
  max-width: 25rem;
}

/* Responsive Styles */
@media (max-width: 600px) {
  .content-container {
    padding: 1rem;
  }

  .login-card {
    max-width: 100%;
  }
}
</style>

<style>
@import "flag-icons/css/flag-icons.min.css";
</style>
<style>
@import "flag-icons/css/flag-icons.min.css";
</style>
