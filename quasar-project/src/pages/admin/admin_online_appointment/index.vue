<template>
  <q-page class="q-px-sm" style="height: auto; min-height: unset">
    <div class="q-ma-md bg-white q-pa-sm shadow-1">
      <q-breadcrumbs class="q-mx-sm">
        <q-breadcrumbs-el icon="home" to="/admin/dashboard" />
        <q-breadcrumbs-el label="Branch Appointment" icon="person" />
        <q-breadcrumbs-el
          label="Appoitnemnt"
          icon="groups"
          to="/admin/appointment/create"
        />
      </q-breadcrumbs>
    </div>
    <q-card class="q-ma-md" flat bordered>
      <q-card-section>
        <div class="text-h6 q-mb-md">Available Slots Configuration</div>

        <q-form @submit.prevent="applySlotsForWeek">
          <div class="">
            <!-- Branch Selection -->
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
              required
              dense
            />

            <!-- Date Picker -->
            <VDatePicker
              v-model="range"
              is-range
              mode="date"
              :is-date-disabled="checkDate"
              :attributes="attributes"
              color="blue"
              :min-date="new Date()"
              :columns="columns"
              expanded
              class="q-my-md"
            >
              <template #default="{ inputValue, inputEvents }">
                <div class="flex justify-center items-center gap-2">
                  <q-input
                    v-model="inputValue.start"
                    v-on="inputEvents.start"
                    dense
                    outlined
                    label="Start Date"
                  />
                  <q-icon name="arrow_forward" class="q-mx-sm" />
                  <q-input
                    v-model="inputValue.end"
                    v-on="inputEvents.end"
                    dense
                    outlined
                    label="End Date"
                  />
                </div>
              </template>
            </VDatePicker>

            <!-- Time Picker -->
            <q-input
              v-model="form.time"
              label="Prepared Time"
              outlined
              dense
              type="time"
              mask="time"
              class="q-my-sm"
            >
              <template v-slot:append>
                <q-popup-proxy
                  cover
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <q-time v-model="form.time" icon="none">
                    <div class="row items-center justify-end">
                      <q-btn v-close-popup label="Close" color="primary" flat />
                    </div>
                  </q-time>
                </q-popup-proxy>
              </template>
            </q-input>

            <!-- Number of Slots per Day -->
            <q-input
              v-model="newSlotCount"
              label="Available Slots per Day"
              type="number"
              filled
              dense
              required
              class="q-my-sm"
            />

            <!-- Apply Slots Button -->
            <q-btn
              label="Apply Slots"
              color="primary"
              type="submit"
              class="q-mt-md"
            />
          </div>
        </q-form>
      </q-card-section>
    </q-card>

    <!-- Display the available slots for the week -->
    <q-card class="q-ma-md" flat bordered>
      <q-card-section>
        <div class="text-h6">Weekdays Slots Overview</div>
        <FullCalendar ref="calendarRef" :options="calendarOptions" />
      </q-card-section>
    </q-card>
  </q-page>
</template>
<script>
import { ref, onMounted, watch, computed, nextTick } from "vue";
import axios from "axios";
import {
  QSelect,
  QInput,
  QBtn,
  QCard,
  QCardSection,
  QTable,
  QForm,
} from "quasar";
import FullCalendar from "@fullcalendar/vue3";
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction";
import { useScreens } from "vue-screen-utils";

