<template>
  <q-page class="q-px-md q-pb-md">
    <div class="q-my-md bg-white q-pa-sm shadow-1">
      <q-breadcrumbs class="q-mx-sm">
        <q-breadcrumbs-el icon="home" to="/admin/dashboard" />
        <q-breadcrumbs-el
          label="Admin Queue"
          icon="admin_panel_settings"
          to="/admin/admin_Queue"
        />
      </q-breadcrumbs>
    </div>
    <!-- queue -->
    <q-card ref="cardRef" class="q-pa-md">
      <div class="q-pa-md q-mb-md">
        <q-btn
          v-if="showFullscreenBtn"
          flat
          round
          dense
          class="q-mr-sm"
          color="primary"
          style="min-width: 32px; width: 32px; height: 32px; position: absolute"
          @click="toggleFullscreen"
          :icon="isFullscreen ? 'fullscreen_exit' : 'fullscreen'"
        />

        <!-- Window Type Select -->
        <div v-if="!adminManagerInformation" class="col-6">
          <q-select
            outlined
            style="width: 250px; margin-left: 40px"
            v-model="branch_value"
            :options="branch_list"
            label="Branch name"
            hide-bottom-space
            dense
            emit-value
            option-label="branch_name"
            option-value="id"
            map-options
          />
        </div>
      </div>

      <!-- Display message if no tellers are assigned -->
      <div v-if="flattenedTellers.length === 0">
        No Window Teller assigned...
      </div>
      <!-- Carousel with exactly 8 tellers per slide in a 2x2 grid repeated twice -->
      <div v-else class="row">
        <q-carousel
          v-model="slide"
          transition-prev="slide-right"
          transition-next="slide-left"
          swipeable
          animated
          control-color="amber"
          :autoplay="autoplay"
          height="auto"
          class="rounded-borders full-width carousel-container"
        >
          <!-- For each slide -->
          <q-carousel-slide
            v-for="(tellerChunk, chunkIndex) in chunkedTellers"
            :key="'chunk-' + chunkIndex"
            :name="chunkIndex"
            class="carousel-slide custom-carousel"
            style="overflow-x: auto; padding-bottom: 50px"
          >
            <div class="grid-container">
              <!-- For each row (4 tellers per row) inside the chunk -->
              <div
                v-for="(row, rowIndex) in chunkByFour(tellerChunk)"
                :key="'row-' + rowIndex"
                class="row q-gutter-md q-mb-md"
              >
                <div
                  v-for="(teller, tellerIndex) in row"
                  :key="teller.id"
                  class="col-3 teller-card-wrapper"
                >
                  <!-- Teller Card -->
                  <q-card class="teller-card">
                    <div class="row sections-row">
                      <!-- Section 1: Service Type and Assigned Teller -->
                      <div class="col-5 service-section">
                        <q-card-section class="q-pa-sm service-type-section">
                          <q-item
                            class="column text-subtitle2 text-center q-pa-none q-pb-xs"
                          >
                            <div class="q-mb-none text-grey-8 q-mb-xs">
                              SERVICE TYPE
                            </div>
                            {{ teller.service.type_name }}
                          </q-item>
                        </q-card-section>
                        <q-card-section class="q-pa-sm">
                          <q-item
                            class="column text-subtitle2 text-center q-pa-none q-pt-xs"
                          >
                            <div class="q-mb-none text-grey-8 q-mb-xs">
                              ASSIGNED TELLER
                            </div>
                            <span
                              class="text-weight-medium text-ellipsis"
                              style="font-size: clamp(8px, 1vw, 10px)"
                            >
                              {{ teller.teller_firstname }}
                              {{ teller.teller_lastname }}
                            </span>
                            <div class="text-caption text-grey q-mt-xs">
                              <template
                                v-if="
                                  teller.windows && teller.windows.length > 0
                                "
                              >
                                <div class="window-list q-mt-xs">
                                  <div
                                    v-for="(window, idx) in teller.windows"
                                    :key="idx"
                                    class="window-item q-mb-xs text-center"
                                  >
                                    <q-chip
                                      dense
                                      size="xs"
                                      class="bg-primary text-white"
                                      style="font-size: 10px"
                                    >
                                      {{ window }}
                                    </q-chip>
                                  </div>
                                </div>
                              </template>
                              <div v-else class="text-italic">
                                No window assigned
                              </div>
                            </div>
                          </q-item>
                        </q-card-section>
                      </div>

                      <q-separator :vertical="true" />

                      <!-- Section 2: Current and Waiting Queue -->
                      <div class="col-5 queue-section">
                        <q-card-section
                          class="q-pa-sm bg-primary current-queue-section"
                          style="border-radius: 15px"
                        >
                          <div
                            v-if="
                              teller.currently_served &&
                              teller.currently_served.name
                            "
                          >
                            <h6 class="text-center text-white q-my-sm">
                              CURRENT QUEUE
                            </h6>
                            <p
                              class="text-center text-white text-ellipsis"
                              style="
                                font-size: clamp(20px, 1.2vw, 12px);
                                display: flex;
                                justify-content: center;
                              "
                            >
                              {{
                                `${teller.service.type_indicator}#-${String(
                                  teller.currently_served.queue_number
                                ).padStart(3, "0")}`
                              }}
                            </p>
                            <p
                              class="text-center text-white text-ellipsis"
                              style="font-size: clamp(8px, 1vw, 10px)"
                            >
                              {{ teller.currently_served.name }}
                            </p>
                          </div>
                          <div v-else>
                            <h6 class="text-center text-white q-my-sm">
                              CURRENT QUEUE
                            </h6>
                            <p
                              class="text-center text-white"
                              style="font-size: clamp(8px, 1vw, 10px)"
                            >
                              No customer is being served currently
                            </p>
                            <p class="text-center text-white">...</p>
                          </div>
                        </q-card-section>
                        <q-card-section class="q-pa-sm waiting-queue-section">
                          <div v-if="queueLists(teller).length">
                            <p
                              class="text-center text-h6 q-my-xs"
                              style="font-size: clamp(10px, 1.2vw, 12px)"
                            >
                              Waiting Queue
                            </p>
                            <q-item class="q-pa-none">
                              <q-item-section class="q-pa-none">
                                <q-scroll-area
                                  class="my-scroll"
                                  style="
                                    height: 80px;
                                    overflow-y: auto;
                                    overflow-x: hidden;
                                  "
                                >
                                  <q-list
                                    class="q-pa-xs queue-list"
                                    bordered
                                    separator
                                  >
                                    <div style="min-height: 80px">
                                      <q-item
                                        v-for="(customer, index) in queueLists(
                                          teller
                                        )"
                                        :key="customer.queue_number"
                                        v-ripple
                                        class="shadow-2 border q-px-xs q-mb-xs q-flex q-items-center queue-item"
                                      >
                                        <q-item-section
                                          class="queue-item-section"
                                        >
                                          <div v-if="customer.priority_service">
                                            <div
                                              class="text-black text-bold q-mb-xs"
                                              style="
                                                font-size: clamp(
                                                  8px,
                                                  1vw,
                                                  10px
                                                );
                                              "
                                            >
                                              <q-badge style="font-size: 6px">
                                                VIP
                                              </q-badge>
                                              <span class="text-ellipsis">
                                                {{
                                                  `${
                                                    teller.service
                                                      .type_indicator
                                                  }#-${String(
                                                    customer.queue_number
                                                  ).padStart(3, "0")}`
                                                }}
                                              </span>
                                              <q-tooltip
                                                anchor="top middle"
                                                self="bottom middle"
                                              >
                                                {{ customer.priority_service }}
                                              </q-tooltip>
                                            </div>
                                          </div>
                                          <div v-else>
                                            <div
                                              class="text-primary text-bold q-mb-xs text-ellipsis"
                                              style="
                                                font-size: clamp(
                                                  18px,
                                                  1vw,
                                                  10px
                                                );
                                              "
                                            >
                                              {{
                                                `${
                                                  teller.service.type_indicator
                                                }#-${String(
                                                  customer.queue_number
                                                ).padStart(3, "0")}`
                                              }}
                                            </div>
                                          </div>
                                          <p
                                            class="text-body2 text-secondary q-mb-none text-ellipsis"
                                            style="
                                              font-size: clamp(
                                                10px,
                                                0.9vw,
                                                9px
                                              );
                                            "
                                          >
                                            {{ customer.name }}
                                          </p>
                                        </q-item-section>
                                        <q-item-section
                                          class="q-px-xs q-justify-center queue-item-section"
                                        >
                                          <span
                                            :class="['fi', customer.flag]"
                                            style="
                                              font-size: 0.8em;
                                              margin-right: 1px;
                                            "
                                          ></span>
                                          <span
                                            style="font-size: 6px"
                                            class="text-ellipsis"
                                          >
                                            {{ customer.currency_name }}
                                          </span>
                                        </q-item-section>
                                        <q-item-section
                                          side
                                          class="queue-item-section"
                                        >
                                          <q-badge
                                            :color="
                                              index <= 0
                                                ? 'primary'
                                                : 'blue-grey'
                                            "
                                            class="text-white text-bold"
                                            style="font-size: 6px"
                                          >
                                            <q-tooltip
                                              anchor="center right"
                                              self="center left"
                                            >
                                              {{
                                                index <= 0
                                                  ? "Up Next"
                                                  : "Waiting"
                                              }}
                                            </q-tooltip>
                                          </q-badge>
                                        </q-item-section>
                                      </q-item>
                                    </div>
                                  </q-list>
                                </q-scroll-area>
                              </q-item-section>
                            </q-item>
                          </div>
                          <div v-else>
                            <p
                              class="text-center text-h6 q-my-xs"
                              style="font-size: clamp(10px, 1.2vw, 12px)"
                            >
                              Waiting Queue
                            </p>
                            <q-item class="q-pa-none">
                              <q-item-section class="q-pa-none">
                                <q-scroll-area
                                  class="my-scroll"
                                  style="
                                    height: 80px;
                                    overflow-y: auto;
                                    overflow-x: hidden;
                                  "
                                >
                                  <q-list
                                    class="q-pa-xs queue-list"
                                    bordered
                                    separator
                                  >
                                    <div style="min-height: 80px">
                                      <q-item
                                        v-for="n in 5"
                                        :key="n"
                                        class="shadow-1 q-px-xs q-mb-xs queue-item"
                                        style="min-height: 30px"
                                      >
                                        <q-item-section
                                          class="queue-item-section"
                                        >
                                          <div class="q-mb-xs">
                                            <div class="skeleton-line short" />
                                          </div>
                                          <div>
                                            <div class="skeleton-line" />
                                          </div>
                                        </q-item-section>
                                        <q-item-section
                                          class="queue-item-section"
                                        >
                                          <div class="skeleton-badge" />
                                        </q-item-section>
                                        <q-item-section
                                          side
                                          class="queue-item-section"
                                        >
                                          <div class="skeleton-badge" />
                                        </q-item-section>
                                      </q-item>
                                    </div>
                                  </q-list>
                                </q-scroll-area>
                              </q-item-section>
                            </q-item>
                          </div>
                        </q-card-section>
                      </div>
                    </div>
                  </q-card>
                </div>
              </div>
            </div>
          </q-carousel-slide>
        </q-carousel>
      </div>
    </q-card>
  </q-page>
