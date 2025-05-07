<template>
    <q-dialog 
        v-model="icon"
        @hide="closeDialog"
    >
        <q-card>
        <q-card-section class="row items-center q-pb-none">
            <div class="text-h6 text-primary">{{formMode}} Feedback</div>
        <q-space />
            <q-btn icon="close" flat round dense v-close-popup style="width: 24px; height: 24px;"/>
        </q-card-section>

        <q-card-section>
            <div class="row q-col-gutter-md">
                <div class="col-12">
                    <q-input 
                    outlined 
                    readonly
                    v-model="formData.name" 
                    label="Respondent's name" 
                    dense 
                    hide-bottom-space
                    />
                </div>
                <div v-if="!adminInformation" class="col-12">
                    <q-input 
                    outlined
                    type="textarea" 
                    label="Suggestions for Improvement" 
                    filled 
                    dense 
                    readonly
                    v-model="formData.suggestions"
                    class="full-width" 
                    />
                </div>
            </div>
        </q-card-section>

        <q-card-actions class="flex justify-center">
                <q-btn 
                color="positive" 
                label="Close" 
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
    } from 'vue'

    import {
    $notify
    } from 'boot/app'

    export default defineComponent({
    name:'WindowFormPage',
    props: {
        url: String,
        rows: Array
    },
    setup(props){
        const icon = ref(false)
    
        const initFormData = () => (
            {
                name: "",
                suggerstions: "",
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
        
        const showDialog = async (mode, row) => {
            formMode.value = mode === 'new' ? 'New' : 'Show';
            

            if (mode === 'Show') {
                formData.value = Object.assign({},row)
                formData.value.name = row.name ? row.name : 'N/A'
            }

            icon.value = true;
        };

        const handleSubmitForm = async () =>{       
            try{
                closeDialog()
            } 
            catch(error){
                if (status === 422){
                    formError.value = error.response.data
                } else if (status === 400) {
                    formError.value.teller_id = error.response.data.message
                } else {
                    console.error('Unexpected error:', error.message || error)
                    $notify('negative', 'error', 'Something went wrong while saving. Please try again.')
                }
            }finally{
            }
        }

        return{
            props,
            icon,
            showDialog,
            formData,
            formError,
            formMode,
            closeDialog,
            handleSubmitForm,
        }
    }
    })
</script>