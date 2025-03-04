<template>
    <div class="q-pa-md">
      <q-card class="q-pa-md shadow-2">
        <q-card-section>
          <div class="text-h6 text-center">
            Your Queue Number: <strong>{{ customerQueueNumber || 'N/A' }}</strong>
          </div>
          <div class="text-subtitle1 text-center">
            Currently Serving: <strong>{{ currentQueue || 'None' }}</strong>
          </div>
          <div class="text-subtitle1 text-center">
            Your Position: <strong>{{ queuePosition || 'Calculating...' }}</strong>
          </div>
        </q-card-section>
  
        <q-separator />
        <q-card-actions v-if="!isBeingServed && !isWaiting" align="center">
          <q-btn label="CANCEL" color="negative" @click="leaveQueue" />
        </q-card-actions>
        <q-card-section>
          <div class="text-h6">Queue List</div>
  
          <!-- Show "You are being served" if the customer is being served -->
          <div v-if="isBeingServed" class="text-center text-bold text-positive q-mb-md">
            You are being served. Enjoy!
          </div>
  
          <!-- Show warning when customer position is <= 5 -->
          <div v-if="queuePosition && queuePosition <= 5 && !isBeingServed" class="text-center text-warning q-mb-md">
            <q-icon name="warning" size="sm" />
            You are near from being served. Please standby!
          </div>
  
          <!-- Show countdown if admin pressed "Wait" -->
          <div v-if="isWaiting && queuePosition === 1 && !isBeingServed" class="text-center text-negative q-mb-md">
            <q-icon name="hourglass_empty" size="sm" />
            The admin is waiting. Please proceed. If not, your queueing number will be cancelled in 
            <strong>{{ countdown }}</strong> seconds.
          </div>
  
          <!-- Show queue list only if the customer is not being served -->
          <q-list v-else bordered separator>
            <!-- Use paginatedQueueList instead of queueList -->
            <q-item v-for="(customer, index) in paginatedQueueList" :key="index">
              <q-item-section>
                <q-item-label class="text-bold">Queue No: {{ customer.queue_number }}</q-item-label>
  
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
  
        <q-separator />
        <!-- Pagination Controls -->
        <q-card-section class="row justify-center">
          <q-pagination
            v-model="currentPage"
            :max="totalPages"
            :max-pages="5"
            boundary-numbers
            color="primary"
          />
        </q-card-section>
      </q-card>
    </div>
  </template>
  
  <script>
  import { ref, onMounted, onUnmounted, computed } from 'vue'
  import { useRoute, useRouter } from 'vue-router'
  import { $axios, $notify } from 'boot/app'
  
  export default {
    setup() {
      const route = useRoute()
      const router = useRouter()
  
      const customerQueueNumber = ref(localStorage.getItem('queue_number') || null)
      const token = ref(localStorage.getItem('customer_token') || null)
      console.log('Token:', token.value) // Check
      
      const queueList = ref([])
      const currentQueue = ref(null)
      const queuePosition = ref(null)
      const isBeingServed = ref(false)
      const isWaiting = ref(false)
      const hasNotified = ref(false) // Prevents repeat notifications
      const countdown = ref(60) // 60 seconds countdown
      let refreshInterval = null
      let countdownInterval = null
  
      const currentPage = ref(1)
      const itemsPerPage = 5 // Number of items per page
  
      const totalPages = computed(() => Math.ceil(queueList.value.length / itemsPerPage))
  
      // Fetch queue list and current serving number
      const fetchQueueData = async () => {
        try {
          const response = await $axios.post('/customer-list')
          queueList.value = response.data.queue.filter(q => !['finished', 'cancelled'].includes(q.status))
  
          currentQueue.value = response.data.current_serving
  
          // Determine customer position in queue
          queuePosition.value = queueList.value.findIndex(q => q.queue_number == customerQueueNumber.value) + 1
  
          // Check if the customer is currently being served
          isBeingServed.value = currentQueue.value == customerQueueNumber.value
  
          // If admin pressed "Wait" for the first in queue, start countdown
          if (response.data.waiting_customer === customerQueueNumber.value && queuePosition.value === 1) {
            isWaiting.value = true
            startCountdown()
          } else {
            isWaiting.value = false
            clearInterval(countdownInterval) // Stop countdown if not waiting
          }
  
          if (isBeingServed.value) {
            queueList.value = queueList.value.filter(q => q.queue_number !== customerQueueNumber.value)
          }
  
          // Notify and redirect when the customer is finished
          const customer = response.data.queue.find(q => q.queue_number == customerQueueNumber.value)
          if (customer && customer.status === 'finished' && !hasNotified.value) {
            hasNotified.value = true // Mark as notified
            $notify('positive', 'check', 'Your turn is finished. Thank you!')
            setTimeout(() => router.push('/customer-register/J5OoCi9vI3'), 2000) // Delay redirect for a smooth transition
          }
          if (customer && customer.status === 'cancelled') {
            $notify('negative', 'error', 'The Admin cancelled your queueing number.')
            setTimeout(() => router.push('/customer-register/FZDXRGKf4c'), 2000)
          }
  
        } catch (error) {
          console.error(error)
        }
      }
  
      // Start countdown timer
      const startCountdown = () => {
        if (!countdownInterval) {
          countdown.value = 60
          $notify('warning', 'hourglass_empty', 'The admin is waiting for you! Please proceed.')
  
          countdownInterval = setInterval(() => {
            if (countdown.value > 0) {
              countdown.value--
            } else {
              clearInterval(countdownInterval)
              isWaiting.value = false
            }
          }, 1000)
        }
      }
  
      // Leave the queue
      const leaveQueue = async () => {
        try {
          await $axios.post('/customer-leave', { queue_number: customerQueueNumber.value })
          $notify('positive', 'check', 'You have left the queue.')
          router.push('/customer-register/'+token.value)
        } catch (error) {
          console.error(error)
          $notify('negative', 'error', 'Failed to leave queue.')
        }
      }
  
      // Paginated queue list
      const paginatedQueueList = computed(() => {
        const start = (currentPage.value - 1) * itemsPerPage
        return queueList.value.slice(start, start + itemsPerPage)
      })
  
      onMounted(() => {
        fetchQueueData()
        refreshInterval = setInterval(fetchQueueData, 5000) // Auto-refresh every 5 seconds
      })
  
      onUnmounted(() => {
        clearInterval(refreshInterval) // Stop auto-refresh
        clearInterval(countdownInterval) // Stop countdown
      })
  
      return { 
        customerQueueNumber, 
        queueList, 
        currentQueue, 
        queuePosition, 
        isBeingServed, 
        isWaiting, 
        countdown, 
        leaveQueue,
  
        // Pagination
        currentPage,
        itemsPerPage,
        paginatedQueueList,
        totalPages
      }
    }
  }
  </script>