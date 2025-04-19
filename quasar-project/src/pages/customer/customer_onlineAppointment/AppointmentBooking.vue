<template>
  <q-page class="q-pa-md" style="min-height: 340px; max-width: 800px; margin: auto;">

    <!-- Create/Edit/Delete Options -->
    <q-card v-if="choosing" class="q-mt-md q-pa-lg">
      <q-card-section>
        <div class="text-h5 text-center">What would you like to do?</div>
      </q-card-section>
      
      <q-card-section class="q-gutter-md">
        <div class="column" v-if="choosing = true" style="height: 250px; max-height: 100%; width: auto;">
          <div class="col-12 clickable-div" @click="startCreate">
           Create Appointment
          </div>
          <div class="col-6 clickable-div" @click="startEdit">
            Edit Appointment
          </div>
          <div class="col-6 clickable-div" @click="startDelete">
            Cancel Appointment
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Appointment Form -->
    <q-card v-if="createCard && !choosing" class="q-pa-lg">
      <q-card-section>
        <div class="text-h5 text-center">Book an Appointment</div>
      </q-card-section>

      <q-card-section class="q-gutter-md">
        <q-select
          v-model="selectedBranch"
          :options="branches"
          emit-value
          map-options
          option-label="branch_name"
          option-value="id"
          dense
          label="Select Bank Branch"
          outlined
          @update:model-value="fetchSlots"
        />

        <div v-if="selectedBranch" class="q-mt-md">
          <q-input
            class="q-mt-md"
            v-model="form.name"
            label="Full Name"
            outlined
            :error="formError.hasOwnProperty('name')"
            :error-message="formError.name ? formError.name[0] : ''"
            dense
          />

          <q-input
            class="q-mt-md"
            v-model="form.email"
            label="Email address"
            outlined
            dense
            :error="formError.hasOwnProperty('email')"
            :error-message="formError.email ? formError.email[0] : ''"
          />

          <q-select
            v-model="form.service"
            dense
            class="q-mt-md"
            :options="services"
            label="Select Service"
            map-options
            emit-value
            :error="formError.hasOwnProperty('service')"
            :error-message="formError.service ? formError.service[0] : ''"
            option-label="name"
            option-value="id"
            outlined
          />

          <div class="q-pa-md">
            <FullCalendar :options="calendarOptions" />
            <div v-if="formError.appointment_date" class="q-mt-md text-negative">
              {{ formError.appointment_date ? formError.appointment_date[0] : '' }}
            </div>
          </div>

          <q-card-actions align="right">
            <q-btn color="primary" label="Submit" @click="submitAppointment" />
          </q-card-actions>
        </div>
      </q-card-section>
    </q-card>

    <!-- create message -->
    <q-card v-if="successCard && !choosing"class="q-mt-md q-pa-lg">
      <q-card-section>
        <div class="text-h5 text-center">Appointment Booked Successfully!</div>
        <div class="text-center q-mt-md">Appointment booked! Please check your email for the reference number.</div>
      </q-card-section>

      <q-card-section>
        <q-card-actions align="right">
          <q-btn color="primary" label="Book again" @click="clearForm" />
        </q-card-actions>
      </q-card-section>
    </q-card>

    <!-- Edit card -->
    <q-card v-if="editCard && !choosing" class="q-pa-lg">
      <q-card-section>
        <div class="text-h5 text-center">Edit an Appointment</div>
      </q-card-section>

      <q-card-section class="q-gutter-md">
        <q-select
          v-model="selectedBranch"
          :options="branches"
          emit-value
          map-options
          option-label="branch_name"
          option-value="id"
          dense
          label="Select Bank Branch"
          outlined
          @update:model-value="fetchSlots"
        />

        <div v-if="selectedBranch" class="q-mt-md">
          <q-input
            class="q-mt-md"
            v-model="form.name"
            label="Full Name"
            outlined
            :error="formError.hasOwnProperty('name')"
            :error-message="formError.name ? formError.name[0] : ''"
            dense
          />

          <q-input
            class="q-mt-md"
            v-model="form.email"
            label="Email address"
            outlined
            dense
            :error="formError.hasOwnProperty('email')"
            :error-message="formError.email ? formError.email[0] : ''"
          />

          <q-select
            v-model="form.service"
            dense
            class="q-mt-md"
            :options="services"
            label="Select Service"
            map-options
            emit-value
            :error="formError.hasOwnProperty('service')"
            :error-message="formError.service ? formError.service[0] : ''"
            option-label="name"
            option-value="id"
            outlined
          />

          <div class="q-pa-md">
            <FullCalendar :options="calendarOptions" />
            <div v-if="formError.appointment_date" class="q-mt-md text-negative">
              {{ formError.appointment_date ? formError.appointment_date[0] : '' }}
            </div>
          </div>

          <q-card-actions align="right">
            <q-btn color="primary" label="Submit" @click="submitAppointment" />
          </q-card-actions>
        </div>
      </q-card-section>
    </q-card>

    <!-- delete card-->

    <q-card v-if="deleteCard && !choosing" class="q-pa-lg">
      <q-card-section>
        <div class="text-h5 text-center">Delete an Appointment</div>
      </q-card-section>

      <q-card-section class="q-gutter-md">
        <q-select
          v-model="selectedBranch"
          :options="branches"
          emit-value
          map-options
          option-label="branch_name"
          option-value="id"
          dense
          label="Select Bank Branch"
          outlined
          @update:model-value="fetchSlots"
        />

        <div v-if="selectedBranch" class="q-mt-md">
          <q-input
            class="q-mt-md"
            v-model="form.name"
            label="Full Name"
            outlined
            :error="formError.hasOwnProperty('name')"
            :error-message="formError.name ? formError.name[0] : ''"
            dense
          />

          <q-input
            class="q-mt-md"
            v-model="form.email"
            label="Email address"
            outlined
            dense
            :error="formError.hasOwnProperty('email')"
            :error-message="formError.email ? formError.email[0] : ''"
          />

          <q-select
            v-model="form.service"
            dense
            class="q-mt-md"
            :options="services"
            label="Select Service"
            map-options
            emit-value
            :error="formError.hasOwnProperty('service')"
            :error-message="formError.service ? formError.service[0] : ''"
            option-label="name"
            option-value="id"
            outlined
          />

          <div class="q-pa-md">
            <FullCalendar :options="calendarOptions" />
            <div v-if="formError.appointment_date" class="q-mt-md text-negative">
              {{ formError.appointment_date ? formError.appointment_date[0] : '' }}
            </div>
          </div>

          <q-card-actions align="right">
            <q-btn color="primary" label="Submit" @click="submitAppointment" />
          </q-card-actions>
        </div>
      </q-card-section>
    </q-card>

  </q-page>
