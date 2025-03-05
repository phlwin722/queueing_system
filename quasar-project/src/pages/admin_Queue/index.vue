<template>
    <q-layout>
      <q-page-container>
        <q-page class="q-pa-md">
  
          <!-- Current Serving Section -->
          <q-card class="q-mb-md q-pa-md" bordered>
            <q-card-section class="text-center">
              <div class="text-h5 text-bold">Now Serving</div>
              <div v-if="currentServing" class="text-h4 text-primary q-mt-md">
                {{ currentServing.name }}
              </div>
              <div v-if="currentServing" class="text-subtitle1 text-grey">
                Queue No: <strong>{{ currentServing.queue_number }}</strong>
              </div>
              <div v-else class="text-grey">No one is being served</div>
            </q-card-section>
  
            <!-- Finish Button -->
            <q-card-actions align="center">
              <q-btn
                v-if="currentServing"
                color="blue"
                label="Finish"
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
  
                <q-item-section side>
                  <q-btn :disable=serving color="green" label="Cater" @click="caterCustomer(customer.id)" />
                  <q-btn :disable=serving color="red" label="Cancel" class="q-ml-sm" @click="beforeCancel(customer)" />
  
                  <!-- Wait Button (Only for the first customer in the queue) -->
                  <q-btn
                    :disable=serving
                    v-if="index === 0 && !waiting"
                    color="orange"
                    label="Wait"
                    class="q-ml-sm"
                    @click="startWait(customer.id, customer.queue_number)"
                  />
  
                  <!-- Countdown Display -->
                  <div v-if="index === 0 && waiting" class="text-orange q-ml-md">
                    Waiting... {{ waitTime }}s
                  </div>
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
      </q-page-container>
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
      const serving = ref(false)
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
        } catch (error) {
          console.error(error)
        }
      }
  
      // Cater customer
      const caterCustomer = async (customerId) => {
        try {
          await $axios.post('/admin/cater', { id: customerId })
          serving.value = true
          fetchQueue()
          $notify('positive', 'check', 'Customer is now being served.')
          stopWait() // Stop wait if customer is catered
        } catch (error) {
          console.error(error)
          $notify('negative', 'error', 'Failed to cater customer.')
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
          serving.value = false
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
          const response = await $axios.post('/admin/start-wait', { queue_number: queueNumber })
          
          waiting.value = true
          waitTime.value = 63
  
          waitTimer = setInterval(() => {
            if (waitTime.value > 0) {
              waitTime.value--
            } else {
              cancelCustomer(customerId) // Auto-cancel after 1 minute
              stopWait()
            }
          }, 1000)
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
        serving,
        beforeCancel,
  
        // Pagination
        currentPage,
        itemsPerPage,
        paginatedQueueList,
        totalPages
      }
    }
  }
  </script>
  