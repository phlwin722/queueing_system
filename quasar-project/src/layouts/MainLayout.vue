<template>
      <!-- Show message for mobile screens -->
      <q-layout v-if="$q.screen.lt.sm">
       <q-header v-if="shouldShowSideNav">
        <q-toolbar>
          <q-img
            src="~assets/vrtlogowhite1.png"
            alt="Logo"
            fit="full"
            :style="{
              maxWidth: $q.screen.lt.sm ? '100px' : '160px',
              cursor: 'pointer', // Fix cursor property
              transition: 'transform 0.3s ease, box-shadow 0.3s ease',
            }"
            @click="goonDashboard"
            class="q-ml-sm"
          />
          <q-space />

          <!-- <div class="row items-center">
            <q-spinner-clock color="white" size="1.5em" class="q-mr-xs" />
            {{ formattedString }}
          </div> -->
          <!-- Admin Account Dropdown (Moved from Drawer to Here) -->
          <q-btn-dropdown
            flat
            dense
            no-caps
            dropdown-icon="keyboard_arrow_down"
            style="max-width: 200px"
          >
            <template v-slot:label>
              <q-avatar size="25px" class="bg-white">
                <img :src="previewAdminImage" />
              </q-avatar>
              <span class="q-ml-sm">
                {{
                  adminInformationContent && adminInformationContent.Firstname
                    ? adminInformationContent.Firstname +
                      " " +
                      adminInformationContent.Lastname
                    : "Loading..."
                }}
              </span>
            </template>

            <q-list>
              <q-item clickable v-ripple @click="logout">
                <q-item-section avatar>
                  <q-icon name="logout" color="red" />
                </q-item-section>
                <q-item-section class="text-red">Logout</q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
        </q-toolbar>
      </q-header>

      <!-- Mobile message for users -->
      <div class="q-pa-md flex flex-center" style="min-height: 100vh;">
        <q-card class="bg-warning text-secondary q-pa-lg" style="max-width: 400px; width: 90%; text-align: center;">
          <q-card-section>
            <q-icon name="mobile_off" size="48px" class="q-mb-md text-accent" />
            <div class="text-h6 q-mb-sm text-accent">
              Feature Unavailable on Mobile
            </div>
            <div class="bg-white rounded-borders q-pa-md">
            <p class="text-body1 q-mb-md text-secondary">
              We’re working hard to improve this feature, but it's not available on mobile devices just yet.
              Please visit us from a desktop for the full experience. Thank you for your patience!
            </p>
            </div>
          </q-card-section>
        </q-card>
      </div>

    </q-layout>
    <q-layout view="hHh LpR fFf" class="shadow-2 rounded-borders" v-else>
      <q-header v-if="shouldShowSideNav">
        <q-toolbar>
          <q-img
            src="~assets/vrtlogowhite1.png"
            alt="Logo"
            fit="full"
            :style="{
              maxWidth: $q.screen.lt.sm ? '100px' : '160px',
              cursor: 'pointer', // Fix cursor property
              transition: 'transform 0.3s ease, box-shadow 0.3s ease',
            }"
            @click="goonDashboard"
            class="q-ml-sm"
          />
          <q-space />

          <!-- <div class="row items-center">
            <q-spinner-clock color="white" size="1.5em" class="q-mr-xs" />
            {{ formattedString }}
          </div> -->
          <!-- Admin Account Dropdown (Moved from Drawer to Here) -->
          <q-btn-dropdown
            flat
            dense
            no-caps
            dropdown-icon="keyboard_arrow_down"
            style="max-width: 200px"
          >
            <template v-slot:label>
              <q-avatar size="25px" class="bg-white">
                <img :src="previewAdminImage" />
              </q-avatar>
              <span class="q-ml-sm">
                {{
                  adminInformationContent && adminInformationContent.Firstname
                    ? adminInformationContent.Firstname +
                      " " +
                      adminInformationContent.Lastname
                    : "Loading..."
                }}
              </span>
            </template>

            <q-list>
              <q-item clickable v-ripple @click="logout">
                <q-item-section avatar>
                  <q-icon name="logout" color="red" />
                </q-item-section>
                <q-item-section class="text-red">Logout</q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
        </q-toolbar>
      </q-header>

      <q-drawer
        v-if="shouldShowSideNav"
        v-model="drawer"
        show-if-above
        :mini="miniState"
        @click.capture="drawerClick"
        :width="250"
        :breakpoint="500"
        bordered
        content-class="fit"
        :class="$q.dark.isActive ? 'bg-accent' : 'bg-accent'"
        class="q-px-sm shadow-1"
      >
        <q-scroll-area
          class="fit"
          :horizontal-thumb-style="{ opacity: 0 }"
          style="height: 100%"
        >
          <q-list padding class="q-pb-xl q-mb-xl text-secondary">
            <template v-for="(item, index) in linksList" :key="index">
              <!-- Parent with children (Dropdown) -->
              <q-expansion-item
                v-if="item.children"
                expand-separator
                :icon="item.icon"
                :label="item.title"
              >
                <q-list>
                  <template
                    v-for="(child, childIndex) in item.children"
                    :key="childIndex"
                  >
                    <!-- Render each child -->
                    <q-item clickable v-ripple :to="child.link">
                      <q-item-section avatar class="q-pl-xl">
                        <q-icon
                          :name="child.icon"
                          :color="
                            child.link === $route.path ? 'primary' : 'secondary'
                          "
                        />
                      </q-item-section>
                      <q-item-section class="text-left">{{
                        child.title
                      }}</q-item-section>
                    </q-item>

                    <!-- Insert "Waiting Time" only after "Personal Info" -->
                    <template v-if="child.title === 'Personal Info' && manager">
                      <q-item clickable v-ripple>
                        <q-item-section avatar class="q-pl-xl">
                          <q-icon
                            name="hourglass_top"
                            :color="isMenuOpen ? 'primary' : 'secondary'"
                          />
                        </q-item-section>
                        <q-item-section
                          class="text-left"
                          :class="{ 'text-primary': isMenuOpen }"
                          >Waiting Time</q-item-section
                        >
                        <!-- Waiting time seamless dialog -->
                        <q-menu
                          fit
                          anchor="top right"
                          self="top left"
                          transition-show="jump-down"
                          transition-hide="jump-up"
                        >
                          <div class="q-pa-sm">
                            <div class="q-gutter-sm">
                              <!-- Help button container -->
                              <div class="row no-wrap" style="width: 100%">
                                <div class="col" />
                                <div class="col-auto">
                                  <q-btn
                                    flat
                                    round
                                    dense
                                    icon="help_outline"
                                    color="primary"
                                    @click="showHelpWaitingTime = true"
                                    style="
                                      min-width: 32px;
                                      width: 32px;
                                      height: 32px;
                                    "
                                  />
                                </div>
                              </div>

                              <!-- Help Dialog -->
                              <q-dialog v-model="showHelpWaitingTime">
                                <q-card
                                  class="q-pa-md"
                                  style="max-width: 400px; width: 90vw"
                                >
                                  <q-card-section>
                                    <div class="text-h6">Instructions</div>
                                  </q-card-section>

                                  <q-card-section>
                                    <p>
                                      The waiting time field allows you to define
                                      the estimated time a customer is expected to
                                      wait before being served. Enter the time in
                                      the format <strong>MM:SS</strong> — for
                                      example, "02:30" for 2 minutes and 30
                                      seconds. This input helps the system provide
                                      more accurate queue estimates and improves
                                      overall service planning.
                                    </p>
                                  </q-card-section>

                                  <q-card-actions align="right">
                                    <q-btn
                                      flat
                                      label="Close"
                                      color="primary"
                                      v-close-popup
                                    />
                                  </q-card-actions>
                                </q-card>
                              </q-dialog>

                              <!-- Main Card Content -->
                              <q-card class="q-pa-md no-border shadow-none" flat>
                                <q-form @submit.prevent="process">
                                  <q-input
                                    v-model="formData.Waiting_time"
                                    mask="##:##"
                                    fill-mask="0"
                                    label="Enter Time (MM:SS)"
                                    :error="
                                      formError.hasOwnProperty('Waiting_time')
                                    "
                                    :error-message="formError.Waiting_time"
                                    :hint="
                                      timeData
                                        ? `Last saved time: ${timeData}`
                                        : 'Format: MM:SS'
                                    "
                                    outlined
                                    class="q-mb-md text-h6"
                                  />
                                  <div class="row justify-center">
                                    <q-btn
                                      color="primary"
                                      label="Save"
                                      icon="save"
                                      @click="process"
                                    />
                                  </div>
                                </q-form>
                              </q-card>
                            </div>
                          </div>

                        </q-menu>
                      </q-item>

                      <q-item clickable v-ripple @click="resetQueue()">
                        <q-item-section avatar class="q-pl-xl">
                          <q-icon name="restart_alt" />
                        </q-item-section>
                        <q-item-section class="text-left"
                          >Reset Queue</q-item-section
                        >
                      </q-item>

                      <q-item clickable v-ripple>
                        <q-item-section avatar class="q-pl-xl">
                          <q-icon
                            name="schedule"
                            :color="isMenuOpen ? 'primary' : 'secondary'"
                          />
                        </q-item-section>
                        <q-item-section
                          class="text-left"
                          :class="{ 'text-primary': isMenuOpen }"
                        >
                          Break Time
                        </q-item-section>
                        <q-menu
                          fit
                          anchor="top right"
                          self="top left"
                          transition-show="jump-down"
                          transition-hide="jump-up"
                        >
                          <div class="q-pa-sm">
                            <!-- Help Button Row -->
                            <div class="row no-wrap" style="width: 100%">
                              <div class="col" />
                              <div class="col-auto">
                                <q-btn
                                  flat
                                  round
                                  dense
                                  icon="help_outline"
                                  color="primary"
                                  style="
                                    min-width: 32px;
                                    width: 32px;
                                    height: 32px;
                                  "
                                  @click="showHelpBreakTime = true"
                                />
                              </div>
                            </div>

                            <!-- Help Dialog -->
                            <q-dialog v-model="showHelpBreakTime">
                              <q-card
                                class="q-pa-md"
                                style="max-width: 400px; width: 90vw"
                              >
                                <q-card-section>
                                  <div class="text-h6">Instructions</div>
                                </q-card-section>

                                <q-card-section>
                                  <p>
                                    The break time feature allows you to define a
                                    time range when a teller is unavailable due to
                                    a scheduled break. Select the starting time
                                    using the "From" field and the ending time
                                    using the "To" field. Make sure both fields
                                    are correctly set in 24-hour format (e.g.,
                                    13:00 for 1 PM). Once both times are selected,
                                    click "Save" to apply the break schedule for
                                    that teller.
                                  </p>
                                </q-card-section>

                                <q-card-actions align="right">
                                  <q-btn
                                    flat
                                    label="Close"
                                    color="primary"
                                    v-close-popup
                                  />
                                </q-card-actions>
                              </q-card>
                            </q-dialog>

                            <!-- Main Card -->
                            <q-card class="q-pa-md no-border shadow-none" flat>
                              <q-form @submit.prevent="saveBreakTime">
                                <!-- From Section -->
                                <q-item>
                                  <q-item-section>
                                    <q-item-label class="text-h6"
                                      >From:</q-item-label
                                    >
                                    <q-btn
                                      color="primary"
                                      icon="schedule"
                                      :label="formattedFromTime"
                                      :error="
                                        formError.hasOwnProperty('break_from')
                                      "
                                      :error-message="formError.break_from"
                                      @click="showFromPicker = true"
                                    />
                                    <q-input
                                      v-model="formDataBreak.break_from"
                                      outlined
                                      dense
                                      type="time"
                                      format24h
                                      v-if="showFromPicker"
                                      @update:model-value="showFromPicker = false"
                                    />
                                  </q-item-section>
                                </q-item>

                                <!-- To Section -->
                                <q-item>
                                  <q-item-section>
                                    <q-item-label class="text-h6"
                                      >To:</q-item-label
                                    >
                                    <q-btn
                                      color="primary"
                                      icon="schedule"
                                      :label="formattedToTime"
                                      :error="
                                        formError.hasOwnProperty('break_to')
                                      "
                                      :error-message="formError.break_to"
                                      @click="showToPicker = true"
                                    />
                                    <q-input
                                      outlined
                                      dense
                                      type="time"
                                      format24h
                                      v-model="formDataBreak.break_to"
                                      v-if="showToPicker"
                                      @update:model-value="showToPicker = false"
                                    />
                                  </q-item-section>
                                </q-item>

                                <div class="row justify-center q-mt-md">
                                  <q-btn
                                    color="primary"
                                    label="Save"
                                    icon="save"
                                    @click="saveBreakTime"
                                  />
                                </div>
                              </q-form>
                            </q-card>
                          </div>
                        </q-menu>
                      </q-item>
                    </template>
                  </template>
                </q-list>
              </q-expansion-item>

              <!-- Normal Menu Item -->
              <q-item v-else clickable v-ripple :to="item.link" exact>
                <q-item-section avatar>
                  <q-icon
                    :name="item.icon"
                    :color="item.link === $route.path ? 'primary' : 'secondary'"
                  />
                </q-item-section>
                <q-item-section v-if="!miniState">{{
                  item.title
                }}</q-item-section>
              </q-item>
            </template>
          </q-list>
        </q-scroll-area>

        <!-- Mini Drawer Toggle Button -->
        <div class="q-mini-drawer absolute" style="top: 10px; right: -17px">
          <q-btn
            dense
            round
            unelevated
            color="primary"
            :icon="miniState ? 'chevron_right' : 'chevron_left'"
            @click="toggleMiniState"
          />
        </div>

        <!-- 🔹 ADMIN ACCOUNT SECTION (USING <div>) -->
        <!-- <div
          class="q-pa-xs absolute-bottom full-width q-mt-md bg-accent"
          style="border-top: 1px solid #ccc"
        >
          <q-btn-dropdown class="full-width" flat no-caps dropdown-icon="none">
            <template v-slot:label>
              <q-item class="none flex items-center">
                <q-item-section avatar>
                  <q-img
                    :src="previewAdminImage"
                    style="
                      width: 30px;
                      height: 30px;
                      border-radius: 50%;
                      border: 1px solid #1c5d99;
                    "
                    fit="cover"
                  />
                </q-item-section>
                <q-item-section>
                  <q-item-label>
                    {{
                      adminInformationContent && adminInformationContent.Firstname
                        ? adminInformationContent.Firstname +
                          " " +
                          adminInformationContent.Lastname
                        : "Loading..."
                    }}
                  </q-item-label>
                </q-item-section>
              </q-item>
            </template>

            <q-list>
              <q-item clickable v-ripple @click="logout">
                <q-item-section avatar>
                  <q-icon name="logout" color="red" />
                </q-item-section>
                <q-item-section class="text-red">Logout</q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
        </div> -->
      </q-drawer>
      <q-page-container style="padding-bottom: 20px">
        <router-view />
      </q-page-container>

      <q-footer class="bg-secondary text-grey-4 q-pa-sm fixed-bottom">
        <div class="row items-center justify-between full-width">
          <!-- Left Side: Copyright Text -->
          <span class="text-caption">
            © 2025 VRTSystems Technologies Corporation.
          </span>

          <!-- Right Side: Formatted Time -->
          <div class="row items-center">
            <q-icon
              name="calendar_today"
              color="white"
              size="1em"
              class="q-mr-xs"
            />
            <span class="text-caption">{{ formattedString }}</span>
          </div>
        </div>
      </q-footer>
    </q-layout>
