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
          <!-- Current Serving Section -->
          <q-card class="q-mb-md q-pa-md" bordered>
            <q-card-section class="text-center">
              <div class="text-h5 text-bold">Now Serving</div>
              <div v-if="currentServing" class="text-h4 text-primary q-mt-md">
                Queue number: <br>{{ currentServing.queue_number }}
              </div>
              <div v-if="currentServing" class="text-subtitle1 text-grey">
                Name: <strong>{{ currentServing.name }}</strong>
              </div>
              <div v-else class="text-grey">No one is being served</div>
            </q-card-section>
  
            
            
            <q-card-actions align="space-between">
                  <!-- Cancel Button -->
                  <q-btn 
                  v-if="currentServing && waitTime == 0"
                  color="red" 
                  label="Cancel" 
                  class="modern-btn"
                  @click="beforeCancel(currentServing)" 
                  />

               <!-- Wait Button (Only for the first customer in the queue) -->
               <q-btn
                    v-if="currentServing"
                    color="orange-5"
                    class="modern-btn"
                    :label="waiting ? `${waitTime} s` : 'Wait'"
                    @click="startWait(currentServing.id, currentServing.queue_number)"
                    :disable="waiting"
                  />

                <!-- Finish Button -->
                <q-btn
                v-if="currentServing"
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
      const queueList = ref([])
      const currentServing = ref(null)
      const waiting = ref(false)
      const waitTime = ref(60)
      const queuePosition = ref(null)
      const isQueuelistEmpty = ref(false)
      let waitTimer = null
      let refreshInterval = null
      const $dialog = useQuasar();
  
      // Pagination
      const currentPage = ref(1)
      const itemsPerPage = 5
  
      // Fetch queue data
      const fetchQueue = async () => {
        try {
          const response = await $axios.post('/admin/queue-list')
          queueList.value = response.data.queue.filter(q => !['finished', 'cancelled'].includes(q.status))
          currentServing.value = response.data.current_serving
          // queuePosition.value = queueList.value.findIndex(q => q.queue_number == response.data.queue_numbers[0]) + 1
          // console.log(queuePosition.value)
          // console.log(response.data.queue_numbers)
          if (queueList.value.length > 0 && queueList.value[0].status === 'waiting' && currentServing.value == null) {
              caterCustomer(queueList.value[0].id);
              startWait(queueList.value[0].id, queueList.value[0].queue_number)
          }
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
          // $notify('positive', 'check', 'Customer is now being served.')
          stopWait() // Stop wait if customer is catered
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
         
            const response = await $axios.post('/admin/start-wait', { queue_number: queueNumber })
          
            waiting.value = true
            waitTime.value = 60
            $notify('positive', 'check', "Waiting for Queue Number: " + queueNumber)
            waitTimer = setInterval(() => {
              if (waitTime.value > 0) {
                waitTime.value--
              } else {
                stopWait()
              }
            }, 1000)
          
        } catch (error) {
          console.error(error)
          $notify('negative', 'error', 'Failed to set waiting customer.')
        }
      }

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
  
        // Pagination
        currentPage,
        itemsPerPage,
        paginatedQueueList,
        totalPages
      }
    }
  }
  </script>
  