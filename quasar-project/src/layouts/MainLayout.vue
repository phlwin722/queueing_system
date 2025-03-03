<template>
  <q-layout view="lHh Lpr lFf">
    <q-header elevated>
      <q-toolbar>
        <q-btn flat dense round icon="menu" aria-label="Menu" @click="toggleLeftDrawer" />

        <q-toolbar-title>Queueing</q-toolbar-title>
        <div>{{ formattedString }}</div>
      </q-toolbar>
    </q-header>

    <q-drawer v-model="leftDrawerOpen" show-if-above bordered>
      <q-list>
        <template v-for="(item, index) in linksList">
          <q-item clickable v-ripple :to="item.link" exact v-if="item.title !== 'Logout'">
            <q-item-section avatar>
              <q-icon :color="item.color" :name="item.icon" />
            </q-item-section>
            <q-item-section>{{ item.title }}</q-item-section>
          </q-item>

          <!-- Logout button -->
          <q-item clickable v-ripple @click="logout" v-if="item.title === 'Logout'">
            <q-item-section avatar>
              <q-icon name="logout" />
            </q-item-section>
            <q-item-section>Logout</q-item-section>
          </q-item>
        </template>
      </q-list>
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

    // Function to update the time
    const updateFormattedTime = () => {
      const timeStamp = Date.now();
      formattedString.value = date.formatDate(timeStamp, "YYYY-MM-DD h:mm:ss A");
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
      linksList: [
        { 
          title: "Dashboard", 
          icon: "dashboard", 
          link: "/admin/dashboard" 
        },
        { 
          title: "QRcode", 
          icon: "qr_code", 
          link: "/admin/qr_code" 
        },
        { 
          title: "Logout", 
          icon: "logout" 
        }
      ],
      leftDrawerOpen,
      formattedString,
      toggleLeftDrawer() {
        leftDrawerOpen.value = !leftDrawerOpen.value;
      },
      logout, // Make logout function available in the template
    };
  },
});
</script>
