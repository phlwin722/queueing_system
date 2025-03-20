<template>
  <q-page class="q-pa-md">
    <q-card-actions align="center">
        <q-btn
          :disable="(!isQueuelistEmpty || currentServing != null)"
          color="green"
          label="Reset Queue Number"
          @click="resetQueue()"
        />
      </q-card-actions>
      <div class="col-12">
                        <q-select
                            outlined
                            v-model="type_id" 
                            label="Service Type"
                            emit-value
                            map-options
                            dense
                            hide-bottom-space
                            :options="serviceTypeList"
                            option-label="name"
                            option-value="id"
                            
                        />    
                    </div>
    <!-- Current Serving Section -->
    <q-card class="q-mb-md q-pa-md" bordered>
      Number of Queue in line: {{noOfQueue}}
      <q-card-section class="text-center">
        <div class="text-h5 text-bold">Now Serving</div>
        <div v-if="servingStatus != null" class="text-h4 text-primary q-mt-md">
          Queue number: <br>{{ cusQueueNum }}
        </div>
        <div v-if="servingStatus != null" class="text-subtitle1 text-grey">
          Name: <strong>{{ cusName }}</strong>
        </div>
        <div v-else class="text-grey">No one is being served</div>
      </q-card-section>

      

      <q-card-actions align="space-between">
            <!-- Cancel Button -->
            <q-btn 
            v-if="currentServing && tempTimer == 0"
            color="red" 
            label="Cancel" 
            class="modern-btn"
            @click="beforeCancel(currentServing)" 
            />

         <!-- Wait Button (Only for the first customer in the queue) -->
          
         <q-btn
              v-if="currentServing && tempTimer == 0"
              color="orange-5"
              class="modern-btn"
              :label="waiting ? formatTime(tempTimer) : 'Wait'"
              @click="startWait(cusId, currentServing.queue_number)"
              :disable="waiting"
            />
          
          <!-- Finish Button -->
          <q-btn
          v-if="currentServing && tempTimer == 0"
          color="blue"
          label="Finish"
          class="modern-btn"
          @click="finishCustomer(currentServing.id)"
        />
          
      </q-card-actions>
    </q-card>

    <!-- Queue List Section -->
    <q-card bordered>
      <q-card-section>
        <div class="text-h6">Waiting Queue</div>
      </q-card-section>

      <q-separator />

      <q-list separator bordered>
        <q-item v-for="(customer, index) in paginatedQueueList" :key="customer.id">
          <q-item-section>
            <q-item-label class="text-bold">Name: {{ customer.name }}</q-item-label>
            <q-item-label class="text-bold">Queue No: {{ customer.queue_number }}</q-item-label>
          </q-item-section>

        </q-item>
      </q-list>

      <!-- Pagination -->
      <q-card-actions align="center">
        <q-pagination
          v-model="currentPage"
          :max="totalPages"
          :max-pages="5"
          boundary-numbers
          color="primary"
        />
      </q-card-actions>
    </q-card>

    
  </q-page>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { $axios, $notify,Dialog } from 'boot/app'
import { useQuasar  } from 'quasar'

export default {
setup() {
const cusId = ref()
const queueList = ref([])
const currentServing = ref(null)
const waiting = ref(false)
const waitTime = ref(30)
const prepared = ref()
let waitTimer = null
const tempTimer = ref()
const originalWaitTime = ref(0); // Store the original wait time
const isQueuelistEmpty = ref(false)
let refreshInterval = null
const $dialog = useQuasar();
const noOfQueue = ref();
const type_id = ref()
const serviceTypeList = ref([]);
const cusName = ref()
const cusQueueNum = ref()
const servingStatus = ref()


// Pagination
const currentPage = ref(1)
const itemsPerPage = 5

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
        });
    queueList.value = response.data.queue.filter(q => !['finished', 'cancelled'].includes(q.status))
    currentServing.value = response.data.current_serving
    cusName.value = response.data.name
    cusQueueNum.value = response.data.queue_number
    servingStatus.value = response.data.status
    // queuePosition.value = queueList.value.findIndex(q => q.queue_number == response.data.queue_numbers[0]) + 1
    // console.log(queuePosition.value)
    // console.log(response.data.queue_numbers)
    noOfQueue.value = queueList.value.length
    if (queueList.value.length > 0 && queueList.value[0].status === 'waiting' && currentServing.value == null) {
        // caterCustomer(queueList.value[0].id);
        // startWait(queueList.value[0].id, queueList.value[0].queue_number)
        
    }
    isQueuelistEmpty.value = queueList.value.length == 0
  } catch (error) {
    console.error(error)
  }
}

