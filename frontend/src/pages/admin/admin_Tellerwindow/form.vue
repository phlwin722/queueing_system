<template>
    <q-dialog 
        v-model="icon"
        @hide="closeDialog"
    >
        <q-card>
        <q-card-section class="row items-center q-pb-none">
            <div class="text-h6 text-primary">{{formMode}} Window</div>
        <q-space />
            <q-btn icon="close" flat round dense v-close-popup style="width: 24px; height: 24px;"/>
        </q-card-section>

        <q-card-section>
            <div class="row q-col-gutter-md">
                <div class="col-12">
                    <q-input 
                    outlined 
                    v-model="formData.window_name" 
                    label="Window name" 
                    dense 
                    hide-bottom-space
                    :error="formError.hasOwnProperty('window_name')"
                    :error-message="formError.window_name"
                    autofocus
                    />
                </div>
                <div v-if="!adminInformation" class="col-12">
                    <q-select
                        outlined 
                        v-model="formData.branch_id" 
                        :options="branch_list"
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
                <div class="col-12">
                    <q-select 
                    outlined 
                    :disable="!adminInformation? !formData.branch_id : windowTypeList"
                    v-model="formData.type_id" 
                    :options="windowTypeList" 
                    label="Window type" 
                    hide-bottom-space
                    :error="formError.hasOwnProperty('type_id')"
                    :error-message="formError.type_id"
                    dense
                    emit-value
                    map-options
                    />
                </div>
                <div class="col-12">
                    <q-select 
                    outlined 
                    :disable="teller_id"
                    v-model="formData.teller_id"
                    :options="personnelList"
                    label="Assign personnel" 
                    dense
                    hide-bottom-space
                    :error="formError.hasOwnProperty('teller_id')"
                    :error-message="formError.teller_id"
                    emit-value
                    map-options
                    option-label="teller_name"
                    option-value="teller_id"
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

        <q-inner-loading
        :showing="isLoading"
        label="Please wait..."
        label-class="text-teal"
        label-style="font-size: 1.1em"
        />
        </q-card>
    </q-dialog>
    </template>

    <script>
    import { 
    defineComponent ,
    ref,
    toRefs,
    onMounted,
    watch,
    nextTick 
    } from 'vue'

    import {
    $axios,
    $notify
    } from 'boot/app'

    export default defineComponent({
    name:'WindowFormPage',
    props: {
        url: String,
        rows: Array
    },
    setup(props){
        const windowTypeList = ref([])
        const personnelList = ref([])
        const icon = ref(false)
        const isLoading= ref(false)
        const branch_list = ref ([]);
        const adminInformation = ref(null);
    
        const initFormData = () => (
            {
                id: null,
                window_name: "",
                type_id: "",
                teller_id: "",
                branch_id: "",
            }
        )

        const formData = ref(initFormData())
        const formError = ref({})
        const formMode = ref('New')
        
        const closeDialog = ()=>{
            icon.value=false
            formData.value = initFormData()
            formError.value = {}
        }
        const oldTEllerId = ref()
        
        const showDialog = async (mode, row) => {
            formMode.value = mode === 'new' ? 'New' : 'Edit';
            

            if (mode === 'edit') {
                formData.value = Object.assign({},row)
                console.log(row.teller_id) 
                formData.value.type_id = row.type_id
                oldTEllerId.value = row.teller_id
                await fetchWindowTypes()
                await fetchPersonnel()

                // Map each selected type ID to its corresponding category from categoriesList
                const selectedCategory = windowTypeList.value.find(type => type.value === row.type_id);
                if (!adminInformation.value) {
                formData.value.type_id = selectedCategory.value
                }
            }

            icon.value = true;
        };

        const handleSubmitForm = async () =>{
            const mode = formMode.value === 'New' ? '/create' : '/update' 
            isLoading.value = true
            try{
                formError.value = {}
                formData.value.window_name = formData.value.window_name
                .split(' ')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                .join(' ')
            if(mode === '/create'){
                const {data} = await $axios.post(props.url +mode, formData.value)
                const rows = toRefs(props).rows
                rows.value.unshift(data.row)
                $notify('positive', 'check', data.message)
                closeDialog()
            }else{
/*                 if (typeof formData.value.teller_id === 'string') {
                    formData.value.teller_id = tempTelId.value
                }else if(formData.value.teller_id === null){
                    formData.value.teller_id = ''
                } */
                const {data} = await $axios.post(props.url +mode,{
                    id: formData.value.id,
                    window_name: formData.value.window_name,
                    type_id: formData.value.type_id,
                    teller_id: formData.value.teller_id,
                    branch_id: formData.value.branch_id ? formData.value.branch_id : adminInformation.value.branch_id,
                    old_teller_id: oldTEllerId.value
                })
                const rows = toRefs(props).rows
                const index = rows.value.findIndex(x => x.id === data.row.id)
                if(index > -1){
                rows.value[index] = Object.assign({}, data.row)
                }
                $notify('positive', 'check', data.message)
                closeDialog()
            } 
            }catch(error){
                const status = error?.response?.status

                console.log(status || 'Unknown error (no response)')

                if (status === 422){
                    formError.value = error.response.data
                } else if (status === 400) {
                    formError.value.teller_id = error.response.data.message
                } else {
                    console.error('Unexpected error:', error.message || error)
                    $notify('negative', 'error', 'Something went wrong while saving. Please try again.')
                }
            }finally{
            isLoading.value = false
            }
        }

        const fetchWindowTypes = async () => { 
            try {
                formData.value.branch_id = adminInformation.value ? adminInformation.value.branch_id : formData.value.branch_id 
                const response = await $axios.post('/windows/types/dropdown',{
                    branch_id: formData.value.branch_id
                });
                    if (Array.isArray(response.data.rows)) {
                    // Map id and section_name correctly
                    windowTypeList.value = response.data.rows.map(sec => ({
                        label: sec.name, // This is what the user sees
                        value: sec.id // This is what will be stored
                    }));

                } else {
                    console.error('Expected "rows" to be an array, but got:', response.data.rows)
                }   

            } catch (error) {
                console.error('Error fetching sections:', error); 
            }
        };

        
        const fetchPersonnel = async () => { 
            try {
                console.log('tp',formData.value.type_id)
                formData.value.branch_id = adminInformation.value ? adminInformation.value.branch_id : formData.value.branch_id 
                const response = await $axios.post('/window/tellers/dropdown', {
                    type_ids_selected: formData.value.type_id,
                    branch_id: formData.value.branch_id,
                });
                    // Ensure response.data.rows is always an array
                    personnelList.value = response.data.rows.map(row => ({
                        teller_name: row.name,
                        teller_id: row.id
                    }));
            } catch (error) {
                console.error('Error fetching sections:', error); 
            }
        };

        watch(() => 
        [formData.value.type_id,formData.value.branch_id], 
        async ([newVal,branch_id],[oldnewVal, oldBranch_id]) => {
            if (!adminInformation.value) {
                if (newVal === null && branch_id === oldBranch_id) {
                personnelList.value = []; // Clear previous personnel list
                await fetchPersonnel(); // Fetch new personnel based on selected type

                }else if (branch_id !== oldBranch_id) {
                    // clear the previos data
                    windowTypeList.value = [];
                    formData.value.type_id = '';
                    await fetchWindowTypes()
                } else if (newVal) {
                    personnelList.value = [];
                    await fetchPersonnel(); // Fetch new personnel based on selected type
                }
            } else {
                formData.value.type_id = newVal
                await fetchPersonnel()
            }
        });

        const fetchBranch = async () => {
            try {
                const { data } = await $axios.post('/type/Branch')

                branch_list.value = data.branch
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
                fetchWindowTypes()
            }
            fetchBranch();
        })

        return{
            props,
            icon,
            showDialog,
            formData,
            formError,
            isLoading,
            formMode,
            closeDialog,
            handleSubmitForm,
            personnelList,
            windowTypeList,
            branch_list,
            adminInformation,
        }
    }
    })
</script>