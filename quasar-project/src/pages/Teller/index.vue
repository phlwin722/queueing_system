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
                              style="height: 60px;"
                              class="bg-accent draggable-item"
                              :class="{ 'drag-over': dragOverIndex === index }"
                              draggable="true"
                              @dragstart="onDragStart($event, index)"
                              @dragover.prevent="onDragOver(index)"
                              @dragleave="onDragLeave"
                              @drop="onDrop(index)"
                            >
                              <q-item-section>
                                <h5 class="q-mb-sm q-mt-sm">
                                  {{ customer.queue_number }}
                                </h5>
                                <p>{{ customer.name }}</p>
                              </q-item-section>
                              <q-item-section side>
                                <q-badge
                                  v-if="index <= 4"
                                  color="orange"
                                  label="Up Next"
                                  class="custom-badge"
                                />
                                <q-badge
                                  v-else
                                  color="blue-grey"
                                  label="Waiting"
                                  class="custom-badge"
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

              <div class="" style="margin-top: 15px;">
                <q-table
                  style="height: 270px;"
                  flat bordered
                  :rows="rows"
                  :columns="columns"
                  row-key="name"
                />
              </div>
            </div>

            <div class="col-12 col-md-6">
              <q-card
                class="q-mb-sm bg-primary text-white shadow-3 rounded-borders"
              >
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
import { ref, computed, onMounted, onUnmounted  } from "vue";
import { $axios, $notify, Dialog } from "boot/app";
import { useQuasar } from "quasar";
import { useRouter } from "vue-router";

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
    const originalWaitTime = ref(0); // Store the original wait time
    const isQueuelistEmpty = ref(false);
    let waitTimer = null;
    const noOfQueue = ref();
    const imageUrl = ref();

    // const waitProgress = ref(0);
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
    // CONTAINTER OF TELLER INFORMATION
    const tellerInformation = ref({
      id: "",
      tellerFirstname: "",
      tellerLastname: "",
      type_id: "",
      type_name: "",
    });

    // Fetch queue data
    const fetchQueue = async () => {
      try {
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

        // Auto-serve next customer if queue has waiting ones
        if (
          queueList.value.length > 0 &&
          queueList.value[0].status === "waiting" &&
          currentServing.value == null
        ) {
          setTimeout(() => {
            caterCustomer(queueList.value[0].id, queueList.value[0].type_id);
            startWait(queueList.value[0].id, queueList.value[0].queue_number);
          }, 1000);
        }

        isQueuelistEmpty.value = queueList.value.length == 0;
      } catch (error) {
        console.error(error);
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
          teller_id: tellerInformation.value.id,
        });

        // Update UI immediately
        const customer = queueList.value.find((q) => q.id === customerId);
        if (customer) {
          customer.status = "serving";
          currentServing.value = customer; // Set as the currently served customer
        }

        fetchQueue();
        fetchId();
      } catch (error) {
        console.error(error);
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

    // Computed property for paginated queue list
    const paginatedQueueList = computed(() => queueList.value);

    // Total pages for pagination
    const totalPages = computed(() =>
      Math.ceil(queueList.value.length / itemsPerPage)
    );

    // fetching the name of value of type id on service type
    const fetchType_idValue = async () => {
      try {
        const { data } = await $axios.post("/teller/typeid-value", {
          type_id: tellerInformation.value.type_id,
        });
        // Update the type_name inside the tellerInformation ref
        tellerInformation.value.type_name = data.servicename;
      } catch (error) {
        if (error.response.status === 422) {
          console.log(error.response.data.message);
        }
      }
    };

    const fetch_Image = async () => {
      try {
        const { data } = await $axios.post("/teller/image-teller", {
          id: tellerInformation.value.id,
        });

        imageUrl.value = data.Image;
      } catch (error) {
        if (error.response.status === 422) {
          console.log(error);
        }
      }
    };
    let draggedIndex = null;
    const dragOverIndex = ref(null);
    const isDragging = ref(false);

    const onDragStart = (event, index) => {
      draggedIndex = index;
      isDragging.value = true;

      // Set a custom drag image (fixes transparency issues)
      const dragImage = event.target.cloneNode(true);
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

      // Ensure the drag image is removed immediately
      setTimeout(() => document.body.removeChild(dragImage), 0);
    };

    const onDragOver = async (index) => {
      if (dragOverIndex.value !== index) {
        dragOverIndex.value = index;
        await nextTick(); // Force Vue to repaint for proper hint visibility
      }
    };

    const onDragLeave = () => {
      dragOverIndex.value = null;
    };

    const onDrop = (targetIndex) => {
      if (draggedIndex === null || draggedIndex === targetIndex) return;

      // Swap positions in queueList
      const item = queueList.value.splice(draggedIndex, 1)[0];
      queueList.value.splice(targetIndex, 0, item);

      // Save updated queue to localStorage
      localStorage.setItem("queueList", JSON.stringify(queueList.value));

      // Reset indexes
      draggedIndex = null;
      dragOverIndex.value = null;
      isDragging.value = false;
    };

    const logout = async () => {
      localStorage.removeItem("authTokenTeller");
      localStorage.removeItem("tellerInformation");
      router.push("/login"); // Redirect to login page
      setTimeout(() => {
        window.location.reload(); // Prevent back navigation
      }, 100);
    };

    let waitingTimeout;
    let queueTimeout;
    let fetchIdTimeout;

    const optimizedFetchQueueData = async () => {
    await fetchQueue();
    queueTimeout = setTimeout(optimizedFetchQueueData, 2000); // Recursive Timeout
    };

    const optimizedFetchWaitingtime = async () => {
      await fetchWaitingtime()
      waitingTimeout = setTimeout(optimizedFetchWaitingtime, 2000); // Recursive Timeout
    };

    const optimizedFetchId = async () => {
      await fetchId()
      fetchIdTimeout = setTimeout(optimizedFetchId, 2000); // Recursive Timeout
    };

    onMounted(() => {
      const storedTellerInfo = localStorage.getItem("tellerInformation");
      if (storedTellerInfo) {
        optimizedFetchQueueData()
        optimizedFetchWaitingtime()
        optimizedFetchId()
        const startTime = parseInt(localStorage.getItem("wait_start_time")) || 0;
        const duration = parseInt(localStorage.getItem("wait_duration")) || 0;
        if (startTime && duration) {
          waiting.value = true;
          startTimer();
        }
        tellerInformation.value = JSON.parse(storedTellerInfo);
        fetchType_idValue();
        fetch_Image();
      } else {
        console.error("No teller information found in localStorage");
      }
    });

    onUnmounted(() => {
      clearTimeout(waitingTimeout);
      clearTimeout(queueTimeout);
      clearTimeout(fetchIdTimeout);
    });

    const columns = [
      { name: 'currency', align: 'center', label: 'Currency', field: 'currency', sortable: true },
      { name: 'symbol', label: 'Symbol', field: 'symbol', sortable: true },
      { name: 'buy', label: 'Buy', field: 'buy' },
      { name: 'sell', label: 'Sell', field: 'sell' },
    ]

    const rows = [
      { currency: 'UDS', symbol: '$', buy: '42', sell: '4.0' },
      { currency: 'UDS', symbol: '$', buy: '42', sell: '4.0' },
      { currency: 'UDS', symbol: '$', buy: '42', sell: '4.0' },
      { currency: 'UDS', symbol: '$', buy: '42', sell: '4.0' },
      { currency: 'UDS', symbol: '$', buy: '42', sell: '4.0' },
      { currency: 'UDS', symbol: '$', buy: '42', sell: '4.0' },
      { currency: 'UDS', symbol: '$', buy: 24, sell: 4.0 },
      { currency: 'UDS', symbol: '$', buy: '42', sell: '4.0' },
      { currency: 'UDS', symbol: '$', buy: '42', sell: '4.0' },
      { currency: 'UDS', symbol: '$', buy: '42', sell: '4.0' }
    ]



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
      noOfQueue,
      logout,
      currentPage,
      itemsPerPage,
      paginatedQueueList,
      totalPages,
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
      rows
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

.draggable-item {
  cursor: grab;
  user-select: none;
  transition: background 0.2s ease-in-out, transform 0.15s ease-in-out,
    border 0.15s ease-in-out;
  opacity: 1 !important;
  border: 2px solid transparent; /* Default transparent border */
}

/* Highlight the item being dragged over */
.draggable-item.drag-over {
  background: rgba(25, 118, 210, 0.3);
  border: 2px dashed #1976d2 !important;
}

/* Drop hint styling */
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

/* Show drop hint when dragging */
.is-dragging .drop-hint {
  visibility: visible;
  opacity: 1;
}

/* Make the entire q-item and border fully visible when holding */
.draggable-item:active,
.draggable-item.is-dragging {
  border: 2px solid #1976d2 !important;
  background: rgba(25, 118, 210, 0.1); /* Subtle background highlight */
  padding: 6px; /* Increase padding to make it more visible */
  box-shadow: 0px 0px 8px rgba(25, 118, 210, 0.5); /* Slight glow effect */
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

.custom-badge {
  font-size: 1.2rem;
  padding: 6px 12px;
  cursor: default;
}
</style>
