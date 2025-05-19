<template>
  <q-page class="q-px-sm q-pb-md">
    <!-- Breadcrumb -->
    <div class="q-ma-md bg-white q-pa-md shadow-1" style="position: relative">
      <q-breadcrumbs class="q-mx-sm">
        <q-breadcrumbs-el icon="home" />
        <q-breadcrumbs-el
          label="Dashboard"
          icon="dashboard"
          to="/admin/dashboard"
        />
        <q-select
          v-if="!adminMangerInformation"
          style="width: 250px; position: absolute; right: 10px"
          outlined
          v-model="branch_name"
          label="Branch name"
          hide-bottom-space
          dense
          emit-value
          map-options
          :options="branchList"
          option-label="branch_name"
          option-value="id"
        />
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
                <div>
                  <q-icon
                    name="people"
                    size="24px"
                    class="text-warning q-mr-xs"
                  />
                  Number of Customers
                </div>
                <div class="text-h4 q-mt-lg">{{ total }}</div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-xs-12 col-sm-6 col-md-4">
            <q-card class="stat-card bg-white text-secondary">
              <q-card-section>
                <div>
                  <q-icon
                    name="check_circle"
                    size="24px"
                    class="text-positive q-mr-xs"
                  />
                  Finished
                </div>
                <div class="text-h4 q-mt-lg">{{ finishedCount }}</div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-xs-12 col-sm-6 col-md-4">
            <q-card class="stat-card bg-white text-secondary">
              <q-card-section>
                <div>
                  <q-icon
                    name="cancel"
                    size="24px"
                    class="text-negative q-mr-xs"
                  />
                  Cancelled
                </div>
                <div class="text-h4 q-mt-lg">{{ cancelledCount }}</div>
              </q-card-section>
            </q-card>
          </div>
        </div>

        <!-- Bar Chart Section -->
        <q-card class="q-mt-md">
          <div class="col-12">
            <BarChart
              :cancelledPercent="cancelledPercent"
              :finishedPercent="finishedPercent"
            />
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
                v-model:pagination="pagination"
                virtual-scroll
                :rows="rowsWorkStation"
                :columns="columnsWorkStation"
                row-key="id"
              >
                <template v-slot:body-cell-status="props">
                  <q-td :props="props">
                    <q-badge
                      :color="
                        props.row.status === 'Available' ? 'green' : 'primary'
                      "
                    >
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
  defineComponent,
  ref,
  computed,
  onMounted,
  onUnmounted,
  watch,
  inject,
} from "vue";

import { $axios, $notify, Dialog } from "boot/app";

import BarChart from "components/BarChart.vue";
import Chart from "chart.js/auto";

