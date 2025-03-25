<template>
    <q-page class="q-px-lg">
        <div class="q-my-md bg-white q-pa-sm shadow-1">
            <q-breadcrumbs 
                class="q-mx-sm"
                >
                <q-breadcrumbs-el icon="home" to="/admin/dashboard" />
                <q-breadcrumbs-el label="Reset window" icon="reset_tv" to="/admin/reset-window" />
            </q-breadcrumbs>
            </div>
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

                        <q-card v-if="form.reset_type" flat bordered class="q-pa-md">
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
                                    :min="minDate"
                                    :max="'9999-12-31'"
                                    @blur="validateDate"
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
                        :disable="isFormInvalid"
                    />
                </q-form>
            </q-card>
        </div>
    </q-page>
</template>

<script>
import { ref, computed, onMounted } from "vue";
import { $axios, $notify } from "src/boot/app";

export default {
    setup() {
        const isSubmitting = ref(false);
        const showTimePicker = ref(false);
        const today = new Date().toISOString().split("T")[0];

        const form = ref({
            id: "",
            auto_reset: false,
            reset_type: null,
            reset_time: "",
            reset_day: "",
            reset_date: ""
        });

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

        const validateDate = () => {
            if (!form.value.reset_date) return;

            let selectedDate = new Date(form.value.reset_date);
            let currentDate = new Date(today);
            let maxDate = new Date("9999-12-31");

            if (selectedDate < currentDate) {
                $notify("negative", "error", "Invalid date. Please select a future date.");
                form.value.reset_date = "";
            }

            if (selectedDate > maxDate) {
                $notify("negative", "error", "Invalid date. Maximum allowed date is 9999-12-31.");
                form.value.reset_date = "";
            }
        };

        const isFormInvalid = computed(() => {
            if (!form.value.auto_reset) return false;
            if (form.value.reset_type === "Daily" && !form.value.reset_time) return true;
            if (form.value.reset_type === "Weekly" && (!form.value.reset_day || !form.value.reset_time)) return true;
            if (form.value.reset_type === "Monthly" && (!form.value.reset_date || !form.value.reset_time)) return true;
            return false;
        });

        const submitForm = async () => {
            isSubmitting.value = true;

            // Remove unnecessary fields based on reset type
            const payload = { ...form.value };

            if (payload.reset_type === "Daily") {
                payload.reset_day = null;
                payload.reset_date = null;
            } else if (payload.reset_type === "Weekly") {
                payload.reset_date = null;
            } else if (payload.reset_type === "Monthly") {
                payload.reset_day = null;
            }

            try {
                await $axios.post("/windows/reset-settings", payload);
                localStorage.setItem("resetSettings", JSON.stringify(payload)); // Save to localStorage
                $notify("positive", "check", "Settings saved successfully.");
            } catch (error) {
                $notify("negative", "error", "Failed to save settings.");
            } finally {
                isSubmitting.value = false;
            }
        };

        onMounted(() => {
            // Load saved settings from localStorage
            const savedSettings = localStorage.getItem("resetSettings");
            if (savedSettings) {
                form.value = JSON.parse(savedSettings);
            }   
        });

        return {
            form,
            submitForm,
            isSubmitting,
            weekDays,
            formatTime,
            validateDate,
            minDate: today,
            isFormInvalid,
            showTimePicker
        };
    }
};
</script>
