<template>
    <div class="flex flex-center">
     <div class="q-pa-md">
      <h2 style="text-center">Login </h2>
     <q-form
       class="q-gutter-md"
     >
       <q-input
         filled
         v-model="formData.Username"
         label="Username "
         type="text"
         hint="Username"
         :error="formError.hasOwnProperty('Username')"
         :error-message="formError.Username"
         >
         </q-input>
       
       <q-input 
           v-model="formData.Password" 
           filled :type="isPwd ? 'password' : 'text'" 
           hint="Password"
           label="Password"
           :error="formError.hasOwnProperty('Password')"
           :error-message="formError.Password"
           >
           <template v-slot:append>
             <q-icon
               :name="isPwd ? 'visibility_off' : 'visibility'"
               class="cursor-pointer"
               @click="isPwd = !isPwd"
             />
           </template>
       </q-input>
       <div>
         <q-btn 
             label="Login" 
             @click="goAdminValidation"
             color="primary"
         />
       </div>
     </q-form>
     <q-inner-loading
        :showing="isLoading"
        label="Please wait..."
        label-class="text-teal"
        label-style="font-size: 1.1em"
      />
     </div>
    </div>
  </template>
  
  <script>

  import { 
    ref,
    onMounted
  } from 'vue'
  import {
    $axios,
    $notify
  } from 'boot/app'

  import {
    useRouter
  } from 'vue-router'

  export default {
   setup () {
    const router = useRouter(); // Initialize Vue Router
    const isLoading = ref(false)

      const initFormData = () => {
      return {
        Username: "",
        Password: "",
      }
    }

    const formData = ref(initFormData ())
    const formError = ref ({})

    const goAdminValidation = async () => {
      isLoading.value = true;
      try {
        const { data } = await $axios.post('/admin/validate', formData.value);
           
        // Store token in sessionStorage instead of localStorage
        sessionStorage.setItem('authToken', data.token); 

        // If login is successful, redirect to the admin dashboard
        $notify('positive','done',data.message)
        router.push('/admin/dashboard'); // Change the path to your desired route

      } catch (error) {
        if (error.response.status === 422) {
          console.log("Validation Errors:", error.response.data); // Show errors in console
          formError.value = error.response.data;
        }
        else if (error.response && error.response.status === 401) {
          $notify('negative','error',"Invalid username or password")
        } else {
          console.log("error", error);
        }
      } finally {
        isLoading.value = false;
      }
    };

    onMounted (() => {
      //Prevent Users from using the back button after logout
      history.pushState(null,null,location.href)
      window.onpopstate = function () {
        history.pushState(null, null, location.href)
      }
    })

     return {
       isPwd: ref(true),
       goAdminValidation,
       formData,
       formError,
      isLoading,
     }
   }
  }
  </script>