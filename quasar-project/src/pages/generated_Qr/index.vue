<template>
  <div class="flex flex-center column">
    <!-- Logo Image -->
    <q-img 
      src="~assets/vrtlogoblack.webp" 
      alt="Logo" 
      fit="contain" 
      class="q-mx-auto q-ma-lg"
      style="max-width: 225px;"
    />

    <!-- QR Code Card -->
    <q-card
      class="q-pa-md shadow-2"
      style="width: 350px; text-align: center;"
    >
      <q-card-section>
        <div class="text-h6">Scan to Join the Queue</div>
      </q-card-section>

      <q-card-section>
        <qrcode-vue
          v-if="registrationLink"
          :value="registrationLink"
          :size="200"
          level="M"
        />
        <div v-else>Generating QR Code...</div>
      </q-card-section>
    </q-card>
  </div>
</template>
  
  <script>
  import { ref, onMounted, onUnmounted } from 'vue'
  import QrcodeVue from 'qrcode.vue'
  import { $axios } from 'boot/app'
  
  export default {
    components: { QrcodeVue },
    setup() {
      const registrationLink = ref(null)
      let intervalId = null  // Store interval ID for cleanup
  
      // Fetch a new QR code link from the backend
      const fetchQrCode = async () => {
        try {
          const response = await $axios.post('/generate-qr')
          
          const fullUrl = response.data.qr_code_url // Example: "http://192.168.1.164:8000/scan-qr/abcd1234"
          const token = fullUrl.slice(22) // Extract token from URL
          
          localStorage.setItem('token', token)
          registrationLink.value = '192.168.0.164:8080/customer-register/' + token
  
          console.log('Token:', token)
        } catch (error) {
          console.error('Error fetching QR code:', error)
        }
      }
  
      onMounted(() => {
        fetchQrCode() // Fetch QR code immediately on mount
        intervalId = setInterval(fetchQrCode, 5000) // Run every 5 seconds
      })
  
      onUnmounted(() => {
        if (intervalId) {
          clearInterval(intervalId) // Clear interval when component is destroyed
        }
      })
  
      return { registrationLink }
    }
  }
  </script>
  
  