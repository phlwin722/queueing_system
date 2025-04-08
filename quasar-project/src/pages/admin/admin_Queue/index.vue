<template>
  <q-page class="q-px-md">
    <div class="q-my-md bg-white q-pa-sm shadow-1">
      <q-breadcrumbs class="q-mx-sm">
        <q-breadcrumbs-el icon="home" to="/admin/dashboard" />
        <q-breadcrumbs-el
          label="Admin Queue"
          icon="admin_panel_settings"
          to="/admin/admin_Queue"
        />
      </q-breadcrumbs>
    </div>

    <q-card class="q-pa-md">
      <!-- Service Type Selector -->
      <div class="q-mb-md row q-col-gutter-md">
        <!-- Window Type Select -->
        <div class="col-6">
          <q-select
            outlined
            v-model="type_id"
            :options="serviceTypeList"
            label="Window type"
            hide-bottom-space
            dense
            emit-value
            map-options
          />
        </div>

        <!-- Assigned Personnel Select -->
        <div class="col-6">
          <q-select
            outlined
            v-model="teller_id"
            :options="personnelList"
            label="Assigned personnel"
            dense
            hide-bottom-space
            emit-value
            map-options
          />
        </div>
      </div>

      <!--       <div class="row gap-12" style="gap: 16px; width: 500px">
        <q-card class="q-pa-md col-12 col-md">
          <q-card-section class="q-pa-md">
            <p>Service Type: dad</p>
            <p>Assigned Teller: dad dasd</p>
          </q-card-section>
          <q-separator />
          <q-card-section
            class="q-pa-md bg-primary"
            style="border: 2px solid black"
          >
            <h6 class="text-center text-white">CURRENT QUEUE</h6>
            <p class="text-center text-white">4324</p>
            <p class="text-center text-white">dasddasd</p>
          </q-card-section>
          <q-card-section>
            <q-separator />
            <p class="text-center text-h6">Waiting Queue</p>
            <q-item class="q-pa-none">
              <q-item-section class="q-pa-none">
                <q-scroll-area
                  class="my-scroll"
                  style="height: 200px; overflow-y: auto; overflow-x: hidden"
                >
                  <q-list class="q-pa-sm" bordered separator>
                    <q-item v-ripple class="shadow-2 border">
                      <q-item-section>
                        <div class="text-primary text-bold text-h6 q-mb-xs">
                          queue
                        </div>

                        <p class="text-body2 text-secondary q-mb-none">
                          Pedro Penduko
                        </p>
                      </q-item-section>
                      <q-item-section side>
                        <q-badge />
                      </q-item-section>
                    </q-item>
                  </q-list>
                </q-scroll-area>
              </q-item-section>
            </q-item>
          </q-card-section>
        </q-card>
      </div> -->

      <!-- creating the list of all tellers -->
      <div v-if="assignedTellers.length === 0">Loading...</div>
      <!-- v-for the service type eg. Deposit, Withdrawal and so on -->
      <div class="row" style="gap: 16px; column-gap: 16px">
        <div v-for="service in assignedTellers" :key="service.type_id">
          <!-- Loop through each teller inside the service -->
          <div
            v-for="teller in sortedTellers(service.tellers)"
            :key="teller.id"
          >
            <q-card class="q-pa-md">
              <!-- Display service type name for each individual teller -->
              <q-card-section class="q-pa-md">
                <p>Service Type: {{ service.type_name }}</p>
                <p>
                  Assigned Teller: {{ teller.teller_firstname }}
                  {{ teller.teller_lastname }}
                </p>
              </q-card-section>

              <q-separator />

              <q-card-section
                class="q-pa-md bg-primary"
                style="border: 2px solid black"
              >
                <!-- Check if a teller is currently serving a customer -->
                <div
                  v-if="teller.currently_served && teller.currently_served.name"
                >
                  <h6 class="text-center text-white">CURRENT QUEUE</h6>
                  <p class="text-center text-white">
                    {{ teller.currently_served.queue_number }}
                  </p>
                  <p class="text-center text-white">
                    {{ teller.currently_served.name }}
                  </p>
                </div>
                <div v-else>
                  <p>No customer is being served currently</p>
                </div>
              </q-card-section>

              <q-card-section>
                <q-separator />
                <div v-if="teller.waiting_customers.length">
                  <p class="text-center text-h6">Waiting Queue</p>
                  <q-item class="q-pa-none">
                    <q-item-section class="q-pa-none">
                      <q-scroll-area
                        class="my-scroll"
                        style="
                          height: 250px;
                          overflow-y: auto;
                          overflow-x: hidden;
                        "
                      >
                        <q-list class="q-pa-sm" bordered separator>
                          <q-item
                            v-ripple
                            class="shadow-2 border"
                            v-for="customer in queueLists(teller)"
                            :key="customer.queue_number"
                          >
                            <q-item-section>
                              <div
                                class="text-primary text-bold text-h6 q-mb-xs"
                              >
                                {{ customer.queue_number }}
                              </div>
                              <p class="text-body2 text-secondary q-mb-none">
                                {{ customer.name }}
                              </p>
                            </q-item-section>
                            <q-item-section side>
                              <q-badge />
                            </q-item-section>
                          </q-item>
                        </q-list>
                      </q-scroll-area>
                    </q-item-section>
                  </q-item>
                </div>
                <div v-else>
                  <p class="text-center text-h6">Waiting Queue</p>
                  <q-item class="q-pa-none">
                    <q-item-section class="q-pa-none">
                      <q-list
                        class="q-pa-sm"
                        bordered
                        separator
                        style="
                          height: 250px;
                          overflow-y: auto;
                          overflow-x: hidden;
                        "
                      >
                      </q-list>
                    </q-item-section>
                  </q-item>
                </div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </div>
    </q-card>
  </q-page>