export default {
  name: "QueueDashboard",
  components: { BarChart },
  setup() {
    const rows = ref([]);
    const cancelledPercent = ref(0);
    const finishedPercent = ref(0);
    const cancelledCount = ref(0);
    const finishedCount = ref(0);
    const total = ref(0);
    const dateToday = new Date().toISOString().slice(0, 10);
    const ratingChart = ref(null);
    const easeOfUseChart = ref(null);
    const waitingTimeChart = ref(null);
    const branchList = ref([]);
    const adminMangerInformation = ref();
    let channel = null

    const rowsWorkStation = ref([]);
    const branch_name = ref(null);
    const columnsWorkStation = ref([]);
    const pusher = inject("$pusher");

    const intervalID = ref(null);

    const getTableData = async () => {
      try {
        const { data } = await $axios.post("/admin/queue-logs", {
          date: dateToday,
          branch_id: branch_name.value,
        });

        rows.value.splice(0, rows.value.length, ...data.rows);
        computePercentages();
      } catch (error) {
        console.log(error);
      }
    };

    const updateAllServingTime = async () => {
      try {
        const { data } = await $axios.post("/teller/update-all-serving-time", {
          branch_id: branch_name.value,
        });
        console.log(data.status);
        if (data.status === "success") {
          console.log("Updated Successfully.");
        } else {
          console.log("Update error.");
        }
      } catch (error) {
        console.error(error);
        console.log("Update error.");
      }
    };

    const computePercentages = () => {
      total.value = rows.value.length;
      if (total === 0) {
        cancelledPercent.value = 0;
        finishedPercent.value = 0;
        return;
      }

      cancelledCount.value = rows.value.filter(
        (row) => row.status === "cancelled"
      ).length;
      finishedCount.value = rows.value.filter(
        (row) => row.status === "finished"
      ).length;

      cancelledPercent.value = (
        (cancelledCount.value / total.value) *
        100
      ).toFixed(2);
      finishedPercent.value = (
        (finishedCount.value / total.value) *
        100
      ).toFixed(2);
    };

    // Fetch the information of teller if assigned
    const fetchWorkStation = async () => {
      try {
        if (adminMangerInformation.value != null) {
          const { data } = await $axios.post("/tellers/index", {
            branch_id: adminMangerInformation.value.branch_id,
          });
          // Process rows data to assign the correct status and full name
          rowsWorkStation.value = data.rows.map((row) => {
            return {
              id: row.id,
              // If type_id is null, status is 'Available', otherwise 'Assigned'
              status: row.type_id === null ? "Available" : "Assigned",
              // Combine teller_firstname and teller_lastname for the full name
              teller_name: `${row.teller_firstname} ${row.teller_lastname}`,
              branch_name: row.branch_name,
            };
          });
        } else if (branch_name.value != null) {
          const { data } = await $axios.post("/tellers/index", {
            branch_id: branch_name.value,
          });
          // Process rows data to assign the correct status and full name
          rowsWorkStation.value = data.rows.map((row) => {
            return {
              id: row.id,
              // If type_id is null, status is 'Available', otherwise 'Assigned'
              status: row.type_id === null ? "Available" : "Assigned",
              // Combine teller_firstname and teller_lastname for the full name
              teller_name: `${row.teller_firstname} ${row.teller_lastname}`,
              branch_name: row.branch_name,
            };
          });
        } else {
          const { data } = await $axios.post("/tellers/index");
          // Process rows data to assign the correct status and full name
          rowsWorkStation.value = data.rows.map((row) => {
            return {
              id: row.id,
              // If type_id is null, status is 'Available', otherwise 'Assigned'
              status: row.type_id === null ? "Available" : "Assigned",
              // Combine teller_firstname and teller_lastname for the full name
              teller_name: `${row.teller_firstname} ${row.teller_lastname}`,
              branch_name: row.branch_name,
            };
          });
        }
      } catch (error) {
        if (error.response.status === 422) {
          console.log(error.message);
        }
      }
    };

    let dataTimeout;

    const renderSurveyCharts = async () => {
      try {
        let data = ""; // Declare a variable to store the fetched data from the server

        // Fetch data based on adminMangerInformation or branch_name
        if (adminMangerInformation.value != null) {
          // If manager information is available
          // Fetch survey stats for the branch managed by the admin
          const response = await $axios.post("/admin/survey-stats", {
            branch_id: adminMangerInformation.value.branch_id, // Use the admin's branch ID
          });
          data = response.data; // Store the fetched data in the variable
        } else if (branch_name.value != null && branch_name.value !== 0) {
          // If a branch name is selected
          // Fetch survey stats for the selected branch
          const response = await $axios.post("/admin/survey-stats", {
            branch_id: branch_name.value, // Use the selected branch ID
          });
          data = response.data; // Store the fetched data in the variable
        } else {
          // If neither admin information nor a specific branch is available
          // Fetch survey stats for all branches
          const response = await $axios.post("/admin/survey-stats");
          data = response.data; // Store the fetched data in the variable
        }

        // Destroy all chart instances first
        if (window.ratingChartInstance) {
          window.ratingChartInstance.destroy();
          window.ratingChartInstance = null;
        }

        if (window.easeOfUseChartInstance) {
          window.easeOfUseChartInstance.destroy();
          window.easeOfUseChartInstance = null;
        }

        if (window.waitingTimeChartInstance) {
          window.waitingTimeChartInstance.destroy();
          window.waitingTimeChartInstance = null;
        }

        window.ratingChartInstance = new Chart(ratingChart.value, {
          type: "bar", // Set chart type to bar
          data: {
            labels: Object.keys(data.ratings), // Set chart labels (rating values)
            datasets: [
              {
                label: "Rating", // Label for the dataset
                data: Object.values(data.ratings), // The actual rating data
                backgroundColor: "#4caf50", // Set color for the bars in the chart
              },
            ],
          },
          options: { responsive: true }, // Make the chart responsive to screen size
        });

        // Create a new chart instance for ease of use
        window.easeOfUseChartInstance = new Chart(easeOfUseChart.value, {
          type: "pie", // Set chart type to pie
          data: {
            labels: Object.keys(data.ease_of_use), // Set chart labels (ease of use values)
            datasets: [
              {
                data: Object.values(data.ease_of_use), // The actual ease of use data
                backgroundColor: ["#4caf50", "#f44336"], // Set colors for the pie chart sections
              },
            ],
          },
          options: { responsive: true }, // Make the chart responsive to screen size
        });

        // Create a new chart instance for waiting time satisfaction
        window.waitingTimeChartInstance = new Chart(waitingTimeChart.value, {
          type: "bar", // Set chart type to bar
          data: {
            labels: Object.keys(data.waiting_time_satisfaction), // Set chart labels (waiting time satisfaction values)
            datasets: [
              {
                label: "Waiting Time", // Label for the dataset
                data: Object.values(data.waiting_time_satisfaction), // The actual waiting time data
                backgroundColor: "#F2C037", // Set color for the bars in the chart
              },
            ],
          },
          options: {
            responsive: true, // Make the chart responsive to screen size
            plugins: {
              tooltip: {
                callbacks: {
                  title: function (tooltipItem) {
                    let xValue = tooltipItem[0].label; // Get the label of the tooltip (x-axis value)
                    let labelText = "";

                    // Map the X-axis value to a custom label (waiting time satisfaction)
                    switch (parseInt(xValue)) {
                      case 1:
                        labelText = "Very Dissatisfied";
                        break;
                      case 2:
                        labelText = "Dissatisfied";
                        break;
                      case 3:
                        labelText = "Neutral";
                        break;
                      case 4:
                        labelText = "Satisfied";
                        break;
                      case 5:
                        labelText = "Very Satisfied";
                        break;
                      default:
                        labelText = "Unknown"; // For unexpected values
                    }

                    return labelText; // Return the custom label for the tooltip
                  },
                  label: function (tooltipItem) {
                    return `Waiting Time: ${tooltipItem.raw}`; // Display the waiting time data in the tooltip
                  },
                },
              },
            },
          },
        });
      } catch (error) {
        // If an error occurs during the data fetch or chart update process
        console.error("Survey chart fetch failed:", error); // Log the error to the console
      }
    };

    const fetchBranch = async () => {
      try {
        const { data } = await $axios.post("/type/Branch");
        branchList.value = [
          { id: 0, branch_name: "All Branches" },
          ...data.branch,
        ];
      } catch (error) {
        if (error.response.status === 422) {
          console.log(error);
        }
      }
    };

    const checkColumn = async () => {
      if (adminMangerInformation.value != null) {
        columnsWorkStation.value = [
          {
            name: "name",
            label: "Name",
            align: "left",
            field: "teller_name",
            sortable: true,
          },
          {
            name: "status",
            label: "Status",
            align: "left",
            field: "status",
            sortable: true,
          },
        ];
      } else {
        columnsWorkStation.value = [
          {
            name: "name",
            label: "Branch name",
            align: "left",
            field: "branch_name",
            sortable: true,
          },
          {
            name: "name",
            label: "Name",
            align: "left",
            field: "teller_name",
            sortable: true,
          },
          {
            name: "status",
            label: "Status",
            align: "left",
            field: "status",
            sortable: true,
          },
        ];
      }
    };

    const fetchgetTable = async () => {
      // clear existing interval to avoid duplicates
      if (intervalID.value !== null) {
        clearInterval(intervalID.value);
      }

      intervalID.value = setInterval(() => {
        getTableData();
      }, 10000);
    };

    watch(
      () => branch_name.value,
      async (newVal) => {
        // Check if the new value is a valid branch ID or 'All branches'

        // Trigger fetch and render for 'All branches' scenario
        fetchWorkStation();
        renderSurveyCharts();
        getTableData();
      }
    );

    onMounted(() => {
      const mangerInformation = localStorage.getItem("managerInformation");
      if (mangerInformation) {
        adminMangerInformation.value = JSON.parse(mangerInformation);
      }
      fetchgetTable();
      updateAllServingTime();
      renderSurveyCharts();
      fetchBranch();
      checkColumn();

      // Make sure `branch_name` is set to a meaningful default value
      if (!adminMangerInformation.value && !branch_name.value) {
        branch_name.value = 0; // Set default to 'All branches' if needed
      } else {
        branch_name.value = adminMangerInformation.value.branch_id;
      }

      // new pusher instanve and subscribe to the channel
      const pusher = new Pusher("8d3b62bc5d67d22d3605", {
        cluster: "us2",
      });

      channel = pusher.subscribe("teller-channel");
      // listen for tellerEvent event on the channel
      channel.bind("TellerEvent", (data) => {
        if (data) {
          fetchWorkStation();
        }
      });
    });

    onUnmounted(() => {
      if (intervalID.value !== null) {
        clearInterval(intervalID.value);
        intervalID.value = null;
      }

      if (pusher) {
        pusher.unsubscribe("teller-channel");
        channel.unbind_all()
      }
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
      waitingTimeChart,
      branchList,
      adminMangerInformation,
      branch_name,
      pagination: ref({ page: 1, rowsPerPage: 10 }),
    };
  },
};
</script>

<style scoped>
span.q-table__bottom-item {
  width: 100px;
  text-align: right;
  display: flex;
  justify-content: flex-end;
}
</style>
