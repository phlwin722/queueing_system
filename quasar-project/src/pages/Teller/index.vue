<template>
  <q-layout view="lHh lpr lFf" class="shadow-2">
    <q-header elevated style="height: 60px">
      <q-toolbar class="justify-center">
        <q-toolbar-title>Teller</q-toolbar-title>

        <q-btn-dropdown v-model="menu" flat dense>
          <template v-slot:label>
            <q-avatar>
              <img src="https://cdn.quasar.dev/img/avatar.png" />
            </q-avatar>
          </template>

          <q-list>
            <q-item clickable v-close-popup @click="onItemClick">
              <q-item-section avatar>
                <q-avatar icon="logout" text-color="red" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-red">Log Out</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-btn-dropdown>
      </q-toolbar>
    </q-header>

    <!-- Queue List Section -->
    <div
      class="q-pa-md"
      style="
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
      "
    >
      <!-- Flex container for horizontal layout (row) -->
      <div
        class="q-gutter-md"
        style="
          display: flex;
          justify-content: space-between;
          gap: 20px;
          flex-direction: row;
          width: 100%;
        "
      >
        <!-- Waiting Queue Card -->
        <q-card
          class="shadow-2 bg-white rounded-borders"
          style="flex: 1; height: 450px; margin-right: 10px"
        >
          <!-- Reset Queue Number Button inside Waiting Queue Card -->
          <q-card-actions align="center" class="col q-mb-md">
            <q-btn
              :disable="!isQueuelistEmpty || currentServing != null"
              unelevated
              dense
              color="orange-5"
              class="modern-btn"
              label="Reset Queue Number"
              @click="resetQueue()"
            />
          </q-card-actions>
          <q-separator />
          <q-card-section class="column q-pa-md custom-scrollbar scroll-area">
            <q-chip
              v-for="(customer, index) in queueList"
              :key="customer.id"
              :style="{
                backgroundColor:
                  currentServing === customer ? '#f39c12' : '#1c5d99',
                color: 'white',
                fontSize: '22px',
                padding: '16px 24px',
                height: '80px',
                position: 'relative',
              }"
              class="full-width q-mb-sm queue-chip row items-center"
              square
              style="
                display: flex;
                justify-content: space-between;
                align-items: center;
              "
            >
              <!-- Name and Queue Number Section -->
              <div
                class="flex"
                style="
                  flex-grow: 1;
                  display: flex;
                  justify-content: space-between;
                  width: 100%;
                "
              >
                <div class="q-mr-md">
                  <span class="text-bold">Name: {{ customer.name }}</span>
                </div>
                <div class="q-mr-md">
                  <span class="text-bold"
                    >Queue: {{ customer.queue_number }}</span
                  >
                </div>
              </div>

              <!-- QButtonGroup positioned at the bottom-right corner -->
              <div
                class="q-pa-none"
                style="position: absolute; bottom: 10px; right: 10px"
              >
                <q-btn-group dense>
                  <q-btn
                    unelevated
                    dense
                    color="red-5"
                    label="Cancel"
                    class="modern-btn"
                    @click="beforeCancel(customer)"
                  />
                  <q-btn
                    v-if="index === 0"
                    unelevated
                    dense
                    color="orange-5"
                    class="modern-btn"
                    :label="waiting ? `${waitTime} s` : 'Wait'"
                    @click="startWait(customer.id, customer.queue_number)"
                    :disable="waiting"
                  />
                  <q-btn
                    unelevated
                    dense
                    color="green-5"
                    label="Cater"
                    class="modern-btn"
                    @click="caterCustomer(customer.id)"
                  />
                </q-btn-group>
              </div>
            </q-chip>

            <div
              v-if="queueList.length === 0"
              class="text-grey text-center q-mt-md"
            >
              No more customers
            </div>
          </q-card-section>
        </q-card>

        <!-- Now Serving Card -->
        <q-card
          class="shadow-2 bg-white rounded-borders"
          style="flex: 1; height: 450px; margin-left: 10px"
        >
          <q-card-section class="column q-pa-md text-center">
            <transition name="fade-scale" mode="out-in">
              <h5
                v-if="currentServing"
                key="serving"
                class="text-bold text-orange-10"
              >
                NOW SERVING
              </h5>
              <h5 v-else key="current" class="text-bold text-grey-8"></h5>
            </transition>
          </q-card-section>
          <q-separator />
          <q-card-section class="column q-pa-md flex-center">
            <div
              class="q-pa-md flex flex-center text-bold text-white now-serving-box"
              :class="currentServing ? 'bg-amber-9' : 'bg-grey-7'"
            >
              <div v-if="currentServing">
                <div class="text-h4">{{ currentServing.queue_number }}</div>
                <div class="text-h6">{{ currentServing.name }}</div>
              </div>
              <span v-else>No Queue in Progress</span>
            </div>
          </q-card-section>

          <q-card-section class="text-center q-pa-md">
            <q-btn
              v-if="currentServing"
              color="indigo-10"
              label="Finish"
              size="lg"
              unelevated
              class="rounded-borders"
              @click="finishCustomer(currentServing.id)"
            />
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-layout>
</template>

