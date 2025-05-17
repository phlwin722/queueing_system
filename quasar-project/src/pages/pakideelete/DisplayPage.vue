<template>
  <!-- Display connection status -->
  <div v-if="connectionStatus">{{ connectionStatus }}</div>

  <!-- QTable component to display the list of students -->
  <q-table :rows="students" :columns="columns" row-key="id" />
</template>

<script>
import { defineComponent, onMounted, ref, onUnmounted, inject } from "vue"; 
import { $axios } from "boot/app"; // Adjust path if needed

export default defineComponent({
  setup() {
    // Access the global pusher instance injected into the app
    const pusher = inject("$pusher"); // Injecting $pusher that was set in the boot file

    // Reactive variable to store the list of students
    const students = ref([]); // `students` will store the list of students and trigger reactivity on change

    // Define the columns for the QTable
    const columns = ref([
      { name: "name", label: "Name", align: "left", field: "name" },
      { name: "grade", label: "Grade", align: "left", field: "grade" },
    ]);

    // Display function to fetch initial data
    const display = async () => {
      try {
        const { data } = await $axios.post("/src-fetch");
        // Assuming the response contains a 'students' array
        students.value = data.row; // Make sure you're assigning to `students.value`
      } catch (error) {
        console.error("Error fetching students:", error);
      }
    };

    // Code that runs when the component is mounted to the DOM
    onMounted(() => {
      // Create a new Pusher instance and subscribe to the channel
      const pusher = new Pusher("8d3b62bc5d67d22d3605", {
        cluster: "us2",
      });

      const channel = pusher.subscribe("students-channel");

      // Listen for the 'StudentCreated' event on the channel
      channel.bind("StudentCreated", (data) => {
        if (data) {
          // Update the students list with the new student data
          alert('hahaa')
        students.value.push(data.student); // Add the new student to the list
        }  
      });

      display(); // Fetch the initial list of students
    });

    // Cleanup when the component is unmounted from the DOM to prevent memory leaks
    onUnmounted(() => {
      pusher.unsubscribe("students-channel");
    });

    return {
      students, // Expose the `students` array to the template
      columns,  // Expose the `columns` array to the template
      display,  // Expose the `display` function to the template
    };
  },
});
</script>
