<template>
  <q-layout view="lHh lpr lFf" class="shadow-2 rounded-borders">
    <q-header elevated style="height: 60px">
      <q-toolbar class="justify-between">
        <q-toolbar-title class="text-center">Teller</q-toolbar-title>
        <q-btn-dropdown v-model="menu" flat dense>
          <template v-slot:label>
            <q-avatar>
              <img src="https://cdn.quasar.dev/img/avatar.png" />
            </q-avatar>
          </template>
          <q-list>
            <q-item clickable v-close-popup @click="onItemClick">
              <q-item-section avatar>
                <q-avatar icon="logout" text-color="red" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-red">Log Out</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-btn-dropdown>
      </q-toolbar>
    </q-header>

    <q-page-container>
      <q-page class="scrollable-container">
        <div
          class="row q-col-gutter-md items-center justify-center q-mx-auto"
          style="min-height: 80vh; max-width: 1200px; padding-bottom: 80px"
        >
          <!-- Queue List Section -->
          <div class="col-12 col-md-6 flex justify-center q-px-sm">
            <q-card
              class="rounded-borders full-height scroll-area"
              style="
                min-height: 400px;
                width: 100%;
                max-width: 550px;
                overflow-y: auto;
              "
            >
              <q-card-section class="text-center">
                <p class="text-bold text-primary text-h5">WAITING QUEUE</p>
              </q-card-section>
              <q-separator />
              <q-card-section
                class="scroll-area custom-scrollbar"
                style="max-height: 50vh; overflow-y: auto; padding-bottom: 80px"
              >
                <q-chip
                  v-for="(queue, index) in queueList"
                  :key="index"
                  class="full-width q-mb-sm q-py-xl q-px-md row justify-between items-center"
                  square
                  :class="{
                    'bg-warning text-white': beingCatered === queue,
                    'bg-primary text-white': beingCatered !== queue,
                  }"
                >
                  <div class="column">
                    <span class="text-bold" style="font-size: 30px">{{
                      queue.qnumber
                    }}</span>
                  </div>
                  <q-space />
                  <q-btn-group spread class="btn-group-responsive">
                    <q-btn
                      unelevated
                      dense
                      color="red-5"
                      label="Cancel"
                      class="modern-btn"
                      @click="cancelQueue(queue)"
                    />
                    <q-btn
                      v-if="index === 0"
                      unelevated
                      dense
                      color="orange-5"
                      class="modern-btn"
                      :label="
                        waitTimes[queue] > 0
                          ? `Wait (${formatTime(waitTimes[queue])})`
                          : 'Wait'
                      "
                      @click="waitQueue(queue)"
                    />
                    <q-btn
                      unelevated
                      dense
                      color="green-5"
                      label="Cater"
                      class="modern-btn"
                      @click="caterQueue(queue)"
                    />
                  </q-btn-group>
                </q-chip>
                <div
                  v-if="queueList.length === 0"
                  class="text-grey text-center q-mt-md"
                >
                  No more customers
                </div>
              </q-card-section>
              <q-card-section class="q-pa-md text-center">
                <p class="text-grey-7">
                  Manage the queue efficiently to serve customers in a timely
                  manner.
                </p>
              </q-card-section>
            </q-card>
          </div>

          <!-- Now Serving Section -->
          <div class="col-12 col-md-6 flex justify-center q-px-sm">
            <q-card
              class="rounded-borders full-height scroll-area"
              style="
                min-height: 400px;
                width: 100%;
                max-width: 550px;
                overflow-y: auto;
                padding-bottom: 80px;
              "
            >
              <q-card-section
                class="flex flex-center"
                style="min-height: 200px"
              >
                <transition name="fade-scale" mode="out-in">
                  <h5
                    v-if="beingCatered"
                    key="serving"
                    class="text-bold text-orange-10"
                  >
                    NOW SERVING
                  </h5>
                  <h5 v-else key="current" class="text-bold text-grey-8">
                    QUEUE IN PROGRESS
                  </h5>
                </transition>
              </q-card-section>
              <q-separator />
              <q-card-section class="flex flex-center">
                <div
                  class="q-pa-md flex flex-center text-bold text-white now-serving-box"
                  :class="beingCatered ? 'bg-amber-9' : 'bg-grey-7'"
                >
                  <div v-if="beingCatered">
                    <div class="text-h4">{{ beingCatered.qnumber }}</div>
                    <div class="text-h6">{{ beingCatered.name }}</div>
                  </div>
                  <span v-else>No Queue in Progress</span>
                </div>
              </q-card-section>
              <q-card-section class="text-center q-pa-md">
                <q-btn
                  color="indigo-10"
                  label="Finish"
                  size="lg"
                  unelevated
                  class="rounded-borders finish-btn"
                  :disable="!beingCatered"
                  @click="finishQueue"
                />
              </q-card-section>
            </q-card>
          </div>
        </div>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script>
import { defineComponent, ref } from "vue";

