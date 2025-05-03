<template>
  <q-page class="q-px-sm" style="height: auto; min-height: unset">
    <div class="q-ma-md bg-white q-pa-sm shadow-1">
      <q-breadcrumbs class="q-mx-sm">
        <q-breadcrumbs-el icon="home" to="/admin/dashboard" />
        <q-breadcrumbs-el
          label="Branch"
          icon="account_balance"
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
      class="q-ma-md q-mt-sm q-pt-sm"
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
                <strong>Add </strong> Branch
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
                <strong>Delete </strong> Branch
              </q-tooltip>
            </q-btn>
          </div>
        </div>
      </template>
...   
      <template v-slot:body-cell-province="props">
       <q-td :props="props">
          {{ props.row.province || 'N\A' }}
        </q-td>
      </template>

      <template v-slot:body-cell-Barangay="props">
        <q-td :props="props">
          {{ props.row.Barangay || 'N\A' }}
        </q-td>
      </template>

      <template v-slot:body-cell-manager="props">
        <q-td :props="props">
            {{ props.row.manager_name || 'No assigned' }}
        </q-td>
      </template>

      <template v-slot:body-cell-status="props">
        <q-td :props="props">
          <q-badge
            :color="props.row.status === 'Active' ? 'green' : 'red'"
          >
          {{ props.row.status }}
          </q-badge>
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
                Edit Branch
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
                Delete Branch
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
import { defineComponent, ref, computed, onMounted } from "vue";
import { $axios, $notify, Dialog } from "boot/app";
import MyForm from "pages/admin/branch/form.vue";
import { useQuasar } from "quasar";

const URL = "/branch";

export default defineComponent({
  name: "IndexPage",
  components: { MyForm },
  setup() {
    const text = ref("");
    const rows = ref([]);
    const $dialog = useQuasar();

    const columns = ref([
      {
        name: "branch_name",
        label: "Branch Name",
        align: "left",
        field: "branch_name",
        sortable: true,
      },
      {
        name: "manager",
        label: "Manager assigned",
        align: "left",
        field: "manager_name",
        sortable: true,
      },
      {
        name: "region",
        label: "Region",
        align: "left",
        field: "region",
        sortable: true,
      },
      {
        name: "province",
        label: "Province",
        align: "left",
        field: "province",
        sortable: true,
      },
      {
        name: "city",
        label: "City",
        align: "left",
        field: "city",
        sortable: true,
      },
      {
        name: "Barangay",
        label: "Barangay",
        align: "left",
        field: "Barangay",
        sortable: true,
      },
      {
        name: "address",
        label: "Address",
        align: "left",
        field: "address",
        sortable: true,
      },
      {
        name: "status",
        label: "Status",
        align: "left",
        field: "status",
        sortable: true,
      },
      {
        name: "opening_date",
        label: "Opening date",
        align: "left",
        field: "opening_date",
        sortable: true,
      },
      { 
        name: "actions", 
        label: "Actions", 
        align: "left", 
        sortable: false
      },
    ]);

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
        const { data } = await $axios.post(URL + "/index");
        rows.value.splice(0, rows.value.length, ...data.rows);
      } catch (error) {
        console.log(error);
      }
    };

    const handleShowForm = (mode, row) => {
      dialogForm.value.showDialog(mode, row);
    };

    const beforeDelete = (isMany, row) => {
      const ids = isMany ? selected.value.map((x) => x.id) : [row.id];
      const itemNames = isMany ? "all branches" : row.branch_name;

      $dialog
        .dialog({
          title: "Confirm",
          message: `Are you sure you want to disable ${itemNames}?`,
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
        if (error.response.status === 400) {
          $notify("negative", "error", error.response.data.message);
        }else {
          console.log("error", error);
        }
      }
    };

    onMounted(() => {
      getTableData();
    });

    return {
      rows,
      columns,
      dialogForm,
      selected,
      pagination: ref({ page: 1, rowsPerPage: 10 }),
      handleShowForm,
      URL,
      beforeDelete,
      filteredRows,
      text,
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

span.q-table__bottom-item{
    width: 200px;
    text-align: right;
    display: flex;
    justify-content: flex-end;
  }
</style>

<style>
@import "flag-icons/css/flag-icons.min.css";
</style>
