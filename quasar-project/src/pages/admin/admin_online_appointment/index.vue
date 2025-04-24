 

<template>
    <q-page class="q-px-sm" style="height: auto; min-height: unset">
      <div class="q-ma-md bg-white q-pa-sm shadow-1">
        <q-breadcrumbs class="q-mx-sm">
          <q-breadcrumbs-el icon="home" to="/admin/dashboard" />
          <q-breadcrumbs-el label="Branch Appointment" icon="person" />
          <q-breadcrumbs-el
            label="Appointment"
            icon="event"
            to="/admin/appointment/create"
          />
        </q-breadcrumbs>
      </div>
      <q-card class="q-ma-md" flat bordered>
            <q-card-section>
              <div class="text-h6">Available Slots Configuration</div>

              <!-- Slot Configuration Form -->
              <q-form @submit.prevent="applySlotsForWeek">
                <div class="q-gutter-md">
                  <q-select
                    v-if="!adminManagerInformation"
                    v-model="selectedBranch"
                    :options="branches"
                    label="Select Branch"
                    map-options
                    emit-value
                    option-label="branch_name"
                    option-value="id"
                    filled
                    :error="formError.hasOwnProperty('branch_id')"
                    :error-message="formError.branch_id? 'The select branch field cannot be blank' :''"
                    required
                    dense
                  />

                  <!-- Input to select the start and end date -->
                    <q-input
                        v-model="form.startDate"
                        label="Start Date (MM-DD-YYYY)"
                        outlined
                        dense
                        type="date"
                        mask="date"
                        :error="formError.hasOwnProperty('start_date')"
                        :error-message="formError.start_date ? formError.start_date[0]: ''"
                    />

                    <q-input
                        v-model="form.endDate"
                        label="End Date (MM-DD-YYYY)"
                        outlined
                        type="date"
                        dense
                        mask="date"
                        :error="formError.hasOwnProperty('end_date')"
                        :error-message="formError.end_date ? formError.end_date[0] : ''"
                    />

                  <q-input 
                    v-model="form.time" 
                    label="Prepared Time" 
                    outlined 
                    dense type="time"
                     mask="time"
                     :error="formError.hasOwnProperty('time')"
                     :error-message="formError.time ? formError.time[0] : ''"
                     >
                    <template v-slot:append>
                      <q-icon name="access_time" class="cursor-pointer">
                        <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                          <q-time v-model="form.time">
                            <div class="row items-center justify-end">
                              <q-btn v-close-popup label="Close" color="primary" flat />
                            </div>
                          </q-time>
                        </q-popup-proxy>
                      </q-icon>
                    </template>
                  </q-input>

                  <q-input
                    v-model="newSlotCount"
                    label="Available Slots per Day"
                    type="number"
                    filled
                    dense
                    required
                  />

                  <q-btn label="Apply Slots" color="primary" type="submit" />
                </div>
              </q-form>
            </q-card-section>
          </q-card>

          <!-- Display the available slots for the week -->
          <q-card class="q-ma-md" flat bordered>
            <q-card-section>
              <div class="text-h6">Weekdays Slots Overview</div>
                <FullCalendar 
                :options="calendarOptions"
                /> 
            </q-card-section>
        </q-card>
    </q-page>
  </template>
<script>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { QSelect, QInput, QBtn, QCard, QCardSection, QTable, QForm } from 'quasar';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import { $notify } from 'src/boot/app';

export default {
  name: 'AdminDashboard',
  components: {
    QSelect,
    QInput,
    QBtn,
    QCard,
    QCardSection,
    QTable,
    QForm,
    FullCalendar
  },
  setup() {
    const branches = ref([]); // List of branches
    const selectedBranch = ref(null); // Selected branch ID
    const newSlotCount = ref(20); // New slot count to be applied
    const events = ref([]);
    const adminManagerInformation = ref ();
    const formError = ref({})

    const form = ref({
      startDate: "",
      endDate: "",
      time: "",
    });

    const calendarOptions = ref({
      plugins: [dayGridPlugin, interactionPlugin],
      initialView: 'dayGridMonth',
      //weekends: false,
      events: events
   });

    // Fetch branches to populate the select field
    const fetchBranches = async () => {
      try {
        const response = await axios.post('/type/Branch'); // Fetch branches
        branches.value = response.data.branch;
      } catch (error) {
        console.error('Error fetching branches:', error);
      }
    };

    // Apply slots for the entire week (Monday to Friday) for the selected branch
    const applySlotsForWeek = async () => {
      try {
        formError.value = {}
        // Send request to backend to apply available slots to all days
        const response = await axios.post('/apply_slots', {
            branch_id: selectedBranch.value,
            slots_per_day: newSlotCount.value,
            start_date: form.value.startDate,
            end_date: form.value.endDate,
            time: form.value.time,
        });

        if (response.data.message) {
          $notify('positive','check',response.data.message)
          // Refresh the weekly slots display
          fetchWeeklySlots();
        }

      } catch (error) {
        if (error.response.status === 422) {
          formError.value = error.response.data.errors
          console.log(error.response.data.errors)
        }
      }
    };

    // Fetch the available slots for each weekday
    const fetchWeeklySlots = async () => {
      if (!selectedBranch.value) return;
      try {
        const { data } = await axios.post(`/branches/slots`, {
            branch_id: selectedBranch.value
        });
        events.value = data.availableSlots.map(slot => ({
          title: `${slot.is_available > 0 ? slot.is_available : 'No'} available`,
          start: `${slot.date}`,
          extendedProps: {
            slotId: slot.id,
            isAvailable: slot.is_available
          },
          color: slot.is_available > 0 ? '#28a745' : '#dc3545' // Green for available, Red for booked
        }));
      } catch (error) {
        console.error('Error fetching weekly slots:', error);
      }
    };

    watch(()=> selectedBranch.value , (newVal) => {
        if (newVal) {
            fetchWeeklySlots()
        }
    });

    // Fetch branches and weekly slots on component mount
    onMounted(() => {
      const managerInformation = localStorage.getItem('managerInformation')
      if (managerInformation) {
        adminManagerInformation.value = JSON.parse(managerInformation)
      }
      console.log(adminManagerInformation.value)
      fetchBranches();
      if (adminManagerInformation.value != null) {
        selectedBranch.value = adminManagerInformation.value.branch_id
        fetchWeeklySlots()
      }

    });

    return {
    form,
      branches,
      selectedBranch,
      newSlotCount,
      calendarOptions,
      applySlotsForWeek,
      fetchWeeklySlots,
      events,
      adminManagerInformation,
      formError,
    };
  },
};
</script>

  
  <style scoped>
   
  </style>
 