<script>
  import { ref, computed, onMounted, onUnmounted } from 'vue'
  import { $axios, $notify,Dialog } from 'boot/app'
  
  export default {
    setup() {
      const queueList = ref([])
      const currentServing = ref(null)
      const waiting = ref(false)
      const waitTime = ref(63)
      const isQueuelistEmpty = ref(false)
      let waitTimer = null
      let refreshInterval = null
  
      // Pagination
      const currentPage = ref(1)
      const itemsPerPage = 5
  
      // Fetch queue data
      const fetchQueue = async () => {
        try {
          const response = await $axios.post('/admin/queue-list')
          queueList.value = response.data.queue.filter(q => !['finished', 'cancelled'].includes(q.status))
          currentServing.value = response.data.current_serving
          console.log(currentServing.value) 
          isQueuelistEmpty.value = queueList.value.length == 0
        } catch (error) {
          console.error(error)
        }
      }
  
      // Cater customer
      const caterCustomer = async (customerId) => {
        try {
          await $axios.post('/admin/cater', { id: customerId })
          fetchQueue()
          $notify('positive', 'check', 'Customer is now being served.')
          stopWait() // Stop wait if customer is catered
        } catch (error) {
          console.error(error)
          $notify('negative', 'error', 'You are currently serving a customer. Please finish it first!.')
        }
      }
  //cancel dialog
      const beforeCancel = (row) => {
     
        const message = 'Are you sure you want to CANCEL this QUEUE?'+'  Name: '+row.name
          Dialog.create({
          title: 'Confirm Cancellation',
          message: message
        }).onOk(() =>{
          cancelCustomer(row.id)
          
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
           
          if(currentServing.value != null){
            $notify('negative', 'error', 'Please finish the current customer first.')
          }else{
            const response = await $axios.post('/admin/start-wait', { queue_number: queueNumber })
          
            waiting.value = true
            waitTime.value = 63
            $notify('positive', 'check', response.data.message)
            waitTimer = setInterval(() => {
              if (waitTime.value > 0) {
                waitTime.value--
              } else {
                cancelCustomer(customerId) // Auto-cancel after 1 minute
                stopWait()
              }
            }, 1000)
          }
          
        } catch (error) {
          console.error(error)
          $notify('negative', 'error', 'Failed to set waiting customer.')
        }
      }

      // Reset Queue Number
      const resetQueue = async () => {
          try { 
           
         
            const response = await $axios.post('/resetQueue')
            $notify('positive', 'check', response.data.message)
            console.log(response.data.message)
         
          
        } catch (error) {
          console.error(error)
          $notify('negative', 'error', 'Failed to set waiting customer.')
        }
      }
  
  
      
  
      // Stop waiting process
      const stopWait = () => {
        waiting.value = false
        clearInterval(waitTimer)
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
        refreshInterval = setInterval(fetchQueue, 5000) // Auto-refresh every 5 seconds
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
        beforeCancel,
        resetQueue,
        isQueuelistEmpty,
  
        
      }
    }
  }
  </script>

<style scoped>
/* Modern button styling */
.modern-btn {
  font-size: 16px;
  padding: 10px 16px;
  border-radius: 8px;
  font-weight: bold;
  min-width: 90px;
  transition: all 0.3s ease-in-out;
}

/* Hover effect for better interaction */
.modern-btn:hover {
  filter: brightness(1.2);
  transform: scale(1.05);
}

/* Text animations */
.fade-scale-enter-active,
.fade-scale-leave-active {
  transition: opacity 0.4s, transform 0.3s ease-out;
}
.fade-scale-enter {
  opacity: 0;
  transform: scale(0.8);
}
.fade-scale-leave-to {
  opacity: 0;
  transform: scale(1.1);
}

/* Bigger Queue Numbers */
.queue-chip {
  font-size: 20px;
  padding: 12px;
}

/* Now Serving Box */
.now-serving-box {
  min-height: 140px;
  width: 100%;
  border-radius: 12px;
  font-size: 36px;
}

/* Custom Scrollbar */
/* Modern and Sleek Scrollbar */
.custom-scrollbar {
  overflow-y: auto;
  max-height: 300px;
  padding-right: 8px;
  scrollbar-width: thin; /* Firefox */
  scrollbar-color: #1c5d99 #f1f1f1; /* Thumb and track colors */
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px; /* Slimmer for a modern look */
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: #f8f9fa; /* Light grey for a soft contrast */
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, #1c5d99, #2980b9); /* Gradient effect */
  border-radius: 10px;
  transition: background 0.3s ease-in-out;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(180deg, #2980b9, #1c5d99); /* Hover effect */
}

/* Even Buttons */
.button-container .q-btn {
  flex: 1;
  max-width: 24%;
}

.rounded-borders {
  border-radius: 12px;
}

/* Prevent Scrolling */
html,
body {
  overflow: hidden;
  height: 100%;
}

/* Ensure the layout fills the screen properly */
.q-layout {
  height: 100vh; /* Full viewport height */
  overflow: hidden;
}
</style>