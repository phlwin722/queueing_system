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

        <q-card-section style="text-align: right;">
          <q-btn
            color="yellow-8"
            icon="download"
            @click="generatePDF"
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
              Download PDF
            </q-tooltip>
          </q-btn>

          <q-btn v-if="queuePosition != 0 && customerStatus != 'serving'"
            @click="showSwitchDialog()"
            color="green-10"
            icon="switch_account"
            dense
            style="min-width: 30px; max-width: 40px; margin-left: 5px;"
            size="sm"
          >
            <q-tooltip
              anchor="top start"
              self="center right"
              :offset="[10, 10]"
              class="bg-secondary"
              @click="fetchType()"
              style="font-size: 12px; padding: 4px 8px; max-width: 120px"
            >
              Switch Teller
            </q-tooltip>
          </q-btn>


          <q-dialog v-model="showDialog">
            <q-card class="q-pa-md" style="min-width: 310px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1)">
              <div class="text-primary" style="font-weight: 600; font-size: 20px;">
                Switch Teller
              </div>
              <q-card-section>

                <!-- Loop through serviceTypeId and create a div for each item -->
                <div 
                  v-for="item in serviceTypeId" 
                  :key="item.value" 
                  @click="changeTeller(item), showDialog = false" 
                  :id="`item-${item.value}`" 
                  class="item-div col cursor-pointer"
                  style="border-bottom: 1px solid #e0e0e0; padding: 10px 0; transition: background-color 0.3s ease;"
                >
                  <div class="row items-center">
                    <div class="col-auto q-ml-sm">
                      <q-img
                        :src="item.teller_image || require('assets/no-image.png')"
                        width="60px"
                        height="60px"
                        class="text-secondary q-mr-md shadow-1 rounded-borders"
                      />
                    </div>
                    <div class="col">
                      <!-- Display item.name and item.service_name inside the p tag -->
                      <p class="text-h6 q-mb-xs" style="font-weight: 600">{{ item.name }}</p>
                      <p class="service-name text-subtitle2" style="color: #21ba45">{{ item.service_name }}</p>
                    </div>
                  </div>
                </div>
              </q-card-section>
            </q-card>
          </q-dialog>


        </q-card-section>
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
              {{
                `${indicator}#-${String(customerQueueNumber || "N/A").padStart(
                  3,
                  "0"
                )}`
              }}
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
              {{
                `${indicator}#-${String(currentQueue || "...").padStart(
                  3,
                  "0"
                )}`
              }}
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
          v-if="approximateWaitTime <= 30 && !isBeingServed && !isNotBeingCatered"
          class="text-center text-warning q-mb-md q-mt-md"
        >
          <q-icon name="warning" size="sm" /> You are near from being served.
          Please standby!
        </div>

        <div
          v-if="queuePosition && queuePosition > 0 && !isBeingServed && !isNotBeingCatered"
          class="text-center text-warning q-mb-md q-mt-md"
        >
          Expected cater time: {{ timeCater }}

          <q-icon
            name="info"
            size="18px"
            class="q-ml-sm cursor-pointer"
            color="grey-7"
          >
            <q-tooltip
              anchor="top middle"
              self="bottom middle"
              :offset="[0, 5]"
              transition-show="scale"
              transition-hide="scale"
            >
              This expected cater time is just an assumption. You may be catered earlier or later than the expected cater time. Thank you.
            </q-tooltip>
          </q-icon>
        </div>

        <div
          v-if="convertExpectedCaterTime >= newTime && newFormattedTime < originalFromBreak && !isBeingServed "
          class="text-center text-warning q-mb-md q-mt-md"
        >
        Warning: you might not be catered, break time soon at: {{ tempFromBreak }}
        </div>

        <div
          v-if="newFormattedTime >= originalFromBreak && newFormattedTime < convertedToBreak && !isBeingServed"
          class="text-center text-warning q-mb-md q-mt-md"
        >
        We are on break, We will be back at: {{ tempToBreak }}
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
                  Queue:
                  {{
                    `${indicator}#-${String(
                      customer.queue_number || "N/A"
                    ).padStart(3, "0")}`
                  }}
                  -
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
import { ref, onMounted, onUnmounted, computed, watch, nextTick, onBeforeUnmount } from "vue";
import { useRoute, useRouter } from "vue-router";
import { $axios, $notify } from "boot/app";
import { useQuasar, date } from "quasar";
import { jsPDF } from "jspdf"; // Import jsPDF for PDF generation
import autoTable from "jspdf-autotable"; // Import the autoTable plugin explicitly
import QRCode from "qrcode"; // Import the qrcode package

