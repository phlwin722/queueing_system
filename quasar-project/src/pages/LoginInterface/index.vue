<template>
  <div class="bg-primary background-container"></div>

  <div class="column flex flex-center q-pa-md content-container">
    <q-img
      src="~assets/vrtlogowhite.webp"
      alt="Logo"
      fit="full"
      class="logo"
      style="width: 250px"
    />

    <q-card class="q-pa-lg shadow-3 login-card">
      <q-card-section class="text-center">
        <h2 class="text-primary welcome-heading">Login</h2>
      </q-card-section>
      <q-card-section>
        <q-form
          class="q-gutter-lg form-container"
          @submit.prevent="goAdminValidation"
        >
          <q-input
            filled
            v-model="formData.Username"
            label="Username"
            type="text"
            :error="formError.hasOwnProperty('Username')"
            :error-message="formError.Username"
          />

          <q-input
            v-model="formData.Password"
            filled
            :type="isPwd ? 'password' : 'text'"
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

          <div class="flex justify-center">
            <q-btn
              label="Login"
              type="submit"
              color="primary"
              class="full-width"
            />
          </div>
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
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { $axios, $notify } from "boot/app";

export default {
  setup() {
    const router = useRouter();
    const isLoading = ref(false);
    const isPwd = ref(true);
    const formData = ref({ Username: "", Password: "" });
    const formError = ref({});

    const goAdminValidation = async () => {
      isLoading.value = true;
      try {
        const { data } = await $axios.post("/admin/validate", formData.value);
        // Reset formError to an empty object after successful validation
        formError.value = {}; // Clear error messages

        if (data.result == "admin") {
          // Store token in localStorage instead of localStorage
          localStorage.setItem("authTokenAdmin", data.token);
          localStorage.setItem(
            "adminInformation",
            JSON.stringify(data.adminInformation)
          );

          // If login is successful, redirect to the admin dashboard
          $notify("positive", "done", data.message);
          router.push("/admin/dashboard"); // Change the path to your desired route
        } else {
          const { data } = await $axios.post(
            "/teller/validate",
            formData.value
          );
          if (data.result == "teller") {
            if (data.tellerInformation.type_id != null) {
              // If login is successful, redirect to the admin dashboard
              $notify("positive", "done", data.message);
              // Store token in localStorage instead of localStorage
              localStorage.setItem(
                "authTokenTeller",
                data.tellerInformation.token
              );
              localStorage.setItem(
                "tellerInformation",
                JSON.stringify(data.tellerInformation)
              );
              router.push("/teller/Layout"); // Change the path to your desired route
            } else {
              $notify("negative", "error", "No service type has been assigned");
            }
          }
        }
      } catch (error) {
        if (error.response.status === 422) {
          console.log("Validation Errors:", error.response.data); // Show errors in console
          formError.value = error.response.data;
        } else if (error.response && error.response.status === 401) {
          // Reset formError to an empty object after successful validation
          formError.value = {}; // Clear error messages

          $notify("negative", "error", "Invalid username or password");
        } else if (error.response.status === 400) {
          $notify("negative", "error", "Invalid credentials");
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
    };
  },
};
</script>

<style scoped>
/* Background */
.background-container {
  position: absolute;
  width: 100%;
  min-height: 45vh;
  z-index: -1;
}

/* Content layout */
.content-container {
  min-height: 80vh;
  width: 100%;
  padding: 2rem;
}

/* Logo */
.logo {
  max-width: 350px;
  width: 80%;
  margin-bottom: 1rem;
}

/* Login Card */
.login-card {
  width: 100%;
  max-width: 25rem;
}

/* Responsive Styles */
@media (max-width: 600px) {
  .content-container {
    padding: 1rem;
  }

  .login-card {
    max-width: 100%;
  }
}
</style>
