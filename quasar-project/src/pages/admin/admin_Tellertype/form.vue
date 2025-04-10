
<template>
    <q-dialog @hide="closeDialog" v-model="isShow">
        <q-card>
            <q-card-section class="row items-center q-pb-none">
                <div class="text-h6 text-primary">{{ formMode }} Type</div>
                <q-space />
                <q-btn icon="close" 
                flat 
                round 
                dense 
                v-close-popup
                style="width: 24px; height: 24px;"
                />
            </q-card-section>

            <q-card-section>
                <div class="row q-col-gutter-sm">
                    <div class="col-12">
                        <q-input 
                            outlined 
                            v-model="formData.name" 
                            label="Type Name:" 
                            dense
                            hide-bottom-space
                            :error="formError.hasOwnProperty('name')"
                            :error-message="formError.name"
                            autofocus
                        />
                    </div>
                    <div class="col-12">
                        <q-input 
                            outlined 
                            v-model="formData.indicator " 
                            label="Indicator symbol" 
                            dense
                            hide-bottom-space
                            :error="formError.hasOwnProperty('indicator')"
                            :error-message="formError.indicator "
                        />
                    </div>
                    <div class="col-12">
                        <q-input 
                            outlined 
                            v-model="formData.serving_time " 
                            label="Serving Time" 
                            placeholder="Minutes"
                            dense
                            hide-bottom-space
                            :error="formError.hasOwnProperty('serving_time')"
                            :error-message="formError.serving_time"
                            type="number"
                            min="1"
                        />
                    </div>
                </div>
            </q-card-section>

            <q-card-actions class="flex justify-center">
                <q-btn 
                color="positive" 
                label="Save" 
                @click="handleSubmitForm" 
                class="full-width" 
                style="max-width: 150px;"
                />
            </q-card-actions>

            <q-inner-loading :showing="isLoading">
                <q-spinner-gears size="50px" color="orange" />
            </q-inner-loading>
        </q-card>
    </q-dialog>
</template>

<script>
import { defineComponent, ref, toRefs } from "vue";
import { $axios, $notify } from 'boot/app';

export default defineComponent({
    name: "TypeFormPage",
    props: {
        url: String,
        rows: Array
    },
    setup(props) {
        const isShow = ref(false);
        const isLoading = ref(false);
        const initFormData = () => ({ 
            id: null, 
            name: '',
            indicator: '',
            serving_time: '',
        });
        const formData = ref(initFormData());
        const formError = ref({});
        const formMode = ref('New');

        const closeDialog = () => {
            isShow.value = false;
            formData.value = initFormData();
            formError.value = {};
        };

        const showDialog = async (mode, row) => {
            formMode.value = mode === 'new' ? 'New' : 'Edit';
            if (mode === 'edit') {
                formData.value = Object.assign({}, row);
            }
            isShow.value = true;
        };

        const handleSubmitForm = async () => {
            isLoading.value = true;
            try {
                formData.value.name = formData.value.name
                .split(' ')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                .join(' ');

                const mode = formMode.value === 'New' ? '/create' : '/update';
                const { data } = await $axios.post(props.url + mode, formData.value);
                const rows = toRefs(props).rows;
                
                if (formMode.value === 'New') {
                    rows.value.unshift(data.row);
                } else {
                    const index = rows.value.findIndex(x => x.id === formData.value.id);
                    if (index > -1) {
                        rows.value[index] = Object.assign({}, formData.value);
                    }
                }
                
                $notify('positive', 'done', data.message);
                closeDialog();
            } catch (error) {
                if (error.response?.status === 422) {
                    formError.value = error.response.data;
                } else {
                    console.error('Error:', error);
                }
            } finally {
                isLoading.value = false;
            }
        };

        return {
            isShow,
            isLoading,
            showDialog,
            closeDialog,
            formData,
            formError,
            formMode,
            handleSubmitForm
        };
    }
});
</script>