const fetchCategories = async () => {
            try {
                const { data } = await $axios.post('/types/index');
              
                serviceTypeList.value = data.rows; // Ensure this matches the API response structure
            } catch (error) {
                console.error('Error fetching categories:', error);
            }
        };

const fetchId = async () => {
  try {
    const response = await $axios.post('/admin/queue-list')
    cusId.value = response.data.current_serving.id
  } catch (error) {
    console.error(error)
  }
}

// Cater customer
const caterCustomer = async (customerId) => {
  try {
    await $axios.post("/teller/cater", {
          id: customerId,
          service_id: type_id.value,
        });
    fetchQueue()
    fetchId()
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
    await $axios.post('/admin/cancel', { id: customerId })
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
    await $axios.post('/admin/finish', { id: customerId })
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
    await $axios.post('/waitCustomer', { id: customerId })

    if (waiting.value) return; // Prevent multiple clicks while waiting

    waiting.value = true;

    // Fetch and store the original wait time if not set
    if (originalWaitTime.value === 0) {
      originalWaitTime.value = prepared.value === "Minutes" ? waitTime.value * 60 : waitTime.value;
    }

    // Set the start time in localStorage
    const startTime = Math.floor(Date.now() / 1000); // Current timestamp in seconds
    localStorage.setItem("wait_start_time", startTime);
    localStorage.setItem("wait_duration", originalWaitTime.value);

    // Reset the wait time
    tempTimer.value = originalWaitTime.value;

    $notify("positive", "check", "Waiting for Queue Number: " + queueNumber);

    // Clear any existing timer
    if (waitTimer) clearInterval(waitTimer);

    startTimer(customerId);
  } catch (error) {
    console.error(error);
    $notify("negative", "error", "Failed to set waiting customer.");
  }
};

  const resetWait = async (id) =>{
    const response = await $axios.post('/waitCustomerReset', { id: id })
    console.log(response.message)
  }


// Start the countdown timer
const startTimer =  (id) => {
  if (waitTimer) clearInterval(waitTimer);

  waitTimer = setInterval(() => {
    const now = Math.floor(Date.now() / 1000);
    const startTime = parseInt(localStorage.getItem("wait_start_time")) || 0;
    const duration = parseInt(localStorage.getItem("wait_duration")) || 0;
    const elapsed = now - startTime;
    const remaining = duration - elapsed;

    if (remaining >= 0) {
    
      tempTimer.value = remaining;
      if(tempTimer.value === 0){
        resetWait(cusId.value)
      }
    } else  {
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
  const { data } = await $axios.post('/admin/waiting_Time-fetch');
  // Ensure that data.dataValue is available before trying to assign it to formData
  if (data && data.dataValue && data.dataValue.length > 0) {
    waitTime.value = data.dataValue[0].Waiting_time; // Assign the first object in dataValue to formData
    prepared.value = data.dataValue[0].Prepared;
  } else {
    console.log('No data available');
  }
} catch (error) {
  console.log('Error fetching data:', error);
}
};

// Format time as MM:SS
const formatTime = (seconds) => {
  if(seconds == null){
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



// Computed property for paginated queue list
const paginatedQueueList = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return queueList.value.slice(start, start + itemsPerPage)
})

// Total pages for pagination
const totalPages = computed(() => Math.ceil(queueList.value.length / itemsPerPage))

onMounted(() => {
  fetchQueue()
  refreshInterval = setInterval(() => {
  fetchQueue();
   // Add more functions as needed
}, 2000);
  fetchWaitingtime()
  const startTime = parseInt(localStorage.getItem("wait_start_time")) || 0;
  const duration = parseInt(localStorage.getItem("wait_duration")) || 0;
  if (startTime && duration) {
    waiting.value = true;
    startTimer();
  }
  fetchId()
  fetchCategories()
  
  
  
})

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

  // Pagination
  currentPage,
  itemsPerPage,
  paginatedQueueList,
  totalPages,

  menuOpen,
  toggleFullscreen,
}
}
}
</script>
