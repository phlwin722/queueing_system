<template>
<div>
    <q-card class="q-pa-md">
    <q-form @submit.prevent="process">
        <q-separator spaced />
        <q-item>
        <q-item-section>
            <q-item-label>Reset Window</q-item-label>
            <q-item-label caption>
            Enable this to automatically reset windows at a set interval.
            </q-item-label>
        </q-item-section>
        <q-item-section side>
            <q-checkbox v-model="formData.autoReset" />
        </q-item-section>
        </q-item>

        <div v-if="formData.autoReset">
        <q-input v-model.number="formData.resetMinutes" type="number" label="Minutes" outlined class="q-mt-sm" />
        <q-input v-model.number="formData.resetDays" type="number" label="Days" outlined class="q-mt-sm" />
        <q-input v-model.number="formData.resetWeeks" type="number" label="Weeks" outlined class="q-mt-sm" />
        </div>

        <q-btn color="primary" label="Save" icon="save" type="submit" class="q-mt-md" :loading="isLoading" />
    </q-form>
    </q-card>
</div>
</template>

<script>
import { ref, onMounted } from "vue";
import { $axios, $notify } from "src/boot/app";

export default {
setup() {
    const isLoading = ref(false);
    const formData = ref({
    id: "",
    autoReset: false,
    resetMinutes: 0,
    resetDays: 0,
    resetWeeks: 0,
    });
    const formError = ref({});

    const fetchSettings = async () => {
    try {
        const { data } = await $axios.post("/waiting_Time-fetch");
        if (data) {
        formData.value = {
            id: data.id || "",
            autoReset: !!data.auto_reset, // Ensure boolean
            resetMinutes: data.reset_minutes || 0,
            resetDays: data.reset_days || 0,
            resetWeeks: data.reset_weeks || 0,
        };
        }
    } catch (error) {
        console.error("Error fetching data:", error);
    }
    };

    const process = async () => {
    isLoading.value = true;
    try {
        const { data } = await $axios.post("/waiting_Time", {
        auto_reset: formData.value.autoReset ? 1 : 0,
        reset_minutes: formData.value.resetMinutes,
        reset_days: formData.value.resetDays,
        reset_weeks: formData.value.resetWeeks,
        });
        formError.value = {}; 

        if (data) {
        $notify("positive", "done", data.message);
        formData.value = {
            ...formData.value,
            autoReset: !!data.data.auto_reset,
            resetMinutes: data.data.reset_minutes,
            resetDays: data.data.reset_days,
            resetWeeks: data.data.reset_weeks,
        };
        }
    } catch (error) {
        if (error.response?.status === 422) {
        formError.value = error.response.data;
        } else {
        console.error("Error", error);
        }
    } finally {
        isLoading.value = false;
    }
    };

    onMounted(fetchSettings);

    return {
    formData,
    process,
    isLoading,
    formError,
    };
},
};
</script>
