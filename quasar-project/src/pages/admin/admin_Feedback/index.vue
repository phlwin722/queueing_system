<template>
    <q-page class="q-px-sm" style="height: auto; min-height: unset">
      <div class="q-ma-md bg-white q-pa-sm shadow-1">
        <q-breadcrumbs class="q-mx-sm">
          <q-breadcrumbs-el icon="home" to="/admin/dashboard" />
          <q-breadcrumbs-el label="Feedback" icon="reviews" to="/admin/feedback" />
        </q-breadcrumbs>
      </div>
      <q-table
        title="Window"
        :rows="filteredRows"
        :columns="columns"
        row-key="id"
        virtual-scroll
        dense
        v-model:pagination="pagination"
        selection="multiple"
        v-model:selected="selected"
        :rows-per-page-options="[0]"
        class="q-ma-md q-mt-sm q-pt-sm"
      >
        <!-- Toolbar -->
        <template v-slot:top>
            <q-toolbar class="q-gutter-y-md">
                <!-- Table Title -->
                <q-toolbar-title>Feedback Management</q-toolbar-title>

                <!-- Spacer to push the search input to the right -->
                <q-space />

                <!-- Search Input -->
                <q-input
                outlined
                dense
                v-model="text"
                placeholder="Search..."
                clearable
                class="q-ml-sm"
                >
                <template v-slot:append>
                    <q-icon name="search" />
                </template>
                </q-input>
            </q-toolbar>
            </template>
  
        <template v-slot:body-cell-Name="props">
          <q-td :props="props">
            {{ props.row.name ? props.row.name : 'N/A' }}
          </q-td>
        </template>
  
        <template v-slot:body-cell-actions="props">
          <q-td :props="props">
            <div class="q-gutter-x-sm">
              <q-btn
                square
                color="positive"
                icon="visibility"
                dense
                size="sm"
                class="custom-btn2"
                @click="handleShowForm('Show', props.row)"
              >
                <q-tooltip
                  anchor="center left"
                  self="center right"
                  :offset="[10, 10]"
                  class="bg-secondary"
                >
                  View Feedback
                </q-tooltip>
              </q-btn>
            </div>
          </q-td>
        </template>
      </q-table>
    </q-page>
    <my-form ref="dialogForm" :url="URL" :rows="rows" />
  </template>
  
  <script>
    import { defineComponent, ref, computed, onMounted, onUnmounted, watch } from "vue";
    import { $axios } from "boot/app";
    import MyForm from "pages/admin/admin_Feedback/form.vue";

  
    const URL = "/feedback";
    
    export default defineComponent({
        name: "IndexPage",
        components: { MyForm },
        setup() {
        const text = ref("");
        const rows = ref([]);
        let intervalId = null;
        const adminInformation = ref (null);
        const branchList = ref ([]);
        const branch_name = ref (null);
    
        const columns = ref([]);
    
        const filteredRows = computed(() => {
            return rows.value.filter((row) =>
            Object.values(row).some((value) =>
                String(value).toLowerCase().includes(text.value.toLowerCase())
            )
            );
        });
  
        const selected = ref([]);
        const dialogForm = ref(null);
    
        const getTableData = async () => {
            try {
            // if managaer login 
            if (adminInformation.value && adminInformation.value.branch_id != null) {
                const { data } = await $axios.post(URL + "/index",{
                branch_id: adminInformation.value.branch_id
                });
                rows.value.splice(0, rows.value.length, ...data.rows);
                console.log(rows.value)
            }
            // if super login 
            else {
                if (branch_name.value != null) {
                const { data } = await $axios.post(URL + "/index",{
                    branch_id: branch_name.value
                });
                rows.value.splice(0, rows.value.length, ...data.rows);
                }else {
                const { data } = await $axios.post(URL + "/index");
                rows.value.splice(0, rows.value.length, ...data.rows);
                }
            }
            } catch (error) {
            console.log(error);
            }
        };

        const handleShowForm = (mode, row) => {
            console.log('haha', row)
            dialogForm.value.showDialog(mode, row);
        };

        const fetchColumn = async () => {
            if (adminInformation.value && adminInformation.value.branch_id) {
            columns.value = [
                {
                    name: "Name",
                    label: "Name",
                    align: "left",
                    field: "name",
                    sortable: true,
                },
                {
                    name: "suggestions",
                    label: "Suggestions",
                    align: "left",
                    style: "max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;",
                    field: "suggestions",
                    sortable: true,
                },
                { 
                    name: "actions", 
                    label: "Actions", 
                    align: "center" 
                },
            ]
            } else {
            columns.value = [
                {
                    name: "branch_name",
                    label: "Branch name",
                    align: "left",
                    field: "branch_name",
                    style: "max-width: 0px;",
                    sortable: true,
                },
                {
                    name: "Name",
                    label: "Name",
                    align: "left",
                    field: "name",
                    sortable: true,
                },
                {
                    name: "suggestions",
                    label: "Suggestions",
                    align: "left",
                    field: "suggestions",
                    style: "max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;",
                    sortable: true,
                },
                { 
                    name: "actions", 
                    label: "Actions", 
                    align: "center" 
                },
            ]
            }
        }

        const fetchBranch = async () => {
            try {
            const { data } = await $axios.post('/type/Branch');
            branchList.value = [{id: 0, branch_name: 'All Branches'}, ...data.branch]
            } catch (error) {
            if (error.response.status == 422) {
                console.log(error)
            } 
            }
        }
    
        watch(()=> branch_name.value, async (newVal) => {
            getTableData()
        });
        
        onMounted(() => {
            const storeManagerInfo = localStorage.getItem('managerInformation');
            if (storeManagerInfo) {
                adminInformation.value = JSON.parse(storeManagerInfo);
            }
            fetchColumn();
            getTableData();
            fetchBranch()
                // Make sure `branch_name` is set to a meaningful default value
                if (!branch_name.value) {
                branch_name.value = 0;  // Set default to 'All branches' if needed
                }
        });
    
        onUnmounted(() => {
            if (intervalId) {
            clearInterval(intervalId);
            }
        });
    
        return {
            rows,
            columns,
            dialogForm,
            selected,
            pagination: ref({ page: 1, rowsPerPage: 10 }),
            handleShowForm,
            URL,
            filteredRows,
            branch_name,
            branchList,
            adminInformation,
        };
        },
    });
  </script>
  
  <style scoped>
  .custom-btn {
    width: 30px;
    height: 30px;
    text-align: center;
    border-radius: 5px;
  }
  
  .custom-btn2 {
    width: 25px;
    height: 25px;
    margin-left: 5px;
    border-radius: 5px;
  }
  </style>
  <style >
    span.q-table__bottom-item{
      width: 200px;
      text-align: right;
      display: flex;
      justify-content: flex-end;
    }
  </style>
  