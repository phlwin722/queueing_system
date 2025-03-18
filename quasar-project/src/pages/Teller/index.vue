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
          <p class="q-mb-none">Juan Dela Cruz</p>
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
            <q-item clickable v-close-popup @click="goToAccountSettings">
              <q-item-section avatar>
                <q-icon name="settings" />
              </q-item-section>
              <q-item-section>Account Settings</q-item-section>
            </q-item>
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
                  <q-item>
                    <q-item-section>

                      <q-btn
                        :disable="!isQueuelistEmpty || currentServing != null"
                        class="bg-primary text-white"
                        label="Reset Queue Number"
                        @click="resetQueue()"
                      />
                    </q-item-section>
                  </q-item>
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
                                  v-if="currentServing && a == 0"
                                  label="Cancel"
                                  color="red-9"
                                  @click="beforeCancel(currentServing)"
                                />

                                <q-btn
                                  v-if="currentServing"
                                  color="orange"
                                  class="q-ml-sm"
                                  @click="
                                    startWait(
                                      currentServing.id,
                                      currentServing.queue_number
                                    )
                                  "

                                  :disable="waiting"
                                  />


                                  <!-- Button Text -->
                                  <div
                                    class="row items-center no-wrap absolute-full flex flex-center"
                                  >
                                    <span v-if="!waiting">Wait</span>
                                    <span v-if="waiting" class="q-ml-xs">{{
                                      formatTime(a)
                                    }}</span>
                                  </div>
                                </q-btn>


                                <q-btn
                                  v-if="currentServing && a == 0"
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

