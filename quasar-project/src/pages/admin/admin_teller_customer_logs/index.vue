<template>
  <q-page class="q-px-sm" style="height: auto; min-height: unset">
    <div class="q-ma-md bg-white q-pa-sm shadow-1">
      <q-breadcrumbs class="q-mx-sm">
        <q-breadcrumbs-el icon="home" to="/admin/dashboard" />
        <q-breadcrumbs-el
          label="Teller Customer Logs"
          icon="people"
          to="/admin/teller-customer-logs"
        />
      </q-breadcrumbs>
    </div>

    <div class="q-px-md q-mt-md">
      <q-table title="Customer Logs" :rows="filteredRows" :columns="columns" row-key="index">
        <!-- 🎯 Insert Search & Date Picker Inside Table Toolbar -->
        <template v-slot:top>
          <q-toolbar class="q-gutter-md">
            <div v-if="!adminManagerInformation"class="col-3">
              <q-select
                outlined
                v-model="branch_value"
                :options="branch_list"
                label="Branch name"
                hide-bottom-space
                dense
                emit-value
                map-options
                option-label="branch_name"
                option-value="id"
              />
        </div>

        <div class="col-3">
              <q-select
                outlined
                v-model="type_id"
                :options="tellerList"
                label="Window teller"
                hide-bottom-space
                dense
                emit-value
                map-options
                label-value="name"
                label-id="id"
              />
        </div>


            <q-space /> <!-- Pushes items to the right -->

            <!-- Search Input -->
            <q-input 
              filled 
              dense 
              outlined 
              class="bg-accent text-black"
              v-model="text" 
              label="Search">
              <template v-slot:append>
                <q-icon name="search" />
              </template>
            </q-input>

            <!-- Date Picker Input -->
            <q-input 
              :disable="!type_id"
              filled 
              dense 
              outlined 
              class="bg-accent text-black"
              v-model="fromDate" 
              type="date" 
              label="From"
              @update:model-value="getTableData"
            />
            <q-input 
              :disable="!type_id"
              filled 
              dense 
              outlined 
              class="bg-accent text-black"
              v-model="toDate" 
              type="date" 
              label="To"
              @update:model-value="getTableData"
            />

            <q-btn
              :disable="!type_id"
              color="yellow-8"
              icon="download"
              @click="generatePDF"
              dense
              style="min-width: 30px; max-width: 40px; margin-top: 15px; margin-left: 15px; height: 38px;"
            />
            
          </q-toolbar>
        </template>


        <!-- Table Actions -->
        <template v-slot:body-cell-actions="props">
          <q-td :props="props"></q-td>
        </template>
      </q-table>
    </div>

    <!-- Bar Chart Section -->
    <div class="q-px-md q-mt-md">
      <div class="q-pa-md row justify-around bg-white-2 rounded-borders shadow-2">
        <!-- Finished Customers -->
        <div class="column items-center text-center">
          <q-icon name="check_circle" color="positive" size="40px" />
          <div class="text-h6 text-positive">Finished</div>
          <div class="text-h5">{{ finishedCount }}</div>
        </div>

        <!-- Cancelled Customers -->
        <div class="column items-center text-center">
          <q-icon name="cancel" color="negative" size="40px" />
          <div class="text-h6 text-negative">Cancelled</div>
          <div class="text-h5">{{ cancelledCount }}</div>
        </div>

        <!-- Total Customers -->
        <div class="column items-center text-center">
          <q-icon name="people" color="primary" size="40px" />
          <div class="text-h6 text-primary">Total</div>
          <div class="text-h5">{{ total }}</div>
        </div>
      </div>
    </div>

    <!-- Bar Chart Component -->
    <div class="q-px-lg q-my-md" id="chartCodeContainer">
      <BarChart :cancelledPercent="cancelledPercent" :finishedPercent="finishedPercent" />
    </div>
  </q-page>
