<template>
  <q-layout view="hHh lpR fFf">
    <q-header class="q-px-md">
      <q-toolbar>
        <!-- Fullscreen Toggle Button -->
        <q-btn
          flat
          round
          dense
          class="q-mr-sm"
          color="white"
          style="min-width: 32px; width: 32px; height: 32px; position: absolute"
          @click="$q.fullscreen.toggle()"
          :icon="$q.fullscreen.isActive ? 'fullscreen_exit' : 'fullscreen'"
        />

        <q-space />
        <q-img
          src="~assets/vrtlogowhite1.png"
          alt="Logo"
          fit="full"
          :style="{ maxWidth: $q.screen.lt.sm ? '100px' : '160px' }"
          class="q-ml-sm"
        />
        <div>{{ formattedString }}</div>

        <q-space />

        <!-- Avatar with Dropdown Menu -->
        <div class="row items-center justify-center q-gutter-md">
          <p class="q-mb-none">{{`${tellerInformation?.tellerFirstname || ''} ${tellerInformation?.tellerLastname || 'Loading'}`}}</p>
          <q-avatar
            size="40px"
            class="cursor-pointer"
            @click="menuOpen = !menuOpen"
          >
            <img
              src="https://cdn.quasar.dev/img/avatar.png"
              alt="User Avatar"
            />
          </q-avatar>
        </div>

        <q-menu
          v-model="menuOpen"
          no-parent-event
          anchor="bottom right"
          self="top right"
        >
          <q-list style="min-width: 150px">
            <q-item clickable v-close-popup @click="logout">
              <q-item-section avatar>
                <q-icon name="logout" />
              </q-item-section>
              <q-item-section>Logout</q-item-section>
            </q-item>
          </q-list>
        </q-menu>
      </q-toolbar>
    </q-header>

    <q-page-container>
      <q-page class="flex flex-center bg-accent">
        <div class="q-pa-md full-width">
          <!-- Main Row Container -->
          <div class="row q-col-gutter-md justify-center full-height">
            <!-- First Item -->
            <div class="col-12 col-md-6">
              <q-card class="q-pa-md">
                <q-card-section>
                  <q-item>
                    <q-item-section>
                      <q-item-label class="text-h4 text-center"
                        >Waiting Queue</q-item-label
                      >
                    </q-item-section>
                  </q-item>
                  <q-separator />
                  <q-separator />
                  <q-item>
                    <q-item-section>
                      <q-scroll-area class="my-scroll" style="height: 455px">
                        <q-list bordered separator>
                          <q-item
                            v-if="paginatedQueueList.length === 0"
                            class="text-center text-grey q-pa-md"
                          >
                            <q-item-section>
                              <h5>Queue Empty</h5>
                            </q-item-section>
                          </q-item>
                          <!-- Items of queue list -->
                          <q-item
                            class="bg-accent"
                            v-for="(customer, index) in paginatedQueueList"
                            :key="customer.id"
                          >
                            <q-item-section>
                              <h5 class="q-mb-sm q-mt-sm">
                                {{ customer.queue_number }}
                              </h5>
                              <p>{{ customer.name }}</p>
                            </q-item-section>
                            <q-item-section>
                              <div class="q-gutter-y-xs q-my-sm"></div>
                            </q-item-section>
                          </q-item>
                        </q-list>
                      </q-scroll-area>
                    </q-item-section>
                  </q-item>
                </q-card-section>
                <q-separator />
              </q-card>
            </div>

            <!-- Second Item -->
            <div class="col-12 col-md-6">
              <q-card
                class="q-mb-sm bg-primary text-white shadow-3 rounded-borders"
              >
                <q-card-section class="flex flex-center">
                  <q-item>
                    <q-item-section class="text-center">
                      <span class="text-h4 text-bold text-uppercase q-pa-sm">
                       {{`${tellerInformation?.type_name || 'Loading...'}`}}
                      </span>
                    </q-item-section>
                  </q-item>
                </q-card-section>
              </q-card>
              <q-card class="q-pa-md">
                <q-card-section>
                  <!-- If the cater line is not empty -->
                  <q-item v-if="currentServing">
                    <q-item-section>
                      <q-item-label class="text-h4 text-center text-primary"
                        ><strong>Current Queue</strong></q-item-label
                      >
                      <q-item-label caption class="text-center"
                        >The queue will be updated once someone is in
                        line.</q-item-label
                      >
                    </q-item-section>
                  </q-item>

                  <!-- If the cater line is empty -->
                  <q-item v-else>
                    <q-item-section>
                      <q-item-label class="text-h4 text-center"
                        ><strong>Queue Idle</strong></q-item-label
                      >
                      <q-item-label caption class="text-center"
                        >The queue will be updated once someone is in
                        line.</q-item-label
                      >
                    </q-item-section>
                  </q-item>

                  <q-separator />
                  <!-- The queue number and name of the customer -->
                  <q-item>
                    <q-item-section>
                      <q-list bordered separator style="max-height: 500px">
                        <!-- Items of queue list -->
                        <div v-if="currentServing">
                          <q-item class="bg-primary rounded-borders">
                            <q-item-section>
                              <h1
                                class="q-mb-sm q-mt-sm text-center text-white"
                              >
                                {{ currentServing.queue_number }}
                              </h1>
                              <p
                                class="text-center text-h6 text-grey-6 text-white"
                              >
                                {{ currentServing.name }}
                              </p>
                            </q-item-section>
                          </q-item>

                          <q-item>
                            <q-item-section>
                              <div class="q-gutter-y-xs q-my-sm items-end">
                                <q-btn
                                    v-if="currentServing && tempTimer == 0"
                                    label="Cancel"
                                    color="red-9"
                                    @click="beforeCancel(currentServing)" 
                                  />
  
                                  <q-btn
                                    v-if="currentServing"
                                    color="orange"
                                    class="q-ml-sm"
                                    :label="waiting ? formatTime(tempTimer) : 'Wait'"
                                    @click="startWait(cusId, currentServing.queue_number)"
                                    :disable="waiting"
                                    />
                                  <q-btn
                                    v-if="currentServing && tempTimer == 0"
                                    label="Finished"
                                    color="primary"
                                    @click="finishCustomer(currentServing.id)"
                                  />
                              </div>
                            </q-item-section>
                          </q-item>
                        </div>

                        <div v-else>
                          <q-item class="bg-grey-9 rounded-borders">
                            <q-item-section>
                              <h1
                                class="q-mb-sm q-mt-md text-center text-white loading-dots"
                              >
                                Queueing<span>.</span><span>.</span
                                ><span>.</span>
                              </h1>
                              <h6 class="text-center text-white"></h6>
                            </q-item-section>
                          </q-item>

                          <q-item>
                            <q-item-section>
                              <div class="q-gutter-y-xs q-my-sm"></div>
                            </q-item-section>
                          </q-item>
                        </div>
                      </q-list>
                    </q-item-section>
                  </q-item>
                </q-card-section>
              </q-card>
            </div>
          </div>
        </div>
      </q-page>
    </q-page-container>
  </q-layout>
