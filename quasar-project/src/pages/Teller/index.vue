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
          style="min-width: 32px; width: 32px; height: 32px"
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
        <q-avatar
          size="40px"
          class="cursor-pointer"
          @click="menuOpen = !menuOpen"
        >
          <img src="https://cdn.quasar.dev/img/avatar.png" alt="User Avatar" />
        </q-avatar>

        <q-menu
          v-model="menuOpen"
          transition-show="scale"
          transition-hide="scale"
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
                              <div class="q-gutter-y-xs q-my-sm">
                                <q-btn
                                  label="Cater"
                                  color="green-9"
                                  @click="caterCustomer(customer.id)"
                                />
                                <q-btn
                                  label="Cancel"
                                  color="red-9"
                                  @click="beforeCancel(customer)"
                                />
                                <q-btn
                                  v-if="index === 0"
                                  color="orange"
                                  class="q-ml-sm"
                                  @click="
                                    startWait(
                                      customer.id,
                                      customer.queue_number
                                    )
                                  "
                                  :disable="waiting"
                                  unelevated
                                >
                                  <!-- Progress Background -->
                                  <q-linear-progress
                                    v-if="waiting"
                                    :value="1 - waitTime / 63"
                                    color="orange-10"
                                    class="full-progress"
                                    height="100%"
                                  />

                                  <!-- Button Text -->
                                  <div
                                    class="row items-center no-wrap absolute-full flex flex-center"
                                  >
                                    <span v-if="!waiting">Wait</span>
                                    <span v-if="waiting" class="q-ml-xs"
                                      >({{ waitTime }}s)</span
                                    >
                                  </div>
                                </q-btn>
                              </div>
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
                        ><strong>Now Serving</strong></q-item-label
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
                                  label="Finished"
                                  color="primary"
                                  @click="finishCustomer(currentServing.id)"
                                />
                                <q-btn
                                  label="Wait"
                                  color="warning"
                                  @click="
                                    startWait(
                                      customer.id,
                                      customer.queue_number
                                    )
                                  "
                                />
                              </div>
                            </q-item-section>
                          </q-item>
                        </div>

                        <div v-else>
                          <q-item class="bg-grey-9 rounded-borders">
                            <q-item-section>
                              <h1
                                class="q-mb-sm q-mt-sm text-center text-white"
                              >
                                No queue
                              </h1>
                              <p class="text-center text-h6 text-white">
                                ........
                              </p>
                            </q-item-section>
                          </q-item>

                          <q-item>
                            <q-item-section>
                              <div class="q-gutter-y-xs q-my-sm">
                                <q-btn
                                  label="Finished"
                                  color="primary"
                                  disable
                                />
                                <q-btn label="Wait" color="warning" disable />
                              </div>
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
import {
  defineComponent,
  ref,
  watch,
  onMounted,
  onUnmounted,
  computed,
} from "vue";
import { $axios, $notify, Dialog } from "boot/app";
import { useQuasar } from "quasar";

