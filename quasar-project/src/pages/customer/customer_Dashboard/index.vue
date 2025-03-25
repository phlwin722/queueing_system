<template>
  <q-layout view="lHh lpr lFf" class="flex flex-center shadow-2">
    <div
      class="row wrap col-md-6 justify-center items-center flex q-gutter-md q-pa-md"
      style="width: 100%; max-width: 600px; margin: auto"
    >
      <!-- User Queue Status -->
      <q-card
        class="col-12 col-md-5 full-width shadow-3 bg-white rounded-borders q-pa-md q-pa-xs"
      >
        <!-- Modernized Service Type & Personnel with Glass Effect -->
        <q-card
          class="q-pa-md glass-card text-dark flex row justify-evenly items-end"
          flat
        >
          <q-card-section class="row items-center">
            <q-icon
              name="work_outline"
              size="md"
              class="text-primary q-mr-sm"
            />
            <div class="column">
              <div class="text-xs text-grey-7 text-uppercase">Service Type</div>
              <div class="text-lg text-bold text-secondary">
                {{ serviceType }}
              </div>
            </div>
          </q-card-section>
          <q-card-section class="row items-center">
            <q-img
              :src="imageUrl || require('assets/no-image.png')"
              width="30px"
              height="30px"
              class="text-secondary q-mr-md shadow-1"
            />
            <div class="column">
              <div class="text-xs text-grey-7 text-uppercase">Personnel</div>
              <div class="text-lg text-bold text-positive">
                {{ assignedTeller }}
              </div>
            </div>
          </q-card-section>
        </q-card>

        <q-separator />

        <q-card-section class="text-center">
          <div class="column items-center">
            <div class="text-bold text-grey-7 text-caption">
              Your Queue Number
            </div>
            <div class="text-h2 text-primary text-bold q-mt-sm">
              {{ customerQueueNumber || "N/A" }}
            </div>
          </div>
        </q-card-section>
        <q-separator />

        <q-card-section
          v-if="queuePosition > 0"
          class="row justify-around q-pa-md"
        >
          <div class="column items-center">
            <div class="text-bold text-grey-7 text-caption">
              Currently Serving
            </div>
            <div class="text-h5 text-blue-10 text-bold">
              {{ currentQueue || "None" }}
            </div>
          </div>
          <div class="column items-center">
            <div class="text-bold text-grey-7 text-caption">
              Your Remaining Position
            </div>
            <div class="text-h5 text-indigo-10 text-bold">
              {{ queuePosition || "N/A" }}
            </div>
          </div>
        </q-card-section>

        <q-card-section v-else class="row justify-around q-pa-md">
          <div class="column items-center">
            <div class="text-center text-h5 text-bold text-positive q-mb-md">
              You Are Currently Being Served
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
              @click="beforeCancel"
            />
          </q-card-actions>
        </q-card-section>
        <div
          v-if="isWaiting"
          class="text-center text-bold text-positive q-mb-md"
        >
          <p class="text-orange">Admin is waiting for you! Please proceed.</p>
          <h2 class="text-red">{{ formatTime(remainingTime) }}</h2>
        </div>
      </q-card>

      <!-- Queue List -->
      <q-card
        class="col-12 col-md-6 full-width shadow-3 rounded-borders q-px-md q-pa-xs q-py-md"
        style="margin-bottom: 20px"
        flat
      >
        <q-card-section class="text-center">
          <p class="text-bold text-primary text-h6">Queue List</p>
        </q-card-section>

        <q-separator inset />


        <div
          v-if="queuePosition && queuePosition <= 5 && !isBeingServed"
          class="text-center text-warning q-mb-md q-mt-md"
        >
          <q-icon name="warning" size="sm" /> You are near from being served.
          Please standby!
        </div>

        <q-card-section
          class="q-pa-md"
          style="max-height: 300px; overflow-y: auto"
        >
          <q-list bordered separator class="queue-list">
            <q-item
              v-for="(customer, index) in queueList"
              :key="index"
              class="queue-item"
            >
              <q-item-section avatar>
                <q-icon
                  name="confirmation_number"
                  size="sm"
                  class="text-primary"
                />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-bold text-grey-8">
                  Queue: {{ customer.queue_number }} -
                  {{ abbreviateName(customer.name) }}
                </q-item-label>
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
  </q-layout>
