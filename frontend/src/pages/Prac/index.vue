<template>
  <div class="q-pa-md">
    <!-- Draggable items -->
    <q-item
      v-for="(person, index) in people"
      :key="person.id"
      draggable="true"
      @dragstart="onDragStart(person)"
      class="bg-grey-2 q-mb-sm"
    >
      <q-item-section>
        {{ person.name }}
      </q-item-section>
    </q-item>

    <!-- Drop area -->
    <div
      ref="dropArea"
      class="bg-blue-1 q-pa-md q-mt-lg text-center"
      style="min-height: 120px;"
      @dragover.prevent
      @drop="onDrop"
    >
      <div v-if="droppedPerson">
        You dropped: <strong>{{ droppedPerson.name }}</strong>
        <q-btn
          color="primary"
          label="Show ID"
          class="q-mt-sm"
          @click="showId"
        />
      </div>
      <div v-else>
        Drop a name here
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const people = ref([
  { id: 1, name: 'Alice' },
  { id: 2, name: 'Bob' },
  { id: 3, name: 'Charlie' }
]);

const droppedPerson = ref(null);
let draggedData = null;

const onDragStart = (person) => {
  draggedData = person;
};

const onDrop = () => {
  droppedPerson.value = draggedData;
  // Accessing the drop area using $refs
  console.log('Dropped onto:', $refs.dropArea);
};

const showId = () => {
  if (droppedPerson.value) {
    alert(`ID: ${droppedPerson.value.id}`);
  }
};
</script>