export default {
  setup() {
    const router = useRouter();
    const route = useRoute();
    const $dialog = useQuasar();
    const tokenurl = ref(route.params.token);
    const customerQueueNumber = ref(0);
    const customerId = ref(0);
    const indicator = ref("");
    const serving_time = ref(10);

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
    const moneyRates = ref([]);
    const currentPage = ref(1);
    const itemsPerPage = 5; // Number of items per page
    let showDialog = ref(false);

    const waitTime = ref(30); // Default wait time (can be fetched dynamically)
    const prepared = ref("");
    const remainingTime = ref(0);
    let waitInterval = null;
    const generatedQrValue = ref(
      "http://192.168.0.164:8080/customer-dashboard/" + tokenurl.value
    ); // User input (for the bank name)

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
      branch_id: "",
      status: "",
      priority_service: "",
    });

    const serviceTypeId = ref([]);
    const selectedTeller = ref(null);

    const fetchType = async () => {
      try {
       
        const { data } = await $axios.post("/customer-fetch", {
          token: tokenurl.value,
          branch_id : userInformation.value.branch_id,
        });

        // get the current teller 
        const currentTellerId = data.userInfo.teller_id;

        // find matching tellers that has the similar type_ids
        serviceTypeId.value = data.matchingTellers
          .filter((teller) => teller.id !== currentTellerId) // Exclude the current teller
          .map((teller) => {
            return {
              name: teller.full_name, // full name ng teller
              teller_id: teller.id, // The id of the teller 
              image: teller.Image,
              service_name: teller.name,
              teller_image: teller.teller_image,
              type_id_teller: teller.type_id,
            };
          });

        console.log("Service Type IDs and Tellers:", serviceTypeId.value);
      } catch (error) {
        console.log(error);
      }
    };

    // Method to handle saving the selected teller's data
  /*   const saveSelectedTeller = async () => {
      if (!selectedTeller.value) {
        console.log("Please select a teller first.");
        return;
      }

      try {
        const { data } = await axios.post("/save-selected-teller", {
          teller_id: selectedTeller.value, // Send the selected teller's ID
        });

        // Handle success
        console.log("Teller selected and saved:", data);
      } catch (error) {
        // Handle error
        console.error("Error saving selected teller:", error);
      }
    }; */

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
    const QueueListlastUpdatedAt = ref(null); // default to null
    let polling = true;
    const fetchQueueData = async () => {
      if (!polling) return;
  try {
    const response = await $axios.post("/customer-list", {
      token: tokenurl.value,
      last_updated: QueueListlastUpdatedAt.value,
    });

    // Only process if there's an update
    if (response.data.updated) {
      // Update the queue
      queueList.value = response.data.queue.filter(
        (q) => !["finished", "cancelled", "serving"].includes(q.status)
      );

      currentQueue.value = response.data.current_serving;
      isBeingServed.value = currentQueue.value == customerQueueNumber.value;

      if (isBeingServed.value) {
        queueList.value = queueList.value.filter(
          (q) => q.queue_number !== customerQueueNumber.value
        );
      }

      const customer = response.data.queue.find(
        (q) => q.id == customerId.value
      );

      if (customer) {
        queuePosition.value = customer.status === 'serving' ? 0 : customer.position;
        customerStatus.value = customer.status;

        // if (customer.status === "finished" && !hasNotified.value) {
        //   await $axios.post("/sent-email-finish", {
        //     id: userInformation.value.id,
        //     email: userInformation.value.email,
        //     subject: "Thank you for visit",
        //   });
        //   console.log("finished")
        //   hasNotified.value = true;
        //   $notify("positive", "check", "Your turn is finished. Thank you!");
        //   polling = false;
        //   waitingPolling = false;
        //   waitingTimePolling = false;
        //   setTimeout(() => router.push("/customer-thankyou/"), 2000);
        // }

        // if (customer.status === "cancelled" && !hasNotified.value) {
        //   hasNotified.value = true;
        //   $notify("negative", "error", "The Admin cancelled your queueing number.");

        //   await $axios.post("/sent-email-finish", {
        //     id: customerId.value,
        //     email: customer.email,
        //     subject: "Thank you for visit",
        //   });
        //   polling = false;
        //   waitingPolling = false;
        //   waitingTimePolling = false;
        //   setTimeout(() => router.push("/customer-thankyou/"), 2000);
        // }
      }

      checkingQueueNumber(); // Always check queue number
      QueueListlastUpdatedAt.value = response.data.last_updated_at; // Update timestamp
    }
  } catch (error) {
    console.error(error);
  }finally{
    if(queueList.value.length > 5){
      if (polling) setTimeout(fetchQueueData, 10000);
    }else{
      if (polling) setTimeout(fetchQueueData, 5000);
    }

  }
};

  // const branch_id = ref()
    const getTableData = async () => {
      try {
        // fetching specific customer
        const { data } = await $axios.post("/customer-fetch", {
          token: tokenurl.value,
          branch_id : userInformation.value.branch_id,
        });
        serviceType.value = data.row.name;
        indicator.value = data.row.indicator;
        serving_time.value = data.row.serving_time;
        if(serving_time.value == null){
          serving_time.value = 10
        }

        assignedTeller.value = data.row.teller_firstname + " " + data.row.teller_lastname;
        typeId.value = data.row.typeId;
        tellerId.value = data.row.id;
        customerQueueNumber.value = data.userInfo.queue_number;
        customerId.value = data.userInfo.id;

        userInformation.value.id = data.userInfo.id;
        userInformation.value.name = data.userInfo.name;
        userInformation.value.token = data.userInfo.token;
        userInformation.value.email_status = data.userInfo.email_status;
        userInformation.value.email = data.userInfo.email;
        userInformation.value.teller_id = data.userInfo.teller_id;
        userInformation.value.type_id = data.userInfo.type_id;
        userInformation.value.window_name = data.window.window_name;
        userInformation.value.branch_id = data.userInfo.branch_id;
        userInformation.value.status = data.userInfo.status;
        userInformation.value.priority_service = data.userInfo.priority_service;

        setInterval(fetchCurrency(userInformation.value.branch_id),30000);
        fetchImage(tellerId.value);
        sendingDashboard(); // trigger sendingDashboard
        // updateBranchId()
        //putTellerId()

      } catch (error) {
        console.log(error);
      }
    };

    watch(
      () => customerStatus.value,
      async (newValue) => { // <-- add "async" here
        console.log("Status changed:", newValue);
        if (newValue == 'cancelled' && !hasNotified.value) {
          console.log("Status Cancelled:", newValue);
          hasNotified.value = true;
          $notify("negative", "error", "The Admin cancelled your queueing number.");
          polling = false;
          waitingPolling = false;
          waitingTimePolling = false;
          setTimeout(() => router.push("/customer-thankyou/"), 2000);
          await $axios.post("/sent-email-finish", {
            id: customerId.value,
            email: userInformation.value.email,
            subject: "Thank you for visit",
          });

        }else if (newValue == 'finished' && !hasNotified.value) {
          console.log("Status Finished:", userInformation.value.email);
          hasNotified.value = true;
          $notify("positive", "check", "Your turn is finished. Thank you!");
          polling = false;
          waitingPolling = false;
          waitingTimePolling = false;
          setTimeout(() => router.push("/customer-thankyou/"), 2000);
          await $axios.post("/sent-email-finish", {
            id: customerId.value,
            email: userInformation.value.email,
            subject: "Thank you for visit",
          });

        }
      }
    );


    // const updateBranchId = async () => {
    //   try {
    //     const { data } = await $axios.post("/update-branch_id",{
    //       id: userInformation.value.id,
    //       branch_id : branch_id.value
    //     });
    //   } catch (error) {
    //     console.log(error);
    //   }
    // }
    const approximateCaterTime = ref();
    const timeCater = ref("");

    const approximateWaitTime = computed(() => {
      if (queuePosition.value === null || !serving_time.value) return "N/A";
      if(isNotBeingCatered.value == true && caughtBreakTime.value == true){
        const estimatedTimeInMinutes = parseTime(toBreak.value)+(queuePosition.value * serving_time.value)
        approximateCaterTime.value = Date.now() + estimatedTimeInMinutes * 60 * 1000; // Convert minutes to milliseconds
        
        return estimatedTimeInMinutes;
      }else{
        const estimatedTimeInMinutes = queuePosition.value * serving_time.value;
        approximateCaterTime.value = Date.now() + estimatedTimeInMinutes * 60 * 1000; // Convert minutes to milliseconds

        return estimatedTimeInMinutes;
      }


    });

    const newTime = ref("")
    const newFormattedTime = ref("")
    const originalFromBreak = ref("")
    const fromBreak = ref("")
    const toBreak = ref("")
    const formattedCurrentTime = ref("")
    const convertedToBreak = ref("")
    const convertExpectedCaterTime = ref()
    const tempFromBreak = ref()
    const tempToBreak = ref()


    const fetchBreakTimeLastUpdatedAt = ref(null); // last update tracker

