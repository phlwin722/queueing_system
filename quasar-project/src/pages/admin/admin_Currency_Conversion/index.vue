<template>
  <q-page class="q-px-sm" style="height: auto; min-height: unset">
    <div class="q-ma-md bg-white q-pa-sm shadow-1">
      <q-breadcrumbs class="q-mx-sm">
        <q-breadcrumbs-el icon="home" to="/admin/dashboard" />
        <q-breadcrumbs-el
          label="Currency Conversion"
          icon="currency_exchange"
          to="/admin/currency_conversion"
        />
      </q-breadcrumbs>
    </div>

    <q-table
      title="Currency"
      :rows="filteredRows"
      :columns="columns"
      row-key="id"
      virtual-scroll
      dense
      v-model:pagination="pagination"
      selection="multiple"
      v-model:selected="selected"
      :rows-per-page-options="[0]"
      class="q-ma-md q-mt-sm q-pt-xs"
    >
      <template v-slot:top>
        <div class="row q-col-gutter-sm q-pb-xs">
          <div class="">
            <q-btn
              color="primary"
              icon="add"
              dense
              @click="handleShowForm('new')"
              class="custom-btn"
              glossy
              size="sm"
            >
              <q-tooltip
                anchor="center left"
                self="center right"
                :offset="[10, 10]"
              >
                <strong>Add </strong> Currency
              </q-tooltip>
            </q-btn>
          </div>
          <div class="col-auto">
            <q-btn
              color="red"
              icon="delete"
              dense
              :disable="selected.length === 0"
              @click="beforeDelete(true)"
              class="custom-btn"
              glossy
              size="sm"
            >
              <q-tooltip
                anchor="center right"
                self="center left"
                :offset="[10, 10]"
              >
                <strong>Delete </strong> Currency
              </q-tooltip>
            </q-btn>
          </div>
          <div 
            v-if="!adminManagerInformation" 
            class="class-auto">
            <q-select
              dense
              outlined 
              style="width: 250px;" 
              label="Branch name"
              hide-bottom-space
              v-model="branch_name"
              emit-value
              map-options
              :options="branch_list"
              option-label="branch_name"
              option-value="id"
            />
          </div>
        </div>
      </template>

      <template v-slot:body-cell-flag="props">
        <q-td :props="props">
          <span :class="['fi', props.row.flag]" style="font-size: 1.5em"></span>
        </q-td>
      </template>

      <template v-slot:body-cell-actions="props">
        <q-td :props="props">
          <div class="q-gutter-x-sm">
            <q-btn
              square
              color="positive"
              icon="edit"
              dense
              glossy
              size="sm"
              class="custom-btn2"
              @click="handleShowForm('edit', props.row)"
            >
              <q-tooltip
                anchor="center left"
                self="center right"
                :offset="[10, 10]"
                class="bg-secondary"
              >
                Edit Currency
              </q-tooltip>
            </q-btn>

            <q-btn
              square
              color="negative"
              icon="delete"
              dense
              glossy
              size="sm"
              class="custom-btn2"
              @click="beforeDelete(false, props.row)"
            >
              <q-tooltip
                anchor="center right"
                self="center left"
                :offset="[10, 10]"
                class="bg-secondary"
              >
                Delete Currency
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
import { defineComponent, ref, computed, onMounted, watch } from "vue";
import { $axios, $notify, Dialog } from "boot/app";
import MyForm from "pages/admin/admin_Currency_Conversion/form.vue";
import { useQuasar } from "quasar";

const URL = "/currency";

