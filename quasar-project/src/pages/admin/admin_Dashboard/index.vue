<template>
  <q-page class="q-px-lg" style="height: auto; min-height: unset;">

    <div class="q-my-md bg-white q-pa-sm shadow-1">
    <q-breadcrumbs 
                class="q-mx-sm"
                >
                <q-breadcrumbs-el icon="home" />
                <q-breadcrumbs-el label="Dashboard" icon="dashboard" to="/admin/dashboard" />
            </q-breadcrumbs>
      </div>

    <!-- Queue Summary Cards -->
    <div class="row q-gutter-md">
      <!-- Total Customers -->
      <q-card class="stat-card bg-white text-secondary">
        <q-card-section>
          <div>
          <q-icon name="people" size="24px" class="text-warning q-mr-xs"/>
          Number of Customers</div>
          <div class="text-h4 q-mt-lg">{{ total }}</div>
        </q-card-section>
      </q-card>

      <!-- Finished Customers -->
      <q-card class="stat-card bg-white text-secondary">
        <q-card-section>
          <div>
          <q-icon name="check_circle" size="24px" class="text-positive q-mr-xs"/>
          Finished
        </div>
          <div class="text-h4 q-mt-lg">{{ finishedCount }}</div>
        </q-card-section>
      </q-card>

      <!-- Cancelled Customers -->
      <q-card class="stat-card bg-white text-secondary">
        <q-card-section>
          <div>
          <q-icon name="cancel" size="24px" class="text-negative q-mr-xs"/>
          Cancelled</div>
          <div class="text-h4 q-mt-lg">{{ cancelledCount }}</div>
        </q-card-section>
      </q-card>

      
      <BarChart
        :cancelledPercent="cancelledPercent"
      ></BarChart>

      <div class="col-4 q-mb-xl">
    <q-card>
      <q-card-section class="bg-primary">
        <div class="text-accent">Teller Workstations</div>
      </q-card-section>
      <q-separator />
      <q-table
          flat
          bordered
          dense
          virtual-scroll
          :rows="rowsWorkStation"
          :columns="columnsWorkStation"
          row-key="id"
          
        >
          <template v-slot:body-cell-status="props">
            <q-td :props="props">
               <!-- Apply color based on the status -->
              <q-badge :color="props.row.status === 'Available' ? 'green' : 'red'">
                {{ props.row.status }}
              </q-badge>
            </q-td>
          </template>
      </q-table>
    </q-card>
  </div>

      <!-- Survey Charts Section -->
      <q-card class="q-mb-xl" style="width: 350px; margin-top: -440px; margin-left: auto;">
          <p class="text-secondary text-center q-mt-sm">
            Survey Analysis
          </p>
        <div class="row q-col-gutter-xs q-mt-md q-mb-lg shadow-1 q-px-md" style="flex-direction: column; gap: .5rem;">
        <!-- Rating Overview -->
        <div class="col-12">
            <div>Rating Overview</div>
            <canvas ref="ratingChart" height="200"></canvas>
        </div>

        <q-separator/>

        <!-- Ease of Use -->
        <div class="col-10">
            <div>Ease of Use</div>
            <canvas ref="easeOfUseChart" style="margin-left: 50px;"></canvas>
        </div>

        <q-separator/>

        <!-- Waiting Time -->
        <div class="col-12">
            <div>Waiting Time Satisfaction</div>
            <canvas ref="waitingTimeChart" height="200"></canvas>
        </div>
      </div>
      </q-card>
  </div>

  </q-page>
</template>

<script>

import { 
  defineComponent ,
  ref,
  computed,
  onMounted,
  onUnmounted
} from 'vue'


import {
  $axios,
  $notify,
  Dialog
} from 'boot/app'

import BarChart from 'components/BarChart.vue';
import Chart from 'chart.js/auto'

