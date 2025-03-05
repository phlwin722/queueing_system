<template>
    <q-page class="flex flex-center">
      <q-card class="q-pa-md shadow-2" style="width: 350px;">
        <q-card-section>
          <div class="text-h6 text-center">Join the Queue</div>
        </q-card-section>
  
        <q-card-section>
          <q-input v-model="name" label="Full Name" 
          :error="formError.hasOwnProperty('name')"
          :error-message="formError.name"
          outlined dense />
          <q-input v-model="mobile" label="Mobile Number" type="tel" 
          :error="formError.hasOwnProperty('mobile')"
          :error-message="formError.mobile"
          outlined dense class="q-mt-md" />
        </q-card-section>
  
        <q-card-actions align="center">
          <q-btn label="Proceed" color="primary" @click="joinQueue" />
        </q-card-actions>
        <q-inner-loading
        :showing="isLoading"
        label="Please wait..."
        label-class="text-teal"
        label-style="font-size: 1.1em"
      />
      </q-card>
    </q-page>
  </template>
  
  <script>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { $axios, $notify } from 'boot/app'

export default {
  setup() {
    const name = ref('')
    const mobile = ref('')
    const isLoading = ref(false)
    const formError = ref({})
    
    const route = useRoute()  // Use useRoute() correctly
    const token = ref(route.params.token)  // Get token from URL
    const router = useRouter()
    console.log(token.value)
    const processQrCode = async () => {
      try {
        const response = await $axios.post('/scan-qr', { token: token.value })  //  Correct way to send token
        
        if (response.data.success) {
          $notify('positive', 'check', 'Please Register')
        } else {
          alert('Invalid or already used QR code.')
          router.push('/queue-qr')
        }
      } catch (error) {
        alert('Invalid or already used QR code.')
        router.push('/queue-qr')
      }
    }

    const joinQueue = async () => {
      isLoading.value = true
      try {
        const response = await $axios.post('/customer-join', {
          name: name.value,
          mobile: mobile.value
        })

        $notify('positive', 'check', response.message)
        localStorage.setItem('queue_number', response.data.queue_number)
        localStorage.setItem('customer_token', token.value)
        window.location.href = '/customer-dashboard'
      } catch (error) {
        if (error.response.status === 422) {
          formError.value = error.response.data
        } else {
          console.log('error', error)
        }
      } finally {
        isLoading.value = false
      }
    }

    onMounted(processQrCode)  // Runs on page load

    return {
      name,
      mobile,
      joinQueue,
      formError,
      isLoading,
      token
    }
  }
}
</script>

  