export default {
  setup() {
  const queueList = ref([])
  const currentServing = ref(null)
  const waiting = ref(false)
  const waitTime = ref(30)
  const prepared = ref()
  const originalWaitTime = ref(0); // Store the original wait time
  const isQueuelistEmpty = ref(false)
  let waitTimer = null


  // const waitProgress = ref(0);
let refreshInterval = null
  const $dialog = useQuasar();
  const a = ref()

  // Pagination
  const currentPage = ref(1)
  const itemsPerPage = 5

  // UI Functions
  const $q = useQuasar();
      const menuOpen = ref(false);
      const toggleFullscreen = () => {
        $q.fullscreen.toggle();
      };
  // CONTAINTER OF TELLER INFORMATION
  const tellerInformation = ref ({
    id: '',
    teller_firstname: '',
    teller_lastname: '',
    type_id: '',
  })

  // Fetch queue data
  const fetchQueue = async () => {
    try {
      const response = await $axios.post('/teller/queue-list', {
        type_id: tellerInformation.value.type_id
      })
    
      queueList.value = response.data.queue.filter(q => !['finished', 'cancelled'].includes(q.status))
      currentServing.value = response.data.current_serving
      // queuePosition.value = queueList.value.findIndex(q => q.queue_number == response.data.queue_numbers[0]) + 1
      // console.log(queuePosition.value)
      // console.log(response.data.queue_numbers)
      if (queueList.value.length > 0 && queueList.value[0].status === 'waiting' && currentServing.value == null) {
          caterCustomer(queueList.value[0].id, queueList.value[0].type_id);
          startWait(queueList.value[0].id, queueList.value[0].queue_number)
      }
      isQueuelistEmpty.value = queueList.value.length == 0
    } catch (error) {
      console.error(error)
    }
  }
  // Cater customer
  const caterCustomer = async (customerId,type_id) => {
    try {
      await $axios.post('/teller/cater', { 
        id: customerId,
        service_id: type_id
      })
      fetchQueue()
    } catch (error) {
      console.error(error)
      $notify('negative', 'error', 'You are currently serving a customer. Please finish it first!.')
    }
  }
  //cancel dialog
  const beforeCancel = (row) => {
    $dialog.dialog({
        title: 'Confirm',
        message: 'Are you sure do you want cancel this queue?',
        cancel: true,
        persistent: true,
        ok: {
          label: 'Yes',
          color: 'primary', // Make confirm button red
          unelevated: true, // Flat button style
          style:'width: 125px;'
        },
        cancel: {
          label: 'Cancel',
          color: 'red-8', // Make cancel button grey
          unelevated: true,
          style: 'width: 125px;'
        },
        style: 'border-radius: 12px; padding: 16px;',
      }).onOk(()=> {
        cancelCustomer(row.id)
      }).onDismiss(() => {
        // console.log('I am triggered on both OK and Cancel')
      })
  }

  // Cancel customer
  const cancelCustomer = async (customerId) => {
    try {
      await $axios.post('/teller/cancel', { id: customerId })
      fetchQueue()
      $notify('positive', 'check', 'Customer has been removed from the queue.')
      stopWait() // Stop wait if customer is canceled
    } catch (error) {
      console.error(error)
      $notify('negative', 'error', 'Failed to cancel customer.')
    }
  }

  // Finish serving customer
  const finishCustomer = async (customerId) => {
    try {
      await $axios.post('/teller/finish', { id: customerId })
      fetchQueue()
      
      $notify('positive', 'check', 'Customer has been marked as finished.')
    } catch (error) {
      console.error(error)
      $notify('negative', 'error', 'Failed to finish serving.')
    }
  }

  // Start waiting process
  const startWait = async (customerId, queueNumber) => {
  try {
    console.log()
    if (waiting.value) return; // Prevent multiple clicks while waiting

    waiting.value = true;
    console.log("original time: "+originalWaitTime.value)
    a.value = waitTime.value
    // If first time clicking, store the original time
    if (originalWaitTime.value === 0) {
      originalWaitTime.value = prepared.value === "Minutes" ? a.value * 60 : a.value;
    }
    console.log("original time: "+originalWaitTime.value)
    // Reset the wait time from stored value
    a.value = originalWaitTime.value;

    $notify("positive", "check", "Waiting for Queue Number: " + queueNumber);

    // Clear any existing timer to prevent duplicatesg
    if (waitTimer) clearInterval(waitTimer);
    // let divisor = a.value

            waitTimer = setInterval(() => {
                
            if (a.value > 0) {
              a.value--;
            } else if(a.value == 0) {
              stopWait();
              originalWaitTime.value = 0;
              console.log("original time: "+originalWaitTime.value) // Reset stored time after countdown finishes
            }
          }, 1000);

  } catch (error) {
    console.error(error);
    $notify("negative", "error", "Failed to set waiting customer.");
  }
  };

  // Fetch the data from the backend when the component is mounted
  const fetchWaitingtime = async () => {
  try {
    const { data } = await $axios.post('/teller/waiting_Time-fetch');
    // Ensure that data.dataValue is available before trying to assign it to formData
    if (data && data.dataValue && data.dataValue.length > 0) {
      waitTime.value = data.dataValue[0].Waiting_time; // Assign the first object in dataValue to formData
      prepared.value = data.dataValue[0].Prepared;
      console.log(data.dataValue[0].Waiting_time)
      console.log(data.dataValue[0].Prepared)
    } else {
      console.log('No data available');
    }
  } catch (error) {
    console.log('Error fetching data:', error);
  }
  };

  const formatTime = (seconds) => {
  if (seconds >= 60) {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes} m ${remainingSeconds} s`;
  }
  return `${seconds} s`;
  };


  // Reset Queue Number
  const resetQueue = async () => {
    try {            
        $dialog.dialog({
        title: 'Confirm',
        message: 'Are you sure do you want reset queue?',
        cancel: true,
        persistent: true,
        color: 'primary',
        ok: {
          label: 'Yes',
          color: 'primary', // Make confirm button red
          unelevated: true, // Flat button style
          style:'width: 125px;'
        },
        cancel: {
          label: 'Cancel',
          color: 'red-8', // Make cancel button grey
          unelevated: true,
          style: 'width: 125px;'
        },
        style: 'border-radius: 12px; padding: 16px;',
      }).onOk( async () => {
        const response = await $axios.post('/resetQueue')
        $notify('positive', 'check', response.data.message)
        console.log(response.data.message)
      }).onDismiss(() => {
        // console.log('I am triggered on both OK and Cancel')
      })
    } catch (error) {
      console.error(error)
      $notify('negative', 'error', 'Failed to set waiting customer.')
    }
  }
            if (a.value > 0) {
              waitProgress.value = waitProgress.value;

  // Computed property for paginated queue list
  const paginatedQueueList = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage
    return queueList.value.slice(start, start + itemsPerPage)
  })

  // Total pages for pagination
  const totalPages = computed(() => Math.ceil(queueList.value.length / itemsPerPage))

  onMounted(() => {
    const storedTellerInfo = sessionStorage.getItem('authTokenTeller')
    if (storedTellerInfo) {
      fetchQueue()
      refreshInterval = setInterval(fetchQueue, 5000) // Auto-refresh every 5 seconds
      fetchWaitingtime()
      tellerInformation.value = JSON.parse(storedTellerInfo) 
    }else {
      console.error("No teller information found in sessionStorage");
    }
  })

  onUnmounted(() => {
    clearInterval(refreshInterval) // Stop interval when component is destroyed
    clearInterval(waitTimer) // Stop wait timer if it exists
  })

  return {
    queueList,
    currentServing,
    caterCustomer,
    cancelCustomer,
    finishCustomer,
    startWait,
    waiting,
    waitTime,
    a,
    beforeCancel,
    resetQueue,
    isQueuelistEmpty,
    prepared,
    formatTime,
    tellerInformation,


    currentPage,
    itemsPerPage,
    paginatedQueueList,
    totalPages,

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