const fetchBreakTime = async () => {

  try {
    const { data } = await $axios.post("/admin/fetch_break_time", {
      last_updated: fetchBreakTimeLastUpdatedAt.value,
      branch_id: userInformation.value.branch_id,
    });
    if (data?.dataValue) {
      fromBreak.value = data.dataValue.break_from.slice(0, 5);
      tempFromBreak.value = formatTo12Hour(fromBreak.value);

      toBreak.value = data.dataValue.break_to.slice(0, 5);
      tempToBreak.value = formatTo12Hour(toBreak.value);
      console.log("Updated ", fromBreak.value, toBreak.value);
      const currentTime = new Date();
      const currentHour = currentTime.getHours().toString().padStart(2, "0");
      const currentMinutes = currentTime.getMinutes().toString().padStart(2, "0");
      formattedCurrentTime.value = `${currentHour}:${currentMinutes}`;

      const totalMinutes = parseTime(fromBreak.value) - 10;
      newTime.value = formatTime2(totalMinutes);

      const OrgtotalMinutes = parseTime(fromBreak.value);
      originalFromBreak.value = formatTime2(OrgtotalMinutes);

      const convertToBreak = parseTime(toBreak.value);
      convertedToBreak.value = formatTime2(convertToBreak);

      const totalFormatMinutes = parseTime(formattedCurrentTime.value);
      newFormattedTime.value = formatTime2(totalFormatMinutes);

      // Condition to check if user is NOT being catered
      if (
        (newFormattedTime.value >= originalFromBreak.value &&
          newFormattedTime.value < convertedToBreak.value &&
          !isBeingServed.value) ||
        (convertExpectedCaterTime.value >= newTime.value &&
          newFormattedTime.value < originalFromBreak.value &&
          !isBeingServed.value)
      ) {
        isNotBeingCatered.value = true;
      } else {
        isNotBeingCatered.value = false;
      }
    }else{
      console.warn("No break time found")
    }
  } catch (error) {
    console.error("Error fetching break time:", error);
  }
};


    function parseTime(timeString) {
        // Make sure we're working with a string (access .value if it's a Vue ref)
        const timeStr = typeof timeString === 'object' && 'value' in timeString 
            ? timeString.value 
            : timeString;
        
        const [hours, minutes] = timeStr.split(':').map(Number);
        return hours * 60 + minutes;
    }

    function formatTime2(totalMinutes) {
        const hours = Math.floor(totalMinutes / 60) % 24;
        const minutes = totalMinutes % 60;
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
    }
    const formatTo12Hour = (time) => {
        const [hour, minute] = time.split(":").map(Number);
        const ampm = hour >= 12 ? "PM" : "AM";
        const formattedHour = hour % 12 || 12; // Convert 0 or 12 to 12, 13 to 1, etc.
        return `${formattedHour}:${minute.toString().padStart(2, "0")} ${ampm}`;
      };

      const caughtBreakTime = ref(false)
      const isNotBeingCatered = ref(false)
    watch(approximateCaterTime, (newTimes) => {
      if (newTimes) {
        timeCater.value = date.formatDate(newTimes, "hh:mm A");
        const timePart = date.formatDate(newTimes)
        console.log(timePart)
        convertExpectedCaterTime.value = timePart.split("T")[1].split(":").slice(0, 2).join(":");
        convertExpectedCaterTime.value= parseTime(convertExpectedCaterTime.value) // Convert to readable time
        convertExpectedCaterTime.value= formatTime2(convertExpectedCaterTime.value)
        if(newFormattedTime.value >= originalFromBreak.value && newFormattedTime.value < convertedToBreak.value && !isBeingServed.value || convertExpectedCaterTime.value >= newTime.value && newFormattedTime.value < originalFromBreak.value && !isBeingServed.value){
          isNotBeingCatered.value = true

        }else{
          isNotBeingCatered.value = false
        }
        if(newFormattedTime.value >= originalFromBreak.value && newFormattedTime.value < convertedToBreak.value && !isBeingServed.value){
          caughtBreakTime.value = true
        }
      }
    });

    watch(
      () => queuePosition.value,
      (newValue) => {
        
        if(isNotBeingCatered.value == false){
        if (newValue !== null && newValue !== 0) {
          
            $notify(
            "info",
            "hourglass_empty",
            `Your approximate wait time is ${
              approximateWaitTime.value
            } minutes. Expected cater time: ${date.formatDate(
              approximateCaterTime.value,
              "hh:mm A"
            )}`
          );
        }
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
    const fetchWaitingStatuslastUpdatedAt = ref(null); // default to null
let waitingPolling = true; // Flag to control recursive polling

const fetchWaitingStatus = async () => {
  if (!waitingPolling) return;

  try {
    const { data } = await $axios.post("/customer-check-waiting", {
      token: tokenurl.value,
      last_updated: fetchWaitingStatuslastUpdatedAt.value,
      branch_id: userInformation.value.branch_id,
    });

    if (data.updated) {
      if (data.waiting_customer === "yes") {
        if (!isWaiting.value) {
          // Start countdown only if not already waiting
          isWaiting.value = true;
          startCountdown();
        }
      } else {
        stopCountdown();
      }
      fetchWaitingStatuslastUpdatedAt.value = data.last_updated_at;
    }

  } catch (error) {
    console.error("Error fetching waiting status:", error);
  } finally {
    if (waitingPolling) setTimeout(fetchWaitingStatus, 5000); // Schedule next poll
  }
};

    


const fetchWaitingTimeLastUpdatedAt = ref(null); // last update tracker
let waitingTimePolling = true; // Flag to control recursive polling

const fetchWaitingtime = async () => {
  if (!waitingTimePolling) return; // Prevent re-fetch if polling is stopped

  try {
    const { data } = await $axios.post("/admin/waiting_Time-fetch", {
      last_updated: fetchWaitingTimeLastUpdatedAt.value,
      branch_id: userInformation.value.branch_id,
    });

    // Only update if data was changed
    if (data.updated) {
      if (data?.dataValue?.Waiting_time) {
        waitTime.value = data.dataValue.Waiting_time;
        console.log(waitTime.value)
        fetchWaitingTimeLastUpdatedAt.value = data.last_updated_at;
      } else {
        console.log("No data available");
      }
    }

  } catch (error) {
    console.log("Error fetching data:", error);
  } finally {
    if (waitingTimePolling) setTimeout(fetchWaitingtime, 10000); // Schedule next poll
  }
};

      // // Fetch waiting time with error handling
      // const fetchWaitingtimelastUpdatedAt = ref(null); // default to null
      // let fetchWaitingtimepolling = true;
      // const fetchWaitingtimes = async () => {
      //   if (!fetchWaitingtimepolling) return;
      //   try {
      //     const { data } = await $axios.post("/admin/waiting_Time-fetch",{
      //       last_updated: fetchWaitingtimelastUpdatedAt.value,
      //       branch_id: tellerInformation.value.branch_id,
      //     });
      //     if (data.updated) {
      //       if (data?.dataValue?.Waiting_time) {
      //         waitTime.value = data.dataValue.Waiting_time;
      //       }
            
      //       fetchWaitingtimelastUpdatedAt.value = data.last_updated_at;
      //     }
          
      //   } catch (error) {
      //     console.error("Error fetching waiting time:", error);
      //   }finally {
      //     if (fetchWaitingtimepolling) setTimeout(fetchWaitingtime, 10000);
      //   }
      // };
    


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
        polling = false;
        waitingPolling = false;
        waitingTimePolling = false;
        setTimeout(() => router.push("/customer-thankyou/"), 1000);
      } catch (error) {
        console.error(error);
        $notify("negative", "error", "Failed to leave queue.");
      }
    };

    // sending link to access the dashboard
    const sendingDashboard = async () => {
      try {
        if (userInformation.value.email_status === "sending_customer") {
          await $axios.post("sent-email-dashboard", {
            id: userInformation.value.id,
            token: userInformation.value.token,
            queue_number: `${indicator.value}#-${String(
              customerQueueNumber.value || "N/A"
            ).padStart(3, "0")}`,
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
          console.log("error sending dashboard", error);
        }
      }
    };

    const showSwitchDialog = async () => {
      try{
        console.log('serviceTypeId.value.length ',serviceTypeId.value )
        serviceTypeId.value.length === 0 ? $notify('negative','error','There are no tellers available at the moment.') : showDialog.value = true
      } catch (error) {
        console.log("ShowSwitchDilog",error)
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
            await $axios.post("/send-email", {
              id: userInformation.value.id, // Recipient's id
              token: userInformation.value.token, // Recipient's token
              name: userInformation.value.name, // Recipient's name
              email: userInformation.value.email, // Recipient's email address
              subject: "Queue Alert", // Email subject
              message: `You are just a few steps away from being served! 
                        Please remain on standby, as your turn is approaching soon.`, // Email message body
            });
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
      await getTableData();
      await fetchType();
      queueTimeout = setTimeout(optimizedFetchQueueData, 5000); // Recursive Timeout
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

    const fetchCurrency = async (branch_id) => {
      try {
        const { data } = await $axios.post("/currency/showData", {
          branch_id :  branch_id,
        });

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
        const logoPath = require("assets/vrtlogoblack.png");

        // Get the page width of the generated PDF document
        const pageWidth = doc.internal.pageSize.width;

        // Set dimensions for the logo image that will be added to the PDF
        const imgWidth = 100;
        const imgHeight = 15;

        // Calculate the horizontal position to center the image on the page
        const centerImage = (pageWidth - imgWidth) / 2; // Horizontal center

        // Set the vertical position for the logo image
        const y = 10; // Top margin of image (can be adjusted as needed)

        // Add the logo image to the PDF at the calculated position
        doc.addImage(logoPath, "PNG", centerImage, y, imgWidth, imgHeight);

        // Set the title text for the PDF
        const title = "Queueing System";

        // Set the font size for the title text
        doc.setFontSize(17);

        // Set the font style to Helvetica, bold
        doc.setFont("helvetica", "bold");

        // Calculate the width of the title text in order to center it horizontally
        const textWidth =
          (doc.getStringUnitWidth(title) * doc.internal.getFontSize()) /
          doc.internal.scaleFactor;

        // Calculate the x-coordinate to center the title on the page
        const titleCenter = (pageWidth - textWidth) / 2;

        // Set the vertical position for the title text
        const top_PositionTitle = 50;

        // Add the title text to the PDF at the calculated position
        doc.text(title, titleCenter, top_PositionTitle);

        // Data for the table to be included in the PDF
        const tableData = [
          [
            "Queue number: ",
            `${indicator.value}#-${String(
              customerQueueNumber.value || "N/A"
            ).padStart(3, "0")}`,
          ],
          ["Name: ", userInformation.value.name],
          ["Email: ", userInformation.value.email],
          ["Service type: ", serviceType.value],
          ["Window: ", userInformation.value.window_name],
        ];

        // Use jsPDF's autoTable plugin to create a table in the PDF
        autoTable(doc, {
          head: [["Description", "Details"]], // Column headers for the table
          body: tableData, // The body data for the table
          theme: "grid", // Grid style for the table (adds borders around cells)
          startY: 60, // Starting Y position for the table
          headStyles: {
            fillColor: [33, 150, 243], // Set background color of table headers to blue
            textColor: [255, 255, 255], // Set text color of table headers to white
            fontStyle: "bold", // Set the font style of table headers to bold
          },
          styles: {
            cellPadding: 5, // Set padding inside each table cell
          },
          margin: { top: 60 }, // Set the top margin for the table
        });

        // Add the title for QR code generation
        const titleGenerateQr = "Generated Qr Code";

        // Calculate the width of the title text
        const textWidthGenerateQr =
          (doc.getStringUnitWidth(titleGenerateQr) *
            doc.internal.getFontSize()) /
          doc.internal.scaleFactor;

        // Calculate the x-coordinate to center the title on the page
        const titleCenterQr = (pageWidth - textWidthGenerateQr) / 2;

        // Set the vertical position for the title text (below the table)
        const top_PositionTitleQR = doc.lastAutoTable.finalY + 15;
        doc.text(titleGenerateQr, titleCenterQr, top_PositionTitleQR);

        // Ensure the QR code value is set correctly
        const qrValue = generatedQrValue.value; // Assuming this contains the value you want to encode

        // Generate the QR code image
        const qrCodeDataUrl = await QRCode.toDataURL(qrValue, {
          errorCorrectionLevel: "H",
          type: "image/png",
        });

        // Set the size of the QR code
        const qrSize = 80; // Adjust size as needed

        // Position the QR code on the PDF (centered below the table)
        const qrX = (pageWidth - qrSize) / 2; // Center horizontally
        const qrY = doc.lastAutoTable.finalY + 17; // Position below the table

        // Add the QR code image to the PDF
        doc.addImage(qrCodeDataUrl, "PNG", qrX, qrY, qrSize, qrSize);

        // Save the generated PDF file with the name 'Customer_queueing_information.pdf'
        doc.save("Customer_queueing_information.pdf");
      } catch (error) {
        // Log any errors that occur during the PDF generation process
        console.log(error);
      }
    };

    // switching teller
    const changeTeller = async (item) => {    
      
      $dialog
        .dialog({
          title: "Confirm",
          message: "Are you sure you want to switch to these personel?",
          cancel: true,
          persistent: true,
          ok: {
            label: "Yes",
            color: "primary",
            unelevated: true,
            style: "width: 125px;",
          },
          cancel: {
            label: "Cancel",
            color: "red-8",
            unelevated: true,
            style: "width: 125px;",
          },
          style: "border-radius: 12px; padding: 16px;",
        })
        .onOk(async () => {
          try{
             await $axios.post('/customer-join-switch-teller',{
              teller_id: item.teller_id,
              type_id_teller: item.type_id_teller,
              userId: userInformation.value.id, // Recipient's id
              branch_id: userInformation.value.branch_id,
              priority_service: userInformation.value.priority_service,
            });
            window.location.reload();
          } catch (error) {
            console.log(error)
          }
        })
        .onDismiss(() => {
          // console.log('I am triggered on both OK and Cancel')
        });
  
    }



    onMounted(() => {
      getTableData()
      fetchQueueData()
      fetchWaitingStatus()
      fetchWaitingtime()
      fetchBreakTime()
      setInterval(() => {
        fetchBreakTime();
      }, 10000); 
      fetchType();
      // updateBranchId()
    });



    return {
      changeTeller,
      showSwitchDialog,
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
      timeCater,
      newTime,
      newFormattedTime,
      originalFromBreak,
      fromBreak,
      toBreak,
      convertExpectedCaterTime,
      convertedToBreak,
      formatTo12Hour,
      tempToBreak,
      tempFromBreak,
      approximateWaitTime,
      isNotBeingCatered,

      // teller switchi SAO
      showDialog,
      serviceTypeId,
      fetchType,
      selectedTeller,
    };
  },
};
</script>

<style scoped>
@import "flag-icons/css/flag-icons.min.css";

/* body {
  background-color: #1c5d99;
} */
 /* Style for each item div */
.item-div {
  margin-top: 10px;
  padding: 15px;
  border: 2px solid #393C3F; /* Adds border */
  border-radius: 8px; /* Adds rounded corners */
  cursor: pointer;
  transition: background-color 0.3s, transform 0.3s; /* Smooth transition for hover effect */
}

/* Hover effect: Change background and slightly enlarge the div */
.item-div:hover {
  background-color: #f0f0f0; /* Light background color on hover */
  transform: scale(1.05); /* Slightly enlarge the div on hover */
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

/* Style the paragraph (item name) inside the div */
.item-div p {
  margin: 0;
  font-size: 13px;
  color: #333; /* Text color */
}

.service-name{
  font-size: 9px;
  color: #333; /* Text color */
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
