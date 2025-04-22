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
            :error-message="formError.name ? 'The full name field is required': ''"
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
 
          <q-input
            class="q-mt-md"
            v-model="form.appointment_date"
            label="Selected Date"
            readonly
            outlined
            
            :error="formError.hasOwnProperty('appointment_date')"
            :error-message="formError.appointment_date ? formError.appointment_date[0] : ''"
            dense
          >
          <template v-slot:append>
          <q-icon name="help" @click="" class="cursor-pointer" />
         </template>
          <q-popup-proxy style="text-align: right;">
            <q-banner>  
              You need to click a specific prepared date to set the appointment date.
            </q-banner>
         </q-popup-proxy>    
        </q-input>

          <div class="q-pa-md">
            <FullCalendar 
            :options="calendarOptions"
             />
          </div>

          <q-card-actions align="right">
            <q-btn color="primary" label="Submit" @click="submitAppointment" />
          </q-card-actions>
        </div>
      </q-card-section>
    </q-card

    <!-- Edit card -->
    <q-card v-if="editCard && !choosing" class="q-pa-lg">
      <q-card-section>
        <div class="text-h5 text-center">Edit an Appointment</div>
      </q-card-section>

      <q-card-section class="q-gutter-md">
        <q-input
          v-model="referenceNum"
          emit-value
          map-options
          dense
          label="Reference number"
          :error="formError.hasOwnProperty('referenceNum')"
          :error-message="formError.referenceNum ? 'The reference number field is required.': ''"
          outlined
        />

        <q-card-actions align="right">
          <q-btn color="primary" label="Validate" @click="editAppointment" />
        </q-card-actions>

        <div v-if="displayEditCard" class="q-mt-md">

          <q-select
            v-model="form.branch_id"
            :options="branches"
            class="q-mt-md"
            emit-value
            map-options
            option-label="branch_name"
            option-value="id"
            dense
            label="Select Bank Branch"
            outlined
            @update:model-value="fetchSlots"
          />

          <q-input
            class="q-mt-md"
            v-model="form.name"
            label="Full Name"
            outlined
            :error="formError.hasOwnProperty('name')"
            :error-message="formError.name ? 'The full name field is required' : ''"
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

          <q-input
              class="q-mt-md"
              v-model="form.appointment_date"
              label="Selected Date"
              readonly  
              outlined
            
              :error="formError.hasOwnProperty('appointment_date')"
              :error-message="formError.appointment_date ? formError.appointment_date[0] : ''"
              dense
            >
            <template v-slot:append>
            <q-icon name="help" @click="" class="cursor-pointer" />
          </template>
            <q-popup-proxy style="text-align: right;">
              <q-banner>  
                You need to click a specific prepared date to set the appointment date.
              </q-banner>
          </q-popup-proxy>    
          </q-input>

          <div class="q-pa-md">
            <FullCalendar :options="calendarOptions" />
          </div>

          <q-card-actions align="right">
            <q-btn color="primary" label="Submit" @click="updateAppointment" />
          </q-card-actions>
        </div>
      </q-card-section>
    </q-card>

    <!-- delete card-->
    <q-card v-if="deleteCard && !choosing" class="q-pa-lg">
      <q-card-section>
        <div class="text-h5 text-center">Cancel an Appointment</div>
      </q-card-section>

      <q-card-section class="q-gutter-md">
        <q-input
          v-model="referenceNum"
          emit-value
          map-options
          dense
          label="Reference number"
          :error="formError.hasOwnProperty('referenceNum')"
          :error-message="formError.referenceNum ? 'The reference number field is required.': ''"
          outlined
        />

        <q-card-actions align="right">
          <q-btn color="primary" label="Validate" @click="cancelAppointment" />
        </q-card-actions>

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
import Swal from 'sweetalert2';

