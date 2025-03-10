<template>
    <q-page class="q-pa-lg">
    <div class="row q-gutter-md">
        <!-- Profile Section -->
        <q-card class="col-3 q-pa-md">
            <div class="column items-center">
                <q-avatar size="120px" color="grey-3" text-color="black">
                    <q-icon name="person" size="64px" />
                </q-avatar>
        <div class="text-center q-mt-md">
            <div class="text-h6">fetch name</div>
        </div>
        </div>
        </q-card>

        <!-- Account Settings -->
            <q-card class="col q-pa-md">
        <!-- Title and Tabs in One Row -->
            <div class="row items-center justify-between">
                <div class="text-h6 text-primary text-bold">Account Settings</div>
                    <q-tabs v-model="tab" active-color="primary" class="text-primary">
                        <q-tab name="info" label="User Account Info" />
                        <q-tab name="avatar" label="Change Avatar" />
                        <q-tab name="password" label="Change Password" />
                    </q-tabs>
                </div>
                <q-separator class="q-my-md" />

                <q-tab-panels v-model="tab" animated>
                <!-- User Account Info Tab -->
                <q-tab-panel name="info">
                    <q-form @submit="saveChanges">
                        <div class="row q-col-gutter-md">
                            <q-input filled v-model="username" placeholder="fetch admin username" hint="Username" :dense="dense" class="col-4"/>
                            <q-input filled v-model="firstname" placeholder="fetch admin firstname" hint="First Name" :dense="dense" class="col-4"/>
                            <q-input filled v-model="lastname" placeholder="fetch admin lastname" hint="Last Name" :dense="dense" class="col-4"/>
                        </div>

                        <div class="q-mt-lg">
                            <q-btn label="Save Changes" type="submit" color="positive" class="q-pa-xs"/>
                            <q-btn label="Cancel" color="negative" class="q-pa-xs" />
                        </div>
                    </q-form>
                </q-tab-panel>

            <!-- Change Avatar -->
            <q-tab-panel name="avatar">
            <div class="row q-col-gutter-md">
                <!-- Image Preview -->
                <div class="col-3">
                <q-img
                    :src="previewImage || 'no-image.png'"
                    spinner-color="primary"
                    class="shadow-2 rounded-borders"
                    style="width: 150px; height: 150px; object-fit: cover;"
                />
                </div>

                <!-- Upload Section -->
                <div class="col">
                <q-file v-model="selectedImage" label="Select image" accept="image/*" @change="previewFile" filled>
                    <q-icon name="attach_file" />
                </q-file>

                <q-banner class="text-red q-mt-md">
                    <q-icon name="warning" />
                    <strong>NOTE!</strong> Attached image thumbnail must
                </q-banner>

                <div class="q-mt-md">
                    <q-btn label="Submit" color="positive" @click="uploadImage" class="q-mr-sm" />
                    <q-btn label="Cancel" color="negative"/>
                </div>
                </div>
            </div>
            </q-tab-panel>

            <!-- Change Password Panel -->
            <q-tab-panel name="password">
                <q-form @submit="changePassword">
                <div class="row q-col-gutter-md">
                    <q-input filled v-model="currentPassword" type="password" label="Current Password" class="col-12" />
                    <q-input filled v-model="newPassword" type="password" label="New Password" class="col-12" />
                    <q-input filled v-model="confirmPassword" type="password" label="Re-type New Password" class="col-12" />
                </div>

                <div class="q-mt-lg">
                    <q-btn label="Change Password" type="submit" color="positive" class="q-pa-xs"/>
                    <q-btn label="Cancel" color="negative"/>
                </div>
                </q-form>
            </q-tab-panel>

            </q-tab-panels>
            </q-card>
        </div>
    </q-page>
</template>

<script>
export default {
data() {
    return {
      tab: "info", // Default selected tab
    };
},
};
</script>