</template>

<script>
import { defineComponent, ref, onMounted, reactive, nextTick } from "vue";
import { date } from "quasar";
import { useRouter } from "vue-router";
import { $axios, $notify } from "src/boot/app";
import { useQuasar } from "quasar";
import { computed } from "vue";
import { isFullscreen } from "src/composables/fullscreenState";
export default defineComponent({
  name: "MainLayout",

  setup() {
    const leftDrawerOpen = ref(false);
    const router = useRouter();
    const formattedString = ref();
    const drawer = ref(false);
    const miniState = ref(false);
    const adminInformation = ref(null);
    const previewAdminImage = ref(null);
    const currentServing = ref(null);
    const isQueuelistEmpty = ref(false);
    const $dialog = useQuasar();
    const linksList = ref([]);
    const branch_id = ref(null);
    const manager_id = ref(null);
    const shouldShowSideNav = computed(() => !isFullscreen.value);
    const adminInformationContent = ref({
      id: "",
      Firstname: "",
      Lastname: "",
      Image: "",
    });
    const isMenuOpen = false;
    const showHelpBreakTime = ref(false);
    const showHelpWaitingTime = ref(false);

    // Function to update the time
    const updateFormattedTime = () => {
      const timeStamp = Date.now();
      formattedString.value = date.formatDate(
        timeStamp,
        "YYYY-MM-DD h:mm:ss A"
      );
    };

    const toggleMiniState = () => {
      miniState.value = !miniState.value;
    };

    const drawerClick = (e) => {
      if (miniState.value) {
        miniState.value = false;
        e.stopPropagation();
      }
    };

    const fetchAdminInformation = async () => {
      try {
        if (adminInformation.value.branch_id) {
          const { data } = await $axios.post("/admin/Information", {
            id: adminInformation.value.id,
            branch_id: adminInformation.value.branch_id,
          });

          if (data.dataValue) {
            adminInformationContent.value = data.dataValue; // Use the object directly, no need for
            previewAdminImage.value = data.dataValue.Image;
          } else {
            console.error("No data found or invalid structure", data);
            adminInformationContent.value = {
              Firstname: "N/A",
              Lastname: "N/A",
            };
          }
        } else {
          const { data } = await $axios.post("/admin/Information", {
            id: adminInformation.value.id,
          });

          if (data.dataValue) {
            adminInformationContent.value = data.dataValue; // Use the object directly, no need for
            previewAdminImage.value = data.dataValue.Image;
          } else {
            console.error("No data found or invalid structure", data);
            adminInformationContent.value = {
              Firstname: "N/A",
              Lastname: "N/A",
            };
          }
        }
      } catch (error) {
        console.error("Error fetching admin info", error);
        adminInformationContent.value = {
          Firstname: "Error",
          Lastname: "Error",
        };
      }
    };

    const goonDashboard = async () => {
      try {
        router.push("/admin/dashboard"); // Redirect to login page
      } catch (error) {
        console.log(error);
      }
    };

    // Set an interval to update the time every second
    onMounted(() => {
      const storedAdminInfo = localStorage.getItem("adminInformation");
      const storeManagerInfo = localStorage.getItem("managerInformation");
      if (storedAdminInfo) {
        adminInformation.value = JSON.parse(storedAdminInfo);
        fetchAdminInformation();
        updateFormattedTime(); // Call it once on mount
        setInterval(fetchAdminInformation, 6000);
        setInterval(updateFormattedTime, 1000); // Update every second
      } else if (storeManagerInfo) {
        adminInformation.value = JSON.parse(storeManagerInfo);
        branch_id.value = adminInformation.value.branch_id;
        manager_id.value = adminInformation.value.id;
        fetchAdminInformation();
        updateFormattedTime(); // Call it once on mount
        setInterval(fetchAdminInformation, 6000);
        setInterval(updateFormattedTime, 1000); // Update every second
      } else {
        console.error("No admin information found in localStorage");
      }
    });

    // Logout function
    const logout = async () => {
      await $axios.post("/manager/logout", {
        manager_id: manager_id.value,
      });
      localStorage.removeItem("authTokenAdmin"); // Remove auth token
      localStorage.removeItem("adminInformation");
      localStorage.removeItem("authTokenManager"); // Remove auth token
      localStorage.removeItem("managerInformation");
      let rout = router.push("/login"); // Redirect to login page
      setTimeout(() => {
        window.location.reload(); // Prevent back navigation
      }, 500);
    };

    // waiting time function section
    const isLoading = ref(false);
    const formData = ref({
      id: "", // Store ID if it exists
      Waiting_time: "",
      branch_id: "",
    });
    const timeData = ref(null);
    const formError = ref({});
    const formDataBreak = reactive({
      id: "", // Store ID if it exists
      break_from: "", // Stores start time
      break_to: "", // Stores end time
    });
    const showFromPicker = ref(false);
    const showToPicker = ref(false);

    // Fetch saved time
    const formatTime = (seconds) => {
      const minutes = Math.floor(seconds / 60);
      const remainingSeconds = seconds % 60;
      return `${String(minutes).padStart(2, "0")}:${String(
        remainingSeconds
      ).padStart(2, "0")}`;
    };

    const fetchWaitingtime = async () => {
      try {
        const { data } = await $axios.post("/admin/waiting_Time-fetch", {
          branch_id: adminInformation.value.branch_id,
        });

        if (data?.dataValue?.Waiting_time) {
          const waitingTimeInSeconds = data.dataValue.Waiting_time;
          timeData.value = formatTime(waitingTimeInSeconds);
          console.log("Updated timeData (MM:SS):", timeData.value);
        } else {
          console.warn("No waiting time found for this branch");
        }
      } catch (error) {
        console.error("Error fetching waiting time:", error);
      }
    };

    const fetchBreakTime = async () => {
      try {
        const { data } = await $axios.post("/admin/fetch_break_time", {
          branch_id: branch_id.value,
        });
        console.log("Fetched Data:", data);
        if (data?.dataValue) {
          formDataBreak.break_to = data.dataValue.break_to.slice(0, 5);
          formDataBreak.break_from = data.dataValue.break_from.slice(0, 5);
          console.log(
            "Updated break time:",
            formDataBreak.break_from,
            formDataBreak.break_to
          );
          console.log(typeof formDataBreak.break_from);
          await nextTick();
        } else {
          console.warn("No break time found");
        }
      } catch (error) {
        console.error("Error fetching break time:", error);
      }
    };

    const process = async () => {
      isLoading.value = true;
      try {
        formData.value.branch_id = adminInformation.value.branch_id;

        if (formData.value.Waiting_time == "00:00") {
          formError.value.Waiting_time = "Invalid waiting time";
          return;
        }

        const time = formData.value.Waiting_time

        // Validate format MM:SS
        const timeRegex = /^(\d{2}):(\d{2})$/;
        const match = time.match(timeRegex);

        if (!match) {
          formError.value.Waiting_time = "Time must be in MM:SS format";
          return;
        }

        const minutes = parseInt(match[1], 10);
        const seconds = parseInt(match[2], 10);

        if (minutes > 59 || seconds > 59) {
          formError.value.Waiting_time = "Waiting time max value 59:59";
          return;
        }

        const endpoint = "/admin/waiting_Time"; // Always use the same endpoint

        const { data } = await $axios.post(endpoint, formData.value);
        formError.value = {}; // Reset form errors

        if (data) {
          $notify("positive", "done", data.message);
          fetchWaitingtime(); // Refresh data after insert/update
        }
      } catch (error) {
        if (error.response.status === 422) {
          formError.value = error.response.data; // Handle validation errors
        } else {
          console.error("Error", error);
        }
      } finally {
        isLoading.value = false;
      }
    };

    const saveBreakTime = async () => {
      isLoading.value = true;
      try {
        const payload = {
          ...formDataBreak,
          branch_id: branch_id.value, // manually include refID
        };
        console.log(branch_id.value); //outputs the actual branch id
        const { data } = await $axios.post("/admin/break_time", payload);
        formError.value = {}; // Reset form errors

        if (data) {
          $notify("positive", "done", data.message);
          fetchBreakTime();
        }
      } catch (error) {
        if (error.response.status === 422) {
          formError.value = error.response.data; // Handle validation errors
        } else {
          $notify(
            "negative",
            "error",
            'The "From" time must be earlier than the "To" time.'
          );
          console.error("Error", error.response ? error.response.data : error); // ✅ Prevent undefined errors
        }
      } finally {
        isLoading.value = false;
      }
    };

    // Resetting Queue Number

    // Reset Queue Number

    const fetchQueue = async () => {
      try {
        const response = await $axios.post("/teller/queue-list", {
          type_id: type_id.value,
        });
        queueList.value = response.data.queue.filter(
          (q) => !["finished", "cancelled"].includes(q.status)
        );
        currentServing.value = response.data.current_serving;
        cusName.value = response.data.name;
        cusQueueNum.value = response.data.queue_number;
        servingStatus.value = response.data.status;
        tellerFullName.value = response.data.fullname;
        // queuePosition.value = queueList.value.findIndex(q => q.queue_number == response.data.queue_numbers[0]) + 1
        // console.log(queuePosition.value)
        // console.log(response.data.queue_numbers)
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

    const resetQueue = async () => {
      try {
        $dialog
          .dialog({
            title: "Confirm Queue Reset",
            message: "Please make sure all the queues are finished",
            cancel: true,
            persistent: true,
            color: "primary",
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
          .onOk(async () => {
            const response = await $axios.post("/resetQueue", {
              branch_id: adminInformation.value.branch_id,
            });
            $notify("positive", "check", response.data.message);
            console.log(response.data.message);
          })
          .onDismiss(() => {
            // console.log('I am triggered on both OK and Cancel')
          });
      } catch (error) {
        console.error(error);
        $notify("negative", "error", error);
      }
    };
    const manager = ref(false);
    const fetchLinks = async () => {
      if (adminInformation.value.branch_id != null) {
        manager.value = true;
        linksList.value = [
          {
            title: "Dashboard",
            icon: "dashboard",
            link: "/admin/dashboard",
          },
          {
            title: "Teller",
            icon: "person",
            children: [
              {
                title: "Service Types",
                icon: "category",
                link: "/admin/teller/types",
              },
              {
                title: "Personnel",
                icon: "groups",
                link: "/admin/teller/tellers",
              },
              {
                title: "Window",
                icon: "computer",
                link: "/admin/teller/window",
              },
            ],
          },
          {
            title: "Generated QrCode",
            icon: "qr_code_2",
            link: "/queue-qr",
          },
          {
            title: "Admin Queue",
            icon: "admin_panel_settings",
            link: "/admin/admin_Queue",
          },
          {
            title: "Currency Conversion",
            icon: "currency_exchange",
            link: "/admin/currency_conversion",
          },
          {
            title: "Branch Appointment",
            icon: "person",
            children: [
              {
                title: "Appointment",
                icon: "category",
                link: "/admin/appointment/create",
              },
              {
                title: "List Appointment",
                icon: "groups",
                link: "/admin/appointment/list",
              },
            ],
          },
          /*       {
            title: "Customer Logs",
            icon: "description",
            link: "/admin/customer-logs",
          }, */
          {
            title: "Teller customer logs",
            icon: "bar_chart",
            link: "/admin/teller-customer-logs",
          },
          {
            title: "Window Logs",
            icon: "upload_file",
            link: "/admin/window-logs",
          },
          {
            title: "Serving Time Logs",
            icon: "timer",
            link: "/admin/serving-time-logs",
          },
          /* {
            title: "Reports",
            icon: "bar_chart",
            link: "/admin/reports",
          }, */
          {
            title: "Archive",
            icon: "archive",
            link: "/admin/archive",
          },
          {
            title: "Feedback",
            icon: "reviews",
            link: "/admin/feedback",
          },
          {
            title: "Settings",
            icon: "settings",
            children: [
              {
                title: "Personal Info",
                icon: "computer",
                link: "/admin/settings",
              },
            ],
          },
        ];
      } else {
        linksList.value = [
          {
            title: "Dashboard",
            icon: "dashboard",
            link: "/admin/dashboard",
          },
          {
            title: "Teller",
            icon: "person",
            children: [
              {
                title: "Service Types",
                icon: "category",
                link: "/admin/teller/types",
              },
              {
                title: "Personnel",
                icon: "groups",
                link: "/admin/teller/tellers",
              },
              {
                title: "Window",
                icon: "computer",
                link: "/admin/teller/window",
              },
            ],
          },

          {
            title: "Admin Queue",
            icon: "admin_panel_settings",
            link: "/admin/admin_Queue",
          },
          // {
          //   title: "Manager",
          //   icon: "supervisor_account",
          //   link: "/admin/bank_manager",
          // },
          {
            title: "Branch",
            icon: "account_balance",
            link: "/admin/branch",
          },
          {
            title: "Currency Conversion",
            icon: "currency_exchange",
            link: "/admin/currency_conversion",
          },
          {
            title: "Branch Appointment",
            icon: "date_range",
            children: [
              {
                title: "Appointment",
                icon: "event",
                link: "/admin/appointment/create",
              },
              {
                title: "List Appointment",
                icon: "event_note",
                link: "/admin/appointment/list",
              },
            ],
          },
          /*         {
          title: "Customer Logs",
          icon: "description",
          link: "/admin/customer-logs",
        }, */
          {
            title: "Teller customer logs",
            icon: "bar_chart",
            link: "/admin/teller-customer-logs",
          },
          {
            title: "Window Logs",
            icon: "upload_file",
            link: "/admin/window-logs",
          },
          {
            title: "Serving Time Logs",
            icon: "timer",
            link: "/admin/serving-time-logs",
          },
          /* {
            title: "Reports",
            icon: "bar_chart",
            link: "/admin/reports",
          }, */
          {
            title: "Archive",
            icon: "archive",
            link: "/admin/archive",
          },
          {
            title: "Feedback",
            icon: "reviews",
            link: "/admin/feedback",
          },
          {
            title: "Settings",
            icon: "settings",
            children: [
              {
                title: "Personal Info",
                icon: "computer",
                link: "/admin/settings",
              },
              {
                title: "Reset Window",
                icon: "reset_tv",
                link: "/admin/reset-window",
              },
            ],
          },
        ];
      }
    };

    // Function to convert 24-hour time to 12-hour AM/PM format
    const convertTo12HourFormat = (time) => {
      const [hours, minutes] = time.split(":");
      let hour = parseInt(hours, 10);
      const ampm = hour >= 12 ? "PM" : "AM";
      hour = hour % 12; // Convert hour to 12-hour format
      hour = hour ? hour : 12; // The hour '0' should be '12'
      return `${hour}:${minutes} ${ampm}`;
    };

    // Computed properties to display formatted time
    const formattedFromTime = computed(() => {
      return formDataBreak.break_from
        ? convertTo12HourFormat(formDataBreak.break_from)
        : "Select Time";
    });

    const formattedToTime = computed(() => {
      return formDataBreak.break_to
        ? convertTo12HourFormat(formDataBreak.break_to)
        : "Select Time";
    });

    onMounted(() => {
      fetchLinks();
      fetchWaitingtime();
      fetchBreakTime();
    });

    return {
      leftDrawerOpen,
      formattedString,
      drawer,
      miniState,
      adminInformation,
      toggleMiniState,
      drawerClick,
      adminInformationContent,
      fetchAdminInformation,
      previewAdminImage,
      goonDashboard,
      logout, // Make logout function available in the template
      linksList,
      isMenuOpen,

      // waiting time function
      formData,
      timeData,
      process,
      saveBreakTime,
      isLoading,
      formError,
      formDataBreak,
      showFromPicker,
      showToPicker,

      // reset queue number functions
      currentServing,
      isQueuelistEmpty,
      fetchQueue,
      resetQueue,
      shouldShowSideNav,

      manager,

      formattedToTime,
      formattedFromTime,
      showHelpBreakTime,
      showHelpWaitingTime,

      toggleLeftDrawer() {
        leftDrawerOpen.value = !leftDrawerOpen.value;
      },
    };
  },
});
</script>
<style scoped>
.no-hover-bg:hover {
  background-color: transparent !important;
}
</style>