</template>

<script>
import {
  ref,
  computed,
  onMounted,
  onUnmounted,
  watch,
  nextTick,
  onBeforeUnmount,
} from "vue";
import { $axios, $notify, Dialog } from "boot/app";
import { useQuasar } from "quasar";

export default {
  setup() {
    const cusId = ref();
    const queueList = ref([]);
    const currentServing = ref(null);
    const waiting = ref(false);
    const waitTime = ref(30);
    const prepared = ref();
    let waitTimer = null;
    const tempTimer = ref();
    const originalWaitTime = ref(0); // Store the original wait time
    const isQueuelistEmpty = ref(false);
    let refreshInterval = null;
    const $dialog = useQuasar();
    const noOfQueue = ref();
    const type_id = ref();
    const teller_id = ref();
    const serviceTypeList = ref([]);
    const personnelList = ref([]);
    const cusName = ref();
    const cusQueueNum = ref();
    const servingStatus = ref();
    const tellerFullName = ref();

    // Pagination
    const currentPage = ref(1);
    const itemsPerPage = 5;

    // UI Functions
    const $q = useQuasar();
    const menuOpen = ref(false);
    const toggleFullscreen = () => {
      $q.fullscreen.toggle();
    };

    // Fetch queue data
    // cusId.value = currentServing.value = response.data.current_serving.id
    const fetchQueue = async () => {
      try {
        const response = await $axios.post("/teller/queue-list", {
          type_id: type_id.value,
          teller_id: teller_id.value,
        });
        queueList.value = response.data.queue.filter(
          (q) => !["finished", "cancelled"].includes(q.status)
        );
        currentServing.value = response.data.current_serving;
        cusName.value = response.data.name;
        cusQueueNum.value = response.data.queue_number;
        servingStatus.value = response.data.status;
        tellerFullName.value = response.data.fullname;
        // queuePosition.value = queueList.value.findIndex(q => q.queue_number == response.data.queue_numbers[0]) + 1
        // console.log(queuePosition.value)
        // console.log(response.data.queue_numbers)
        noOfQueue.value = queueList.value.length;
        if (
          queueList.value.length > 0 &&
          queueList.value[0].status === "waiting" &&
          currentServing.value == null
        ) {
          // caterCustomer(queueList.value[0].id);
          // startWait(queueList.value[0].id, queueList.value[0].queue_number)
        }
        isQueuelistEmpty.value = queueList.value.length == 0;
      } catch (error) {
        console.error(error);
      }
    };

    const fetchWindowTypes = async () => {
      try {
        const response = await $axios.post("/types/dropdown");
        if (Array.isArray(response.data.rows)) {
          // Map id and section_name correctly
          serviceTypeList.value = response.data.rows.map((sec) => ({
            label: sec.name, // This is what the user sees
            value: sec.id, // This is what will be stored
          }));
        } else {
          console.error(
            'Expected "rows" to be an array, but got:',
            response.data.rows
          );
        }
      } catch (error) {
        console.error("Error fetching sections:", error);
      }
    };

    const fetchPersonnel = async () => {
      try {
        if (!type_id.value) return; // Stop if no grade level is selected
        const response = await $axios.post("/tellers/dropdown", {
          type_id: type_id.value,
        });

        console.log(response.tellers);
        if (Array.isArray(response.data.rows)) {
          personnelList.value = response.data.rows; // Response is already in { label, value } format
        } else {
          console.error(
            'Expected "rows" to be an array, but got:',
            response.data.rows
          );
        }
      } catch (error) {
        console.error("Error fetching sections:", error);
      }
      console.log(type_id.value);
    };

    const fetchId = async () => {
      try {
        const response = await $axios.post("/admin/queue-list");
        cusId.value = response.data.current_serving.id;
      } catch (error) {
        console.error(error);
      }
    };

    watch(
      () => type_id.value,
      async (newVal) => {
        if (newVal) {
          personnelList.value = []; // Clear previous personnel list

          await fetchPersonnel(); // Fetch new personnel based on selected type

          // Wait for Vue to update the list, then set the first teller
          nextTick(() => {
            if (typeof type_id.value === "string") {
              teller_id.value = teller_id.value; // Assign first teller's ID
              fetchtypeId();
            } else {
              if (personnelList.value.length > 0) {
                teller_id.value = personnelList.value[0].value;
              } else {
                teller_id.value = null;
                return;
              }

              fetchtypeId();
            }
          });
        }
      }
    );

    // Cater customer
    const caterCustomer = async (customerId) => {
      try {
        await $axios.post("/teller/cater", {
          id: customerId,
          service_id: type_id.value,
        });
        fetchQueue();
        fetchId();
      } catch (error) {
        console.error(error);
        $notify(
          "negative",
          "error",
          "You are currently serving a customer. Please finish it first!."
        );
      }
    };

    //cancel dialog
    const beforeCancel = (row) => {
      $dialog
        .dialog({
          title: "Confirm",
          message: "Are you sure do you want cancel this queue?",
          cancel: true,
          persistent: true,
          ok: {
            label: "Yes",
            color: "primary", // Make confirm button red
            unelevated: true, // Flat button style
            style: "width: 125px;",
          },
          cancel: {
            label: "Cancel",
            color: "red-8", // Make cancel button grey
            unelevated: true,
            style: "width: 125px;",
          },
          style: "border-radius: 12px; padding: 16px;",
        })
        .onOk(() => {
          cancelCustomer(row.id);
        })
        .onDismiss(() => {
          // console.log('I am triggered on both OK and Cancel')
        });
    };

    // Cancel customer
    const cancelCustomer = async (customerId) => {
      try {
        await $axios.post("/admin/cancel", { id: customerId });
        fetchQueue();
        $notify(
          "positive",
          "check",
          "Customer has been removed from the queue."
        );
        stopWait(); // Stop wait if customer is canceled
      } catch (error) {
        console.error(error);
        $notify("negative", "error", "Failed to cancel customer.");
      }
    };

    // Finish serving customer
    const finishCustomer = async (customerId) => {
      try {
        await $axios.post("/admin/finish", { id: customerId });
        fetchQueue();

        $notify("positive", "check", "Customer has been marked as finished.");
      } catch (error) {
        console.error(error);
        $notify("negative", "error", "Failed to finish serving.");
      }
    };

    // Start waiting process
    const startWait = async (customerId, queueNumber) => {
      try {
        await $axios.post("/waitCustomer", { id: customerId });

        if (waiting.value) return; // Prevent multiple clicks while waiting

        waiting.value = true;

        // Fetch and store the original wait time if not set
        if (originalWaitTime.value === 0) {
          originalWaitTime.value =
            prepared.value === "Minutes" ? waitTime.value * 60 : waitTime.value;
        }

        // Set the start time in localStorage
        const startTime = Math.floor(Date.now() / 1000); // Current timestamp in seconds
        localStorage.setItem("wait_start_time", startTime);
        localStorage.setItem("wait_duration", originalWaitTime.value);

        // Reset the wait time
        tempTimer.value = originalWaitTime.value;

        $notify(
          "positive",
          "check",
          "Waiting for Queue Number: " + queueNumber
        );

        // Clear any existing timer
        if (waitTimer) clearInterval(waitTimer);

        startTimer(customerId);
      } catch (error) {
        console.error(error);
        $notify("negative", "error", "Failed to set waiting customer.");
      }
    };

    const resetWait = async (id) => {
      const response = await $axios.post("/waitCustomerReset", { id: id });
      console.log(response.message);
    };

    // Start the countdown timer
    const startTimer = (id) => {
      if (waitTimer) clearInterval(waitTimer);

      waitTimer = setInterval(() => {
        const now = Math.floor(Date.now() / 1000);
        const startTime =
          parseInt(localStorage.getItem("wait_start_time")) || 0;
        const duration = parseInt(localStorage.getItem("wait_duration")) || 0;
        const elapsed = now - startTime;
        const remaining = duration - elapsed;

        if (remaining >= 0) {
          tempTimer.value = remaining;
          if (tempTimer.value === 0) {
            resetWait(cusId.value);
          }
        } else {
          stopWait();
          originalWaitTime.value = 0;
          localStorage.removeItem("wait_start_time");
          localStorage.removeItem("wait_duration");
        }
      }, 1000);
    };

    // Fetch the data from the backend when the component is mounted
    const fetchWaitingtime = async () => {
      try {
        const { data } = await $axios.post("/admin/waiting_Time-fetch");
        // Ensure that data.dataValue is available before trying to assign it to formData
        if (data && data.dataValue && data.dataValue.length > 0) {
          waitTime.value = data.dataValue[0].Waiting_time; // Assign the first object in dataValue to formData
          prepared.value = data.dataValue[0].Prepared;
        } else {
          console.log("No data available");
        }
      } catch (error) {
        console.log("Error fetching data:", error);
      }
    };

    // Format time as MM:SS
    const formatTime = (seconds) => {
      if (seconds == null) {
        return `.....`;
      }
      if (seconds >= 60) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        return `${minutes} m ${remainingSeconds} s`;
      }
      return `${seconds} s`;
    };

    // Stop waiting process
    const stopWait = () => {
      waiting.value = false;
      clearInterval(waitTimer);
      localStorage.removeItem("wait_start_time");
      localStorage.removeItem("wait_duration");
    };

    // Reset Queue Number
    const resetQueue = async () => {
      try {
        $dialog
          .dialog({
            title: "Confirm",
            message: "Are you sure do you want reset queue?",
            cancel: true,
            persistent: true,
            color: "primary",
            ok: {
              label: "Yes",
              color: "primary", // Make confirm button red
              unelevated: true, // Flat button style
              style: "width: 125px;",
            },
            cancel: {
              label: "Cancel",
              color: "red-8", // Make cancel button grey
              unelevated: true,
              style: "width: 125px;",
            },
            style: "border-radius: 12px; padding: 16px;",
          })
          .onOk(async () => {
            const response = await $axios.post("/resetQueue");
            $notify("positive", "check", response.data.message);
            console.log(response.data.message);
          })
          .onDismiss(() => {
            // console.log('I am triggered on both OK and Cancel')
          });
      } catch (error) {
        console.error(error);
        $notify("negative", "error", "Failed to set waiting customer.");
      }
    };

    // Computed property for paginated queue list
    const paginatedQueueList = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage;
      return queueList.value.slice(start, start + itemsPerPage);
    });

    // Total pages for pagination
    const totalPages = computed(() =>
      Math.ceil(queueList.value.length / itemsPerPage)
    );

    let waitingQueue;

    const optimizedFetchQueue = async () => {
      await fetchQueue();
      waitingQueue = setTimeout(optimizedFetchQueue, 3000); // Recursive Timeout
    };

    const assignedTellers = ref([]);
    const lastFetched = ref(null);
    const queueListFromStorage = ref([]);

    const fetchAssignedTellers = async () => {
      try {
        const response = await $axios.post("/tellers/fetch-assigned");
        assignedTellers.value = response.data.services
          .map((service) => ({
            ...service,
            tellers: service.tellers.filter((teller) => teller.window_name),
          }))
          .filter((service) => service.tellers.length > 0);
      } catch (error) {
        console.log("Error fetching: ", error);
      }
    };

    const queueLists = (teller) => {
      const fromStorage = queueListFromStorage.value.filter(
        (q) => q.teller_id === teller.id
      );
      return fromStorage.length ? fromStorage : teller.waiting_customers || [];
    };

    const sortedTellers = (tellers) => {
      return tellers.sort((a, b) => {
        if (a.currently_served?.name) return -1;
        if (b.currently_served?.name) return 1;
        return 0;
      });
    };

    const loadQueueList = () => {
      const raw = localStorage.getItem("queueList");
      if (raw) {
        const parsed = JSON.parse(raw);
        if (
          JSON.stringify(parsed) !== JSON.stringify(queueListFromStorage.value)
        ) {
          queueListFromStorage.value = parsed;
        }
      }
    };

    let intervalQueue, intervalFetch;

    onMounted(() => {
      loadQueueList();
      intervalQueue = setInterval(loadQueueList, 1000);

      fetchAssignedTellers();
      intervalFetch = setInterval(() => {
        lastFetched.value = new Date().toISOString();
      }, 2000);
    });

    onBeforeUnmount(() => {
      clearInterval(intervalQueue);
      clearInterval(intervalFetch);
    });

    watch(lastFetched, fetchAssignedTellers);

    onMounted(() => {
      optimizedFetchQueue();
      fetchWaitingtime();
      const startTime = parseInt(localStorage.getItem("wait_start_time")) || 0;
      const duration = parseInt(localStorage.getItem("wait_duration")) || 0;
      if (startTime && duration) {
        waiting.value = true;
        startTimer();
      }
      fetchId();
      fetchWindowTypes();
      fetchPersonnel();
    });

    onUnmounted(() => {
      clearTimeout(waitingQueue);
    });

    return {
      queueList,
      currentServing,
      caterCustomer,
      cancelCustomer,
      finishCustomer,
      startWait,
      waiting,
      waitTime,
      tempTimer,
      beforeCancel,
      resetQueue,
      isQueuelistEmpty,
      prepared,
      formatTime,
      cusId,
      noOfQueue,
      type_id,
      serviceTypeList,
      cusName,
      cusQueueNum,
      servingStatus,
      tellerFullName,
      personnelList,
      teller_id,

      // Pagination
      currentPage,
      itemsPerPage,
      paginatedQueueList,
      totalPages,

      menuOpen,
      toggleFullscreen,
      // fetching assigned tellers
      assignedTellers,
      fetchAssignedTellers,
      sortedTellers,
      queueLists,
    };
  },
};
</script>

<style scoped>
.rounded-borders {
  border-radius: 12px;
}
</style>