export default {
  name: "QueueDashboard",
  components: { BarChart },
  setup() {
    const rows = ref([]);
    const cancelledPercent = ref(0);
    const finishedPercent = ref(0);
    const cancelledCount = ref (0)
    const finishedCount = ref(0)
    const total = ref(0)
    const dateToday = new Date().toISOString().slice(0, 10);
    const ratingChart = ref(null)
    const easeOfUseChart = ref(null)
    const waitingTimeChart = ref(null)


    const getTableData = async () => {
          try{           
            const { data } = await $axios.post('/admin/queue-logs',{
              date: dateToday
            })
            
            rows.value.splice(0, rows.value.length, ...data.rows);
            computePercentages();
          }catch(error){
            console.log(error);
          }
        }
    const updateAllServingTime = async () => {
      try {
        const { data } = await $axios.post('/teller/update-all-serving-time');
        console.log(data.status);
        if (data.status === 'success') {
          $notify("positive", "check", "Updated Successfully.");
        } else {
          $notify("negative", "error", "Update Error.");
        }
      } catch (error) {
        console.error(error);
        $notify("negative", "error", "Update Errors.");
      }
    }

        const computePercentages = () => {
          total.value = rows.value.length;
          if (total === 0) {
            cancelledPercent.value = 0;
            finishedPercent.value = 0;
            return;
          }

          cancelledCount.value = rows.value.filter(row => row.status === 'cancelled').length;
          finishedCount.value = rows.value.filter(row => row.status === 'finished').length;

          cancelledPercent.value = ((cancelledCount.value / total.value) * 100).toFixed(2);
          finishedPercent.value = ((finishedCount.value / total.value) * 100).toFixed(2);

        };

       const rowsWorkStation = ref([]);
          const columnsWorkStation = ([
            {
              name:'name',
              label:'Name',
              align:'left',
              field: 'teller_name',
              sortable: true
            },
            {
              name: 'status',
              label: 'Status',
              align: 'left',
              field: 'status',
              sortable: true
            }
          ])

         // Fetch the information of teller if assigned
         const fetchWorkStation = async () => {
            try {
              const { data } = await $axios.post('/tellers/index');
              // Process rows data to assign the correct status and full name
              rowsWorkStation.value = data.rows.map(row => {
                return {
                  id: row.id,
                  // If type_id is null, status is 'Available', otherwise 'Assigned'
                  status: row.type_id === null ? 'Available' : 'Assigned',
                  // Combine teller_firstname and teller_lastname for the full name
                  teller_name: `${row.teller_firstname} ${row.teller_lastname}`
                };
              });
            } catch (error) {
              if (error.response.status === 422) {
                console.log(error.message);
              }
            }
          };

        let dataTimeout;
        let workTimeout;

        const optimizedFetchData = async () => {
          await getTableData()
          dataTimeout = setTimeout(optimizedFetchData, 5000); // Recursive Timeout
        };

          const optimizedFetchWork = async () => {
            await fetchWorkStation()
            workTimeout = setTimeout(optimizedFetchWork, 5000); // Recursive Timeout
        };

        const renderSurveyCharts = async () => {
          try {
            const { data } = await $axios.get('/admin/survey-stats') // adjust your endpoint if needed

            // Chart 1: Ratings
            new Chart(ratingChart.value, {
              type: 'bar',
              data: {
                labels: Object.keys(data.ratings),
                datasets: [{
                  label: 'Rating',
                  data: Object.values(data.ratings),
                  backgroundColor: '#42a5f5'
                }]
              },
              options: { responsive: true }
            })

            // Chart 2: Ease of Use
            new Chart(easeOfUseChart.value, {
              type: 'pie',
              data: {
                labels: Object.keys(data.ease_of_use),
                datasets: [{
                  data: Object.values(data.ease_of_use),
                  backgroundColor: ['#4caf50', '#f44336']
                }]
              },
              options: { responsive: true }
            })

            // Chart 3: Waiting Time
            new Chart(waitingTimeChart.value, {
            type: 'bar',
            data: {
              labels: Object.keys(data.waiting_time_satisfaction), // X-axis labels
              datasets: [{
                label: 'Waiting Time',
                data: Object.values(data.waiting_time_satisfaction), // Y-axis values
                backgroundColor: '#ff9800'
              }]
            },
            options: {
              responsive: true,
              plugins: {
                tooltip: {
                  callbacks: {
                    // Modify the title (header) of the tooltip to show a custom label
                    title: function(tooltipItem) {
                      let xValue = tooltipItem[0].label; // Get the X-axis value (category)
                      let labelText = '';

                      // Map the X-axis value to a custom label
                      switch(parseInt(xValue)) {
                        case 1:
                          labelText = 'Very Dissatisfied';
                          break;
                        case 2:
                          labelText = 'Dissatisfied';
                          break;
                        case 3:
                          labelText = 'Neutral';
                          break;
                        case 4:
                          labelText = 'Satisfied';
                          break;
                        case 5:
                          labelText = 'Very Satisfied';
                          break;
                        default:
                          labelText = 'Unknown'; // In case there's an unexpected value
                      }

                      // Return the custom title with the label
                      return labelText;
                    },
                    // Optionally, you can modify the label part of the tooltip (if needed)
                    label: function(tooltipItem) {
                      return `Waiting Time: ${tooltipItem.raw}`; // Show the raw value as label
                    }
                  }
                }
              }
            }
          });


          } catch (error) {
            console.error('Survey chart fetch failed:', error)
          }
        }


        onMounted(() => {
          optimizedFetchData()
          optimizedFetchWork()
          updateAllServingTime()
          renderSurveyCharts()
        })

        onUnmounted(() => {
          clearTimeout(dataTimeout);
          clearTimeout(workTimeout);
        });

    return {
      columnsWorkStation,
      rowsWorkStation,
      fetchWorkStation,
      cancelledPercent,
      finishedPercent,
      cancelledCount,
      finishedCount,
      total,
      ratingChart,
      easeOfUseChart,
      waitingTimeChart
    };
  },
};

</script>

<style scoped>
.stat-card {
  width: 272.5px; /* Adjust width to match your image */
  height: 125px; /* Adjust height to match your image */
  border-radius: 10px; /* Rounded corners */
  display: flex;
}

.q-table {
  overflow: hidden !important;
}

</style>
