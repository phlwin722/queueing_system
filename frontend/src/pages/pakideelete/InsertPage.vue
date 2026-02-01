<template>

    <!-- Input for student's name -->
    <q-input v-model="name" label="Name" />

    <!-- Input for student's grade -->
    <q-input v-model="grade" label="Grade" />

    <!-- Button to submit the data -->
    <q-btn label="Insert" @click="insertStudent" />
  
</template>

<script setup>
import { ref } from 'vue'; // Import Vue's 'ref' to create reactive variables
import { $axios } from 'boot/app'; // Import Axios instance configured in Quasar

// Create reactive variables to bind to the inputs
const name = ref('');
const grade = ref('');

// Function to insert student data
const insertStudent = async () => {
  try {
    // Send a POST request to Laravel to insert student data
    const { data } = await $axios.post('/src-insert', {
      name: name.value, // Pass the 'name' value from the form input
      grade: grade.value // Pass the 'grade' value from the form input
    });

    // Log success message in the console
    console.log(data.message);

    // Clear the input fields after insertion
    name.value = '';
    grade.value = '';
  } catch (error) {
    // Log any errors to the console
    console.error('Error inserting student:', error);
  }
};
</script>
