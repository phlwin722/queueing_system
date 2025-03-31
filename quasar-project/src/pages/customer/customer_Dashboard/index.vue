<template>
  <q-layout view="lHh lpr lFf" class="flex flex-center shadow-2 bg-primary">
    <div
      class="row wrap col-md-6 justify-center items-center flex q-gutter-md q-pa-md"
      style="width: 100%; max-width: 600px; margin: auto"
    >

      <!-- User Queue Status -->
      <q-card
        class="col-12 col-md-5 full-width shadow-3 bg-white rounded-borders q-pa-md q-pa-xs"
      >
      <q-btn
            color="yellow-8"
            icon="download"
            @click="generatePDF"
            dense
            class="q-ml-sm"
            style="min-width: 30px; max-width: 40px;"
            size="sm"
          >
            <q-tooltip
              anchor="top start"
              self="center right"
              :offset="[10, 10]"
              class="bg-secondary"
              style="font-size: 12px; padding: 4px 8px; max-width: 120px"
            >
              Download PDF
            </q-tooltip>
          </q-btn>
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
              width="60px"
              height="60px"
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
              {{ `${indicator}#-${String(customerQueueNumber || "N/A").padStart(3, '0')}` }}
            </div>
          </div>
        </q-card-section>
        <q-separator />

        <q-card-section
          v-if="queuePosition == 0 && customerStatus == 'serving'"
          class="row justify-around q-pa-md"
        >
          <div class="column items-center">
            <div class="text-center text-h5 text-bold text-positive q-mb-md">
              You Are Currently Being Served
            </div>
          </div>
        </q-card-section>

        <q-card-section v-else class="row justify-around q-pa-md">
          <div class="column items-center">
            <div class="text-bold text-grey-7 text-caption">
              Currently Serving
            </div>
            <div class="text-h5 text-blue-10 text-bold">
              {{ `${indicator}#-${String(currentQueue || "..." ).padStart(3, '0')}` }}
            </div>
          </div>
          <div class="column items-center">
            <div class="text-bold text-grey-7 text-caption">
              Your Position in Queue
            </div>
            <div class="text-h5 text-indigo-10 text-bold">
              {{ queuePosition || "..." }}
            </div>
          </div>
        </q-card-section>
        <div
          v-if="queuePosition && queuePosition <= 5 && !isBeingServed"
          class="text-center text-warning q-mb-md q-mt-md"
        >
          <q-icon name="warning" size="sm" /> You are near from being served.
          Please standby!
        </div>
      
        <div
          v-if="queuePosition && queuePosition > 0 && !isBeingServed"
          class="text-center text-warning q-mb-md q-mt-md"
        >
          Expected cater time: {{timeCater}}
        </div>

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
        class="col-12 col-md-6 full-width shadow-3 rounded-borders q-px-md q-pa-xs q-py-sm"
        flat
      >
        <q-card-section class="row items-center">
          <p class="text-bold text-primary text-h6 q-mb-none">Queue List</p>
          <q-space />
          <!-- Pushes the button to the right -->

          <q-btn
            color="yellow-8"
            icon="attach_money"
            @click="isMoneyRatesDialogOpen = true"
            dense
            class="q-ml-sm"
            style="min-width: 30px; max-width: 40px"
            size="sm"
          >
            <q-tooltip
              anchor="top start"
              self="center right"
              :offset="[10, 10]"
              class="bg-secondary"
              style="font-size: 12px; padding: 4px 8px; max-width: 120px"
            >
              Money Rates
            </q-tooltip>
          </q-btn>
        </q-card-section>

        <q-separator inset />


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
                  Queue: {{ `${indicator}#-${String(customer.queue_number || "N/A").padStart(3, '0')}` }} -
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

        <!-- "Money Rates" button in the lower right -->

        <!-- QDialog for Money Rates Table -->
        <q-dialog v-model="isMoneyRatesDialogOpen">
          <q-card class="q-dialog-plugin" style="width: 90vw; max-width: 500px">
            <q-card-section class="row items-center bg-primary">
              <q-icon
                name="attach_money"
                size="sm"
                class="text-yellow q-mr-xs col-shrink"
              />
              <span class="text-white col-grow text-no-wrap">
                Money Exchange Rates
              </span>
              <q-space />
              <q-btn
                flat
                dens
                round
                icon="close"
                @click="isMoneyRatesDialogOpen = false"
                style="width: 24px; height: 24px"
                class="text-accent"
              />
            </q-card-section>

            <q-separator />

            <!-- q-table inside dialog -->
            <q-card-section>
              <q-table
                flat
                bordered
                :rows="moneyRates"
                :columns="columns"
                row-key="currency"
                :rows-per-page-options="[0]"
                hide-bottom
                virtual-scroll
              >
                <!-- Custom slot for rendering the content of the Currency column -->
                <template v-slot:body-cell-currency="props">
                  <q-td :props="props">
                    <!-- Display the flag icon -->
                    <span
                      :class="['fi', props.row.currency.flag]"
                      style="font-size: 1.5em; margin-right: 8px"
                    ></span>
                    <!-- Display the currency symbol and name-->
                    <span
                      >{{ props.row.currency.symbol }} -
                      {{ props.row.currency.name }}</span
                    >
                  </q-td>
                </template>
              </q-table>
            </q-card-section>
          </q-card>
        </q-dialog>
      </q-card>
    </div>
  </q-layout>
