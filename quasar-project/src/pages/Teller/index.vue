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
          <p class="q-mb-none">
            {{
              `${tellerInformation?.tellerFirstname || ""} ${
                tellerInformation?.tellerLastname || "Loading"
              }`
            }}
          </p>
          <q-avatar
            size="40px"
            class="cursor-pointer"
            @click="menuOpen = !menuOpen"
          >
            <q-img
              :src="imageUrl || require('assets/no-image.png')"
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
                      <q-item-label class="text-h6 text-center">
                        Number of Queue in line: {{ paginatedQueueList.length }}
                      </q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-separator />
                  <q-item>
                    <q-item-section>
                      <q-item-label class="text-h6 text-center"
                        >Waiting Queue</q-item-label
                      >
                    </q-item-section>
                  </q-item>
                  <q-separator />
                  <q-separator />
                  <q-item>
                    <q-item-section>
                      <q-scroll-area
                        class="my-scroll"
                        style="height: 200px; overflow-y: auto"
                      >
                        <q-list bordered separator>
                          <q-item
                            v-if="paginatedQueueList.length === 0"
                            class="text-center text-grey q-pa-md"
                          >
                            <q-item-section>
                              <h5>Queue Empty</h5>
                            </q-item-section>
                          </q-item>

                          <!-- Queue Items -->
                          <template
                            v-for="(customer, index) in paginatedQueueList"
                            :key="customer.id"
                          >
                          <q-item
                          style="height: 80px; border-radius: 10px;"
                          class="bg-white draggable-item shadow-2 q-mb-sm"
                          :class="{ 'drag-over': dragOverIndex === index }"
                          draggable="true"
                          @dragstart="onDragStart($event, index)"
                          @dragover.prevent="onDragOver(index)"
                          @dragleave="onDragLeave"
                          @drop="onDrop(index)"
                        >
                        
                        <div 
                          class="text-white text-bold"
                          :style="customer.priority_service ? 
                            {
                              'background-color': 'yellow',
                              'padding': '5px',
                              'height': '30px',
                              'margin-top': '18px',
                              'margin-right': '8px'
                            } : {}">
                            <!-- Content goes here -->
                            {{ customer.priority_service ? "VIP" : "" }}
                            <q-tooltip
                              anchor="center right"
                              self="center left"
                              :offset="[10, 10]"
                              class="bg-secondary"
                            >
                              {{ customer.priority_service }}
                            </q-tooltip>
                          </div>

                          <!-- Queue Number and Customer Name -->
                          <q-item-section class="flex flex-col justify-center q-pr-md">
                            <div class="text-primary text-bold text-h6 q-mb-xs">
                              <div class="text-primary text-bold text-h6 q-mb-xs">
                                {{ `${tellerInformation.indicator}#-${String(customer.queue_number).padStart(3, '0')}` }}
                              </div>

                            </div>

                            <p class="text-body2 text-secondary q-mb-none">{{ customer.name }}</p>
                          </q-item-section>

                          <!-- Conditional Currency Info -->
                          <q-item-section v-if="customer.currency_name && customer.currency_symbol && customer.flag" class="flex items-center justify-start q-pr-md">
                            <span :class="['fi', customer.flag]" style="font-size: 1.8em; margin-right: 8px;"></span>
                            <div class="text-body1">
                              {{ customer.currency_symbol }} - {{ customer.currency_name }}
                            </div>
                          </q-item-section>

                          <!-- Cancel Button -->
                          <q-item-section side>
                            <q-btn
                              label="Cancel"
                              color="negative"
                              text-color="white"
                              unelevated
                              rounded
                              dense
                              style="width: 100px; height: 36px;"
                              class="q-my-xs q-mx-sm shadow-2 hover-opacity"
                              @click="beforeCancel(customer)"
                            />
                          </q-item-section>

                          <!-- Status Badge -->
                          <q-item-section side>
                            <q-badge
                              :color="index <= 4 ? 'orange' : 'blue-grey'"
                              :label="index <= 4 ? 'Up Next' : 'Waiting'"
                              class="text-white text-bold"
                              style="min-width: 100px; padding: 4px 10px; border-radius: 12px; font-size: 1.1em;"
                            />
                          </q-item-section>
                        </q-item>
                          </template>
                        </q-list>
                      </q-scroll-area>
                    </q-item-section>
                  </q-item>
                </q-card-section>
                <q-separator />
              </q-card>

              <div class="" style="margin-top: 15px">
                <q-table
                  style="height: 200px;"
                  flat
                  bordered
                  :rows="rowsCurrency"
                  :columns="columns"
                  hide-bottom
                  :rows-per-page-options="[0]"
                  virtual-scroll
                  row-key="id"
                  class="modern-table my-sticky-header-table"
                >
                  <!-- Custom slot for rendering the content of the Currency column -->
                  <template v-slot:body-cell-currency="props">
                    <q-td :props="props">
                      <!-- Display the flag icon -->
                      <span :class="['fi', props.row.currency.flag]" style="font-size: 1.5em; margin-right: 8px;"></span>
                      <!-- Display the currency symbol and name -->
                      <span>{{ props.row.currency.symbol }} - {{ props.row.currency.name }}</span>
                    </q-td>
                  </template>
                </q-table>
              </div>
            </div>

            <div class="col-12 col-md-6">
              <q-card
                class="q-mb-sm bg-primary text-white shadow-3 rounded-borders"
              >
              <q-card-section class="row items-center">
                <q-toggle v-model="autoServing" label="Auto Serving" color="green" />
                <q-chip v-if="autoServing" color="green" text-color="white" class="q-ml-md">
                  Auto Serving ON
                </q-chip>
                <q-chip v-else color="red" text-color="white" class="q-ml-md">
                  Auto Serving OFF
                </q-chip>
              </q-card-section>
                <q-card-section class="flex flex-center">
                  <q-item>
                    <q-item-section class="text-center">
                      <span class="text-h4 text-bold text-uppercase q-pa-sm">
                        {{ `${tellerInformation?.type_name || "Loading..."}` }}
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
                                {{ `${tellerInformation.indicator}#-${String(currentServing.queue_number).padStart(3, '0')}` }}
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
                                  :label="
                                    waiting ? formatTime(tempTimer) : 'Wait'
                                  "
                                  @click="
                                    startWait(
                                      cusId,
                                      currentServing.queue_number
                                    )
                                  "
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
import { ref, computed, onMounted, onUnmounted, watch, nextTick, onBeforeUnmount } from "vue";
import { $axios, $notify, Dialog } from "boot/app";
import { useQuasar } from "quasar";
import { useRouter } from "vue-router";
import { debounce } from 'quasar'