</template>

<script>
import { ref, onMounted, onUnmounted, computed, watch, nextTick } from "vue";
import { useRoute, useRouter } from "vue-router";
import { $axios, $notify } from "boot/app";
import { useQuasar } from "quasar";

export default {
  setup() {
    const router = useRouter();
    const route = useRoute();
    const $dialog = useQuasar();
    const tokenurl = ref(route.params.token);
    const customerQueueNumber = ref(
      localStorage.getItem("queue_number" + tokenurl.value) || null
    );
    const customerId = ref(
      localStorage.getItem("customer_id" + tokenurl.value) || null
    );
    const token = ref(
      localStorage.getItem("customer_token" + tokenurl.value) || null
    );

    const queueList = ref([]);
    const currentQueue = ref(null);
    const serviceType = ref();
    const assignedTeller = ref("");
    const typeId = ref();
    const queuePosition = ref(null);
    const isBeingServed = ref(false);
    const isWaiting = ref(false);
    const hasNotified = ref(false); // Prevents repeat notifications
    const countdown = ref(); // 60 seconds countdown
    const tellerId = ref()
    const imageUrl = ref()
    let refreshInterval = null;
    let countdownInterval = null;

    const waitTime = ref(30); // Default wait time (can be fetched dynamically)
    const prepared = ref("");
    const remainingTime = ref(0);
    let waitInterval = null;

    const emailData = ref({
      // Email data list
      name: "",
      email: "",
      subject: "",
      message: "",
    });

    const currentPage = ref(1);
    const itemsPerPage = 5; // Number of items per page

    const totalPages = computed(() =>
      Math.ceil(queueList.value.length / itemsPerPage)
    );

    // Function to abbreviate name as per your requirement
    const abbreviateName = (name) => {
      const words = name.split(" "); // Split the name by spaces (e.g., "John Doe" -> ["John", "Doe"])
      return words
        .map((word,index) => {
            if (index === 0) {
              // Take first letter of each word and append "..."
              return word[0].toUpperCase() + "...";
            }
            // For all other words (e.g., last name), leave them as is
            return word;
        })
        .join(" "); // Join back the abbreviated words  Output: "J... Dy"
    };

    // const putTellerId = async () => {
    //   await $axios.post("/update-teller_id",{
    //     token: tokenurl.value,
    //     teller_id : tellerId.value
    //   });
    // }
    // Fetch queue list and current serving number
    const fetchQueueData = async () => {
      try {
        const response = await $axios.post("/customer-list", {
          token: tokenurl.value,
        });

        queueList.value = response.data.queue.filter(
          (q) => !["finished", "cancelled", "serving"].includes(q.status)
        );
        currentQueue.value = response.data.current_serving;
        // Check if the customer is currently being served
        isBeingServed.value = currentQueue.value == customerQueueNumber.value;
        // Determine customer position in queue
        queuePosition.value =
          queueList.value.findIndex(
            (q) => q.queue_number == customerQueueNumber.value
          ) + 1;

        // If admin pressed "Wait" for the first in queue, start countdown
        // if (
        //   response.data.waiting_customer === 'yes') {
        //   console.log(response.data.waiting_customer)
        //   isWaiting.value = true;
        //   startWait();
        // } else {
        //   isWaiting.value = false;
        //   clearInterval(countdownInterval); // Stop countdown if not waiting
        // }

        if (isBeingServed.value) {
          queueList.value = queueList.value.filter(
            (q) => q.queue_number !== customerQueueNumber.value
          );
        }

        // Notify and redirect when the customer is finished
        const customer = response.data.queue.find(
          (q) => q.id == customerId.value
        );
        if (customer.status === "finished" && !hasNotified.value) {
          hasNotified.value = true; // Mark as notified
          $notify("positive", "check", "Your turn is finished. Thank you!");
          setTimeout(() => router.push("/customer-thankyou/"), 2000); // Delay redirect for a smooth transition
        }
        if (customer && customer.status === "cancelled" && !hasNotified.value) {
          hasNotified.value = true; // Mark as notified
          $notify(
            "negative",
            "error",
            "The Admin cancelled your queueing number."
          );
          setTimeout(
            () => router.push("/customer-thankyou/"),
            2000
          );
        }
        checkingQueueNumber(); // Call the function to check queue number after updating data
      } catch (error) {
        console.error(error);
      }
    };
    
    const getTableData = async () => {
        try{
            const { data } = await $axios.post('/customer-fetch',{token: tokenurl.value})
            serviceType.value = data.row.name
            assignedTeller.value = data.row.teller_firstname +" "+data.row.teller_lastname
            typeId.value = data.row.typeId
            tellerId.value = data.row.id
            fetchImage(tellerId.value)
          //putTellerId()
        }catch(error){
            console.log(error);
        }
    }
    // Start countdown timer
    // const startCountdown = () => {
    //   if (!countdownInterval) {
    //     countdown.value = 60
    //     $notify('warning', 'hourglass_empty', 'The admin is waiting for you! Please proceed. '+ formatTime(remainingTime))

    //     countdownInterval = setInterval(() => {
    //       if (countdown.value > 0) {
    //         countdown.value--
    //       } else {
    //         clearInterval(countdownInterval)
    //         isWaiting.value = false
    //       }
    //     }, 1000)
    //   }
    // }
    //resets countdown
    // const resetCountdown = () => {
    //   const newStartTime = Math.floor(Date.now() / 1000);
    //   localStorage.setItem('countdown_start_time', newStartTime);
    //   countdown.value = 60; // Reset to 60 seconds
    // };

    // Fetch the waiting status from the backend
    const fetchWaitingStatus = async () => {
    try {
          const { data } = await $axios.post('/customer-check-waiting', { token: tokenurl.value });

          if (data.waiting_customer === "yes") {
              if (!isWaiting.value) { // Start countdown only if not already waiting
                  isWaiting.value = true;
                  startCountdown();
              }
          } else {
              stopCountdown();
          }
      } catch (error) {
          console.error("Error fetching waiting status:", error);
      }
  };


    const fetchWaitingtime = async () => {
      try {
        const { data } = await $axios.post("/admin/waiting_Time-fetch");

        if (data && data.dataValue && data.dataValue.length > 0) {
          let fetchedTime = data.dataValue[0].Waiting_time;
          let fetchedPrepared = data.dataValue[0].Prepared;

          // Convert to seconds if "Minutes"
          waitTime.value =
            fetchedPrepared === "Minutes" ? fetchedTime * 60 : fetchedTime;
          prepared.value = fetchedPrepared;
        } else {
          console.log("No data available");
        }
      } catch (error) {
        console.log("Error fetching data:", error);
      }
    };

  const startCountdown = () => {
      if (remainingTime.value > 0) return; // Prevent resetting the countdown
      
      const storedTime = localStorage.getItem("waitStartTime" + tokenurl.value);

      if (storedTime) {
          // Calculate remaining time after refresh
          const startTime = parseInt(storedTime);
          const elapsedTime = Math.floor((Date.now() - startTime) / 1000);
          remainingTime.value = Math.max(waitTime.value - elapsedTime, 0);
      } else {
          // First time starting countdown
          localStorage.setItem("waitStartTime" + tokenurl.value, Date.now());
          remainingTime.value = waitTime.value;
      }

      if (waitInterval) clearInterval(waitInterval);

      waitInterval = setInterval(() => {
          if (remainingTime.value > 0) {
              remainingTime.value--;
          } else {
              stopCountdown();
          }
      }, 1000);
  };

    const stopCountdown = () => {
      isWaiting.value = false;
      clearInterval(waitInterval);
      localStorage.removeItem("waitStartTime" + tokenurl.value);
      remainingTime.value = 0; // Reset to avoid negative values
  };

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

    //cancel dialog
    const beforeCancel = () => {
      $dialog
        .dialog({
          title: "Confirm",
          message: "Are you sure you want to leave the queue?",
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
          leaveQueue()
        })
        .onDismiss(() => {
          // console.log('I am triggered on both OK and Cancel')
        });
    };


    // Leave the queue
    const leaveQueue = async () => {
      try {
        await $axios.post("/customer-leave", { id: customerId.value });
        hasNotified.value = true; // Mark as notified
        $notify("positive", "check", "You have left the queue.");
        setTimeout(
            () => router.push("/customer-thankyou/"),
            1000
          );
      } catch (error) {
        console.error(error);
        $notify("negative", "error", "Failed to leave queue.");
      }
    };

    // Function to check if the user's queue number is 5, then send an email notification
    const checkingQueueNumber = async () => {
      try {
        const response = await $axios.post("/customer-list", {
          token: tokenurl.value,
        });
        queueList.value = response.data.queue.filter(
          (q) => !["finished", "cancelled", "serving"].includes(q.status)
        );

        if (queuePosition.value === 1) {
          if (queueList.value.length > 0) {
            const { data } = await $axios.post("/send-fetchInfo", {
              id: queueList.value[0].id,
            });
            if (data.Information.email_status === "pending") {
              // Assign email data with the recipient's details and email content
              emailData.value = {
                id: data.Information.id, // Recipient's id
                token: data.Information.token, // Recipient's token
                name: data.Information.name, // Recipient's name
                email: data.Information.email, // Recipient's email address
                subject: "Queue Alert", // Email subject
                message: "You are near from being served. Please standby!", // Email message body
              };

              // Send a POST request to the '/send-email' endpoint with emailData as payload
              const { emailContent } = await $axios.post(
                "/send-email",
                emailData.value
              );

            
            }
          }
        }
      } catch (error) {
        // Log the error in the console if the request fails
        console.log(error);
      }
    };

    const fetchImage = async (tellerId) => {
      try {
        const { data } = await $axios.post('/teller/image-fetch-csdashboard',{
          id: tellerId,
        })

        imageUrl.value = data.Image
      } catch (error) {
        if (error.response.status === 422) {
          console.log(error)
        }
      }
    }

    onMounted(() => {
      getTableData();
      fetchQueueData();
      fetchWaitingtime();
      refreshInterval = setInterval(fetchWaitingtime, 2000);
      refreshInterval = setInterval(fetchQueueData, 2000); // Auto-refresh every 5 seconds
      fetchWaitingStatus();
      setInterval(fetchWaitingStatus, 2000);
    });

    // onUnmounted(() => {
    //   clearInterval(refreshInterval); // Stop auto-refresh
    //   clearInterval(countdownInterval); // Stop countdown
    // });

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
      serviceType,
      assignedTeller,
      tellerId,
      formatTime,
      remainingTime,
      abbreviateName, // Expose the abbreviateName function
      beforeCancel,
      fetchImage,
      imageUrl,
    };
  },
};
</script>

<style>
body {
  background-color: #1c5d99;
}

.rounded-borders {
  border-radius: 16px;
}
.shadow-3 {
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
}

/* Queue List Styling */
.queue-list {
  border-radius: 8px;
  padding: 4px;
}

.queue-item {
  transition: all 0.3s ease-in-out;
}

.queue-item:hover {
  background: rgba(0, 0, 0, 0.05);
}

.custom-badge {
  cursor: default;
}
</style>