export default {
  name: "AppointmentBooking",
  components: {
    FullCalendar,
  },
  setup() {
    const $quasar = useQuasar();
    const services = ref([]);
    const branches = ref([]);
    const selectedBranch = ref(null);
    const events = ref([]);
    const formError = ref({});
    const createCard = ref (false)
    const editCard = ref (false)
    const deleteCard = ref (false)
    const choosing = ref(true);
    const referenceNum = ref (null)
    const displayEditCard = ref (false);
    const successUpdateCard = ref (false);

    const form = ref({
      id: '',
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
      eventClick: handleEventClick,
    });

    function handleDateClick(data) {
      form.value.appointment_date = data.dateStr;
    }

    function handleEventClick(info) {
      // This function handles clicking on an event
      // Here, info.event is the FullCalendar event object
      const eventDate = info.event.start;
      form.value.appointment_date = eventDate.toISOString().split('T')[0];  // Format as YYYY-MM-DD
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
          branch_id: selectedBranch.value || form.value.branch_id,
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
          branch_id: selectedBranch.value  || form.value.branch_id,
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
        $quasar.loading.show({
          message: "Process is in progress. Hang on...",
        });

        formError.value = {};
        form.value.branch_id = selectedBranch.value;

        await $axios.post("/appointments", form.value);
        fetchAvailableSlot();

        setTimeout(() => {
          $quasar.loading.hide();
          Swal.fire({
                  title: 'Appointment Booked Successfully!',
                  message: 'Appointment booked! Please check your email for the reference number.',
                  icon: "success",
                  draggable: true,
                  confirmButtonText: 'Book  ',
                  confirmButtonColor: '#3085d6',  // Set the confirm button color to Tomato redcs
                }).then((result) =>  {
                  if (result.isConfirmed) {
                    choosing.value = true;
                    createCard.value = false
                  }
                });
        }, 1500);
      } catch (error) {
        $quasar.loading.hide();
        if (error.response?.status === 422) {
          $notify('negative','error','Please fill in some fields.')
          formError.value = error.response.data.errors;
        } else if (error.response?.status === 400) {
          Swal.fire({
            icon: "error",
            title: "Oops...",
            text: error.response.data.error,
            confirmButtonText: "OK",  // Custom text for the button
            confirmButtonColor: '#3085d6',  // Set the confirm button color to Tomato redcs
          });  
        }
      }
    };

    const editAppointment = async () => {
      try {
        $quasar.loading.show({
          message: "Process is in progress. Hang on...",
        });

        formError.value = {};
        const { data } = await $axios.post('/validate/Appointment', {
          referenceNum: referenceNum.value
        });

        if (data.reference) {
          form.value = data.reference
          form.value.appointment_date = data.reference.appointment_date
          form.value.service = data.reference.type_id
          await fetchTypes()
          await fetchAvailableSlot()
          await displayfunctionEditCard()
          $quasar.loading.hide()
        }
      } catch (error) {
        $quasar.loading.hide();

        if (error.response.status === 422) {
          formError.value = error.response.data.errors
        }else if (error.response.status === 400) {
          Swal.fire({
            icon: "error",
            title: "Oops...",
            text: error.response.data.error,
            confirmButtonText: "OK",  // Custom text for the button
            confirmButtonColor: '#3085d6',  // Set the confirm button color to Tomato redcs
          });
          displayEditCard.value = false;
        }
      }
    }

    const cancelAppointment = async () => {
      try {
        $quasar.loading.show({
          message: "Process is in progress. Hang on...",
        });

        formError.value = {};
        const { data } = await $axios.post('/validate/Appointment',{
          referenceNum: referenceNum.value
        })

        if (data.reference) {
          $quasar.loading.hide();
            $quasar.dialog({
            title: "Confirm",
            message: "Are you sure you want to cancel appointment?",
            cancel: true,
            persistent: true,
            ok: {
              label: "Yes",
              color: "primary",
              unelevated: true,
              style: "width: 125px;",
            },
            cancel: {
              label: "Cancel",
              color: "red-8",
              unelevated: true,
              style: "width: 125px;",
            },
            style: "border-radius: 12px; padding: 16px;",
          })
          .onOk(async () => {
            handleDelete([data.reference])
          })
          .onDismiss(()=> {

          });
        }

      } catch (error) {
        $quasar.loading.hide();
        
        if (error.response?.status === 422) {
          formError.value = error.response.data.errors
          console.log(error.response.data.errors)
        }else if (error.response.status === 400) {
          Swal.fire({
            icon: "error",
            title: "Oops...",
            text: error.response.data.error,
            confirmButtonText: "OK",  // Custom text for the button
            confirmButtonColor: '#3085d6',  // Set the confirm button color to Tomato redcs
          }); 
        }
      }
    }

    const handleDelete = async (dataHandleCancel) => {
      try {
          const { data } = await $axios.post('/cancel/Appointment', {
            /* id: reference.id,
            appointment_date: reference.appointment_date,
            branch_id: reference.branch_id, */
            dataHandleCancel
          });
         
          if (data.message) {
            Swal.fire({
                  title: data.message,
                  icon: "success",
                  draggable: true,
                  confirmButtonText: 'OK',
                  confirmButtonColor: '#3085d6',  // Set the confirm button color to Tomato redcs
                });
            clearForm()
          }
      } catch (error) {
        if (error.response.status === 422) {
          console.log(error)
        }
      }
    }
    
    const updateAppointment = async () => {
      try {
        $quasar.dialog({
            title: "Confirm",
            message: "Are you sure you want to update appointment?",
            cancel: true,
            persistent: true,
            ok: {
              label: "Yes",
              color: "primary",
              unelevated: true,
              style: "width: 125px;",
            },
            cancel: {
              label: "Cancel",
              color: "red-8",
              unelevated: true,
              style: "width: 125px;",
            },
            style: "border-radius: 12px; padding: 16px;",
          })
          .onOk(async () => {
            formError.value = {}
              const { data } = await $axios.post('/update/Appointment', form.value)

              if (data.message) {
                Swal.fire({
                  title: data.message,
                  icon: "success",
                  draggable: true,
                  confirmButtonText: 'OK',
                  confirmButtonColor: '#3085d6',  // Set the confirm button color to Tomato redcs
                });
                clearForm()
              }
          })
          .onDismiss(()=> {

          });

      } catch (error) {
        if (error.response.status === 422) {
          console.log(error.response.data.error)
          formError.value = error.response.data.errors
        }
      } finally {

      }
    }

    const clearForm = () => {
      form.value = {
        branch_id: "",
        name: "",
        email: "",
        service: "",
        appointment_date: "",
      };
      referenceNum.value = null;
      selectedBranch.value = null;
      formError.value = {};
      choosing.value = true;
      displayEditCard.value = false;
      editCard.value = false;
      deleteCard.value = false;
    };
    

    const startCreate = () => {
      clearForm();
      choosing.value = false;
      editCard.value = false;
      deleteCard.value = false;
      createCard.value = true
    };

    const startEdit = () => {
      choosing.value = false;
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

    const displayfunctionEditCard = () => {
      displayEditCard.value = true
      choosing.value = false;
      editCard.value = true;
      deleteCard.value = false;
      createCard.value = false
    }

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
      cancelAppointment,
      formError,
      createCard,
      deleteCard,
      referenceNum,
      editCard,
      choosing,
      clearForm,
      startCreate,
      startEdit,
      startDelete,
      editAppointment,
      displayEditCard,
      displayfunctionEditCard,
      updateAppointment,
      successUpdateCard,
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
