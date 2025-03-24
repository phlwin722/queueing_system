<template>
<div class="row q-mx-lg q-mt-md items-center">
    <!-- Search Input -->
    <q-input
    filled
    class="bg-accent text-black col q-mr-sm"
    v-model="text"
    label="Search"
    dense
    outlined
    />

    <!-- Date Picker Input -->
    <q-input
    filled
    dense
    class="bg-accent text-black"
    v-model="selectedDate"
    type="date"
    label="Select Date"
    @update:model-value="getTableData"
    outlined
    />
</div>

<q-page>
    <div class="q-px-lg q-mt-md">
    <q-table title="Window Logs" :rows="filteredRows" :columns="columns" row-key="index">
        <template v-slot:body-cell-actions="props">
        <q-td :props="props"></q-td>
        </template>
    </q-table>
    </div>
</q-page>
</template>

<script>
import { defineComponent, ref, computed, onMounted } from 'vue';
import { $axios } from 'boot/app';

export default defineComponent({
name: 'WindowLogsPage',

setup() {
    const text = ref("");
    const rows = ref([]);
    const selectedDate = ref("");

    const columns = ref([
    { name: 'window_name', label: 'Window Name', align: 'left', field: 'window_name', sortable: true },
    { name: 'type_id', label: 'Window Type', align: 'left', field: 'type_name', sortable: true },
    { name: 'teller_id', label: 'Assigned Personnel', align: 'left', field: row => row.teller_name || 'Unassigned', sortable: true },
    { name: 'archived_at', label: 'Archived Date', align: 'left', field: 'archived_at', sortable: true } // Ensure correct field
    ]);

    const filteredRows = computed(() => {
    return rows.value.filter(row =>
        Object.values(row).some(value =>
        String(value).toLowerCase().includes(text.value.toLowerCase())
        )
    );
    });

    const getTableData = async () => {
    try {
        const payload = selectedDate.value ? { date: selectedDate.value } : {};
        const { data } = await $axios.post('/admin/window-logs', payload);
        rows.value = data.rows.map(row => ({
        ...row,
        type_name: row.type_name || 'Unknown Type',  // Ensure default values
        teller_name: row.teller_name || 'Unassigned'
        }));
    } catch (error) {
        console.log(error);
    }
    };

    let refreshInterval = null;
    onMounted(() => {
    refreshInterval = setInterval(getTableData, 5000);
    getTableData();
    });

    return {
    rows,
    columns,
    filteredRows,
    text,
    selectedDate,
    };
}
});
</script>
