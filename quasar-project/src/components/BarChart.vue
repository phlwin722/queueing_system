<!-- components/BarChart.vue -->
<template>
    <div style="height: 300px; width:69%;" class="shadow-1 q-pa-sm">
      <Bar :data="chartData" :options="options" />
    </div>
  </template>
  
  <script setup>
  import { Bar } from 'vue-chartjs';
  import {
    Chart as ChartJS,
    Title,
    BarController,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale
  } from 'chart.js';
  import { computed } from 'vue';
  
  ChartJS.register(Title, BarController, Tooltip, Legend, BarElement, CategoryScale, LinearScale);
  
  const props = defineProps({
    cancelledPercent: {
      type: Number,
      default: 0,
    },
    finishedPercent: {
      type: Number,
      default: 0,
    },
  });
  
  // Computed property for chart data to ensure reactivity
  const chartData = computed(() => ({
    labels: ['Finished', 'Cancelled'],
    datasets: [
      {
        label: 'Customer Status Percentage (%)',
        data: [props.finishedPercent, props.cancelledPercent],
        backgroundColor: ['#4caf50', '#f44336'], // Red for cancelled, green for finished
      },
    ],
  }));
  
  const options = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          stepSize: 10, // Adjust step size for better visualization
        },
      },
    },
    plugins: {
      legend: {
        display: true,
        position: 'top',
      },
    },
  };
  </script>
  