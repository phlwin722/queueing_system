<template>
  <q-layout view="hHh LpR fFf" class="shadow-2 rounded-borders">
    <q-header>
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
        <!-- <q-toolbar-title class="text-center">QUEUING SYSTEM</q-toolbar-title> -->
        <div class="row items-center">
          <q-spinner-clock color="white" size="1.5em" class="q-mr-xs" />
          {{ formattedString }}
        </div>
      </q-toolbar>
    </q-header>

    <q-drawer
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
                  <template v-if="child.title === 'Personal Info'">
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
                        <q-card class="q-pa-md">
                          <q-form @submit.prevent="process">
                            <q-input
                              v-model="formData.Waiting_time"
                              mask="##:##"
                              fill-mask="0"
                              label="Enter Time (MM:SS)"
                              :error="formError.hasOwnProperty('Waiting_time')"
                              :error-message="formError.Waiting_time"
                              :hint="
                                timeData
                                  ? `Last saved time: ${timeData}`
                                  : 'Format: MM:SS'
                              "
                              :model-value="timeData"
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
                      </q-menu>
                    </q-item>

                    <q-item
                      clickable
                      v-ripple
                      @click="resetQueue()"
                    >
                      <q-item-section avatar class="q-pl-xl">
                        <q-icon name="restart_alt" />
                      </q-item-section>
                      <q-item-section class="text-left"
                        >Reset Queue</q-item-section
                      >
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
      <div class="q-mini-drawer-hide absolute" style="top: 20px; right: -17px">
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
      <div
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
      </div>
    </q-drawer>
    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script>
import { defineComponent, ref, onMounted } from "vue";
import { date } from "quasar";
import { useRouter } from "vue-router";
import { $axios, $notify } from "src/boot/app";
import { useQuasar } from "quasar";

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
    const adminInformationContent = ref({
      id: "",
      Firstname: "",
      Lastname: "",
      Image: "",
    });
    const isMenuOpen = false;

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
      if (storedAdminInfo) {
        adminInformation.value = JSON.parse(storedAdminInfo);
        fetchAdminInformation();
        updateFormattedTime(); // Call it once on mount
        setInterval(fetchAdminInformation, 6000);
        setInterval(updateFormattedTime, 1000); // Update every second
      } else {
        console.error("No admin information found in localStorage");
      }
    });

    // Logout function
    const logout = () => {
      localStorage.removeItem("authTokenAdmin"); // Remove auth token
      localStorage.removeItem("adminInformation");
      router.push("/login"); // Redirect to login page
      setTimeout(() => {
        window.location.reload(); // Prevent back navigation
      }, 100);
    };

    // waiting time function section
    const isLoading = ref(false);
    const formData = ref({
      id: "", // Store ID if it exists
      Waiting_time: "",
    });
    const timeData = ref(null);
    const formError = ref({});

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
        const { data } = await $axios.post("/admin/waiting_Time-fetch");
        console.log("Fetched Data:", data);

        if (data && data.dataValue && data.dataValue.length > 0) {
          const waitingTimeInSeconds = data.dataValue[0].Waiting_time; // Fetch as seconds
          timeData.value = formatTime(waitingTimeInSeconds); // Convert to MM:SS
          console.log("Updated timeData (MM:SS):", timeData.value);
        } else {
          console.warn("No waiting time found");
        }
      } catch (error) {
        console.error("Error fetching waiting time:", error);
      }
    };

    const process = async () => {
      isLoading.value = true;
      try {
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
            const response = await $axios.post("/resetQueue");
            $notify("positive", "check", response.data.message);
            console.log(response.data.message);
          })
          .onDismiss(() => {
            // console.log('I am triggered on both OK and Cancel')
          });
      } catch (error) {
        console.error(error);
        $notify("negative", "error",error);
      }
    };

    onMounted(() => {
      fetchWaitingtime();
    });

    const linksList = [
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
            title: "Window",
            icon: "computer",
            link: "/admin/teller/window",
          },
          {
            title: "Personnel",
            icon: "groups",
            link: "/admin/teller/tellers",
          },
          {
            title: "Service Types",
            icon: "category",
            link: "/admin/teller/types",
          },
        ],
      },
      {
        title: "Archive",
        icon: "archive",
        link: "/admin/archive",
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
        title: "Customer Logs",
        icon: "description",
        link: "/admin/customer-logs",
      },
      {
        title: "Window Logs",
        icon: "upload_file",
        link: "/admin/window-logs",
      },
      {
        title: "Reports",
        icon: "bar_chart",
        link: "/admin/reports",
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
      isLoading,
      formError,

      // reset queue number functions
      currentServing,
      isQueuelistEmpty,
      fetchQueue,
      resetQueue,

      toggleLeftDrawer() {
        leftDrawerOpen.value = !leftDrawerOpen.value;
      },
    };
  },
});
</script>
