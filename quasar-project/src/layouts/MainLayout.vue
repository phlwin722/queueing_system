<template>
  <q-layout view="hHh LpR fFf" class="shadow-2 rounded-borders">
    <q-header>
      <q-toolbar>
          <q-img 
        src="~assets/vrtlogowhite1.png" 
        alt="Logo" 
        fit="full" 
        :style="{ maxWidth: $q.screen.lt.sm ? '100px' : '160px' }"
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
      :class="$q.dark.isActive ? 'bg-accent' : 'bg-accent'"
    >
    <q-scroll-area class="fit" :horizontal-thumb-style="{ opacity: 0 }">
      <q-list padding>
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
                <q-icon name="account_circle" size="30px" color="primary" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ adminInformation?.Firstname + ' ' +  adminInformation?.Lastname }}</q-item-label>
              </q-item-section>
            </q-item>
          </template>

          <q-list>
            <q-item clickable v-ripple :to="'/admin/settings'">
              <q-item-section avatar>
                <q-icon name="settings" color="primary" />
              </q-item-section>
              <q-item-section>Account Settings</q-item-section>
            </q-item>

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

export default defineComponent({
  name: "MainLayout",

  setup() {
    const leftDrawerOpen = ref(false);
    const router = useRouter();
    const formattedString = ref();
    const drawer = ref(false);
    const miniState = ref(false);
    const adminInformation = ref(null)

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

    // Set an interval to update the time every second
    onMounted(() => {
      console.log(sessionStorage.getItem('adminInformation'));

      updateFormattedTime(); // Call it once on mount
      setInterval(updateFormattedTime, 1000); // Update every second
      adminInformation.value = JSON.parse(sessionStorage.getItem('adminInformation'))
    });

    // Logout function
    const logout = () => {
      sessionStorage.removeItem("authToken"); // Remove auth token
      router.push("/login"); // Redirect to login page
      setTimeout(() => {
        window.location.reload(); // Prevent back navigation
      }, 100);
    };

    return {
      leftDrawerOpen,
      formattedString,
      drawer,
      miniState,
      adminInformation,
      toggleMiniState,
      drawerClick,
      logout, // Make logout function available in the template

      linksList: [
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
              title: "Manage Tellers", 
              icon: "supervisor_account", 
              link: "/admin/teller/manage",  
            },
            { 
              title: "Service Types", 
              icon: "category", 
              link: "/admin/teller/servicetype" 
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
          title: "Waiting Time",
          icon: "hourglass_top", 
          link: "/admin/waiting-time",
        },
      ],

      toggleLeftDrawer() {
        leftDrawerOpen.value = !leftDrawerOpen.value;
      },
    };
  },
});
</script>
