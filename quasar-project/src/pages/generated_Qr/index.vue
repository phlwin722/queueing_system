<template>
  <q-layout
    :style="{
      overflow: $q.fullscreen.isActive ? 'hidden' : 'auto',
      width: '100%',
      height: '100vh',
    }"
    style="overflow-x: hidden"
  >
    <div
      style="height: 30vh; width: 100%; position: absolute; z-index: -1"
      class="bg-primary"
    ></div>

    <div class="q-pa-md">
      <q-btn
        flat
        round
        dense
        class="q-mr-sm"
        color="white"
        style="min-width: 32px; width: 32px; height: 32px; position: absolute"
        @click="$q.fullscreen.toggle()"
        :icon="$q.fullscreen.isActive ? 'fullscreen_exit' : 'fullscreen'"
      />
    </div>

    <!-- Main Row Container -->
    <div
      class="column items-center justify-center"
      style="min-height: 100vh; padding-top: 2em"
    >
      <q-img
        src="../../assets/vrtlogowhite1.png"
        alt="Logo"
        fit="full"
        :style="{ maxWidth: $q.screen.lt.sm ? '250px' : '300px' }"
        class="q-mb-xl"
      />
      <!-- First Item -->
      <div class="col q-mb-xl">
        <q-card class="shadow-2" style="width: 350px; text-align: center">
          <q-card-section>
            <div class="text-h6">Scan to Join the Queue</div>
          </q-card-section>

          <q-card-section>
            <qrcode-vue
              v-if="registrationLink"
              :value="registrationLink"
              :size="200"
              level="M"
            />
            <div v-else>Generating QR Code...</div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Second Item -->
      <div class="col q-px-none">
        <div class="row flex flex-wrap justify-evenly q-mb-xl">
          <div
            v-for="(iconss, index) in icons"
            :key="index"
            :style="{ width: '130px' }"
            class="col flex flex-center q-mx-xl column"
          >
            <q-icon
              :name="iconss.icon"
              :color="iconss.color"
              :style="{ width: '120px' }"
              size="100px"
            />
            <div>
              <q-card flat class="q-mt-sm q-pa-xs text-center bg-black">
                <q-card-section class="q-pa-none text-white">
                  {{ iconss.label }}
                </q-card-section>
              </q-card>
              <q-card
                flat
                :style="{ width: '130px' }"
                class="q-mt-sm q-pa-xs text-center bg-transparent"
              >
                <p class="q-mb-none" style="margin: 0; height: 100px">
                  {{ iconss.description }}
                </p>
              </q-card>
            </div>
          </div>
        </div>
      </div>
    </div>
  </q-layout>
</template>

<script>
import { ref, onMounted, onUnmounted } from "vue";
import QrcodeVue from "qrcode.vue";
import { $axios } from "boot/app";
import { useQuasar } from "quasar";
import { watch } from "vue";

export default {
  components: { QrcodeVue },
  setup() {
    const registrationLink = ref(null);
    let intervalId = null; // Store interval ID for cleanup
    const icons = ref([
      {
        icon: "qr_code",
        color: "primary",
        label: "1. Scan",
        description: "Scan the QR Code to join the E-Queue",
      },
      {
        icon: "badge",
        color: "primary",
        label: "2. Enter",
        description: "Enter the necessary details",
      },
      {
        icon: "phone_iphone",
        color: "primary",
        label: "3. Receive",
        description: "Will give an E-Ticket and update through email",
      },
      {
        icon: "directions_walk",
        color: "primary",
        label: "4. Return",
        description: "It will notify you when its your turn",
      },
    ]);
    const $q = useQuasar();

    // Fetch a new QR code link from the backend
    const fetchQrCode = async () => {
      try {
        const response = await $axios.post("/generate-qr");

        const fullUrl = response.data.qr_code_url; // Example: "http://192.168.1.164:8000/scan-qr/abcd1234"
        const token = fullUrl.slice(26)// Extract token from URL

        localStorage.setItem("token", token);
        registrationLink.value =
          "192.168.0.165:8080/customer-register/" + token;

        console.log("Token:", token);
      } catch (error) {
        console.error("Error fetching QR code:", error);
      }
    };

    watch(
      () => $q.fullscreen.isActive,
      (val) => {
        console.log(val ? "In fullscreen now" : "Exited fullscreen");
      }
    );

    onMounted(() => {
      fetchQrCode(); // Fetch QR code immediately on mount
      intervalId = setInterval(fetchQrCode, 5000); // Run every 5 seconds
    });

    onUnmounted(() => {
      if (intervalId) {
        clearInterval(intervalId); // Clear interval when component is destroyed
      }
    });

    return { registrationLink, icons };
  },
};
</script>

<style lang="scss" scoped>
.full-page {
  height: 100vh;
  width: 100vw;
  background: linear-gradient(to bottom, $primary 50%, white 50%);
}
</style>
