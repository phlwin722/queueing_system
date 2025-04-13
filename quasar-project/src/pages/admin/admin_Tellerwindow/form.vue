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
                    :disable="!adminInformation? !formData.branch_id : ''"
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
                    :disable="!formData.fetchPersonnel"
                    v-model="formData.teller_id"
                    :options="personnelList"
                    label="Assign personnel" 
                    dense
                    hide-bottom-space
                    :error="formError.hasOwnProperty('teller_id')"
                    :error-message="formError.teller_id"
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
        const tempTelId =ref()
        const branch_list = ref ([]);
        const adminInformation = ref(null);
    
        const initFormData = () => (
            {
                id: null,
                window_name: "",
                type_id: "",
                teller_id: "",
                branch_id: "",
                fetchPersonnel: "",
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

        
        const showDialog = (mode, row) => {
            formMode.value = mode === 'new' ? 'New' : 'Edit';
            

            if (mode === 'edit') {
    
                formData.value = Object.assign({},row)
                tempTelId.value = row.pId
                fetchtypeId()
                
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
                    branch_id: formData.value.branch_id ? formData.value.branch_id : adminInformation.value.branch_id
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
            console.log(error.response.status)
            if(error.response.status === 422){
                formError.value = error.response.data
                
            }else if (error.response.status === 400) {
                formError.value.teller_id = error.response.data.message
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
                formData.value.branch_id = adminInformation.value ? adminInformation.value.branch_id : formData.value.branch_id 
                const response = await $axios.post('/window/tellers/dropdown', {
                    type_id: formData.value.type_id,
                    branch_id: formData.value.branch_id,
                });
                    // Ensure response.data.rows is always an array
                    personnelList.value = response.data.rows;
                    formData.value.fetchPersonnel = personnelList.value.length > 0 ? true : false;

            } catch (error) {
                console.error('Error fetching sections:', error); 
            }
        };
        
/*         const fetchtypeId = async () => { 
            try {
                const response = await $axios.post('/tellers/dropdown', {
                    type_id: formData.value.type_id // Send selected grade level
                });
                tempId.value = response.data.id_type
                console.log(tempId.value)

            } catch (error) {
                console.error('Error fetching sections:', error); 
            }
        }; */

        
        watch(() => 
        [formData.value.type_id,formData.value.branch_id], 
        async ([newVal,branch_id],[oldnewVal, oldBranch_id]) => {
            if (!adminInformation.value) {
                if (newVal === null && branch_id === oldBranch_id) {
                personnelList.value = []; // Clear previous personnel list

                alert('hha')
                await fetchPersonnel(); // Fetch new personnel based on selected type

/*                 // Wait for Vue to update the list, then set the first teller
                nextTick(() => {
                    if (typeof formData.value.type_id === 'string') {
                        formData.value.teller_id = formData.value.teller_id // Assign first teller's ID
                        fetchtypeId()
                    }else{
                        if(personnelList.value.length>0){
                            formData.value.teller_id = personnelList.value[0].value
                            console.log("person: "+formData.value.teller_id)
                            
                        }else{
                            formData.value.teller_id = null
                            return
                        }
                        
                        fetchtypeId()
                    }
                }); */
                }else if (branch_id !== oldBranch_id) {
                    // clear the previos data
                    windowTypeList.value = [];
                    formData.value.type_id = '';
                    alert('bobo')
                    await fetchWindowTypes()
                } else if (newVal) {
                    alert('bobobla')
                    await fetchPersonnel(); // Fetch new personnel based on selected type
                }
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