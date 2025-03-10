<template>
  <q-layout view="lHh lpr lFf" class="shadow-2 rounded-borders">
    <div class="absolute-center full-width full-height bg-grey-2">
      <div
        class="row wrap justify-center q-gutter-md q-pt-md"
        style="max-width: 600px; margin: auto"
      >
        <!-- User Queue Status -->
        <q-card
          class="col-12 full-width shadow-3 bg-white rounded-borders q-pa-md"
        >
          <q-card-section class="text-center">
            <div class="column items-center">
              <div class="text-bold text-grey-7 text-caption">
                Your Queue Number
              </div>
              <div class="text-h2 text-deep-orange-10 text-bold">
                {{ customerQueueNumber || 'N/A' }}
              </div>
            </div>
          </q-card-section>
          <q-separator />
          <q-card-section class="row justify-around q-pa-md">
            <div class="column items-center">
              <div class="text-bold text-grey-7 text-caption">
                Currently Serving
              </div>
              <div class="text-h5 text-blue-10 text-bold">
                {{ currentQueue || 'None' }}
              </div>
            </div>
            <div class="column items-center">
              <div class="text-bold text-grey-7 text-caption">
                Your Position
              </div>
              <div class="text-h5 text-indigo-10 text-bold">
                {{ queuePosition || 'N/A' }}
              </div>
            </div>
          </q-card-section>

          <q-separator />

          <q-card-section class="text-center q-mt-md">
            <q-card-actions v-if="!isBeingServed && !isWaiting" align="center">
              <q-btn
              color="red-10"
              label="Cancel Queue"
              size="lg"
              unelevated
              class="rounded-borders full-width text-bold"
              @click="leaveQueue"
            />
            </q-card-actions>
            
          </q-card-section>
        </q-card>

        <!-- Queue List -->
        <q-card
          class="col-12 full-width shadow-3 bg-white rounded-borders q-pa-md"
        >
          <!-- Show "You are being served" if the customer is being served -->
          <div v-if="isBeingServed" class="text-center text-bold text-positive q-mb-md">
            You are being served !
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
          <q-separator />
          <q-card-section class="text-center">
            <p class="text-bold text-primary text-h6">Queue List</p>
          </q-card-section>
          
          <q-card-section
            class="q-pa-md"
            style="max-height: 300px; overflow-y: auto"
          >
            <q-list bordered separator>
              <q-item v-for="(customer, index) in queueList" :key="index">
                <q-item-section>
                  <q-item-label class="text-bold text-grey-8"
                    >Queue: {{ customer.queue_number }}</q-item-label
                  >
                </q-item-section>
              </q-item>
            </q-list>
            <div
              v-if="queueList.length === 0"
              class="text-grey text-center q-mt-md"
            >
              No more customers
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-layout>
</template>

