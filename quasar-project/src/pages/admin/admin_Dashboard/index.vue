<template>
  <q-page class="q-px-sm">

    <!-- Breadcrumb -->
    <div class="q-ma-md bg-white q-pa-sm shadow-1 ">
      <q-breadcrumbs class="q-mx-sm">
        <q-breadcrumbs-el icon="home" />
        <q-breadcrumbs-el label="Dashboard" icon="dashboard" to="/admin/dashboard" />
      </q-breadcrumbs>
    </div>

    <!-- Summary Cards + Survey Chart -->
    <div class="q-px-md row q-col-gutter-md">
      <!-- Summary Cards -->
      <div class="col-xs-12 col-md-9">
        <div class="row q-col-gutter-md">
          <div class="col-xs-12 col-sm-6 col-md-4">
            <q-card class="stat-card bg-white text-secondary">
              <q-card-section>
                <div><q-icon name="people" size="24px" class="text-warning q-mr-xs" /> Number of Customers</div>
                <div class="text-h4 q-mt-lg">{{ total }}</div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-xs-12 col-sm-6 col-md-4">
            <q-card class="stat-card bg-white text-secondary">
              <q-card-section>
                <div><q-icon name="check_circle" size="24px" class="text-positive q-mr-xs" /> Finished</div>
                <div class="text-h4 q-mt-lg">{{ finishedCount }}</div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-xs-12 col-sm-6 col-md-4">
            <q-card class="stat-card bg-white text-secondary">
              <q-card-section>
                <div><q-icon name="cancel" size="24px" class="text-negative q-mr-xs" /> Cancelled</div>
                <div class="text-h4 q-mt-lg">{{ cancelledCount }}</div>
              </q-card-section>
            </q-card>
          </div>
        </div>

        <!-- Bar Chart Section -->
        <q-card class="q-mt-md">
        <div class="col-12">
          <BarChart :cancelledPercent="cancelledPercent" :finishedPercent="finishedPercent" />
        </div>
        </q-card>

        <!-- Teller Table Section -->
      <div class="row q-col-gutter-sm q-mt-sm">
        <div class="col-xs-6 col-md-12">
          <q-card>
            <q-card-section class="">
              <div class="text-primary">Teller Workstations</div>
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
                  <q-badge :color="props.row.status === 'Available' ? 'green' : 'red'">
                    {{ props.row.status }}
                  </q-badge>
                </q-td>
              </template>
            </q-table>
          </q-card>
        </div>
      </div>

      

      </div>

      <!-- Survey Chart -->
      <div class="col-xs-12 col-md-3">
        <q-card class="q-mb-xl">
          <q-card-section>
            <div class="text-primary text-center">Survey Analysis</div>
          </q-card-section>
          <div class="q-pa-md">
            <div class="q-mb-md">
              <div>Rating Overview</div>
              <canvas ref="ratingChart" height="150"></canvas>
            </div>
            <q-separator />
            <div class="q-mb-md q-mt-sm">
              <div>Ease of Use</div>
              <canvas ref="easeOfUseChart" height="150"></canvas>
            </div>
            <q-separator />
            <div class="q-mb-md q-mt-sm">
              <div>Waiting Time Satisfaction</div>
              <canvas ref="waitingTimeChart" height="150"></canvas>
            </div>
          </div>
        </q-card>
      </div>
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
          console.log("Updated Successfully.")
        } else {
          console.log("Update error.")
        }
      } catch (error) {
        console.error(error);
        console.log("Update error.")
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
                  backgroundColor: '#4caf50'
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
                backgroundColor: '#F2C037'
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


</style>
