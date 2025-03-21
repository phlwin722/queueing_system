<template>
    <div>
        <q-card class="q-pa-md">
            <q-form @submit.prevent="submitForm">
                <q-separator spaced />
                
                <q-item>
                    <q-item-section>
                        <q-item-label>Reset Window</q-item-label>
                        <q-item-label caption>
                            Enable this to automatically reset windows at a set interval.
                        </q-item-label>
                    </q-item-section>
                    <q-item-section side>
                        <q-checkbox v-model="form.auto_reset" />
                    </q-item-section>
                </q-item>
                
                <div v-if="form.auto_reset">
                    <q-select 
                        v-model="form.reset_type" 
                        :options="['Daily', 'Weekly', 'Monthly']" 
                        label="Reset Frequency" 
                        outlined 
                        class="q-mt-sm"
                    />
                    
                    <div v-if="form.reset_type">
                        <q-card flat bordered class="q-pa-md">
                            <q-input 
                                v-model="form.reset_time" 
                                label="Time (HH:MM)" 
                                outlined 
                                class="q-mt-sm" 
                                placeholder="00:00" 
                                @update:model-value="formatTime"
                                maxlength="5"
                                inputmode="numeric"
                            >
                                <template v-slot:append>
                                    <q-icon name="access_time" class="cursor-pointer" @click="showTimePicker = true" />
                                </template>
                            </q-input>
                            
                            <q-dialog v-model="showTimePicker">
                                <q-card>
                                    <q-time v-model="form.reset_time" format24h @update:model-value="formatTime" />
                                    <q-card-actions align="right">
                                        <q-btn flat label="OK" v-close-popup />
                                    </q-card-actions>
                                </q-card>
                            </q-dialog>
                        </q-card>
                    </div>
                    
                    <div v-if="form.reset_type === 'Weekly'" class="q-mt-sm">
                        <q-card flat bordered class="q-pa-md">
                            <q-option-group
                                v-model="form.reset_day"
                                :options="weekDays"
                                type="radio"
                                label="Select Day"
                                inline
                            />
                        </q-card>
                    </div>
                    
                    <div v-if="form.reset_type === 'Monthly'" class="q-mt-sm">
                        <q-card flat bordered class="q-pa-md">
                            <q-input 
                                v-model="form.reset_date" 
                                label="Select Date" 
                                outlined 
                                class="q-mt-sm" 
                                type="date" 
                            />
                        </q-card>
                    </div>
                </div>
                
                <q-btn 
                    color="primary" 
                    label="Save" 
                    icon="save" 
                    type="submit" 
                    class="q-mt-md" 
                    :loading="isSubmitting"
                    :disable="form.auto_reset && ((form.reset_type === 'Daily' && !form.reset_time) || (form.reset_type === 'Weekly' && !form.reset_day) || (form.reset_type === 'Monthly' && !form.reset_date))" 
                />
            </q-form>
        </q-card>
    </div>
</template>

<script>
import { ref, onMounted } from "vue";
import { $axios, $notify } from "src/boot/app";

export default {
    setup() {
        const isSubmitting = ref(false);
        const showTimePicker = ref(false);
        const form = ref({
            id: "",
            auto_reset: false,
            reset_type: null,
            reset_time: "", 
            reset_day: "",
            reset_date: ""
        });
        const formErrors = ref({});

        const weekDays = [
            { label: "Sunday", value: "Sunday" },
            { label: "Monday", value: "Monday" },
            { label: "Tuesday", value: "Tuesday" },
            { label: "Wednesday", value: "Wednesday" },
            { label: "Thursday", value: "Thursday" },
            { label: "Friday", value: "Friday" },
            { label: "Saturday", value: "Saturday" }
        ];

        const formatTime = () => {
            let value = form.value.reset_time;
            if (!value) return;

            let time = value.replace(/\D/g, "");
            if (time.length > 4) time = time.slice(0, 4);
            if (time.length >= 3) time = time.slice(0, 2) + ":" + time.slice(2);
            let [hh, mm] = time.split(":");
            if (hh && parseInt(hh) > 23) hh = "23";
            if (mm && parseInt(mm) > 59) mm = "59";
            form.value.reset_time = hh + (mm !== undefined ? ":" + mm : "");
        };

        const submitForm = async () => {
            isSubmitting.value = true;
            try {
                await $axios.post("/windows/reset-settings", form.value);
                localStorage.setItem('resetSettings', JSON.stringify(form.value));  // Save to localStorage
                $notify('positive', 'check', "Settings saved successfully.");
            } catch (error) {
                $notify('negative', 'error', "Failed to save settings.");
            } finally {
                isSubmitting.value = false;
            }
        };

        onMounted(() => {
            // Check if there are saved settings in localStorage
            const savedSettings = localStorage.getItem('resetSettings');
            if (savedSettings) {
                form.value = JSON.parse(savedSettings);  // Load saved settings into the form
            }
        });

        return {
            form,
            submitForm,
            isSubmitting,
            formErrors,
            weekDays,
            formatTime,
            showTimePicker
        };
    },
};
</script>