export default {
  setup() {
    const cusId = ref();
    const queueList = ref([]);
    const router = useRouter();
    const currentServing = ref(null);
    const waiting = ref(false);
    const waitTime = ref(30);
    const tempTimer = ref();
    const prepared = ref();
    const originalWaitTime = ref(0);
    const isQueuelistEmpty = ref(false);
    let waitTimer = null;
    const noOfQueue = ref();
    const imageUrl = ref();
    const rowsCurrency = ref([]);
    const isLoading = ref(false);
    const autoServing = ref(false);
    let refreshInterval = null;
    const $dialog = useQuasar();

    // Pagination
    const currentPage = ref(1);
    const itemsPerPage = 10;

    // UI Functions
    const $q = useQuasar();
    const menuOpen = ref(false);
    const toggleFullscreen = () => {
      $q.fullscreen.toggle();
    };

    // Teller Information
    const tellerInformation = ref({
      id: "",
      tellerFirstname: "",
      tellerLastname: "",
      type_id: "",
      type_name: "",
    });

    // Fetch queue data with error handling
    const fetchQueue = async () => {
      try {
        isLoading.value = true;
        
        // Load locally stored queue order if available
        const storedQueue = JSON.parse(localStorage.getItem("queueList")) || [];
        
        const response = await $axios.post("/teller/queue-list", {
          type_id: tellerInformation.value.type_id,
          teller_id: tellerInformation.value.id,
        });

        const fetchedQueue = response.data.queue.filter(
          (q) => !["finished", "cancelled"].includes(q.status)
        );

        // Update and store current serving
        currentServing.value = response.data.current_serving;
        localStorage.setItem(
          "currentServing",
          JSON.stringify(currentServing.value)
        );

        // Remove current serving from the queue list
        const updatedQueue = fetchedQueue.filter(
          (q) => q.id !== currentServing.value?.id
        );

        // Preserve the local order while updating new queue items
        queueList.value = reorderQueue(storedQueue, updatedQueue);

        // Save updated queue order
        localStorage.setItem("queueList", JSON.stringify(queueList.value));

        noOfQueue.value = queueList.value.length;
        // if (queueList.value.length > 0 &&
        //     queueList.value[0].status === "waiting" &&
        //     currentServing.value == null
        // ) {
        //   const nextCustomer = queueList.value[0];
        //   console.log(nextCustomer.id)
        //   console.log(nextCustomer.type_id)
        //   if (nextCustomer) {
        //     setTimeout(() => {
        //       caterCustomer(nextCustomer.id, nextCustomer.type_id);
        //       startWait(nextCustomer.id, nextCustomer.queue_number);
        //     }, 2000);
        //   }
        // }

        isQueuelistEmpty.value = queueList.value.length == 0;
      } catch (error) {
        console.error("Error fetching queue:", error);
        $notify("negative", "error", "Failed to fetch queue data");
      } finally {
        isLoading.value = false;
      }
    };

    // Helper function to maintain the local order
    const reorderQueue = (storedQueue, updatedQueue) => {
      const orderMap = new Map(storedQueue.map((q, index) => [q.id, index]));
      return updatedQueue.sort(
        (a, b) =>
          (orderMap.get(a.id) ?? Infinity) - (orderMap.get(b.id) ?? Infinity)
      );
    };

    const fetchId = async () => {
      try {
        const response = await $axios.post("/teller/queue-list", {
          type_id: tellerInformation.value.type_id,
          teller_id: tellerInformation.value.id,
        });
        if (response.data.current_serving) {
          cusId.value = response.data.current_serving.id;
        }
      } catch (error) {
        console.error("Error fetching customer ID:", error);
      }
    };
    // Cater customer with error handling
    const caterCustomer = async (customerId, type_id) => {
      try {
                // Update UI immediately
        const customer = queueList.value.find((q) => q.id === customerId);
        await $axios.post("/teller/cater", {
          id: customerId,
          service_id: type_id,
          teller_id: tellerInformation.value.id,
        });
        if (customer) {
          customer.status = "serving";
          currentServing.value = customer;
        }


      } catch (error) {
        console.error("Error catering customer:", error);
      }
    };

    // Cancel dialog with confirmation
    const beforeCancel = (row) => {
      $dialog
        .dialog({
          title: "Confirm",
          message: `Are you sure you want to cancel ${row.name}?`,
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
        .onOk(() => {
          cancelCustomer(row.id);
        });
    };

    // Cancel customer with error handling
    const cancelCustomer = async (customerId) => {
      try {
        await $axios.post("/teller/cancel", { id: customerId });
        await fetchQueue();
        $notify(
          "positive",
          "check",
          "Customer has been removed from the queue."
        );
        stopWait();
      } catch (error) {
        console.error("Error canceling customer:", error);
        $notify("negative", "error", "Failed to cancel customer.");
      }
    };

    // Finish serving customer with error handling
    const finishCustomer = async (customerId) => {
      try {
        await $axios.post("/teller/finish", { id: customerId });
        await fetchQueue();
        await serveEnd();
        $notify("positive", "check", "Customer has been marked as finished.");
      } catch (error) {
        console.error("Error finishing customer:", error);
        $notify("negative", "error", "Failed to finish serving.");
      }
    };

    // Start waiting process with error handling
    const startWait = async (customerId, queueNumber) => {
      try {
        if (waiting.value) return;

        await $axios.post("/waitCustomer", { id: customerId });

        waiting.value = true;

        // Fetch and store the original wait time if not set
        if (originalWaitTime.value === 0) {
          originalWaitTime.value =
            prepared.value === "Minutes" ? waitTime.value * 60 : waitTime.value;
        }

        // Set the start time in localStorage
        const startTime = Math.floor(Date.now() / 1000);
        localStorage.setItem("wait_start_time", startTime);
        localStorage.setItem("wait_duration", originalWaitTime.value);

        // Reset the wait time
        tempTimer.value = originalWaitTime.value;

        $notify(
          "positive",
          "check",
          "Waiting for Customer"
        );

        // Clear any existing timer
        if (waitTimer) clearInterval(waitTimer);

        startTimer(customerId);
      } catch (error) {
        console.error("Error starting wait:", error);
        $notify("negative", "error", "Failed to set waiting customer.");
      }
    };

    const resetWait = async (id) => {
      try {
        await $axios.post("/waitCustomerReset", { id: id });
      } catch (error) {
        console.error("Error resetting wait:", error);
      }
    };

    // Start the countdown timer
    const startTimer = () => {
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

    // Fetch waiting time with error handling
    const fetchWaitingtime = async () => {
      try {
        const { data } = await $axios.post("/admin/waiting_Time-fetch");
        if (data?.dataValue?.length > 0) {
          waitTime.value = data.dataValue[0].Waiting_time;
          prepared.value = data.dataValue[0].Prepared;
        }
      } catch (error) {
        console.error("Error fetching waiting time:", error);
      }
    };

    // Format time as MM:SS
    const formatTime = (seconds) => {
      if (seconds == null) return ".....";
      if (seconds >= 60) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        return `${minutes}m ${remainingSeconds}s`;
      }
      return `${seconds}s`;
    };

    // Stop waiting process
    const stopWait = () => {
      waiting.value = false;
      if (waitTimer) {
        clearInterval(waitTimer);
        waitTimer = null;
      }
      localStorage.removeItem("wait_start_time");
      localStorage.removeItem("wait_duration");
    };

    // Computed property for paginated queue list
    const paginatedQueueList = computed(() => queueList.value);

 
  const debouncedUpdateQueuePositions = debounce(async () => {
    const updatedPositions = paginatedQueueList.value.map((customer, index) => ({
      id: customer.id,
      position: index + 1,
    }));

    try {
      await $axios.post("/update-queue-positions", { positions: updatedPositions });
      // Consider adding success feedback
    } catch (error) {
      console.error("Error updating positions:", error);
      // Add user feedback here (e.g., toast notification)
    }
  }, 300); // Reduced debounce time

  // Watch both length and array reference
  watch(
    () => [...queueList.value], // Creates a new array reference to trigger on reordering
    () => {
      debouncedUpdateQueuePositions();
    },
    { deep: false } // We're creating a new reference so deep isn't needed
  );
  let autoServingInterval = null; // Store the interval ID
  let serveStartTime = null;
  watch(autoServing, (newValue) => {
    if (newValue) {
      $notify(
          "positive",
          "check",
          "Auto Serving Enabled"
        );
      console.log("Auto Serving Enabled");
      // Start the interval when autoServing is turned on
      autoServingInterval = setInterval(() => {
        if (
          queueList.value.length > 0 &&
          queueList.value[0].status === "waiting" &&
          currentServing.value == null
        ) {
          const nextCustomer = queueList.value[0];

          if (nextCustomer) {
            setTimeout(() => {
              caterCustomer(nextCustomer.id, nextCustomer.type_id);
              startWait(nextCustomer.id, nextCustomer.queue_number);
              serveStartTime = new Date();
            }, 2000);
          }
        }
      }, 2000); // Check every 3 seconds (adjust as needed)
    } else {
      $notify(
          "positive",
          "check",
          "Auto Serving Disabled"
        );
      console.log("Auto Serving Disabled");
      // Clear the interval when autoServing is turned off
      if (autoServingInterval) {
        clearInterval(autoServingInterval);
        autoServingInterval = null;
      }
    }
  }, { immediate: true }); // immediate: true will run the callback immediately on mount


  const serveEnd = async () => {
  if (serveStartTime) {
    const now = new Date();
    const diffMs = now - serveStartTime; // in ms
    let minutes = Math.round(diffMs / 60000); // convert to minutes
    if (minutes < 1) minutes = 1;
    // const seconds = Math.floor((diffMs % 60000) / 1000); // if you want seconds too

    // Save to backend
    try {
      await $axios.post("/teller/save-serving-time", {
        minutes,
        type_id: tellerInformation.value.type_id,
        teller_id: tellerInformation.value.id,
      });
      console.log("Serving time saved:", minutes, "minutes");
    } catch (err) {
      console.error("Failed to save serving time", err);
    }

    serveStartTime = null; // Reset
  }
};

  const fetchTodayServingStats = async () => {
    try {
      const response = await $axios.post('/teller/today-serving-stats',{
        type_id: tellerInformation.value.type_id,
      });
      const stats = response.data;
      const updatedServingTime = Math.round(response.data.avg)
      await $axios.post("/teller/update-serving-time", {
        minutes: updatedServingTime,
        type_id: tellerInformation.value.type_id,
      });
    } catch (error) {
      console.error("Failed to fetch today's serving stats", error);
    }
  };
  // Drag and drop improvements
  let draggedIndex = null;
  const dragOverIndex = ref(null);
  const isDragging = ref(false);
  let dragImage = null; // Track the drag image

  const onDragStart = (event, index) => {
    draggedIndex = index;
    isDragging.value = true;

    // Clean up any existing drag image
    if (dragImage && document.body.contains(dragImage)) {
      document.body.removeChild(dragImage);
    }

    dragImage = event.target.cloneNode(true);
    Object.assign(dragImage.style, {
      position: "absolute",
      top: "-9999px",
      width: `${event.target.offsetWidth}px`,
      height: `${event.target.offsetHeight}px`,
      opacity: "1",
      background: "white",
      border: "2px solid #1976d2",
      boxShadow: "0px 4px 10px rgba(0,0,0,0.3)",
    });

    document.body.appendChild(dragImage);
    event.dataTransfer.setDragImage(dragImage, 0, 0);
  };

  const cleanupDrag = () => {
    if (dragImage && document.body.contains(dragImage)) {
      document.body.removeChild(dragImage);
    }
    dragImage = null;
  };

  const onDragOver = async (index) => {
    if (dragOverIndex.value !== index) {
      dragOverIndex.value = index;
      await nextTick();
    }
  };

  const onDragLeave = () => {
    dragOverIndex.value = null;
    cleanupDrag();
  };

  const onDrop = async (targetIndex) => {
    if (draggedIndex === null || draggedIndex === targetIndex) return;

    const item = queueList.value.splice(draggedIndex, 1)[0];
    queueList.value.splice(targetIndex, 0, item);

    localStorage.setItem("queueList", JSON.stringify(queueList.value));

    draggedIndex = null;
    dragOverIndex.value = null;
    isDragging.value = false;
    cleanupDrag();
    
    // Immediately update positions without waiting for debounce
    await debouncedUpdateQueuePositions();
    debouncedUpdateQueuePositions.flush(); // Force immediate execution
  };

    const logout = async () => {
      try {
        localStorage.removeItem("authTokenTeller");
        localStorage.removeItem("tellerInformation");
        router.push("/login");
        setTimeout(() => {
          window.location.reload();
        }, 100);
      } catch (error) {
        console.error("Error during logout:", error);
      }
    };

    // Table columns for currency
    const columns = ref([
      { name: 'currency', align: 'left', label: 'Currency', field: 'currency', sortable: true },
      { name: 'buy', align: 'left', label: 'Buy', field: 'buy' },
      { name: 'sell', align: 'left', label: 'Sell', field: 'sell' },
    ]);

    // Fetch currency data with error handling
    const fetchCurrency = async () => {
      try {
        const { data } = await $axios.post('/currency/showData');
        rowsCurrency.value = data.rows.map(row => ({
          id: row.id,
          currency: {
            flag: row.flag,
            symbol: row.currency_symbol,
            name: row.currency_name,
          },
          buy: `${row.buy_value}`,
          sell: `${row.sell_value}`,
        }));
      } catch (error) {
        console.error("Error fetching currency data:", error);
      }
    };

    // Fetch the name of value of type id on service type
    const fetchType_idValue = async () => {
      try {
        const { data } = await $axios.post("/teller/typeid-value", {
          type_id: tellerInformation.value.type_id,
        });
        tellerInformation.value.type_name = data.servicename;
        tellerInformation.value.indicator = data.indicator;
      } catch (error) {
        console.error("Error fetching service type:", error);
      }
    };

    const fetch_Image = async () => {
      try {
        const { data } = await $axios.post("/teller/image-teller", {
          id: tellerInformation.value.id,
        });
        imageUrl.value = data.Image;
      } catch (error) {
        console.error("Error fetching teller image:", error);
      }
    };

    // Timer references for cleanup
    let waitingTimeout;
    let queueTimeout;
    let fetchIdTimeout;
    let currencyInterval;

    // Optimized fetch functions with error handling
    const optimizedFetchQueueData = async () => {
      try {
        await fetchQueue();
      } catch (error) {
        console.error("Error in queue data fetch:", error);
      } finally {
        queueTimeout = setTimeout(optimizedFetchQueueData, 2000);
      }
    };

    const optimizedFetchWaitingtime = async () => {
      try {
        await fetchWaitingtime();
      } catch (error) {
        console.error("Error in waiting time fetch:", error);
      } finally {
        waitingTimeout = setTimeout(optimizedFetchWaitingtime, 2000);
      }
    };

    const optimizedFetchId = async () => {
      try {
        await fetchId();
      } catch (error) {
        console.error("Error in customer ID fetch:", error);
      } finally {
        fetchIdTimeout = setTimeout(optimizedFetchId, 2000);
      }
    };

    onMounted(() => {
      try {
        const storedTellerInfo = localStorage.getItem("tellerInformation");
        if (storedTellerInfo) {
          tellerInformation.value = JSON.parse(storedTellerInfo);
          fetchType_idValue();
          fetch_Image();

          // Start periodic data fetching
          optimizedFetchQueueData();
          optimizedFetchWaitingtime();
          optimizedFetchId();
          fetchTodayServingStats()
          // Start currency data fetching
          fetchCurrency();
          currencyInterval = setInterval(fetchCurrency, 30000);

          // Restore wait timer if exists
          const startTime = parseInt(localStorage.getItem("wait_start_time")) || 0;
          const duration = parseInt(localStorage.getItem("wait_duration")) || 0;
          if (startTime && duration) {
            waiting.value = true;
            startTimer();
          }
        } else {
          console.error("No teller information found");
          router.push("/login");
        }
      } catch (error) {
        console.error("Initialization error:", error);
        router.push("/login");
      }
    });

    onUnmounted(() => {
      // Cleanup all timers and intervals
      clearTimeout(waitingTimeout);
      clearTimeout(queueTimeout);
      clearTimeout(fetchIdTimeout);
      clearInterval(currencyInterval);
      if (waitTimer) clearInterval(waitTimer);
      if (refreshInterval) clearInterval(refreshInterval);
    });
    onBeforeUnmount(() => {
      if (autoServingInterval) {
        clearInterval(autoServingInterval);
      }
    });

    return {
      fetchCurrency,
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
      noOfQueue,
      logout,
      currentPage,
      itemsPerPage,
      paginatedQueueList,
      menuOpen,
      toggleFullscreen,
      fetch_Image,
      imageUrl,
      dragOverIndex,
      isDragging,
      onDragStart,
      onDragOver,
      onDragLeave,
      onDrop,
      columns,
      rowsCurrency,
      isLoading,
      autoServing,
    };
  },
};
</script>

<style>
@import 'flag-icons/css/flag-icons.min.css';

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

.draggable-item {
  cursor: grab;
  user-select: none;
  transition: background 0.2s ease-in-out, transform 0.15s ease-in-out,
    border 0.15s ease-in-out;
  opacity: 1 !important;
  border: 2px solid transparent;
}

.draggable-item.drag-over {
  background: rgba(25, 118, 210, 0.3);
  border: 2px dashed #1976d2 !important;
}

.drop-hint {
  height: 10px;
  background: rgba(25, 118, 210, 0.4);
  border-top: 2px dashed #1976d2 !important;
  border-bottom: 2px dashed #1976d2 !important;
  margin: 2px 0;
  visibility: hidden;
  opacity: 0;
  transition: opacity 0.15s ease-in-out, visibility 0.15s;
}

.is-dragging .drop-hint {
  visibility: visible;
  opacity: 1;
}

.draggable-item:active,
.draggable-item.is-dragging {
  border: 2px solid #1976d2 !important;
  background: rgba(25, 118, 210, 0.1);
  padding: 6px;
  box-shadow: 0px 0px 8px rgba(25, 118, 210, 0.5);
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
  border-color: #ff5733;
  border-width: 50px;
  margin: 5px 0;
}

/* Responsive styles */
@media (max-width: 1024px) {
  .q-card {
    margin-bottom: 16px;
  }
}

@media (max-width: 768px) {
  .text-h4 {
    font-size: 1.5rem;
  }
  .q-btn {
    padding: 8px 12px;
  }
}

@media (max-width: 480px) {
  .q-toolbar {
    flex-wrap: wrap;
  }
  .q-img {
    max-width: 80px !important;
  }
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
    gap: 8px;
  }
}

.q-gutter-y-xs {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.q-btn.full-width {
  width: 100%;
}

.my-scroll {
  scrollbar-width: thin;
  scrollbar-color: #4caf50 #f1f1f1;
}

.my-scroll::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

.my-scroll::-webkit-scrollbar-thumb {
  background-color: #4caf50;
  border-radius: 10px;
}

.my-scroll::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.custom-badge {
  font-size: 1.2rem;
  padding: 6px 12px;
  cursor: default;
}

/* Loading state */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}
</style>