export default {
  name: "AdminDashboard",
  components: {
    QSelect,
    QInput,
    QBtn,
    QCard,
    QCardSection,
    QTable,
    QForm,
    FullCalendar,
  },
  setup() {
    const branches = ref([]); // List of branches
    const selectedBranch = ref(null); // Selected branch ID
    const newSlotCount = ref(20); // New slot count to be applied
    const events = ref([]);
    const adminManagerInformation = ref();
    const form = ref({
      startDate: "",
      endDate: "",
      time: "",
    });

    const { mapCurrent } = useScreens({
      xs: "0px",
      sm: "640px",
      md: "768px",
      lg: "1024px",
    });
    const columns = mapCurrent({ lg: 2 }, 1);

    const range = ref({
      start: null,
      end: null,
    });

    const isWeekend = (date) => {
      const day = new Date(date).getDay();
      return day === 0 || day === 6;
    };

    const attributes = computed(() => {
      const attrs = [];

      // Disable weekends (greyed out)
      attrs.push({
        key: "disabled-weekends",
        dates: isWeekend,
        contentStyle: {
          backgroundColor: "#f0f0f0",
          color: "#999",
          pointerEvents: "none",
          opacity: 0.6,
        },
      });

      // Range highlight excluding weekends
      if (range.value.start && range.value.end) {
        const days = [];
        const current = new Date(range.value.start);
        const end = new Date(range.value.end);

        while (current <= end) {
          const day = current.getDay();
          if (day !== 0 && day !== 6) {
            days.push(new Date(current));
          }
          current.setDate(current.getDate() + 1);
        }

        attrs.push({
          key: "highlight-range",
          highlight: {
            start: { fillMode: "outline" },
            base: { fillMode: "light" },
            end: { fillMode: "outline" },
          },
          dates: days,
        });
      }

      return attrs;
    });

    watch(range, (val) => {
      console.log('Selected Date Range:', {
        raw: val,
        formatted: {
          start: val.start ? new Date(val.start.getTime() - (val.start.getTimezoneOffset() * 60000))
            .toISOString().split('T')[0] : null,
          end: val.end ? new Date(val.end.getTime() - (val.end.getTimezoneOffset() * 60000))
            .toISOString().split('T')[0] : null
        }
      });
      form.value.startDate = val.start;
      form.value.endDate = val.end;
      form.value.time = val.start
        ? `${val.start.getHours().toString().padStart(2, "0")}:${val.start
            .getMinutes()
            .toString()
            .padStart(2, "0")}`
        : "";
    });

    const checkDate = ({ date, mode }) => {
      const day = new Date(date).getDay();
      if (mode === "start" || mode === "end") {
        return day === 0 || day === 6; // Sunday or Saturday
      }
      return false;
    };

    const calendarOptions = ref({
      plugins: [dayGridPlugin, interactionPlugin],
      initialView: "dayGridMonth",
      //weekends: false,
      events: events,
    });

    // Fetch branches to populate the select field
    const fetchBranches = async () => {
      try {
        const response = await axios.post("/type/Branch"); // Fetch branches
        branches.value = response.data.branch;
      } catch (error) {
        console.error("Error fetching branches:", error);
      }
    };

    // Fetch the available slots for each weekday
    const fetchWeeklySlots = async () => {
      if (!selectedBranch.value) return;

      // If we have a date range, use it; otherwise use today to end of month
      let startDate = form.value.startDate;
      let endDate = form.value.endDate;

      if (!startDate || !endDate) {
        const today = new Date();
        startDate = today;
        endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0); // Last day of current month
      }

      // Format dates for API with timezone adjustment
      const formattedStartDate = new Date(startDate.getTime() - (startDate.getTimezoneOffset() * 60000))
        .toISOString().split('T')[0];
      const formattedEndDate = new Date(endDate.getTime() - (endDate.getTimezoneOffset() * 60000))
        .toISOString().split('T')[0];

      console.log(
        "Fetching slots for date range:",
        formattedStartDate,
        "to",
        formattedEndDate
      );

      try {
        const { data } = await axios.post(`/branches/slots`, {
          branch_id: selectedBranch.value,
          start_date: formattedStartDate,
          end_date: formattedEndDate,
          fetch_only_range: true, // Add this flag to tell backend to only return dates in range
        });
        events.value = data.availableSlots.map((slot) => ({
          title: `${
            slot.is_available > 0 ? slot.is_available : "No"
          } available`,
          start: `${slot.date}`,
          extendedProps: {
            slotId: slot.id,
            isAvailable: slot.is_available,
          },
          color: slot.is_available > 0 ? "#28a745" : "#dc3545", // Green for available, Red for booked
        }));
      } catch (error) {
        console.error("Error fetching weekly slots:", error);
      }
    };

    // Apply slots for the entire week (Monday to Friday) for the selected branch
    const applySlotsForWeek = async () => {
      if (!form.value.startDate || !form.value.endDate) {
        alert("Please select a date range");
        return;
      }

      try {
        // Ensure we're using the full day for start and end dates
        const startDate = new Date(form.value.startDate);
        startDate.setHours(0, 0, 0, 0);
        const endDate = new Date(form.value.endDate);
        endDate.setHours(23, 59, 59, 999);

        // Format dates for backend with timezone adjustment
        const formattedStartDate = new Date(startDate.getTime() - (startDate.getTimezoneOffset() * 60000))
          .toISOString().split('T')[0];
        const formattedEndDate = new Date(endDate.getTime() - (endDate.getTimezoneOffset() * 60000))
          .toISOString().split('T')[0];

        console.log("Applying slots for date range:", formattedStartDate, "to", formattedEndDate);

        // Send request to backend to apply available slots to all days
        const response = await axios.post("/apply_slots", {
          branch_id: selectedBranch.value,
          slots_per_day: newSlotCount.value,
          start_date: formattedStartDate,
          end_date: formattedEndDate,
          time: form.value.time,
        });

        // Update calendar view to show only the selected date range
        updateCalendarView(formattedStartDate, formattedEndDate);

        // Refresh the weekly slots display with the same date range
        await fetchWeeklySlots();
      } catch (error) {
        console.error("Error applying slots for the week:", error);
      }
    };

    // Update calendar view to display only the selected date range
    const updateCalendarView = (startDate, endDate) => {
      nextTick(() => {
        if (calendarRef.value) {
          const calendarApi = calendarRef.value.getApi();
          if (calendarApi) {
            calendarApi.gotoDate(startDate);

            // Focus calendar view on the selected date range
            const viewStart = new Date(startDate);
            const viewEnd = new Date(endDate);
            calendarApi.changeView("dayGridMonth", {
              start: viewStart,
              end: new Date(viewEnd.getTime() + 86400000), // Add one day to include end date
            });
          }
        }
      });
    };

    watch(
      () => selectedBranch.value,
      (newVal) => {
        if (newVal) {
          fetchWeeklySlots();
        }
      }
    );

    // Fetch branches and weekly slots on component mount
    onMounted(() => {
      const managerInformation = localStorage.getItem("managerInformation");
      if (managerInformation) {
        adminManagerInformation.value = JSON.parse(managerInformation);
      }
      console.log(adminManagerInformation.value);
      fetchBranches();
      if (adminManagerInformation.value != null) {
        selectedBranch.value = adminManagerInformation.value.branch_id;

        // Set default date range to current month for initial load
        const today = new Date();
        const endOfMonth = new Date(
          today.getFullYear(),
          today.getMonth() + 1,
          0
        ); // Last day of current month

        // Update both the form values and the date picker range
        form.value.startDate = today;
        form.value.endDate = new Date(
          today.getFullYear(),
          today.getMonth() + 1,
          0
        ); // Last day of current month

        fetchWeeklySlots();
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
      attributes,
      range,
      checkDate,
      columns,
      calendarRef: ref(null),
      updateCalendarView,
    };
  },
};
</script>

<style scoped></style>