</template>

<script>
import { ref, onMounted, onUnmounted, computed, watch, nextTick } from "vue";
import { useRoute, useRouter } from "vue-router";
import { $axios, $notify } from "boot/app";
import { useQuasar, date } from "quasar";
import { jsPDF } from "jspdf"; // Import jsPDF for PDF generation
import autoTable from 'jspdf-autotable'; // Import the autoTable plugin explicitly
import QRCode from 'qrcode'; // Import the qrcode package

export default {
  setup() {
    const router = useRouter();
    const route = useRoute();
    const $dialog = useQuasar();
    const tokenurl = ref(route.params.token);
    const customerQueueNumber = ref(0);
    const customerId = ref(0);
    const indicator = ref("");
    const serving_time = ref(0);

    const queueList = ref([]);
    const currentQueue = ref(null);
    const serviceType = ref();
    const assignedTeller = ref("");
    const typeId = ref();
    const queuePosition = ref(null);
    const customerStatus = ref("");
    const isBeingServed = ref(false);
    const isWaiting = ref(false);
    const hasNotified = ref(false); // Prevents repeat notifications
    const countdown = ref(); // 60 seconds countdown
    const tellerId = ref();
    const imageUrl = ref();
    let refreshInterval = null;
    let countdownInterval = null;
    let waitingTimeout;
    let queueTimeout;
    let statusTimeout;
    const moneyRates = ref([]);
    const currentPage = ref(1);
    const itemsPerPage = 5; // Number of items per page

    const waitTime = ref(30); // Default wait time (can be fetched dynamically)
    const prepared = ref("");
    const remainingTime = ref(0);
    let waitInterval = null;
    const generatedQrValue = ref('http://192.168.0.164:8080/customer-dashboard/' + tokenurl.value); // User input (for the bank name)

    const userInformation = ref({
      id: "",
      token: "",
      queue_number: "",
      name: "",
      email: "",
      email_status: "",
      teller_id: "",
      type_id: "",
      window_name: "",
    });

    const totalPages = computed(() =>
      Math.ceil(queueList.value.length / itemsPerPage)
    );

    // Function to abbreviate name as per your requirement
    const abbreviateName = (name) => {
      const words = name.split(" "); // Split the name by spaces (e.g., "John Doe" -> ["John", "Doe"])
      return words
        .map((word, index) => {
          if (index === 0) {
            // Take first letter of each word and append "..."
            return word[0].toUpperCase() + "...";
          }
          // For all other words (e.g., last name), leave them as is
          return word;
        })
        .join(" "); // Join back the abbreviated words  Output: "J... D.."
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
        queuePosition.value = customer.position
        customerStatus.value = customer.status;
        if (customer.status === "finished" && !hasNotified.value) {

          await $axios.post('/sent-email-finish',{
            id :  userInformation.value.id,
            email :   userInformation.value.email,
            subject : 'Thankyou for visit' 
          })

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
          
          await $axios.post('/sent-email-finish',{
              id : customerId.value,
              email :  customer.email,
              subject : 'Thankyou for visit' 
            })
          setTimeout(() => router.push("/customer-thankyou/"), 2000);
        }
        checkingQueueNumber(); // Call the function to check queue number after updating data
      } catch (error) {
        console.error(error);
      }
    };

    const getTableData = async () => {
      try {
        // fetching specific customer
        const { data } = await $axios.post("/customer-fetch", {
          token: tokenurl.value,
        });
        serviceType.value = data.row.name;
        indicator.value = data.row.indicator;
        serving_time.value = data.row.serving_time;
        assignedTeller.value =
          data.row.teller_firstname + " " + data.row.teller_lastname;
        typeId.value = data.row.typeId;
        tellerId.value = data.row.id;
        customerQueueNumber.value = data.userInfo.queue_number;
        customerId.value = data.userInfo.id;

        userInformation.value.id = data.userInfo.id
        userInformation.value.name = data.userInfo.name
        userInformation.value.token = data.userInfo.token
        userInformation.value.email_status = data.userInfo.email_status
        userInformation.value.email = data.userInfo.email
        userInformation.value.teller_id = data.userInfo.teller_id
        userInformation.value.type_id = data.userInfo.type_id
        userInformation.value.window_name = data.window.window_name

        fetchImage(tellerId.value);
        sendingDashboard(); // trigger sendingDashboard
        //putTellerId()
        console.log('buratka',data)
      } catch (error) {
        console.log(error);
      }
    };
    const approximateCaterTime = ref();
    const timeCater = ref ("")

    const approximateWaitTime = computed(() => {
      if (queuePosition.value === null || !serving_time.value) return "N/A";

      const estimatedTimeInMinutes = queuePosition.value * serving_time.value;
      approximateCaterTime.value = Date.now() + estimatedTimeInMinutes * 60 * 1000; // Convert minutes to milliseconds

      return estimatedTimeInMinutes;
    });

    watch(approximateCaterTime, (newTime) => {
      if (newTime) {
        timeCater.value = date.formatDate(newTime, "hh:mm A"); // Convert to readable time
        console.log("Updated Cater Time:", timeCater.value);
        console.log("Updated Cater Time:", approximateWaitTime.value);
      }
    });

    watch(
      () => queuePosition.value,
      (newValue) => {
        if (newValue !== null && newValue !== 0) {
          $notify(
            "info",
            "hourglass_empty",
            `Your approximate wait time is ${approximateWaitTime.value} minutes. Expected cater time: ${date.formatDate(approximateCaterTime.value, "hh:mm A")}`
          );
        }
      }
    );
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
        const { data } = await $axios.post("/customer-check-waiting", {
          token: tokenurl.value,
        });

        if (data.waiting_customer === "yes") {
          if (!isWaiting.value) {
            // Start countdown only if not already waiting
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
          leaveQueue();
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
        setTimeout(() => router.push("/customer-thankyou/"), 1000);
      } catch (error) {
        console.error(error);
        $notify("negative", "error", "Failed to leave queue.");
      }
    };

    // sending link to access the dashboard
    const sendingDashboard = async () => {
      try {
        if (userInformation.value.email_status ===  'sending_customer') {
          await $axios.post('sent-email-dashboard',{
            id : userInformation.value.id,
            token: userInformation.value.token,
            queue_number: `${indicator.value}#-${String(customerQueueNumber.value || "N/A").padStart(3, '0')}`,
            email: userInformation.value.email,
            name: userInformation.value.name,
            subject: "Queue Alert", // Email subject
            message: `Welcome to our bank! To provide you with a seamless and efficient service experience, 
                      we’ve implemented a queue system that helps manage customer flow. 
                      Our system is designed to prioritize your needs and minimize waiting times. 
                      You are free to go about your activities, and once your turn is approaching, 
                      you’ll receive an email notification with further details. Thank you for choosing us!`, // Email message body
          });
        }

      } catch (error) {
        if (error.response.status === 422) {
          console.log('error sending dashboard', error)
        }
      }
    }

    // Function to check if the user's queue number is 5, then send an email notification
    const checkingQueueNumber = async () => {
      try {
        const response = await $axios.post("/customer-list", {
          token: tokenurl.value,
        });
        queueList.value = response.data.queue.filter(
          (q) => !["finished", "cancelled", "serving"].includes(q.status)
        );
        if (approximateWaitTime.value <= 45) {
          
          if (queueList.value.length > 0) {  
              await $axios.post(
                "/send-email",{
                  id: userInformation.value.id, // Recipient's id
                  token:  userInformation.value.token, // Recipient's token
                  name:  userInformation.value.name, // Recipient's name
                  email:  userInformation.value.email, // Recipient's email address
                  subject: "Queue Alert", // Email subject
                  message: `You are just a few steps away from being served! 
                            Please remain on standby, as your turn is approaching soon.`, // Email message body
              }
              );
            
          }
        }
      } catch (error) {
        // Log the error in the console if the request fails
        console.log(error);
      }
    };

    // fetching image of teller 
    const fetchImage = async (tellerId) => {
      try {
        const { data } = await $axios.post("/teller/image-fetch-csdashboard", {
          id: tellerId,
        });

        imageUrl.value = data.Image;
      } catch (error) {
        if (error.response.status === 422) {
          console.log(error);
        }
      }
    };

    const optimizedFetchQueueData = async () => {
    await fetchQueueData();
    queueTimeout = setTimeout(optimizedFetchQueueData, 2000); // Recursive Timeout
    };

    const optimizedFetchWaitingStatus = async () => {
      await fetchWaitingStatus();
      statusTimeout = setTimeout(optimizedFetchWaitingStatus, 2000); // Recursive Timeout
    };

    const optimizedFetchWaitingtime = async () => {
      await fetchWaitingtime();
      waitingTimeout = setTimeout(optimizedFetchWaitingtime, 2000); // Recursive Timeout
    };

    const isMoneyRatesDialogOpen = ref(false);

    const columns = [
      {
        name: "currency",
        required: true,
        label: "Currency",
        align: "left",
        field: (row) => row.currency,
        format: (val) => `${val}`,
      },
      {
        name: "buy",
        required: true,
        label: "Buy",
        align: "right",
        field: (row) => row.buy,
        format: (val) => `${val}`,
      },
      {
        name: "sell",
        required: true,
        label: "Sell",
        align: "right",
        field: (row) => row.sell,
        format: (val) => `${val}`,
      },
    ];

    const fetchCurrency = async () => {
      try {
        const { data } = await $axios.post("/currency/showData");

        // map the api response to match the expected table structure
        moneyRates.value = data.rows.map((row) => ({
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
        if (error.response.status === 422) {
          console.log(error);
        }
      } 
    };

      // Start of the function to generate a PDF
      const generatePDF = async () => {
        // Try-catch block for error handling
        try {
          // Create a new jsPDF instance, which will be used to generate the PDF
          const doc = new jsPDF();

          // Path to the logo image; this is specific to Quasar's Webpack setup
          const logoPath = require('assets/vrtlogoblack.png'); 
          
          // Get the page width of the generated PDF document
          const pageWidth = doc.internal.pageSize.width;
          
          // Set dimensions for the logo image that will be added to the PDF
          const imgWidth = 100;
          const imgHeight = 15;
          
          // Calculate the horizontal position to center the image on the page
          const centerImage = (pageWidth - imgWidth) / 2;  // Horizontal center
          
          // Set the vertical position for the logo image
          const y = 10;  // Top margin of image (can be adjusted as needed)
          
          // Add the logo image to the PDF at the calculated position
          doc.addImage(logoPath, 'PNG', centerImage, y, imgWidth, imgHeight);

          // Set the title text for the PDF
          const title = "Queueing System";
          
          // Set the font size for the title text
          doc.setFontSize(17);
          
          // Set the font style to Helvetica, bold
          doc.setFont("helvetica", "bold");
          
          // Calculate the width of the title text in order to center it horizontally
          const textWidth = doc.getStringUnitWidth(title) * doc.internal.getFontSize() / doc.internal.scaleFactor;
          
          // Calculate the x-coordinate to center the title on the page
          const titleCenter = (pageWidth - textWidth) / 2;
          
          // Set the vertical position for the title text
          const top_PositionTitle = 50;
          
          // Add the title text to the PDF at the calculated position
          doc.text(title, titleCenter, top_PositionTitle);

          // Data for the table to be included in the PDF
          const tableData = [
            ['Queue number: ', `${indicator.value}#-${String(customerQueueNumber.value || "N/A").padStart(3, '0')}`],
            ['Name: ',  userInformation.value.name],
            ['Email: ',  userInformation.value.email],
            ['Service type: ', serviceType.value],
            ['Window: ', userInformation.value.window_name]
          ];

          // Use jsPDF's autoTable plugin to create a table in the PDF
          autoTable(doc, {
            head: [['Description', 'Details']],  // Column headers for the table
            body: tableData,  // The body data for the table
            theme: 'grid',  // Grid style for the table (adds borders around cells)
            startY: 60,  // Starting Y position for the table
            headStyles: {
              fillColor: [33, 150, 243], // Set background color of table headers to blue
              textColor: [255, 255, 255], // Set text color of table headers to white
              fontStyle: 'bold', // Set the font style of table headers to bold
            },
            styles: {
              cellPadding: 5, // Set padding inside each table cell
            },
            margin: { top: 60 }, // Set the top margin for the table
          });

          // Add the title for QR code generation
          const titleGenerateQr = 'Generated Qr Code';

          // Calculate the width of the title text
          const textWidthGenerateQr = doc.getStringUnitWidth(titleGenerateQr) * doc.internal.getFontSize() / doc.internal.scaleFactor;

          // Calculate the x-coordinate to center the title on the page
          const titleCenterQr = (pageWidth - textWidthGenerateQr) / 2;

          // Set the vertical position for the title text (below the table)
          const top_PositionTitleQR = doc.lastAutoTable.finalY + 15
          doc.text(titleGenerateQr, titleCenterQr, top_PositionTitleQR);

          // Ensure the QR code value is set correctly
          const qrValue = generatedQrValue.value; // Assuming this contains the value you want to encode

          // Generate the QR code image
          const qrCodeDataUrl = await QRCode.toDataURL(qrValue, { errorCorrectionLevel: 'H', type: 'image/png' });

          // Set the size of the QR code
          const qrSize = 80; // Adjust size as needed

          // Position the QR code on the PDF (centered below the table)
          const qrX = (pageWidth - qrSize) / 2; // Center horizontally
          const qrY = doc.lastAutoTable.finalY + 17; // Position below the table

          // Add the QR code image to the PDF
          doc.addImage(qrCodeDataUrl, 'PNG', qrX, qrY, qrSize, qrSize);

          // Save the generated PDF file with the name 'Customer_queueing_information.pdf'
          doc.save('Customer_queueing_information.pdf');
    
        } catch (error) {
          // Log any errors that occur during the PDF generation process
          console.log(error);
        }
      };

    onMounted(() => {
      getTableData();
      optimizedFetchWaitingtime();
      optimizedFetchQueueData();
      optimizedFetchWaitingStatus();
      setInterval(fetchCurrency(),30000);
    });

    onUnmounted(() => {
      clearTimeout(waitingTimeout);
      clearTimeout(queueTimeout);
      clearTimeout(statusTimeout);
    });

    return {
      generatedQrValue, // Return the generated QR value to be used in the template
      generatePDF,
      customerQueueNumber,
      queueList,
      currentQueue,
      queuePosition,
      indicator,
      customerStatus,
      isBeingServed,
      isWaiting,
      countdown,
      leaveQueue,
      checkingQueueNumber,
      userInformation,
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
      isMoneyRatesDialogOpen,
      columns,
      moneyRates,
      timeCater
    };
  },
};
</script>

<style scoped>
@import "flag-icons/css/flag-icons.min.css";

/* body {
  background-color: #1c5d99;
} */

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
