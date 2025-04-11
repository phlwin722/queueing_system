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
        v-if="!$q.fullscreen.isActive"
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
      <!-- <div v-if="newFormattedTime >= newTime && newFormattedTime < originalFromBreak" class="col q-mb-xl">
        <q-card class="q-pa-md q-mx-auto" style="max-width: 400px; border-left: 6px solid #1c5d99;">
        <q-card-section class="text-center">
          <q-icon name="access_time" size="40px" style="color: #1c5d99;" class="q-mb-sm" />
          <div class="text-h5 text-weight-bold" style="color: #1c5d99;">Break Time Soon...</div>
        </q-card-section>
        </q-card>
      </div> -->
      <div v-if="newFormattedTime >= newTime && formattedCurrentTime < toBreak" class="col q-mb-xl">
        <q-card class="q-pa-xl q-mx-auto" style="max-width: 600px; border-left: 8px solid #1c5d99;">
              <q-card-section class="text-center">
                <q-icon name="access_time" size="60px" style="color: #1c5d99;" class="q-mb-md" />
                <div class="text-h4 text-weight-bold" style="color: #1c5d99;">Break Time</div>
                <div class="text-subtitle1 text-grey-7">We’ll be on break</div>
              </q-card-section>

              <q-separator spaced />

              <q-card-section class="row justify-around items-center q-pt-lg">
                <div class="column items-center">
                  <q-icon name="schedule" size="32px" style="color: #1c5d99;" />
                  <div class="text-caption text-grey-7 q-mt-xs">From</div>
                  <div class="text-h5 q-mt-xs">{{ formatTo12Hour(fromBreak) }}</div>
                </div>
                <q-icon name="arrow_forward" size="32px" color="grey-6" />
                <div class="column items-center">
                  <q-icon name="schedule" size="32px" style="color: #1c5d99;" />
                  <div class="text-caption text-grey-7 q-mt-xs">To</div>
                  <div class="text-h5 q-mt-xs">{{ formatTo12Hour(toBreak) }}</div>
                </div>
              </q-card-section>
            </q-card>
      </div>
      <div v-else class="col q-mb-xl">
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
    const fromBreak = ref("");
    const toBreak = ref("");
    const formattedCurrentTime = ref("");

    // Fetch a new QR code link from the backend
    const fetchQrCode = async () => {
      try {
        const response = await $axios.post("/generate-qr");

        const fullUrl = response.data.qr_code_url; // Example: "http://192.168.1.164:8000/scan-qr/abcd1234"
        const token = fullUrl.slice(26); // Extract token from URL

        localStorage.setItem("token", token);
        registrationLink.value =
          "192.168.0.164:8080/customer-register/" + token;

        console.log("Token:", token);
      } catch (error) {
        console.error("Error fetching QR code:", error);
      }
    };
    const newTime = ref("")
    const newFormattedTime = ref("")
    const originalFromBreak = ref("")
    const fetchBreakTime = async () => {
      try {
        const { data } = await $axios.post("/admin/fetch_break_time");
        // ✅ Correctly assign break start & end times
        fromBreak.value = data.dataValue.break_from.slice(0, 5); // Start of break
        toBreak.value = data.dataValue.break_to.slice(0, 5); // End of break
        // ✅ Get current time in HH:mm format
        const currentTime = new Date();
        const currentHour = currentTime.getHours().toString().padStart(2, "0");
        const currentMinutes = currentTime.getMinutes().toString().padStart(2, "0");
        formattedCurrentTime.value = `${currentHour}:${currentMinutes}`;
        const totalMinutes = parseTime(fromBreak.value)-5
        newTime.value = formatTime(totalMinutes);
        const OrgtotalMinutes = parseTime(fromBreak.value)
        originalFromBreak.value = formatTime(OrgtotalMinutes);
        const totalFormatMinutes = parseTime(formattedCurrentTime.value)
        newFormattedTime.value = formatTime(totalFormatMinutes);
      } catch (error) {
        console.error("Error fetching break time:", error);
      }
    }
    

    const formatTo12Hour = (time) => {
        const [hour, minute] = time.split(":").map(Number);
        const ampm = hour >= 12 ? "PM" : "AM";
        const formattedHour = hour % 12 || 12; // Convert 0 or 12 to 12, 13 to 1, etc.
        return `${formattedHour}:${minute.toString().padStart(2, "0")} ${ampm}`;
      };

      function parseTime(timeString) {
        // Make sure we're working with a string (access .value if it's a Vue ref)
        const timeStr = typeof timeString === 'object' && 'value' in timeString 
            ? timeString.value 
            : timeString;
        
        const [hours, minutes] = timeStr.split(':').map(Number);
        return hours * 60 + minutes;
    }

    function formatTime(totalMinutes) {
        const hours = Math.floor(totalMinutes / 60) % 24;
        const minutes = totalMinutes % 60;
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
    }

    watch(
      () => $q.fullscreen.isActive,
      (val) => {
        console.log(val ? "In fullscreen now" : "Exited fullscreen");
      }
    );
    let Qrtimeout;
    const optimizedFecthQr = async () => {
        await fetchQrCode()
        Qrtimeout = setTimeout(optimizedFecthQr, 3000); // Recursive Timeout
    };

    onMounted(() => {
      optimizedFecthQr();
      fetchBreakTime();
      intervalId = setInterval(() => {
        fetchBreakTime();
      }, 30000); 
    });

    onUnmounted(() => {
      clearTimeout(Qrtimeout);
    });

    return { registrationLink,
       icons,
        fromBreak,
        toBreak,
        formattedCurrentTime,
        formatTo12Hour,
        newTime,
        newFormattedTime,
        originalFromBreak,
       };
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
