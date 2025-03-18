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
              cursor: 'pointer',  // Fix cursor property
              transition: 'transform 0.3s ease, box-shadow 0.3s ease'
            }"
            @click="goonDashboard"
            class="q-ml-sm"
          />
        <q-toolbar-title class="text-center">Queuing System</q-toolbar-title>
        <div>{{ formattedString }}</div>
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
    >
    <q-scroll-area class="fit" :horizontal-thumb-style="{ opacity: 0 }" style="height: 100%;">
      <q-list padding class="q-pb-xl q-mb-xl">
        <template v-for="(item, index) in linksList" :key="index">
          <!-- Parent with children (Dropdown) -->
          <q-expansion-item
            v-if="item.children"
            expand-separator
            :icon="item.icon"
            :label="item.title"
          >
            <q-list>
              <q-item
                v-for="(child, childIndex) in item.children"
                :key="childIndex"
                clickable
                v-ripple
                :to="child.link"
              >
                <q-item-section avatar class="q-pl-xl">
                  <q-icon :name="child.icon" :color="child.link === $route.path ? 'primary' : 'secondary'"/>
                </q-item-section>
                <q-item-section class="text-left ">{{ child.title }}</q-item-section>
              </q-item>
            </q-list>
          </q-expansion-item>

          <!-- Normal Menu Item -->
          <q-item v-else clickable v-ripple :to="item.link" exact>
            <q-item-section avatar>
              <q-icon :name="item.icon" :color="item.link === $route.path ? 'primary' : 'secondary'" />
            </q-item-section>
            <q-item-section v-if="!miniState">{{ item.title }}</q-item-section>
          </q-item>
        </template>
      </q-list>

      </q-scroll-area>
      
      <!-- Mini Drawer Toggle Button -->
      <div class="q-mini-drawer-hide absolute" style="top: 15px; right: -17px">
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
      <div class="q-pa-xs absolute-bottom full-width q-mt-md bg-accent" style="border-top: 1px solid #ccc;">
        <q-btn-dropdown class="full-width" flat no-caps dropdown-icon="none">
          <template v-slot:label>
            <q-item class="none flex items-center">
              <q-item-section avatar>
                <q-img 
                  :src="previewAdminImage"
                  style="width: 30px; height: 30px; border-radius: 50%; border: 1px solid  #1c5d99;" 
                  fit="cover" />
              </q-item-section>
              <q-item-section>
                <q-item-label>
                {{ adminInformationContent && adminInformationContent.Firstname ? adminInformationContent.Firstname + " " + adminInformationContent.Lastname : "Loading..." }}
              </q-item-label>

              </q-item-section>
            </q-item>
          </template>

          <q-list>
            <q-item clickable v-ripple 
              @click="logout">
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
import { $axios } from "src/boot/app";

export default defineComponent({
  name: "MainLayout",

  setup() {
    const leftDrawerOpen = ref(false);
    const router = useRouter();
    const formattedString = ref();
    const drawer = ref(false);
    const miniState = ref(false);
    const adminInformation = ref(null)
    const previewAdminImage = ref(null)

    const adminInformationContent = ref({
      id: '',
      Firstname: '',
      Lastname: '',
      Image: '',
    })

    // Function to update the time
    const updateFormattedTime = () => {
      const timeStamp = Date.now();
      formattedString.value = date.formatDate(timeStamp, "YYYY-MM-DD h:mm:ss A");
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
        const { data } = await $axios.post('/admin/Information', {
          id: adminInformation.value.id
        });

        if (data.dataValue) {
          adminInformationContent.value = data.dataValue; // Use the object directly, no need for 
          previewAdminImage.value = data.dataValue.Image
        } else {
          console.error('No data found or invalid structure', data);
          adminInformationContent.value = {
            Firstname: 'N/A',
            Lastname: 'N/A'
          };
        }
      } catch (error) {
        console.error('Error fetching admin info', error);
        adminInformationContent.value = {
          Firstname: 'Error',
          Lastname: 'Error'
        };
      }
    };

    const goonDashboard = async () => {
      try {
        router.push("/admin/dashboard"); // Redirect to login page
      } catch (error) {
        console.log(error)
      }
    }


    // Set an interval to update the time every second
    onMounted(() => {
      const storedAdminInfo = sessionStorage.getItem('adminInformation');
      if (storedAdminInfo) {
        adminInformation.value = JSON.parse(storedAdminInfo);
        fetchAdminInformation()
        updateFormattedTime(); // Call it once on mount
        setInterval(fetchAdminInformation,1000);
        setInterval(updateFormattedTime, 1000); // Update every second
      } else {
        console.error("No admin information found in sessionStorage");
      }
    });

    // Logout function
    const logout = () => {
      sessionStorage.removeItem("authTokenAdmin"); // Remove auth token
      router.push("/login"); // Redirect to login page
      setTimeout(() => {
        window.location.reload(); // Prevent back navigation
      }, 100);
    };

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
              link: "/admin/teller/window" 
            },
            { 
              title: "Personnel", 
              icon: "groups", 
              link: "/admin/teller/tellers" 
            },
            { 
              title: "Service Types", 
              icon: "category", 
              link: "/admin/teller/types" 
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
                link: "/admin/settings" 
              },
              { 
                title: "Waiting Time",
                icon: "hourglass_top", 
                link: "/admin/waiting-time",
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

      toggleLeftDrawer() {
        leftDrawerOpen.value = !leftDrawerOpen.value;
      },
    };
  },
});
</script>