</template>
<script>
import {
  ref,
  computed,
  onMounted,
  onUnmounted,
  watch,
  nextTick,
  onBeforeUnmount,
} from "vue";
import { $axios, $notify, Dialog } from "boot/app";
import { useQuasar } from "quasar";

export default {
  setup() {
    const cusId = ref();
    const queueList = ref([]);
    const currentServing = ref(null);
    const waiting = ref(false);
    const waitTime = ref(30);
    const prepared = ref();
    let waitTimer = null;
    const tempTimer = ref();
    const originalWaitTime = ref(0);
    const isQueuelistEmpty = ref(false);
    let refreshInterval = null;
    const $dialog = useQuasar();
    const noOfQueue = ref();
    const type_id = ref();
    const branch_value = ref();
    const teller_id = ref();
    const branch_list = ref([]);
    const personnelList = ref([]);
    const cusName = ref();
    const cusQueueNum = ref();
    const servingStatus = ref();
    const tellerFullName = ref();
    const adminManagerInformation = ref();
    const cardRef = ref(null);
    let autoplayTimer = null;
    const slide = ref(0);
    const isFullscreen = ref(false);
    const showFullscreenBtn = ref(true);

    // Pagination
    const currentPage = ref(1);
    const itemsPerPage = 5;

    // UI Functions
    const $q = useQuasar();
    const menuOpen = ref(false);

    // Toggle fullscreen
    const toggleFullscreen = () => {
      const element = cardRef.value?.$el;
      if (!element) {
        console.error("Card element not found");
        return;
      }

      isFullscreen.value = !isFullscreen.value;
      showFullscreenBtn.value = false; // Hide button when toggling

      if (isFullscreen.value) {
        $q.fullscreen
          .request(element)
          .then(() => {
            console.log("Entered fullscreen");
          })
          .catch((err) => {
            console.error("Failed to enter fullscreen:", err);
            isFullscreen.value = false; // Revert state on failure
            showFullscreenBtn.value = true; // Show button on failure
          });
      } else {
        $q.fullscreen
          .exit()
          .then(() => {
            console.log("Exited fullscreen");
            showFullscreenBtn.value = true; // Show button after exiting
          })
          .catch((err) => {
            console.error("Failed to exit fullscreen:", err);
            isFullscreen.value = true; // Revert state on failure
          });
      }
    };

    // Handle escape key press
    const handleEscape = async (e) => {
      if (e.key === "Escape" && isFullscreen.value) {
        console.log("Escape key pressed, exiting fullscreen");
        try {
          await $q.fullscreen.exit();
          isFullscreen.value = false;
          showFullscreenBtn.value = true; // Show button after exiting
        } catch (err) {
          console.error("Failed to exit fullscreen via Escape:", err);
        }
      }
    };

    // Listen for fullscreen changes
    const handleFullscreenChange = () => {
      const isCurrentlyFullscreen = !!document.fullscreenElement;
      console.log(
        "Fullscreen change detected, isFullscreen:",
        isCurrentlyFullscreen
      );
      isFullscreen.value = isCurrentlyFullscreen;
      showFullscreenBtn.value = !isCurrentlyFullscreen; // Show button when not in fullscreen
    };

    // Fetch queue data
    const fetchQueue = async () => {
      try {
        const response = await $axios.post("/teller/queue-list", {
          type_id: type_id.value,
          teller_id: teller_id.value,
        });
        queueList.value = response.data.queue.filter(
          (q) => !["finished", "cancelled"].includes(q.status)
        );
        currentServing.value = response.data.current_serving;
        cusName.value = response.data.name;
        cusQueueNum.value = response.data.queue_number;
        servingStatus.value = response.data.status;
        tellerFullName.value = response.data.fullname;
        noOfQueue.value = queueList.value.length;
        if (
          queueList.value.length > 0 &&
          queueList.value[0].status === "waiting" &&
          currentServing.value == null
        ) {
          // caterCustomer(queueList.value[0].id);
          // startWait(queueList.value[0].id, queueList.value[0].queue_number)
        }
        isQueuelistEmpty.value = queueList.value.length == 0;
      } catch (error) {
        console.error(error);
      }
    };

    const fetchBranch = async () => {
      try {
        const response = await $axios.post("/type/Branch");
        branch_list.value = response.data.branch;
        console.log(branch_list.value);
      } catch (error) {
        console.error("Error fetching sections:", error);
      }
    };

    const fetchPersonnel = async () => {
      try {
        if (!type_id.value) return;
        const response = await $axios.post("/tellers/dropdown", {
          type_id: type_id.value,
        });
        if (Array.isArray(response.data.rows)) {
          personnelList.value = response.data.rows;
        } else {
          console.error(
            'Expected "rows" to be an array, but got:',
            response.data.rows
          );
        }
      } catch (error) {
        console.error("Error fetching sections:", error);
      }
    };

    const fetchId = async () => {
      try {
        const response = await $axios.post("/admin/queue-list");
        cusId.value = response.data.current_serving.id;
      } catch (error) {
        console.error(error);
      }
    };

    watch(
      () => type_id.value,
      async (newVal) => {
        if (newVal) {
          personnelList.value = [];
          await fetchPersonnel();
          nextTick(() => {
            if (typeof type_id.value === "string") {
              teller_id.value = teller_id.value;
              fetchtypeId();
            } else {
              if (personnelList.value.length > 0) {
                teller_id.value = personnelList.value[0].value;
              } else {
                teller_id.value = null;
                return;
              }
              fetchtypeId();
            }
          });
        }
      }
    );

    // Cater customer
    const caterCustomer = async (customerId) => {
      try {
        await $axios.post("/teller/cater", {
          id: customerId,
          service_id: type_id.value,
        });
        fetchQueue();
        fetchId();
      } catch (error) {
        console.error(error);
        $notify(
          "negative",
          "error",
          "You are currently serving a customer. Please finish it first!."
        );
      }
    };

    // Cancel dialog
    const beforeCancel = (row) => {
      $dialog
        .dialog({
          title: "Confirm",
          message: "Are you sure do you want cancel this queue?",
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
        .onOk(() => {
          cancelCustomer(row.id);
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
        stopWait();
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
        await $axios.post("/waitCustomer", { id: customerId });
        if (waiting.value) return;
        waiting.value = true;
        if (originalWaitTime.value === 0) {
          originalWaitTime.value =
            prepared.value === "Minutes" ? waitTime.value * 60 : waitTime.value;
        }
        const startTime = Math.floor(Date.now() / 1000);
        localStorage.setItem("wait_start_time", startTime);
        localStorage.setItem("wait_duration", originalWaitTime.value);
        tempTimer.value = originalWaitTime.value;
        $notify(
          "positive",
          "check",
          "Waiting for Queue Number: " + queueNumber
        );
        if (waitTimer) clearInterval(waitTimer);
        startTimer(customerId);
      } catch (error) {
        console.error(error);
        $notify("negative", "error", "Failed to set waiting customer.");
      }
    };

    const resetWait = async (id) => {
      const response = await $axios.post("/waitCustomerReset", { id: id });
      console.log(response.message);
    };

    // Start the countdown timer
    const startTimer = (id) => {
      if (waitTimer) clearInterval(waitTimer);
      waitTimer = setInterval(() => {
        const now = Math.floor(Date.now() / 1000);
        const startTime =
          parseInt(localStorage.getItem("wait_start_time")) || 0;
        const duration = parseInt(localStorage.getItem("wait_duration")) || 0;
        const elapsed = now - startTime;
        const remaining = duration - elapsed;
        if (remaining >= 0) {
          tempTimer.value = remaining;
          if (tempTimer.value === 0) {
            resetWait(cusId.value);
          }
        } else {
          stopWait();
          originalWaitTime.value = 0;
          localStorage.removeItem("wait_start_time");
          localStorage.removeItem("wait_duration");
        }
      }, 1000);
    };

    // Fetch waiting time
    const fetchWaitingtime = async () => {
      try {
        const { data } = await $axios.post("/admin/waiting_Time-fetch");
        if (data && data.dataValue && data.dataValue.length > 0) {
          waitTime.value = data.dataValue[0].Waiting_time;
          prepared.value = data.dataValue[0].Prepared;
        } else {
          console.log("No data available");
        }
      } catch (error) {
        console.log("Error fetching data:", error);
      }
    };

    // Format time as MM:SS
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
        $dialog
          .dialog({
            title: "Confirm",
            message: "Are you sure do you want reset queue?",
            cancel: true,
            persistent: true,
            color: "primary",
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
            const response = await $axios.post("/resetQueue");
            $notify("positive", "check", response.data.message);
            console.log(response.data.message);
          });
      } catch (error) {
        console.error(error);
        $notify("negative", "error", "Failed to set waiting customer.");
      }
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

    let waitingQueue;

    const optimizedFetchQueue = async () => {
      await fetchQueue();
      waitingQueue = setTimeout(optimizedFetchQueue, 3000);
    };

    const assignedTellers = ref([]);
    const lastFetched = ref(null);
    const queueListFromStorage = ref([]);

    const fetchAssignedTellers = async () => {
      try {
        const response = await $axios.post("/tellers/fetch-assigned", {
          branch_id: branch_value.value,
        });
        assignedTellers.value = response.data.services
          .map((service) => ({
            ...service,
            tellers: service.tellers.filter((teller) => teller.window_name),
          }))
          .filter((service) => service.tellers.length > 0);
      } catch (error) {
        console.log("Error fetching: ", error);
      }
    };

    const queueLists = (teller) => {
      const fromStorage = queueListFromStorage.value.filter(
        (q) => q.teller_id === teller.id
      );
      return fromStorage.length ? fromStorage : teller.waiting_customers || [];
    };

    const flattenedTellers = computed(() => {
      return assignedTellers.value.flatMap((service) =>
        service.tellers.map((teller) => ({
          ...teller,
          windows: teller.window_name ? teller.window_name : [],
          service: {
            type_id: service.type_id,
            type_name: service.type_name,
            type_indicator: service.type_indicator,
          },
        }))
      );
    });

    const sortedTellers = computed(() => {
      return flattenedTellers.value.sort((a, b) => {
        if (a.currently_served?.name) return -1;
        if (b.currently_served?.name) return 1;
        return 0;
      });
    });

    const chunkedTellers = computed(() => {
      const chunkSize = 8; // 8 cards per slide
      const result = [];
      const tellers = sortedTellers.value;

      for (let i = 0; i < tellers.length; i += chunkSize) {
        result.push(tellers.slice(i, i + chunkSize));
      }

      return result;
    });

    const loadQueueList = () => {
      const raw = localStorage.getItem("queueList");
      if (raw) {
        const parsed = JSON.parse(raw);
        if (
          JSON.stringify(parsed) !== JSON.stringify(queueListFromStorage.value)
        ) {
          queueListFromStorage.value = parsed;
        }
      }
    };

    const chunkByFour = (list) => {
      const result = [];
      for (let i = 0; i < list.length; i += 4) {
        result.push(list.slice(i, i + 4));
      }
      return result;
    };

    let intervalQueue, intervalFetch;

    // Autoplay carousel every 5s
    const startAutoplay = () => {
      stopAutoplay(); // clear existing interval
      autoplayTimer = setInterval(() => {
        if (chunkedTellers.value.length > 1) {
          slide.value = (slide.value + 1) % chunkedTellers.value.length;
        }
      }, 5000); // 5 seconds
    };

    const stopAutoplay = () => {
      if (autoplayTimer) {
        clearInterval(autoplayTimer);
        autoplayTimer = null;
      }
    };

    onMounted(() => {
      // Add event listeners
      window.addEventListener("keydown", handleEscape);
      document.addEventListener("fullscreenchange", handleFullscreenChange);

      // Existing onMounted logic
      loadQueueList();
      intervalQueue = setInterval(loadQueueList, 1000);
      fetchAssignedTellers();
      intervalFetch = setInterval(() => {
        lastFetched.value = new Date().toISOString();
      }, 2000);
      startAutoplay();

      // Additional onMounted logic
      const managerInformation = localStorage.getItem("managerInformation");
      if (managerInformation) {
        adminManagerInformation.value = JSON.parse(managerInformation);
        branch_value.value = adminManagerInformation.value.branch_id;
      }
      optimizedFetchQueue();
      fetchWaitingtime();
      const startTime = parseInt(localStorage.getItem("wait_start_time")) || 0;
      const duration = parseInt(localStorage.getItem("wait_duration")) || 0;
      if (startTime && duration) {
        waiting.value = true;
        startTimer();
      }
      fetchId();
      fetchBranch();
      fetchPersonnel();
    });

    onBeforeUnmount(() => {
      // Remove event listeners
      window.removeEventListener("keydown", handleEscape);
      document.removeEventListener("fullscreenchange", handleFullscreenChange);
      clearInterval(intervalQueue);
      clearInterval(intervalFetch);
    });

    onUnmounted(() => {
      stopAutoplay();
      clearTimeout(waitingQueue);
    });

    watch(lastFetched, fetchAssignedTellers);

    watch(
      () => branch_value.value,
      async (newVal) => {
        if (newVal) {
          fetchAssignedTellers();
        }
      }
    );

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
      branch_list,
      resetQueue,
      isQueuelistEmpty,
      prepared,
      chunkByFour,
      formatTime,
      cusId,
      noOfQueue,
      type_id,
      cusName,
      branch_value,
      cusQueueNum,
      servingStatus,
      tellerFullName,
      personnelList,
      teller_id,
      adminManagerInformation,
      currentPage,
      itemsPerPage,
      paginatedQueueList,
      totalPages,
      menuOpen,
      toggleFullscreen,
      assignedTellers,
      fetchAssignedTellers,
      queueLists,
      flattenedTellers,
      sortedTellers,
      slide,
      chunkedTellers,
      cardRef,
      isFullscreen,
      showFullscreenBtn,
    };
  },
};
</script>
<style scoped>
.full-width {
  width: 100%;
}

/* Carousel container */
.carousel-container {
  overflow-x: hidden; /* Prevent horizontal scrolling */
}

/* Carousel slide */
.carousel-slide {
  overflow-x: hidden; /* Ensure no horizontal overflow */
  padding: 8px, 4px, 0px, 4px; /* Padding for the grid */
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* Grid container for 2x2 layout repeated twice (4 rows, 2 cards each) */
.grid-container {
  display: flex;
  flex-direction: column;
  width: 100%;
  max-width: 1200px; /* Limit total width to fit viewport */
  height: 100%;
}

/* Grid rows */
.grid-container .row {
  display: flex;
  flex-wrap: nowrap; /* Prevent cards from wrapping */
  flex: 1; /* Each row takes equal height */
  width: 100%; /* Ensure row takes full width */
  min-height: 50px; /* Reduced height to fit 4 rows */
}

/* Grid columns */
.grid-container .teller-card-wrapper {
  flex: 0 0 50%; /* Each card takes exactly 50% of the row width */
  min-width: 300px; /* Minimum width for content */
  max-width: 450px; /* Maximum width to prevent overflow */
  padding: 8px; /* Spacing between cards */
  box-sizing: border-box;
  min-height: 300px;
}

/* Teller card */
.teller-card {
  width: 100%;
  height: 100%; /* Fill the wrapper */
  display: flex;
  flex-direction: column;
  overflow: hidden; /* Prevent content from spilling out */
}

.custom-carousel {
  /* Hide scrollbar by default for Webkit browsers (Chrome, Safari) */
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* Internet Explorer, Edge */
}

.custom-carousel::-webkit-scrollbar {
  display: none; /* Webkit browsers */
}

.custom-carousel:hover {
  /* Show scrollbar on hover for Firefox */
  scrollbar-width: auto;
  -ms-overflow-style: auto; /* Internet Explorer, Edge */
}

.custom-carousel:hover::-webkit-scrollbar {
  display: block; /* Webkit browsers */
  width: 8px; /* Optional: Customize scrollbar width */
  height: 8px; /* Optional: Customize scrollbar height for horizontal */
}

.custom-carousel:hover::-webkit-scrollbar-thumb {
  background: #888; /* Optional: Customize scrollbar thumb color */
  border-radius: 4px;
}

.custom-carousel:hover::-webkit-scrollbar-track {
  background: #f1f1f1; /* Optional: Customize scrollbar track color */
}

/* Row for sections inside q-card */
.sections-row {
  display: flex;
  flex-wrap: nowrap; /* Ensure sections stay in a single row */
  height: 100%; /* Fill the card height */
}

/* Section columns */
.sections-row .col-5 {
  flex: 0 0 50%; /* Each section takes 50% of the row width */
  padding: 4px; /* Reduced padding */
  overflow: hidden; /* Prevent overflow */
  display: flex;
  flex-direction: column;
}

/* Individual sections */
.service-section {
  background: #f5f5f5;
  border: 1px solid #ddd;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between; /* Distribute space evenly */
  height: 100%; /* Ensure it takes full height */
}

.service-section .q-card-section {
  flex: 1; /* Each section takes equal space */
  padding: 8px; /* Reduced padding */
  overflow: hidden; /* Prevent overflow */
}

.service-type-section {
  max-height: 60px; /* Fixed height for Service Type */
}

.assigned-teller-section {
  max-height: 140px; /* Fixed height for Assigned Teller */
}

.queue-section {
  background: #fff;
  border: 1px solid #ddd;
  display: flex;
  flex-direction: column;
  flex-grow: 1; /* Allow queue section to expand */
  height: 100%; /* Ensure it takes full height */
}

.current-queue-section {
  flex: 1; /* Takes half the queue-section height */
  max-height: 100px; /* Fixed height for Current Queue */
  overflow: hidden; /* Prevent overflow */
}

.waiting-queue-section {
  flex: 1; /* Takes half the queue-section height */
  max-height: 88px; /* Adjusted to fit content */
  display: flex;
  flex-direction: column;
}

/* Scroll area */
.my-scroll {
  overflow-x: hidden; /* Hide horizontal overflow */
  width: 100%; /* Fill section width */
  flex-grow: 1; /* Expand to fill remaining space in waiting-queue-section */
}

/* Queue list */
.queue-list {
  width: 100%; /* Ensure list fills scroll area */
  overflow: hidden; /* Prevent list overflow */
}

/* Queue item */
.queue-item {
  min-height: 40px; /* Reduced height for denser list */
  padding: 2px; /* Reduced padding */
  width: 100%; /* Ensure items fit within list */
  box-sizing: border-box;
}

/* Queue item section */
.queue-item-section {
  overflow: hidden; /* Prevent content overflow */
  text-overflow: ellipsis; /* Truncate long text */
  min-width: 0; /* Allow shrinking */
  min-height: 70px;
}

/* Text ellipsis for long content */
.text-ellipsis {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100%;
  display: inline-block;
}

/* Scrollbar styling */
.my-scroll::-webkit-scrollbar {
  width: 4px; /* Thinner scrollbar */
}
.my-scroll::-webkit-scrollbar-track {
  background: #f1f1f1;
}
.my-scroll::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 2px;
}
.my-scroll::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Skeleton loaders */
.skeleton-line {
  background: #e0e0e0;
  height: 6px;
  border-radius: 3px;
  margin-bottom: 4px;
}
.skeleton-line.short {
  width: 60%;
}
.skeleton-badge {
  background: #e0e0e0;
  width: 20px;
  height: 14px;
  border-radius: 3px;
}

/* Window list */
.window-list {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
}
.window-item {
  margin: 0 2px 2px 0;
}

.row-scroll {
  width: 100%;
  overflow-y: hidden; /* Hide vertical overflow */
}

/* Scrollbar styling for horizontal scroll (rows) */
.row-scroll::-webkit-scrollbar {
  height: 4px; /* Thinner scrollbar */
}
.row-scroll::-webkit-scrollbar-track {
  background: #f1f1f1;
}
.row-scroll::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 2px;
}
.row-scroll::-webkit-scrollbar-thumb:hover {
  background: #555;
}

.row-content {
  display: flex;
  flex-direction: row;
  flex-wrap: nowrap; /* Prevent cards from wrapping */
  min-width: fit-content; /* Ensure content doesn't shrink */
}
</style>
