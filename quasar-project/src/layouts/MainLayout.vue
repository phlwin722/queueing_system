<template>
  <q-layout class="shadow-2 rounded-borders">
    <q-header elevated>
      <q-toolbar>
        <q-toolbar-title>Queueing</q-toolbar-title>
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
      :class="$q.dark.isActive ? 'bg-grey-9' : 'bg-grey-3'"
    >
    <q-scroll-area class="fit" :horizontal-thumb-style="{ opacity: 0 }">
        <q-list padding>
          <template v-for="(item, index) in linksList" :key="index">
            <!-- Check if the item has children (Dropdown) -->
            <q-expansion-item
              v-if="item.children"
              expand-separator
              :icon="item.icon"
              :label="item.title"
            >
              <q-item
                v-for="(child, childIndex) in item.children"
                :key="childIndex"
                clickable
                v-ripple
                :to="child.link"
              >
                <q-item-section class="text-center">{{ child.title }}</q-item-section>
              </q-item>
            </q-expansion-item>

            <!-- Normal Menu Item
              icon: server_person
            -->
            <q-item v-else clickable v-ripple :to="item.link" exact>
              <q-item-section avatar>
                <q-icon :name="item.icon" :color="item.link === $route.path ? 'secondary' : 'secondary'" />
              </q-item-section>
              <q-item-section v-if="!miniState">{{
                item.title
              }}</q-item-section>
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
      <div class="q-pa-md bg-grey-3 absolute-bottom full-width">
        <q-btn-dropdown class="full-width" flat no-caps>
          <template v-slot:label>
            <q-item clickable>
              <q-item-section avatar>
                <q-avatar size="40px">
                  <img src="https://cdn.quasar.dev/img/avatar.png" alt="Admin" />
                </q-avatar>
              </q-item-section>
            <q-item-section>
            <q-item-label> Rafael </q-item-label>
          </q-item-section>
        </q-item>
      </template>

        <q-list>
            <q-item clickable v-ripple @click="logout">
              <q-item-section avatar><q-icon name="logout" color="red" /></q-item-section>
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
      updateFormattedTime(); // Call it once on mount
      setInterval(updateFormattedTime, 1000); // Update every second
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
      toggleMiniState,
      drawerClick,
      logout, // Make logout function available in the template

      linksList: [
      {
        title: "Dashboard",
        icon: "home",
        link: "/admin/dashboard",
        },
        {
          title: "Teller",
          icon: "person",
          children: [
            { 
              title: "Manage Tellers", 
              link: "/admin/teller/manage",  
            },
            { 
              title: "Service Types", 
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
      ],

      toggleLeftDrawer() {
        leftDrawerOpen.value = !leftDrawerOpen.value;
      },
     };
  },
});
</script>
