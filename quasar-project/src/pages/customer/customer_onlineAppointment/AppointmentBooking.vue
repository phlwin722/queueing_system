<template>
  <q-page class="q-pa-md" style="max-width: 800px; margin: auto;">
    <q-card class="q-pa-lg">
      <q-card-section>
        <div class="text-h5 text-center">Book an Appointment</div>
      </q-card-section>

      <q-card-section class="q-gutter-md">
        <q-select
          v-model="selectedBranch"
          :options="branches"
          option-label="branch_name"
          option-value="branch_name"
          label="Select Bank Branch"
          outlined
          @update:model-value="fetchSlots"
        />

        <div v-if="selectedBranch && Object.keys(slots).length" class="q-mt-md">
          <q-calendar-month
            v-model="calendarDate"
            animated
            locale="en-us"
            style="height: 600px;"
            :day-min-height="100"
          >
            <template v-slot:day="{ scope }">
              <div class="q-pa-xs text-center">
                <div class="text-subtitle2">{{ scope.timestamp.day }}</div>
                <q-btn
                  v-if="slots[scope.timestamp.date]"
                  dense
                  color="primary"
                  label="Slots: {{ slots[scope.timestamp.date] }}"
                  size="sm"
                  @click="selectDate(scope.timestamp.date)"
                />
              </div>
            </template>
          </q-calendar-month>
        </div>

        <div v-if="form.date" class="q-mt-md">
          <q-input v-model="form.name" label="Full Name" outlined />
          <q-input v-model="form.contact" label="Contact Number" outlined />
          <q-select
            v-model="form.service"
            :options="services"
            label="Select Service"
            outlined
          />
          <q-input v-model="form.date" label="Selected Date" outlined readonly />
        </div>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn color="primary" label="Submit" @click="submitAppointment" :disable="!form.date" />
      </q-card-actions>
    </q-card>
  </q-page>
</template>

<script>
import { ref, onMounted } from "vue";
import { $axios, $notify } from "boot/app";
import { useRouter } from "vue-router";

export default {
  name: "AppointmentBooking",
  setup() {
    const router = useRouter();

    const form = ref({
      name: "",
      contact: "",
      service: "",
      date: "",
    });

    const services = [
      "Account Opening",
      "Cash Deposit",
      "Cash Withdrawal",
      "Loan Inquiry",
      "Others",
    ];

    const branches = ref([]);
    const selectedBranch = ref(null);
    const slots = ref({});
    const calendarDate = ref(new Date().toISOString().substr(0, 10));

    const fetchBranches = async () => {
      try {
        const res = await $axios.get("/branches"); // You should have a route that returns branch list
        branches.value = res.data;
      } catch (err) {
        console.error("Failed to load branches", err);
      }
    };

    const fetchSlots = async () => {
      if (!selectedBranch.value) return;

      try {
        const res = await $axios.get(`/branches/${selectedBranch.value}/slots`);
        slots.value = res.data;
      } catch (err) {
        console.error("Failed to fetch slots", err);
      }
    };

    const selectDate = (date) => {
      form.value.date = date;
    };

    const submitAppointment = async () => {
      try {
        await $axios.post("/appointments", {
          ...form.value,
          branch_name: selectedBranch.value,
        });

        $notify("positive", "check", "Appointment Booked Successfully!");
        router.push("/vrtsystem/onlineAppointment");
      } catch (err) {
        console.error(err);
        $notify("negative", "error", "Failed to book appointment!");
      }
    };

    onMounted(() => {
      fetchBranches();
    });

    return {
      form,
      services,
      selectedBranch,
      branches,
      slots,
      fetchSlots,
      selectDate,
      calendarDate,
      submitAppointment,
    };
  },
};
</script>
