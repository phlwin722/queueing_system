<template>
  <!-- Display connection status -->
  <div v-if="connectionStatus">{{ connectionStatus }}</div>

  <!-- QTable component to display the list of students -->
  <q-table :rows="students" :columns="columns" row-key="id" />
</template>

<script setup>
import { ref, onMounted, onUnmounted, inject } from "vue"; // Importing Vue Composition API functions

// Access the global pusher instance injected into the app
const pusher = inject("$pusher"); // Injecting $pusher that was set in the boot file

// Reactive variable to store the list of students
const students = ref([]); // `students` will store the list of students and trigger reactivity on change

// Define the columns for the QTable
const columns = ref([
  { name: "name", label: "Name", align: "left", field: "name" }, // Column for student names
  { name: "grade", label: "Grade", align: "left", field: "grade" }, // Column for student grades
]);

// Code that runs when the component is mounted to the DOM
onMounted(() => {
  // Create a new Pusher instance and subscribe to the channel
  const pusher = new Pusher("8d3b62bc5d67d22d3605", {
    // Pass your Pusher app key and options (e.g., cluster)
    cluster: "us2", // Specify the Pusher cluster
  });

  // Subscribe to the "students-channel" in Pusher
  const channel = pusher.subscribe("students-channel");

  // Listen for the 'StudentCreated' event on the channel
  channel.bind("StudentCreated", function (data) {
    // When a new student is created, push the student data into the `students` array
    students.value.push(data.student); // The `data.student` is added to the list of students
  });
});

// Cleanup when the component is unmounted from the DOM to prevent memory leaks
onUnmounted(() => {
  // Unsubscribe from the Pusher channel when the component is destroyed
  pusher.unsubscribe("students-channel");
});
</script>