export default defineComponent({
  name: "HeaderComponent",
  setup() {
    // Queue functions
    const queueList = ref([]);
    const currentServing = ref(null);
    const waiting = ref(false);
    const waitTime = ref(63);
    const isQueuelistEmpty = ref(false);
    let waitTimer = null;
    let refreshInterval = null;
    const $dialog = useQuasar();

    // UI Functions
    const $q = useQuasar();
    const menuOpen = ref(false);
    const toggleFullscreen = () => {
      $q.fullscreen.toggle();
    };
    // Close dropdown when fullscreen is toggled (both enter and exit)
    watch(
      () => $q.fullscreen.isActive,
      () => {
        menuOpen.value = false;
      }
    );

    // Pagination
    const currentPage = ref(1);
    const itemsPerPage = 5;

    // Fetch queue data
    const fetchQueue = async () => {
      try {
        const response = await $axios.post("/admin/queue-list");
        queueList.value = response.data.queue.filter(
          (q) => !["finished", "cancelled"].includes(q.status)
        );
        currentServing.value = response.data.current_serving;
        console.log(currentServing.value);
        isQueuelistEmpty.value = queueList.value.length == 0;
      } catch (error) {
        console.error(error);
      }
    };

    // Cater customer
    const caterCustomer = async (customerId) => {
      try {
        await $axios.post("/admin/cater", { id: customerId });
        fetchQueue();
        $notify("positive", "check", "Customer is now being served.");
        stopWait(); // Stop wait if customer is catered
      } catch (error) {
        console.error(error);
        $notify(
          "negative",
          "error",
          "You are currently serving a customer. Please finish it first!."
        );
      }
    };

    //cancel dialog
    const beforeCancel = (row) => {
      $dialog
        .dialog({
          title: "Confirm",
          message: "Are you sure do you want cancel this queue?",
          cancel: true,
          persistent: true,
        })
        .onOk(() => {
          cancelCustomer(row.id);
        })
        .onDismiss(() => {
          // console.log('I am triggered on both OK and Cancel')
        });
    };

    // Cancel customer
    const cancelCustomer = async (customerId) => {
      try {
        await $axios.post("/admin/cancel", { id: customerId });
        fetchQueue();
        $notify(
          "positive",
          "check",
          "Customer has been removed from the queue."
        );
        stopWait(); // Stop wait if customer is canceled
      } catch (error) {
        console.error(error);
        $notify("negative", "error", "Failed to cancel customer.");
      }
    };

    // Finish serving customer
    const finishCustomer = async (customerId) => {
      try {
        await $axios.post("/admin/finish", { id: customerId });
        fetchQueue();

        $notify("positive", "check", "Customer has been marked as finished.");
      } catch (error) {
        console.error(error);
        $notify("negative", "error", "Failed to finish serving.");
      }
    };

    // Start waiting process
    const startWait = async (customerId, queueNumber) => {
      try {
        if (currentServing.value !== null) {
          $notify(
            "negative",
            "error",
            "Please finish the current customer first."
          );
          return;
        }

        if (waiting.value) return; // Prevent multiple clicks

        waiting.value = true; // Disable button
        waitTime.value = 63;

        const response = await $axios.post("/admin/start-wait", {
          queue_number: queueNumber,
        });

        $notify("positive", "check", response.data.message);

        waitTimer = setInterval(() => {
          if (waitTime.value > 0) {
            waitTime.value--;
          } else {
            clearInterval(waitTimer);
            cancelCustomer(customerId); // Auto-cancel after 63 seconds
            stopWait();
            waiting.value = false; // Re-enable button after timer
          }
        }, 1000);
      } catch (error) {
        console.error(error);
        $notify("negative", "error", "Failed to set waiting customer.");
        waiting.value = false; // Re-enable button on failure
      }
    };

    // Reset Queue Number
    const resetQueue = async () => {
      try {
        $dialog
          .dialog({
            title: "Confirm",
            message: "Are you sure do you want reset queue?",
            cancel: true,
            persistent: true,
          })
          .onOk(async () => {
            const response = await $axios.post("/resetQueue");
            $notify("positive", "check", response.data.message);
            console.log(response.data.message);
          })
          .onDismiss(() => {
            // console.log('I am triggered on both OK and Cancel')
          });
      } catch (error) {
        console.error(error);
        $notify("negative", "error", "Failed to set waiting customer.");
      }
    };

    // Stop waiting process
    const stopWait = () => {
      waiting.value = false;
      clearInterval(waitTimer);
    };

    // Computed property for paginated queue list
    const paginatedQueueList = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage;
      return queueList.value.slice(start, start + itemsPerPage);
    });

    // Total pages for pagination
    const totalPages = computed(() =>
      Math.ceil(queueList.value.length / itemsPerPage)
    );

    onMounted(() => {
      fetchQueue();
      refreshInterval = setInterval(fetchQueue, 5000); // Auto-refresh every 5 seconds
    });

    onUnmounted(() => {
      clearInterval(refreshInterval); // Stop interval when component is destroyed
      clearInterval(waitTimer); // Stop wait timer if it exists
    });

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
      totalPages,

      // UI Function Menu & Fullscreen
      menuOpen,
      toggleFullscreen,
    };
  },
});
</script>

<style>
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