</template>
    
    <script>
    import { 
      defineComponent ,
      ref,
      computed,
      onMounted,
      onUnmounted,
      watch, // Assuming you are using Vue's Composition API
      toRaw 
    } from 'vue'
    
    import { debounce } from 'lodash'; // Optional: to debounce the function call

    import {
      $axios,
      $notify,
    } from 'boot/app'

    import BarChart from 'components/BarChart.vue';
    import jsPDF from 'jspdf'; // Import jsPDF for PDF generation
    import autoTable from 'jspdf-autotable'; // Import the autoTable plugin explicitly
    import html2canvas from 'html2canvas';
 
    export default defineComponent({
      name: 'IndexPage',
      components: { BarChart },
  
      setup(){
        const text = ref("");
        const rows = ref([]);
        const branch_value = ref (null);
        const branch_list = ref ([]);
        const adminManagerInformation = ref(null);
        const columns = ref([
          
        {
            name: 'name',
            label: 'Customer Name',
            align: 'left',
            field: 'name',
            sortable: true
          },
        {
            name: 'email',
            label: 'Email address',
            align: 'left',
            field: 'email',
            sortable: true
          },
          {
            name: 'queue_number',
            label: 'Queue Number',
            align: 'left',
            field: 'queue_number',
            sortable: true
          },
          {
            name: 'type_id',
            label: 'Service Type',
            align: 'left',
            field: 'type_id',
            sortable: true
          },
          {
            name: 'teller_id',
            label: 'Assigned Personnel',
            align: 'left',
            field: 'teller_id',
            sortable: true
          },
          {
            name: 'status',
            label: 'Status',
            align: 'left',
            field: 'status',
            sortable: true
          },
          {
            name: 'updated_at',
            label: 'Date and Time',
            align: 'left',
            field: 'updated_at',
            sortable: true
          },
        ]);

        let refreshInterval = null
        const filteredRows = computed(() => {
        return rows.value.filter((row) =>
        Object.values(row).some((value) =>
          String(value).toLowerCase().includes(text.value.toLowerCase())
        )
      );
    });

        const cancelledPercent = ref(0);
        const finishedPercent = ref(0);
        const cancelledCount = ref (0)
        const finishedCount = ref(0)
        const total = ref(0)
        const fromDate = ref(""); // Holds the selected date
        const toDate = ref("")
        const tellerList = ref ([]);
        const type_id = ref(null);  // Store the selected teller's ID

        const getTableData = async () => {
          try{
            if (toDate.value && fromDate.value && toDate.value < fromDate.value) {
              $notify('negative', 'error', 'To date cannot be earlier than From date.');
              toDate.value = '';
              return
            }

            const { data } = await $axios.post('/teller/queue-logs',{
                fromDate: fromDate.value,  // Ensure date is passed as an empty string if not set
                toDate: toDate.value,
                teller_id: type_id.value    // Send type_id, assuming it's reactive
            })
            
            rows.value.splice(0, rows.value.length, ...data.rows);
            computePercentages();
          }catch(error){
            console.log(error);
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

        const fetchTeller = async () => {
          try {
            const { data } = await $axios.post('/tellers/indexx',{
              branch_id : branch_value.value
            });

            // Map the API response to match the expected structure for q-select
            tellerList.value = data.rows;

          } catch (error) {
            if (error.response && error.response.status === 422) {
              console.log('Validation error:', error.response.data);
            } else {
              console.error('Error fetching tellers:', error);
            }
          }
        };

        // generating pdf
        const generatePDF = async () => {
          try {
 
            if (filteredRows.value.length === 0) {
              $notify('negative','error','No data available. Please check your filters.')
              return
            }
            // create a new jspdf instance
            const doc = new jsPDF();

            // get the page width of the generated pdf document
            const pageWidth = doc.internal.pageSize.width;

           const addHeader = () => {
            // path the logo of image
            const logoPath = require('assets/vrtlogoblack.png')

            // set dimensions for the logo image 
            const imgWidth = 100;
            const imgHeight = 15;

            // calculate the horizontal position to center the image
            const centerImage = (pageWidth - imgWidth) / 2;

            // set the verital position for the logo
            const y = 5;

            // add the logo image to the pdf
            doc.addImage(logoPath, 'PNG', centerImage, y, imgWidth, imgHeight);

            // set the lenght of the underline
            const linelength = 180; 

            // calculate the starting point of the line to be centered
            const xStart = (pageWidth - linelength) / 2;
            const yStart = 20; // vertical position of the line

            // draw the horizontal line
            doc.line(xStart, yStart, xStart + linelength, yStart);
          }

            // set the title text 
            const title = "Teller Customer Logs";

            // set the font size for the title text
            doc.setFontSize(17);

            // set the font style
            doc.setFont("helvetica", "bold")

            // calculate the width of the title
            const textWidth = doc.getStringUnitWidth(title) * doc.internal.getFontSize() / doc.internal.scaleFactor;
          
            const titleCenter = (pageWidth - textWidth) / 2;

            // set the vertical position
            const top_PositionTitle = 40;

            // add the title to the pdf
            doc.text(title, titleCenter, top_PositionTitle)

            // Define some margin and positioning for content
            const marginHorizontal = 30;
            const iconSize = 8; // Icon size (for simplicity, we can just draw a rectangle to represent an icon)
            
            const iconFinished = require('assets/check_png.png');
            const iconCancelled = require('assets/cancel_png.png');
            const iconGroup = require('assets/groups_png.png');

            // Set font for text
            doc.setFont("helvetica", "normal");
            
            // "Finished" Section
            doc.setFontSize(13);
            doc.addImage(iconFinished, 'PNG', marginHorizontal, 63, iconSize, iconSize); // Add check icon (X, Y, width, height)
            doc.setTextColor('#11ff00');
            doc.text("Finished", marginHorizontal + 10, 68); // horizontal margin, vertical margin
            doc.setTextColor('#000000')
            doc.text(finishedCount.value.toString(), marginHorizontal + 40, 68); // value, horizontal margin, vertical margin
            
            // "Cancelled" Section
            doc.setTextColor('#f50018');
            doc.addImage(iconCancelled, 'PNG', marginHorizontal, 81, iconSize, iconSize); // Add check icon (X, Y, width, height)
            doc.text("Cancelled", marginHorizontal + 10, 86); // value, horizontal margin, vertical margin
            doc.setTextColor('#000000')
            doc.text(cancelledCount.value.toString(), marginHorizontal + 40, 86); // value, horizontal margin, vertical margin
          
            // "Total" Section
            doc.setTextColor('#0082e6');
            doc.addImage(iconGroup, 'PNG', marginHorizontal, 97, iconSize, iconSize); // Add check icon (X, Y, width, height)
            doc.text("Total", marginHorizontal + 10, 103); // horizontal margin, vertical margin
            doc.setTextColor('#000000')
            doc.text(total.value.toString(), marginHorizontal + 40, 103); // value, horizontal margin, vertical margin
            
            // bar graph
            const chartElement = document.querySelector('#chartCodeContainer'); // the div containing the chart

            setTimeout(async () => {
              const canvas = await html2canvas(chartElement, {
                logging: true,
                allowTaint: true,
                useCORS: true,
                scale: 2, // This can improve the resolution
              });

              const chartImageData = canvas.toDataURL('image/png');
              doc.addImage(chartImageData, 'PNG', marginHorizontal + 60, 55, 100, 55); // Add the chart image to the PDF

              // Extract the labels from the columns array
              const columnLabels = columns.value.map((column) => column.label);

              // Unwrap columns and rows to remove the Proxy object
              const unwrappedRows = toRaw(filteredRows.value);

              // Create table in the PDF
              autoTable(doc, {
                head: [columnLabels],
                body: unwrappedRows.map((row) => {
                  return columnLabels.map((label) => {
                    const column = columns.value.find((c) => c.label === label);
                    if (column) {
                      return row[column.field] || ''; // Return empty string if data is missing
                    }
                    return '';
                  });
                }),
                theme: 'grid',
                headStyles: {
                  fillColor: [33, 150, 243],
                  textColor: [255, 255, 255],
                  fontStyle: 'bold',
                },
                margin: { top: 120, bottom: 20 },
                didDrawPage: function (data) {
                  // Add header for each page
                  addHeader()
                  if (data.pageNumber > 1) {
                    // For subsequent pages, reduce the margin-top
                    autoTable(doc, {
                      head: [columnLabels],
                      body: unwrappedRows.map((row) => {
                        return columnLabels.map((label) => {
                          const column = columns.value.find((c) => c.label === label);
                          return column ? row[column.field] || '' : '';
                        });
                      }),
                      theme: 'grid',
                      headStyles: {
                        fillColor: [33, 150, 243],
                        textColor: [255, 255, 255],
                        fontStyle: 'bold',
                      },
                      margin: { top: 30, bottom: 20 },
                    });
                  }

                  // Add page number to the bottom-right corner
                  doc.setFontSize(10);
                  doc.text(
                    `Page ${data.pageNumber}`,
                    pageWidth - 15,
                    doc.internal.pageSize.height - 10,
                    { align: 'right' }
                  );
                },
              });

              // Save the generated PDF
              doc.save('Teller_customer_log.pdf');
            }, 1000); // Adjust timeout to allow enough time for rendering

          } catch(error) {
            console.log(error)
          }
        } 

        // Define a debounced version of getTableData to optimize performance
        const debouncedGetTableData = debounce(() => {
          getTableData();
        }, 300); // Adjust debounce delay as needed

        // Watch for changes in both 'type_id' and 'fromDate'
        watch([type_id, fromDate, toDate, branch_value], ([newTypeId, newFromDate, newToDate, newBranch], [oldTypeId, oldFromDate, oldToDate, oldBranch]) => {
          if (newBranch != oldBranch) {
            // Branch changed — reset state and fetch tellers
            type_id.value = '';
            rows.value = [];
            cancelledPercent.value = 0;
            finishedPercent.value = 0;
            cancelledCount.value = 0;
            finishedCount.value = 0;
            total.value = 0;
            fetchTeller();
          } else if (newTypeId) {
            // Teller was selected/changed — fetch data
            debouncedGetTableData();
          }
        });


        const fetchBranch = async () => {
          try {
            const { data } = await $axios.post('/type/Branch');
            branch_list.value = data.branch
          } catch (error) {
            if (error.response.status === 422) {
              console.log(error)
            }
          }
        }

        onMounted(() => {
          const managerInformation = localStorage.getItem('managerInformation')
          if (managerInformation) {
            adminManagerInformation.value = JSON.parse(managerInformation);
          }

          if (adminManagerInformation.value == null) {
            branch_value.value = 1
          }else  {
            branch_value.value = adminManagerInformation.value.branch_id
          }

          fetchTeller();

          fetchBranch();
        })
    
        return{
          generatePDF,
          tellerList,
          type_id,
          rows,
          columns,
          filteredRows,
          text,
          fromDate,
          toDate,
          cancelledPercent,
          finishedPercent,
          finishedCount,
          cancelledCount,
          total,
          branch_value,
          branch_list,
          adminManagerInformation
        }
      }
    
    });
    </script>

<style scoped>
.q-mx-lg {
  margin-left: 20px;
  margin-right: 20px;
}

.q-mt-md {
  margin-top: 16px;
}
</style>
<style >
  span.q-table__bottom-item{
    width: 200px;
  }
</style>