</template>

<script>
import { ref, onMounted, watch } from "vue";
import { $axios, $notify } from "boot/app";
import FullCalendar from "@fullcalendar/vue3";
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction";
import { useQuasar } from "quasar";

export default {
  name: "AppointmentBooking",
  components: {
    FullCalendar,
  },
  setup() {
    const $loading = useQuasar();
    const services = ref([]);
    const branches = ref([]);
    const selectedBranch = ref(null);
    const events = ref([]);
    const formError = ref({});
    const successCard = ref(false);
    const createCard = ref (false)
    const editCard = ref (false)
    const deleteCard = ref (false)
    const choosing = ref(true);

    const form = ref({
      branch_id: "",
      name: "",
      email: "",
      service: "",
      appointment_date: "",
    });

    const calendarOptions = ref({
      plugins: [dayGridPlugin, interactionPlugin],
      initialView: "dayGridMonth",
      dateClick: handleDateClick,
      events: events,
    });

    function handleDateClick(data) {
      form.value.appointment_date = data.dateStr;
    }

    const fetchBranches = async () => {
      try {
        const res = await $axios.post("/type/Branch");
        branches.value = res.data.branch;
      } catch (err) {
        console.error("Failed to load branches", err);
      }
    };

    const fetchTypes = async () => {
      try {
        const { data } = await $axios.post("/type/service", {
          branch_id: selectedBranch.value,
        });

        services.value = data.servce;
      } catch (error) {
        if (error.response?.status === 422) {
          console.log(error);
        }
      }
    };

    const fetchAvailableSlot = async () => {
      try {
        const { data } = await $axios.post("/branches/slots", {
          branch_id: selectedBranch.value,
        });

        events.value = data.availableSlots.map((slot) => ({
          title: `${slot.is_available} available`,
          start: `${slot.date}`,
          extendedProps: {
            slotId: slot.id,
            isAvailable: slot.is_available,
          },
          color: slot.is_available > 0 ? "#28a745" : "#dc3545",
        }));
      } catch (error) {
        if (error.response?.status === 422) {
          console.log(error);
        }
      }
    };

    const submitAppointment = async () => {
      try {
        $loading.loading.show({
          message: "Process is in progress. Hang on...",
        });

        formError.value = {};
        form.value.branch_id = selectedBranch.value;

        const { data } = await $axios.post("/appointments", form.value);
        fetchAvailableSlot();

        setTimeout(() => {
          $loading.loading.hide();
          successCard.value = true;
          choosing.value = false;
          createCard.value = false
        }, 1500);
      } catch (error) {
        $loading.loading.hide();
        if (error.response?.status === 422) {
          formError.value = error.response.data.errors;
        } else if (error.response?.status === 400) {
          $notify("negative", "error", error.response.data);
        }
      }
    };

    const clearForm = () => {
      form.value = {
        branch_id: "",
        name: "",
        email: "",
        service: "",
        appointment_date: "",
      };
      selectedBranch.value = null;
      formError.value = {};
      choosing.value = true;
      successCard.value = false;
    };

    const startCreate = () => {
      clearForm();
      choosing.value = false;
      successCard.value = false;
      editCard.value = false;
      deleteCard.value = false;
      createCard.value = true
    };

    const startEdit = () => {
      choosing.value = false;
      successCard.value = false;
      editCard.value = true;
      deleteCard.value = false;
      createCard.value = false
    };

    const startDelete = () => {
      choosing.value = false;
      editCard.value = false;
      deleteCard.value = true;
      createCard.value = false
    };

    watch(
      () => selectedBranch.value,
      async (newVal) => {
        if (newVal) {
          fetchTypes();
          form.value.service = "";
          fetchAvailableSlot();
        }
      }
    );

    onMounted(() => {
      fetchBranches();
    });

    return {
      calendarOptions,
      form,
      services,
      selectedBranch,
      branches,
      events,
      submitAppointment,
      formError,
      successCard,
      createCard,
      deleteCard,
      editCard,
      choosing,
      clearForm,
      startCreate,
      startEdit,
      startDelete,
    };
  },
};
</script>

<style>
.clickable-div {
  border: 1px solid black;
  width: 345px;
  padding: 12px;
  text-align: center;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s ease;
  user-select: none;
}

.clickable-div:hover {
  background-color: #f0f0f0;
  transform: scale(1.02);
}
</style>
