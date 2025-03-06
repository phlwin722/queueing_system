<template>
  <div class="flex flex-center q-mt-md q-pa-lg full-height">
    <q-card class="q-pa-lg q-mt-sm shadow-3 login-card">
      <q-card-section class="text-center">
        <h2 class="text-primary welcome-heading">Welcome</h2>
        <p class="text-grey-7">Login to your account</p>
      </q-card-section>
      <q-card-section>
        <q-form class="q-gutter-lg form-container" @submit.prevent="goAdminValidation">
          <q-input
            filled
            v-model="formData.Username"
            label="Username"
            type="text"
            hint="Enter your username"
            :error="formError.hasOwnProperty('Username')"
            :error-message="formError.Username"
            class="none"
          />

          <q-input 
            v-model="formData.Password" 
            filled 
            :type="isPwd ? 'password' : 'text'" 
            hint="Enter your password"
            label="Password"
            :error="formError.hasOwnProperty('Password')"
            :error-message="formError.Password"
            class="none"
          >
            <template v-slot:append>
              <q-icon
                :name="isPwd ? 'visibility_off' : 'visibility'"
                class="cursor-pointer"
                @click="isPwd = !isPwd"
              />
            </template>
          </q-input>

          <q-btn 
            label="Login" 
            type="submit"
            color="primary"
            style="width: 350px;"
          />
</q-form>

      </q-card-section>
      
      <q-inner-loading
        :showing="isLoading"
        label="Please wait..."
        label-class="text-teal"
        label-style="font-size: 1.1em"
      />
    </q-card>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { $axios, $notify } from 'boot/app'

export default {
  setup () {
    const router = useRouter();
    const isLoading = ref(false);
    const isPwd = ref(true);
    const formData = ref({ Username: "", Password: "" });
    const formError = ref({});

    const goAdminValidation = async () => {
      isLoading.value = true;
      try {
        const { data } = await $axios.post('/admin/validate', formData.value);
        // Reset formError to an empty object after successful validation
        formError.value = {}; // Clear error messages

        if (data.result == 'admin') {
          // Store token in sessionStorage instead of localStorage
          sessionStorage.setItem('authToken', data.token); 
          sessionStorage.setItem('adminInformation', JSON.stringify(data.adminInformation));

          // If login is successful, redirect to the admin dashboard
          $notify('positive','done',data.message)
          router.push('/admin/dashboard'); // Change the path to your desired route
        }

      } catch (error) {
        if (error.response.status === 422) {
          console.log("Validation Errors:", error.response.data); // Show errors in console
          formError.value = error.response.data;
        }
        else if (error.response && error.response.status === 401) {
          // Reset formError to an empty object after successful validation
          formError.value = {}; // Clear error messages
          
          $notify('negative','error',"Invalid username or password")
        } else {
          console.log("error", error);
        }
      } finally {
        isLoading.value = false;
      }
    };


    onMounted(() => {
      //Prevent Users from using the back button after logout
      history.pushState(null, null, location.href);
      window.onpopstate = () => history.pushState(null, null, location.href);
    });

     return {
       isPwd,
       goAdminValidation,
       formData,
       formError,
      isLoading,
     }
  }
}
</script>