<script>
  import { ref, onMounted, onUnmounted, computed } from 'vue'
  import { useRoute, useRouter } from 'vue-router'
  import { $axios, $notify } from 'boot/app'
  
  export default {
    setup() {
      
      const router = useRouter()
      const route = useRoute()
      const tokenurl = ref(route.params.token)
      const customerQueueNumber = ref(localStorage.getItem('queue_number'+tokenurl.value) || null)
      const customerId = ref(localStorage.getItem('customer_id'+tokenurl.value) || null)
      const token = ref(localStorage.getItem('customer_token'+tokenurl.value) || null)
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
    
      const emailSended = ref ('')
      const emailData = ref({ // Email data list
        name: "",
        email: "",
        subject: "",
        message: "",
      });
  
      const currentPage = ref(1)
      const itemsPerPage = 5 // Number of items per page
  
      const totalPages = computed(() => Math.ceil(queueList.value.length / itemsPerPage))
  
      // Fetch queue list and current serving number
      const fetchQueueData = async () => {
        try {
          const response = await $axios.post('/customer-list');
          queueList.value = response.data.queue.filter(q => !['finished', 'cancelled','serving'].includes(q.status));

          currentQueue.value = response.data.current_serving;
        

          // Check if the customer is currently being served
          isBeingServed.value = currentQueue.value == customerQueueNumber.value;
          let count = 0
          if(currentQueue == null){
            count =1
          }else{
            count = 0
          }
          // Determine customer position in queue
          queuePosition.value = queueList.value.findIndex(q => q.queue_number == customerQueueNumber.value) + 1+count

          // If admin pressed "Wait" for the first in queue, start countdown
          if (response.data.waiting_customer === customerQueueNumber.value && queuePosition.value === 1) {
            isWaiting.value = true;
            startCountdown();
          } else {
            isWaiting.value = false;
            clearInterval(countdownInterval); // Stop countdown if not waiting
          }

          if (isBeingServed.value) {
            queueList.value = queueList.value.filter(q => q.queue_number !== customerQueueNumber.value);
          }

          // Notify and redirect when the customer is finished
          const customer = response.data.queue.find(q => q.id == customerId.value);
          if (customer.status === 'finished' && !hasNotified.value) {
            hasNotified.value = true; // Mark as notified
            $notify('positive', 'check', 'Your turn is finished. Thank you!');
            setTimeout(() => router.push('/customer-register/J5OoCi9vI3'), 2000); // Delay redirect for a smooth transition
          }
          if (customer && customer.status === 'cancelled') {
            $notify('negative', 'error', 'The Admin cancelled your queueing number.');
            setTimeout(() => router.push('/customer-register/FZDXRGKf4c'), 2000);
          }
          checkingQueueNumber()  // Call the function to check queue number after updating data
        } catch (error) {
          console.error(error);
        }
}
  
      // Start countdown timer
      const startCountdown = () => {
        const storedStartTime = localStorage.getItem('countdown_start_time');

        if (!storedStartTime) {
          // If no stored time, set the countdown start time
          const newStartTime = Math.floor(Date.now() / 1000);
          localStorage.setItem('countdown_start_time', newStartTime);
        }

        // Get the correct countdown start time
        const countdownStartTime = parseInt(localStorage.getItem('countdown_start_time'), 10);
        const currentTime = Math.floor(Date.now() / 1000);

        // Calculate remaining time
        countdown.value = Math.max(60 - (currentTime - countdownStartTime), 0);

        if (!countdownInterval && countdown.value > 0) {
          countdownInterval = setInterval(() => {
            if (countdown.value > 0) {
              countdown.value--;
            } else {
              clearInterval(countdownInterval);
              isWaiting.value = false;
              localStorage.removeItem('countdown_start_time'); // Clear storage when countdown ends
            }
          }, 1000);
        }
      };


  
      // Leave the queue
      const leaveQueue = async () => {
        try {
          console.log(customerId.value)
          await $axios.post('/customer-leave', { id: customerId.value })
          $notify('positive', 'check', 'You have left the queue.')
          console.log('cancelled')
          router.push('/customer-register/'+token.value)
        } catch (error) {
          console.error(error)
          console.log('cancelled')
          $notify('negative', 'error', 'Failed to leave queue.')
        }
      }
  
      // Paginated queue list
      const paginatedQueueList = computed(() => {
        const start = (currentPage.value - 1) * itemsPerPage
        return queueList.value.slice(start, start + itemsPerPage)
      })


     // Function to check if the user's queue number is 5, then send an email notification
     const checkingQueueNumber = async () => {
        try {
          if (queuePosition.value === 1) {
             const { data } = await $axios.post('/send-fetchInfo', {
                  queue_number: customerQueueNumber.value
              });

             if (data.Information.email_status === 'pending'){
              // Assign email data with the recipient's details and email content
              emailData.value = {
                  id: data.Information.id, // Recipient's id
                  token: data.Information.token, // Recipient's token
                  name: data.Information.name,      // Recipient's name
                  email: data.Information.email, // Recipient's email address
                  subject: "Queue Alert",      // Email subject
                  message: "You are near from being served. Please standby!"     // Email message body
              };

              // Send a POST request to the '/send-email' endpoint with emailData as payload
              const { emailContent } = await $axios.post('/send-email', emailData.value);

              // Show an alert to confirm that the email was sent successfully
              console.log('Message succesuf', emailContent)
             }
          }
        } catch (error) {
            // Log the error in the console if the request fails
            console.log(error);
        }
    };

  
      onMounted(() => {
        fetchQueueData()
        refreshInterval = setInterval(fetchQueueData, 5000) // Auto-refresh every 5 seconds
        
      })

      onMounted(() => {
        const startTime = localStorage.getItem('countdown_start_time')
          if (startTime) {
            startCountdown() // Resume countdown
          }
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
        checkingQueueNumber,
        emailData,
        customerId,
  
        // Pagination
      }
    }
  }
  </script>

<style scoped>
.rounded-borders {
  border-radius: 16px;
}
.shadow-3 {
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
}
</style>