export default defineComponent({
  name: "IndexPage",
  setup() {
    const queueList = ref([
      {
        name: "Sung Jin Woo",
        qnumber: "A001",
      },
      {
        name: "Kayden",
        qnumber: "A002",
      },
      {
        name: "Robin",
        qnumber: "A003",
      },
      {
        name: "Brook",
        qnumber: "A004",
      },
      {
        name: "Chopper",
        qnumber: "A005",
      },
      {
        name: "Mikasa",
        qnumber: "A006",
      },
      {
        name: "Nami",
        qnumber: "A007",
      },
      {
        name: "Kaido",
        qnumber: "A008",
      },
      {
        name: "Kaido",
        qnumber: "A009",
      },
      {
        name: "Kaido",
        qnumber: "A010",
      },
    ]);
    const finishedQueueList = ref([]);
    const beingCatered = ref(null);
    const waitTimes = ref({}); // Stores wait time per queue
    let waitIntervals = {}; // Stores interval references

    const caterQueue = (queue) => {
      beingCatered.value = queue;
      queueList.value = queueList.value.filter(
        (q) => q.qnumber !== queue.qnumber
      );

      // Stop any wait timer for this queue
      if (waitIntervals[queue]) {
        clearInterval(waitIntervals[queue]);
        delete waitTimes.value[queue];
        delete waitIntervals[queue];
      }
    };

    const cancelQueue = (queue) => {
      queueList.value = queueList.value.filter((q) => q !== queue);
      if (beingCatered.value === queue) beingCatered.value = null;

      // Stop the wait timer if it exists
      if (waitIntervals[queue]) {
        clearInterval(waitIntervals[queue]);
        delete waitTimes.value[queue];
        delete waitIntervals[queue];
      }
    };

    const waitQueue = (queue) => {
      if (waitIntervals[queue]) clearInterval(waitIntervals[queue]);

      waitTimes.value[queue] = 60; // 1-minute timer

      waitIntervals[queue] = setInterval(() => {
        if (waitTimes.value[queue] > 0) {
          waitTimes.value[queue]--;
        } else {
          clearInterval(waitIntervals[queue]);
          queueList.value = queueList.value.filter((q) => q !== queue);
          delete waitTimes.value[queue];
          delete waitIntervals[queue];
        }
      }, 1000);
    };

    const finishQueue = () => {
      if (beingCatered.value) {
        finishedQueueList.value.push(beingCatered.value);
        beingCatered.value = null;
      }
    };

    const formatTime = (seconds) => {
      const minutes = Math.floor(seconds / 60);
      const secs = seconds % 60;
      return `${minutes}:${secs < 10 ? "0" : ""}${secs}`;
    };

    return {
      queueList,
      finishedQueueList,
      beingCatered,
      waitTimes,
      caterQueue,
      cancelQueue,
      waitQueue,
      finishQueue,
      formatTime,
    };
  },
});
</script>

<style scoped>
.scrollable-container {
  overflow-y: auto;
  max-height: 100vh;
}

.btn-group-responsive {
  display: flex;
  flex-wrap: nowrap;
  justify-content: flex-end;
  gap: 4px;
}

.finish-btn {
  width: 100%;
  max-width: 200px;
  margin-top: 16px;
}

.scroll-area {
  overflow-y: auto;
  max-height: 100%;
  padding-bottom: 80px;
}
/* Modern button styling */
.modern-btn {
  font-size: 16px;
  padding: 10px 16px;
  border-radius: 8px;
  font-weight: bold;
  min-width: 90px;
  transition: all 0.3s ease-in-out;
}

/* Hover effect for better interaction */
.modern-btn:hover {
  filter: brightness(1.2);
  transform: scale(1.05);
}

/* Text animations */
.fade-scale-enter-active,
.fade-scale-leave-active {
  transition: opacity 0.4s, transform 0.3s ease-out;
}
.fade-scale-enter {
  opacity: 0;
  transform: scale(0.8);
}
.fade-scale-leave-to {
  opacity: 0;
  transform: scale(1.1);
}

/* Now Serving Box */
.now-serving-box {
  min-height: 140px;
  width: 100%;
  border-radius: 12px;
  font-size: 36px;
}

/* Custom Scrollbar */
/* Modern and Sleek Scrollbar */
.custom-scrollbar {
  overflow-y: auto;
  max-height: 300px;
  padding-right: 8px;
  scrollbar-width: thin; /* Firefox */
  scrollbar-color: #1c5d99 #f1f1f1; /* Thumb and track colors */
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px; /* Slimmer for a modern look */
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: #f8f9fa; /* Light grey for a soft contrast */
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, #1c5d99, #2980b9); /* Gradient effect */
  border-radius: 10px;
  transition: background 0.3s ease-in-out;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(180deg, #2980b9, #1c5d99); /* Hover effect */
}

/* Even Buttons */
.button-container .q-btn {
  flex: 1;
  max-width: 24%;
}

.rounded-borders {
  border-radius: 12px;
}

/* Prevent Scrolling */
html,
body {
  overflow: hidden;
  height: 100%;
}

/* Ensure the layout fills the screen properly */
.q-layout {
  height: 100vh; /* Full viewport height */
  overflow: hidden;
}
</style>