export default defineComponent({
  name: "IndexPage",
  components: { MyForm },
  setup() {
    const text = ref("");
    const rows = ref([]);
    const $dialog = useQuasar();
    const adminManagerInformation = ref (null)
    const branch_name = ref (null);
    const branch_list =ref ([]);

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
        if (adminManagerInformation.value != null) {
          const { data } = await $axios.post(URL + "/showData", {
            branch_id: adminManagerInformation.value.branch_id
          });
          rows.value.splice(0, rows.value.length, ...data.rows);
        }else {
          if ( branch_name.value != null) {
            const { data } = await $axios.post(URL + "/showData", {
              branch_id: branch_name.value
            });
            rows.value.splice(0, rows.value.length, ...data.rows);
          }else {
            const { data } = await $axios.post(URL + "/showData");
            rows.value.splice(0, rows.value.length, ...data.rows);
          }
        }
      } catch (error) {
        console.log(error);
      }
    };

    const handleShowForm = (mode, row) => {
      dialogForm.value.showDialog(mode, row);
    };

    const beforeDelete = (isMany, row) => {
      const ids = isMany ? selected.value.map((x) => x.id) : [row.id];
      const itemNames = isMany ? "Currencies" : row.currency_name;

      $dialog
        .dialog({
          title: "Confirm",
          message: `Are you sure you want to delete ${itemNames}?`,
          cancel: true,
          persistent: true,
          ok: {
            label: "Yes",
            color: "primary",
            unelevated: true,
            style: "width: 125px;",
          },
          cancel: {
            label: "Cancel",
            color: "red-8",
            unelevated: true,
            style: "width: 125px;",
          },
          style: "border-radius: 12px; padding: 16px;",
        })
        .onOk(async () => {
          handleDelete(ids);
        });
    };

    const handleDelete = async (ids) => {
      try {
        if (!Array.isArray(ids)) {
          ids = [ids];
        }
        const { data } = await $axios.post(URL + "/delete", { ids });
        rows.value = rows.value.filter((row) => !ids.includes(row.id));
        $notify("positive", "check", data.message);
        selected.value = [];
      } catch (error) {
        console.log("error", error);
        $notify("negative", "error", error.response.data.message);
      }
    };

    const fetchBranch = async () => {
      try {
        const { data } = await $axios.post('/type/Branch');
        branch_list.value = [{id: 0, branch_name: 'All Branches'}, ...data.branch]
      } catch (error) {
        if (error.response.status === 422) {
          console.log(error)
        }
      }
    }

    const columnCheck = async () => {
      try {
        if (adminManagerInformation.value === null) {
          columns.value = [
           {
              name: "branch_name",
              label: "Branch name",
              align: "left",
              field: "branch_name",
              sortable: "true",
            },
            {
              name: "currency_name",
              label: "Currency Name",
              align: "left",
              field: "currency_name",
              sortable: true,
            },
            {
              name: "currency_symbol",
              label: "Currency Symbol",
              align: "left",
              field: "currency_symbol",
              sortable: true,
            },
            {
              name: "flag",
              label: "Flag",
              align: "center",
              field: "flag",
              sortable: true,
              format: (val) => val,
            },
            {
              name: "buy_value",
              label: "Buy Value",
              align: "left",
              field: "buy_value",
              sortable: true,
            },
            {
              name: "sell_value",
              label: "Sell Value",
              align: "left",
              field: "sell_value",
              sortable: true,
            },
            { name: "actions", label: "Actions", align: "left", sortable: false },
          ]
        }else {
          columns.value = [
           {
              name: "currency_name",
              label: "Currency Name",
              align: "left",
              field: "currency_name",
              sortable: true,
            },
            {
              name: "currency_symbol",
              label: "Currency Symbol",
              align: "left",
              field: "currency_symbol",
              sortable: true,
            },
            {
              name: "flag",
              label: "Flag",
              align: "center",
              field: "flag",
              sortable: true,
              format: (val) => val,
            },
            {
              name: "buy_value",
              label: "Buy Value",
              align: "left",
              field: "buy_value",
              sortable: true,
            },
            {
              name: "sell_value",
              label: "Sell Value",
              align: "left",
              field: "sell_value",
              sortable: true,
            },
            { name: "actions", label: "Actions", align: "left", sortable: false },
          ]
        }
      } catch (error) {
        if (error.response.status === 422) {
          console.log(error)
        }
      }
    }

    watch (() => branch_name.value, (newVal) => {
      getTableData();
    })

    onMounted( async () => {
      const managerInformation = localStorage.getItem('managerInformation')
      if (managerInformation) {
        adminManagerInformation.value = JSON.parse(managerInformation)
      }

      if (adminManagerInformation.value == null ) {
        branch_name.value = 0;
      }
      await getTableData()
      await fetchBranch()
      await  columnCheck()
    });

    return {
      rows,
      columns,
      dialogForm,
      selected,
      handleShowForm,
      URL,
      beforeDelete,
      filteredRows,
      text,
      adminManagerInformation,
      branch_list,
      branch_name
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

<style>
@import "flag-icons/css/flag-icons.min.css";
</style>
<style >
  span.q-table__bottom-item{
    width: 200px;
  }
</style>
