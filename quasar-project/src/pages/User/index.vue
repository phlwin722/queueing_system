<template>
  <q-layout view="lHh lpr lFf" class="shadow-2 rounded-borders">
    <div class="absolute-center full-width full-height bg-grey-2">
      <div
        class="row wrap justify-center q-gutter-md q-pt-md"
        style="max-width: 600px; margin: auto"
      >
        <!-- User Queue Status -->
        <q-card
          class="col-12 full-width shadow-3 bg-white rounded-borders q-pa-md"
        >
          <q-card-section class="text-center">
            <div class="column items-center">
              <div class="text-bold text-grey-7 text-caption">
                Your Queue Number
              </div>
              <div class="text-h2 text-deep-orange-10 text-bold">
                {{ userQueue?.qnumber || "N/A" }}
              </div>
            </div>
          </q-card-section>
          <q-separator />
          <q-card-section class="row justify-around q-pa-md">
            <div class="column items-center">
              <div class="text-bold text-grey-7 text-caption">
                Currently Serving
              </div>
              <div class="text-h5 text-blue-10 text-bold">
                {{ beingCatered?.qnumber || "N/A" }}
              </div>
            </div>
            <div class="column items-center">
              <div class="text-bold text-grey-7 text-caption">
                Your Position
              </div>
              <div class="text-h5 text-indigo-10 text-bold">
                {{ queuePosition }}
              </div>
            </div>
          </q-card-section>

          <q-separator />

          <q-card-section class="text-center q-mt-md">
            <q-btn
              color="red-10"
              label="Cancel Queue"
              size="lg"
              unelevated
              class="rounded-borders full-width text-bold"
              :disable="!userQueue"
              @click="cancelUserQueue"
            />
          </q-card-section>
        </q-card>

        <!-- Queue List -->
        <q-card
          class="col-12 full-width shadow-3 bg-white rounded-borders q-pa-md"
        >
          <q-card-section class="text-center">
            <p class="text-bold text-primary text-h6">Queue List</p>
          </q-card-section>
          <q-separator />

          <q-card-section
            class="q-pa-md"
            style="max-height: 300px; overflow-y: auto"
          >
            <q-list bordered separator>
              <q-item v-for="(queue, index) in queueList" :key="queue.qnumber">
                <q-item-section>
                  <q-item-label class="text-bold text-grey-8"
                    >Queue: {{ queue.qnumber }}</q-item-label
                  >
                  <q-item-label class="text-grey-7"
                    >Name: {{ queue.name }}</q-item-label
                  >
                </q-item-section>
              </q-item>
            </q-list>
            <div
              v-if="queueList.length === 0"
              class="text-grey text-center q-mt-md"
            >
              No more customers
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-layout>
</template>

<script>
import { defineComponent, ref, computed } from "vue";

export default defineComponent({
  name: "UserQueuePage",
  setup() {
    const queueList = ref([
      { name: "Sung Jin Woo", qnumber: "A001" },
      { name: "Kayden", qnumber: "A002" },
      { name: "Robin", qnumber: "A003" },
      { name: "Brook", qnumber: "A004" },
      { name: "Chopper", qnumber: "A005" },
      { name: "Mikasa", qnumber: "A006" },
      { name: "Eren", qnumber: "A007" },
      { name: "Levi", qnumber: "A008" },
      { name: "Zoro", qnumber: "A009" },
      { name: "Sanji", qnumber: "A010" },
    ]);

    const beingCatered = ref(queueList.value.shift());
    const userQueue = ref(queueList.value[2]);

    // Compute user position in queue
    const queuePosition = computed(() => {
      if (!userQueue.value) return "N/A";

      if (userQueue.value.qnumber === beingCatered.value.qnumber) {
        return 1; // If user is being served, position is 1
      }

      const positionInQueue = queueList.value.findIndex(
        (q) => q.qnumber === userQueue.value.qnumber
      );

      // +2 to account for beingCatered
      return positionInQueue !== -1 ? positionInQueue + 2 : "N/A";
    });

    const cancelUserQueue = () => {
      if (userQueue.value) {
        queueList.value = queueList.value.filter(
          (q) => q.qnumber !== userQueue.value.qnumber
        );
        userQueue.value = null;
      }
    };

    return {
      queueList,
      beingCatered,
      userQueue,
      queuePosition,
      cancelUserQueue,
    };
  },
});
</script>

<style scoped>
.rounded-borders {
  border-radius: 16px;
}
.shadow-3 {
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
}
</style>