</template>


<script>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { $axios, $notify, Dialog } from "boot/app";
import { useQuasar } from "quasar";
import { useRouter } from "vue-router";

export default {
  setup() {
    const cusId = ref();
    const queueList = ref([]);
    const router = useRouter()
    const currentServing = ref(null);
    const waiting = ref(false);
    const waitTime = ref(30);
    const tempTimer = ref();
    const prepared = ref();
    const originalWaitTime = ref(0); // Store the original wait time
    const isQueuelistEmpty = ref(false);
    let waitTimer = null;
    const tTypeid = ref();

    // const waitProgress = ref(0);
    let refreshInterval = null;
    const $dialog = useQuasar();

    // Pagination
    const currentPage = ref(1);
    const itemsPerPage = 5;

    // UI Functions
    const $q = useQuasar();
    const menuOpen = ref(false);
    const toggleFullscreen = () => {
      $q.fullscreen.toggle();
    };
    // CONTAINTER OF TELLER INFORMATION
    const tellerInformation = ref({
      id: "",
      tellerFirstname: "",
      tellerLastname: "",
      type_id: "",
      type_name: '',
    });

    // Fetch queue data
    const fetchQueue = async () => {
      try {
        const response = await $axios.post("/teller/queue-list", {
          type_id: tellerInformation.value.type_id,
        });

        queueList.value = response.data.queue.filter(
          (q) => !["finished", "cancelled"].includes(q.status)
        );
        currentServing.value = response.data.current_serving;
        // queuePosition.value = queueList.value.findIndex(q => q.queue_number == response.data.queue_numbers[0]) + 1
        // console.log(queuePosition.value)
        // console.log(response.data.queue_numbers)
        if (
          queueList.value.length > 0 &&
          queueList.value[0].status === "waiting" &&
          currentServing.value == null
        ) {
          caterCustomer(queueList.value[0].id, queueList.value[0].type_id);
          startWait(queueList.value[0].id, queueList.value[0].queue_number);
        }
        isQueuelistEmpty.value = queueList.value.length == 0;
      } catch (error) {
        console.error(error);
      }
    };

    const fetchId = async () => {
      try {
        const response = await $axios.post("/teller/queue-list", {
          type_id: tellerInformation.value.type_id,
        });
        cusId.value = response.data.current_serving.id;
      } catch (error) {
        console.error(error);
      }
    };
    // Cater customer
    const caterCustomer = async (customerId, type_id) => {
      try {
        await $axios.post("/teller/cater", {
          id: customerId,
          service_id: type_id,
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
        await $axios.post("/teller/cancel", { id: customerId });
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
        await $axios.post("/teller/finish", { id: customerId });
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
        console.log("customerId: " + customerId);
        console.log("cusId: " + cusId.value);
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
      await $axios.post("/waitCustomerReset", { id: id });
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
          console.log("tempTimer: " + tempTimer.value);
          console.log("cusId: " + cusId.value);
          if (tempTimer.value === 0) {
            resetWait(cusId.value);
            console.log("tempTimer: " + tempTimer.value);
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
          console.log(data.dataValue[0].Waiting_time);
          console.log(data.dataValue[0].Prepared);
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

    // Computed property for paginated queue list
    const paginatedQueueList = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage;
      return queueList.value.slice(start, start + itemsPerPage);
    });

    // Total pages for pagination
    const totalPages = computed(() =>
      Math.ceil(queueList.value.length / itemsPerPage)
    );

    // fetching the name of value of type id on service type
    const fetchType_idValue = async () => {
      try {
        const { data } = await $axios.post('/teller/typeid-value',{
          type_id: tellerInformation.value.type_id
        })
        // Update the type_name inside the tellerInformation ref
        tellerInformation.value.type_name = data.servicename;
      } catch (error) {
        if (error.response.status === 422) {
          console.log(error.response.data.message)
        }
      }
    }
    
    const logout = async () => {
      sessionStorage.removeItem('authTokenTeller');
      sessionStorage.removeItem('tellerInformation');
      router.push("/login"); // Redirect to login page
      setTimeout(() => {
        window.location.reload(); // Prevent back navigation
      }, 100);
    }

    onMounted(() => {
      const storedTellerInfo = sessionStorage.getItem("tellerInformation");
      if (storedTellerInfo) {
        fetchQueue();
        refreshInterval = setInterval(fetchQueue, 2000); // Auto-refresh every 5 seconds
        fetchWaitingtime();
        const startTime =
          parseInt(localStorage.getItem("wait_start_time")) || 0;
        const duration = parseInt(localStorage.getItem("wait_duration")) || 0;
        if (startTime && duration) {
          waiting.value = true;
          startTimer();
        }
        fetchId();
        refreshInterval = setInterval(fetchId, 2000);
        tellerInformation.value = JSON.parse(storedTellerInfo);
        fetchType_idValue();
      } else {
        console.error("No teller information found in sessionStorage");
      }
    });

    // onUnmounted(() => {
    //   clearInterval(refreshInterval) // Stop interval when component is destroyed
    //   clearInterval(waitTimer) // Stop wait timer if it exists
    // })

    return {
      queueList,
      currentServing,
      caterCustomer,
      cancelCustomer,
      finishCustomer,
      startWait,
      waiting,
      waitTime,
      fetchType_idValue,
      tempTimer,
      beforeCancel,
      isQueuelistEmpty,
      prepared,
      formatTime,
      cusId,
      tellerInformation,
      logout,
      currentPage,
      itemsPerPage,
      paginatedQueueList,
      totalPages,
      menuOpen,
      toggleFullscreen,
    };
  },
};
</script>
<style>
@keyframes queueDots {
  0% {
    opacity: 0.2;
  }
  50% {
    opacity: 1;
  }
  100% {
    opacity: 0.2;
  }
}

.loading-dots span {
  animation: queueDots 1.5s infinite ease-in-out;
}

.loading-dots span:nth-child(1) {
  animation-delay: 0s;
}
.loading-dots span:nth-child(2) {
  animation-delay: 0.2s;
}
.loading-dots span:nth-child(3) {
  animation-delay: 0.4s;
}

.full-progress {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 0;
}
.q-item__separator {
  border-color: #ff5733; /* Set custom color */
  border-width: 50px; /* Adjust thickness */
  margin: 5px 0; /* Adjust spacing */
}
@media (max-width: 320px) {
  .text-left,
  .text-right {
    width: 100%;
  }

  .action-btn {
    min-width: 80px;
    height: 45px;
    font-size: 12px;
  }
  .q-gutter-y-xs {
    display: flex;
    flex-direction: column;
    gap: 8px; /* Optional: controls the spacing between buttons */
  }
}
.q-gutter-y-xs {
  display: flex;
  flex-direction: column;
  gap: 5px; /* Optional: control the spacing between buttons */
}

/* Ensure buttons are full-width */
.q-btn.full-width {
  width: 100%;
}

.my-scroll {
  /* Customize scrollbars */
  scrollbar-width: thin;
  scrollbar-color: #4caf50 #f1f1f1; /* thumb color and track color */
}

/* Webkit Browsers (Chrome, Safari, etc.) */
.my-scroll::-webkit-scrollbar {
  width: 8px; /* vertical scrollbar width */
  height: 8px; /* horizontal scrollbar height */
}

.my-scroll::-webkit-scrollbar-thumb {
  background-color: #4caf50; /* thumb color */
  border-radius: 10px; /* round the corners */
}

.my-scroll::-webkit-scrollbar-track {
  background: #f1f1f1; /* track color */
}
</style>
