
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
               
                            v-model="formData.name" 
                            :readonly="formData.name === 'Foreign Exchange' || formData.name === 'Online Appointment' || formData.name === 'Manual Queueing'"
                            outlined 
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
                    <div v-if="!adminInformation" class="col-12">
                            <q-select
                            outlined 
                            v-model="formData.branch_id" 
                            :options="branchList"
                            option-label="branch_name"
                            option-value="id"
                            label="Branch name" 
                            hide-bottom-space
                            :error="formError.hasOwnProperty('branch_id')"
                            :error-message="formError.branch_id"
                            dense
                            emit-value
                            map-options
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
import { defineComponent, ref, toRefs, onMounted } from "vue";
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
            branch_id: '',
        });
        const formData = ref(initFormData());
        const formError = ref({});
        const formMode = ref('New');
        const adminInformation = ref(null);
        const branchList = ref([]);

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

                // Check if adminInformation is available before accessing branch_id
                if (adminInformation.value && adminInformation.value.branch_id != null) {
                    formData.value.branch_id = adminInformation.value.branch_id;
                }
                
                const mode = formMode.value === 'New' ? '/create' : '/update';
                const { data } = await $axios.post(props.url + mode, formData.value);
                const rows = toRefs(props).rows;
                
                if (mode === '/create') {
                    rows.value.unshift(data.row);
                } else {
                    const rows = toRefs(props).rows
                    const index = rows.value.findIndex(x => x.id === data.row.id)
                    if(index > -1){
                        rows.value[index] = Object.assign({}, data.row)
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

        const fetchBranch = async () => {
            try {
                const { data } = await $axios.post('/type/Branch')

                branchList.value = data.branch
                console.log('bramc', branchList.value)
            } catch (error) {
                if (error.response.status === 422) {
                    console.log(error)
                }
            }
        }

        onMounted(() => {
        const storeManagerInfo = localStorage.getItem('managerInformation');
            if (storeManagerInfo) {
                adminInformation.value = JSON.parse(storeManagerInfo);
            }

            fetchBranch();
        });

        return {
            isShow,
            isLoading,
            showDialog,
            closeDialog,
            formData,
            formError,
            formMode,
            handleSubmitForm,
            adminInformation,
            branchList,
        };
    }
});
</script>
