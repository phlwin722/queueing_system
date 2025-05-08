<template>
  <!-- Show message for mobile screens -->
  <q-layout v-if="$q.screen.lt.sm">
    <q-header class="q-px-md">
      <q-toolbar>
        <!-- Fullscreen Toggle Button -->
        <q-btn
          flat
          round
          dense
          class="q-mr-sm"
          color="white"
          style="min-width: 32px; width: 32px; height: 32px; position: absolute"
          @click="$q.fullscreen.toggle()"
          :icon="$q.fullscreen.isActive ? 'fullscreen_exit' : 'fullscreen'"
        />
        <q-img
          src="~assets/vrtlogowhite1.png"
          alt="Logo"
          fit="full"
          :style="{
            maxWidth: $q.screen.lt.sm ? '100px' : '160px',
            marginLeft: '50px',
          }"
          class="q-ml-sm"
        />
        <div>{{ formattedString }}</div>

        <q-space />

        <!-- Avatar with Dropdown Menu -->
        <div class="row items-center justify-center q-gutter-md">
          <p class="q-mb-none">
            {{
              `${tellerInformation?.tellerFirstname || ""} ${
                tellerInformation?.tellerLastname || "Loading"
              }`
            }}
          </p>
          <q-avatar
            size="40px"
            class="cursor-pointer"
            @click="menuOpen = !menuOpen"
          >
            <q-img
              :src="imageUrl || require('assets/no-image.png')"
              alt="User Avatar"
            />
          </q-avatar>
        </div>

        <q-menu
          v-model="menuOpen"
          no-parent-event
          anchor="bottom right"
          self="top right"
        >
          <q-list style="min-width: 150px">
            <q-item clickable v-close-popup @click="logout">
              <q-item-section avatar>
                <q-icon name="logout" />
              </q-item-section>
              <q-item-section>Logout</q-item-section>
            </q-item>
          </q-list>
        </q-menu>
      </q-toolbar>
    </q-header>

    <!-- Mobile message for users -->
    <div class="q-pa-md flex flex-center" style="min-height: 100vh">
      <q-card
        class="bg-warning text-secondary q-pa-lg"
        style="max-width: 400px; width: 90%; text-align: center"
      >
        <q-card-section>
          <q-icon name="mobile_off" size="48px" class="q-mb-md text-accent" />
          <div class="text-h6 q-mb-sm text-accent">
            Feature Unavailable on Mobile
          </div>
          <div class="bg-white rounded-borders q-pa-md">
            <p class="text-body1 q-mb-md text-secondary">
              We’re working hard to improve this feature, but it's not available
              on mobile devices just yet. Please visit us from a desktop for the
              full experience. Thank you for your patience!
            </p>
          </div>
        </q-card-section>
      </q-card>
    </div>
  </q-layout>
  <q-layout view="hHh lpR fFf" v-else>
    <q-header class="q-px-md">
      <q-toolbar>
        <!-- Fullscreen Toggle Button -->
        <q-btn
          flat
          round
          dense
          class="q-mr-sm"
          color="white"
          style="min-width: 32px; width: 32px; height: 32px; position: absolute"
          @click="$q.fullscreen.toggle()"
          :icon="$q.fullscreen.isActive ? 'fullscreen_exit' : 'fullscreen'"
        />
        <q-img
          src="~assets/vrtlogowhite1.png"
          alt="Logo"
          fit="full"
          :style="{
            maxWidth: $q.screen.lt.sm ? '100px' : '160px',
            marginLeft: '50px',
          }"
          class="q-ml-sm"
        />
        <div>{{ formattedString }}</div>

        <q-space />

        <!-- Avatar with Dropdown Menu -->
        <div class="row items-center justify-center q-gutter-md">
          <p class="q-mb-none">
            {{
              `${tellerInformation?.tellerFirstname || ""} ${
                tellerInformation?.tellerLastname || "Loading"
              }`
            }}
          </p>
          <q-avatar
            size="40px"
            class="cursor-pointer"
            @click="menuOpen = !menuOpen"
          >
            <q-img
              :src="imageUrl || require('assets/no-image.png')"
              alt="User Avatar"
            />
          </q-avatar>
        </div>

        <q-menu
          v-model="menuOpen"
          no-parent-event
          anchor="bottom right"
          self="top right"
        >
          <q-list style="min-width: 150px">
            <q-item clickable v-close-popup @click="logout">
              <q-item-section avatar>
                <q-icon name="logout" />
              </q-item-section>
              <q-item-section>Logout</q-item-section>
            </q-item>
          </q-list>
        </q-menu>
      </q-toolbar>
    </q-header>

    <q-page-container>
      <q-page class="flex flex-center bg-accent">
        <div class="q-pa-md full-width">
          <!-- Main Row Container -->
          <div class="row q-col-gutter-md justify-center full-height">
            <!-- First Item -->
            <div
              class="col-12 col-md-6"
              v-if="
                newFormattedTime >= fromBreak && formattedCurrentTime < toBreak
              "
            >
              <q-card
                class="q-pa-xl q-mx-auto"
                style="
                  max-width: 650px;
                  min-height: 500px;
                  border-left: 8px solid #1c5d99;
                "
              >
                <q-card-section class="text-center">
                  <q-icon
                    name="access_time"
                    size="60px"
                    style="color: #1c5d99"
                    class="q-mb-md"
                  />
                  <div class="text-h4 text-weight-bold" style="color: #1c5d99">
                    Break Time
                  </div>
                  <div class="text-subtitle1 text-grey-7">
                    You’re currently on break
                  </div>
                </q-card-section>

                <q-separator spaced />

                <q-card-section class="row justify-around items-center q-pt-lg">
                  <div class="column items-center">
                    <q-icon
                      name="schedule"
                      size="32px"
                      style="color: #1c5d99"
                    />
                    <div class="text-caption text-grey-7 q-mt-xs">From</div>
                    <div class="text-h5 q-mt-xs">
                      {{ formatTo12Hour(fromBreak) }}
                    </div>
                  </div>
                  <q-icon name="arrow_forward" size="32px" color="grey-6" />
                  <div class="column items-center">
                    <q-icon
                      name="schedule"
                      size="32px"
                      style="color: #1c5d99"
                    />
                    <div class="text-caption text-grey-7 q-mt-xs">To</div>
                    <div class="text-h5 q-mt-xs">
                      {{ formatTo12Hour(toBreak) }}
                    </div>
                  </div>
                </q-card-section>
              </q-card>
            </div>

            <!-- Second Item -->
            <div v-else class="col-12 col-md-6">
              <q-card
                class="q-pa-md"
                v-if="
                  tellerInformation?.type_name != 'Online Appointment' &&
                  tellerInformation?.type_name != 'Manual Queueing'
                "
              >
                <q-card-section>
                  <q-item>
                    <q-item-section>
                      <q-item-label class="text-h6 text-center">
                        Number of Queue in line: {{ paginatedQueueList.length }}
                      </q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-separator />
                  <q-item>
                    <q-item-section>
                      <q-item-label class="text-h6 text-center"
                        >Waiting Queue</q-item-label
                      >
                    </q-item-section>
                  </q-item>
                  <q-separator />
                  <q-separator />
                  <q-item>
                    <q-item-section>
                      <q-scroll-area
                        :horizontal-thumb-style="{ opacity: 0 }"
                        style="
                          height: 200px;
                          overflow-x: hidden;
                          overflow-x: auto;
                        "
                      >
                        <q-list style="padding: 5px; overflow-x: hidden">
                          <q-item
                            v-if="paginatedQueueList.length === 0"
                            class="text-center text-grey q-pa-md"
                          >
                            <q-item-section>
                              <h5>Queue Empty</h5>
                            </q-item-section>
                          </q-item>

                          <!-- Queue Items -->
                          <template
                            v-for="(customer, index) in paginatedQueueList"
                            :key="customer.id"
                          >
                            <q-item
                              style="height: 80px; border-radius: 10px"
                              class="bg-white draggable-item shadow-2 q-mb-sm"
                              :class="{ 'drag-over': dragOverIndex === index }"
                              draggable="true"
                              @dragstart="onDragStart($event, index)"
                              @dragover.prevent="onDragOver(index)"
                              @dragleave="onDragLeave"
                              @drop="onDrop(index)"
                            >
                              <div
                                class="text-white text-bold glossy"
                                :style="
                                  customer.priority_service
                                    ? {
                                        'background-color': '#fad72a',
                                        padding: '5px',
                                        height: '30px',
                                        'margin-top': '18px',
                                        'margin-right': '8px',
                                      }
                                    : {}
                                "
                              >
                                <!-- Content goes here -->
                                {{ customer.priority_service ? "PRIO" : "" }}
                                <q-tooltip
                                  anchor="center right"
                                  self="center left"
                                  :offset="[10, 10]"
                                  class="bg-secondary"
                                >
                                  {{ customer.priority_service }}
                                </q-tooltip>
                              </div>

                              <!-- Queue Number and Customer Name -->
                              <q-item-section
                                class="flex flex-col justify-center q-pr-md"
                              >
                                <div
                                  class="text-primary text-bold text-h6 q-mb-xs"
                                >
                                  <div
                                    class="text-primary text-bold text-h6 q-mb-xs"
                                  >
                                    {{
                                      `${tellerInformation.indicator}#-${String(
                                        customer.queue_number
                                      ).padStart(3, "0")}`
                                    }}
                                  </div>
                                </div>

                                <p class="text-body2 text-secondary q-mb-none">
                                  {{ customer.name }}
                                </p>
                              </q-item-section>

                              <!-- Conditional Currency Info -->
                              <q-item-section
                                v-if="
                                  customer.currency_name &&
                                  customer.currency_symbol &&
                                  customer.flag
                                "
                                class="flex items-center justify-start q-pr-md"
                              >
                                <span
                                  :class="['fi', customer.flag]"
                                  style="font-size: 1.8em; margin-right: 8px"
                                ></span>
                                <div
                                  class="text-body1"
                                  style="max-width: 200px"
                                >
                                  {{ customer.currency_symbol }}
                                  <br />
                                  {{ customer.currency_name }}
                                </div>
                              </q-item-section>

                              <!-- Cancel Button -->
                              <q-item-section side>
                                <q-btn
                                  label="Cancel"
                                  color="negative"
                                  text-color="white"
                                  unelevated
                                  rounded
                                  dense
                                  style="width: 100px; height: 36px"
                                  class="q-my-xs q-mx-sm shadow-2 hover-opacity"
                                  @click="beforeCancel(customer)"
                                />
                              </q-item-section>

                              <!-- Status Badge -->
                              <q-item-section side>
                                <q-badge
                                  :color="index <= 0 ? 'orange' : 'blue-grey'"
                                  :label="index <= 0 ? 'Up Next' : 'Waiting'"
                                  class="text-white text-bold"
                                  style="
                                    min-width: 100px;
                                    padding: 4px 10px;
                                    border-radius: 12px;
                                    font-size: 1.1em;
                                  "
                                />
                              </q-item-section>
                            </q-item>
                          </template>
                        </q-list>
                      </q-scroll-area>
                    </q-item-section>
                  </q-item>
                </q-card-section>
                <q-separator />
              </q-card>

              <!-- Drag area of Currency -->

              <div class="q-mt-md">
                <!-- <q-scroll-area 
                class="my-scroll" 
                style="height: 250px; overflow-y: auto"> -->
                <div class="row bg-info text-secondary q-pa-md rounded-borders">
                  <div class="col-6"><strong>Currency</strong></div>
                  <div class="col-3"><strong>Buy</strong></div>
                  <div class="col-3"><strong>Sell</strong></div>
                </div>
                <q-scroll-area
                  :horizontal-thumb-style="{ opacity: 0 }"
                  :style="{
                    height:
                      tellerInformation?.type_name == 'Online Appointment' ||
                      tellerInformation?.type_name == 'Manual Queueing'
                        ? '500px'
                        : '200px',
                    marginBottom: '10px',
                  }"
                >
                  <q-item
                    v-for="(row, index) in rowsCurrency"
                    :key="row.id"
                    draggable="true"
                    @dragstart="onDragStartCurrency($event, row)"
                    class="bg-white q-pl-md no-margin hover-animate"
                  >
                    <q-item-section class="q-pt-sm">
                      <div class="row">
                        <div class="col-6">
                          <!-- Flag icon -->
                          <span
                            :class="['fi', row.currency.flag]"
                            style="font-size: 1.5em; margin-right: 8px"
                          ></span>
                          <span
                            >{{ row.currency.symbol }} -
                            {{ row.currency.name }}</span
                          >
                        </div>
                        <div class="col-3">
                          <!-- Buy value -->
                          <span> {{ row.buy }}</span>
                        </div>
                        <div class="col-3">
                          <!-- Sell value -->
                          <span>{{ row.sell }}</span>
                        </div>
                      </div>
                    </q-item-section>
                  </q-item>
                </q-scroll-area>

                <!-- </q-scroll-area> -->
              </div>
            </div>

            <div
              class="col-12 col-md-6"
              v-if="
                (newFormattedTime >= fromBreak &&
                  formattedCurrentTime < toBreak) == false ||
                isCurrentlyServing === true
              "
            >
              <q-card
                class="q-mb-sm bg-primary text-white shadow-3 rounded-borders"
              >
                <q-card-section
                  class="row items-center"
                  v-if="
                    tellerInformation?.type_name != 'Online Appointment' &&
                    tellerInformation?.type_name != 'Manual Queueing'
                  "
                >
                  <q-toggle v-model="autoServing" color="green" />
                  <q-chip
                    v-if="autoServing"
                    color="green"
                    text-color="white"
                    class="q-ml-md"
                  >
                    Automatic Serving ON
                  </q-chip>
                  <q-chip v-else color="red" text-color="white" class="q-ml-md">
                    Automatic Serving OFF
                  </q-chip>
                </q-card-section>
                <q-card-section class="flex flex-center">
                  <q-item>
                    <q-item-section class="text-center">
                      <span class="text-h4 text-bold text-uppercase q-pa-sm">
                        {{ `${tellerInformation?.type_name || "Loading..."}` }}
                      </span>
                    </q-item-section>
                  </q-item>
                </q-card-section>
              </q-card>
              <q-card
                class="q-pa-md"
                v-if="
                  tellerInformation?.type_name != 'Online Appointment' &&
                  tellerInformation?.type_name != 'Manual Queueing'
                "
              >
                <q-card-section>
                  <!-- If the cater line is not empty -->
                  <q-item v-if="currentServing">
                    <q-item-section>
                      <q-item-label class="text-h4 text-center text-primary"
                        ><strong>Current Queue</strong></q-item-label
                      >
                      <q-item-label caption class="text-center"
                        >The queue will be updated once someone is in
                        line.</q-item-label
                      >
                    </q-item-section>
                  </q-item>

                  <!-- If the cater line is empty -->
                  <q-item v-else>
                    <q-item-section>
                      <q-item-label class="text-h4 text-center"
                        ><strong>Queue Idle</strong></q-item-label
                      >
                      <q-item-label caption class="text-center"
                        >The queue will be updated once someone is in
                        line.</q-item-label
                      >
                    </q-item-section>
                  </q-item>

                  <q-separator />
                  <!-- The queue number and name of the customer -->
                  <q-item>
                    <q-item-section>
                      <q-list separator style="min-height: 300px">
                        <!-- Items of queue list -->
                        <div v-if="currentServing">
                          <q-item class="bg-primary rounded-borders">
                            <q-item-section>
                              <h1
                                class="q-mb-sm q-mt-sm text-center text-white"
                              >
                                {{
                                  `${tellerInformation.indicator}#-${String(
                                    currentServing.queue_number
                                  ).padStart(3, "0")}`
                                }}
                              </h1>
                              <p
                                class="text-center text-h6 text-grey-6 text-white"
                              >
                                {{ currentServing.name }}
                              </p>
                            </q-item-section>
                          </q-item>

                          <q-item>
                            <q-item-section>
                              <div class="q-gutter-y-xs q-my-sm items-end">
                                <q-btn
                                  v-if="currentServing && tempTimer == 0"
                                  label="Cancel"
                                  color="red-9"
                                  @click="beforeCancel(currentServing)"
                                />

                                <q-btn
                                  v-if="currentServing"
                                  color="orange"
                                  class="q-ml-sm"
                                  :label="
                                    waiting ? formatTime(tempTimer) : 'Wait'
                                  "
                                  @click="
                                    startWait(
                                      cusId,
                                      currentServing.queue_number
                                    )
                                  "
                                  :disable="waiting"
                                />
                                <q-btn
                                  v-if="currentServing && tempTimer == 0"
                                  label="Finished"
                                  color="primary"
                                  @click="finishCustomer(currentServing.id)"
                                />
                              </div>
                            </q-item-section>
                          </q-item>
                        </div>

                        <!-- Online Appointment -->
                        <div v-else>
                          <q-item class="bg-grey-9 rounded-borders">
                            <q-item-section>
                              <h2
                                class="q-mb-sm q-mt-md text-center text-white loading-dots"
                              >
                                Queueing<span>.</span><span>.</span
                                ><span>.</span>
                              </h2>
                              <h6 class="text-center text-white"></h6>
                            </q-item-section>
                          </q-item>

                          <q-item>
                            <q-item-section>
                              <div class="q-gutter-y-xs q-my-sm"></div>
                            </q-item-section>
                          </q-item>
                        </div>
                      </q-list>
                    </q-item-section>
                  </q-item>
                </q-card-section>
              </q-card>

              <q-card
                class="q-px-md"
                v-if="tellerInformation?.type_name == 'Online Appointment'"
                style="min-height: 500px; padding-bottom: 20px"
              >
                <q-card-section>
                  <div class="row">
                    <div class="col-10">
                      <q-input
                        v-model="tellerInformation.referenceNumber"
                        outlined
                        label="Reference number"
                        hide-bottom-space
                        dense
                        :error="formError.hasOwnProperty('referenceNumber')"
                      />
                    </div>
                    <div class="col-1">
                      <q-btn
                        color="warning"
                        icon="verified"
                        @click="validateReference"
                        class="q-ml-md full-width"
                      >
                        <q-tooltip
                          anchor="center left"
                          self="center right"
                          :offset="[10, 10]"
                        >
                          <strong>Validate </strong>
                        </q-tooltip>
                      </q-btn>
                    </div>
                    <div class="col-1">
                      <q-btn
                        color="negative"
                        icon="close"
                        @click="clearReference"
                        class="q-ml-md full-width"
                      >
                        <q-tooltip
                          anchor="center left"
                          self="center right"
                          :offset="[10, 10]"
                        >
                          <strong>Clear </strong>
                        </q-tooltip>
                      </q-btn>
                    </div>
                  </div>
                </q-card-section>

                <!-- Drop Zone for Currency and customerInfoOnline -->

                <div @dragover.prevent @drop="onDropToCustomerInfo">
                  <q-item v-if="customerInfoOnline != null">
                    <q-item-section>
                      <q-card
                        class="q-px-sm shadow-2 rounded-borders bg-grey-1"
                      >
                        <div class="text-subtitle2 text-primary q-my-sm">
                          Customer Details
                        </div>
                        <q-separator class="q-mb-md" />

                        <q-list dense class="q-gutter-y-xs text-caption">
                          <div
                            v-for="(value, label) in {
                              'Full Name': customerInfoOnline.fullname,
                              Branch: customerInfoOnline.branch_name,
                              Status: customerInfoOnline.status,
                              'Service Type': customerInfoOnline.name,
                              'Appointment Date':
                                customerInfoOnline.appointment_date,
                              Email: customerInfoOnline.email,
                            }"
                            :key="label"
                            class="row q-mb-xs items-center"
                          >
                            <div class="col-4 text-grey-7 text-weight-medium">
                              {{ label }}:
                            </div>
                            <div class="col-8 text-dark ellipsis">
                              {{ value }}
                            </div>
                          </div>
                          <!-- Display Currency Details if Available -->
                          <template v-if="customerInfoOnline.currencyDetails">
                            <div class="row q-mb-xs items-center">
                              <div class="col-4 text-grey-7 text-weight-medium">
                                Currency:
                              </div>
                              <div class="col-8 text-dark ellipsis">
                                <span
                                  :class="[
                                    'fi',
                                    customerInfoOnline.currencyDetails.currency
                                      .flag,
                                  ]"
                                  style="margin-right: 8px"
                                ></span>
                                {{
                                  customerInfoOnline.currencyDetails.currency
                                    .symbol
                                }}
                                -
                                {{
                                  customerInfoOnline.currencyDetails.currency
                                    .name
                                }}
                              </div>
                            </div>
                            <div class="row q-mb-xs items-center">
                              <div class="col-4 text-grey-7 text-weight-medium">
                                Buy:
                              </div>
                              <div class="col-8 text-dark ellipsis">
                                {{ customerInfoOnline.currencyDetails.buy }}
                              </div>
                            </div>
                            <div class="row q-mb-xs items-center">
                              <div class="col-4 text-grey-7 text-weight-medium">
                                Sell:
                              </div>
                              <div class="col-8 text-dark ellipsis">
                                {{ customerInfoOnline.currencyDetails.sell }}
                              </div>
                            </div>
                          </template>
                        </q-list>
                      </q-card>

                      <div class="q-mt-md text-right">
                        <q-btn
                          color="primary"
                          label="Assigned Teller"
                          @click="handleAssignedTellerClick"
                          class="full-width"
                          style="max-width: 200px"
                        />
                      </div>
                    </q-item-section>
                  </q-item>

                  <q-item v-else>
                    <q-item-section>
                      <q-list style="max-height: 500px">
                        <div>
                          <q-item class="bg-grey-9 rounded-borders">
                            <q-item-section>
                              <h1
                                class="q-mb-sm q-mt-md text-center text-white loading-dots"
                              >
                                Waiting<span>.</span><span>.</span
                                ><span>.</span>
                              </h1>
                              <h6 class="text-center text-white"></h6>
                            </q-item-section>
                          </q-item>

                          <q-item>
                            <q-item-section>
                              <div class="q-gutter-y-xs q-my-sm"></div>
                            </q-item-section>
                          </q-item>
                        </div>
                      </q-list>
                    </q-item-section>
                  </q-item>
                </div>
              </q-card>

              <q-card
                class="q-px-md"
                style="height: 480px"
                v-if="tellerInformation?.type_name == 'Manual Queueing'"
              >
                <q-card-section>
                  <div class="row">
                    <div class="col-12">
                      <q-input
                        v-model="name"
                        label="Full Name"
                        :error="formError.hasOwnProperty('name')"
                        :error-message="formError.name"
                        outlined
                        dense
                      />
                    </div>
                    <div class="col-12">
                      <q-input
                        v-model="email"
                        label="Email address"
                        :error="formError.hasOwnProperty('email')"
                        :error-message="formError.email"
                        outlined
                        dense
                      />
                    </div>
                    <div class="col-12">
                      <q-input
                        v-model="Address"
                        label="Address"
                        :error="formError.hasOwnProperty('Address')"
                        :error-message="formError.Address"
                        outlined
                        dense
                      />
                    </div>
                    <div class="col-12">
                      <q-select
                        v-model="selectId"
                        :label="
                          isServiceAvailable
                            ? 'Service Available'
                            : 'No Service Available'
                        "
                        transition-show="flip-up"
                        transition-hide="flip-down"
                        outlined
                        @update:modelValue="fecthCurrencty"
                        :error="formError.hasOwnProperty('selectId')"
                        :error-message="formError.selectId"
                        dense
                        :options="categoriesList"
                        option-label="name"
                        option-value="id"
                      />
                    </div>
                    <div
                      class="col-12"
                      v-if="currentCiesList && currentCiesList.length > 0"
                    >
                      <q-select
                        v-model="currencySelected"
                        label="Currency Available"
                        transition-show="flip-up"
                        transition-hide="flip-down"
                        outlined
                        emit-value
                        map-options
                        dense
                        :options="currentCiesList"
                        option-label="name"
                        :error="formError.hasOwnProperty('currency')"
                        :error-message="formError.currency"
                        option-value="id"
                      >
                        <template v-slot:option="scope">
                          <q-item v-bind="scope.itemProps">
                            <q-item-section avatar>
                              <span :class="['fi', scope.opt.flag]"></span>
                            </q-item-section>
                            <q-item-section>
                              <q-item-label
                                >{{ scope.opt.symbol }} -
                                {{ scope.opt.name }}</q-item-label
                              >
                              <q-item-label caption
                                >Buy: {{ scope.opt.buy_value }} | Sell:
                                {{ scope.opt.sell_value }}</q-item-label
                              >
                            </q-item-section>
                          </q-item>
                        </template>

                        <template v-slot:selected-item="scope">
                          <div class="flex items-center">
                            <span
                              :class="['fi', scope.opt.flag]"
                              style="margin-right: 8px"
                            ></span>
                            {{ scope.opt.symbol }} - {{ scope.opt.name }}
                          </div>
                        </template>
                      </q-select>
                    </div>
                    <div class="col-12">
                      <q-checkbox
                        v-model="customModel"
                        color="green"
                        label="Priority services"
                        true-value="yes"
                        false-value="no"
                      />
                    </div>
                    <div class="col-12">
                      <q-select
                        v-if="customModel === 'yes'"
                        v-model="prioritySelected"
                        label="Priority Service"
                        transition-show="flip-up"
                        transition-hide="flip-down"
                        outlined
                        emit-value
                        map-options
                        dense
                        :options="categoriesPriority"
                        :error="formError.hasOwnProperty('priority')"
                        :error-message="formError.priority"
                        option-label="name"
                        option-value="id"
                      />
                    </div>
                    <div class="col-12">
                      <q-card-actions align="center">
                        <q-btn
                          label="Print"
                          color="primary"
                          @click="joinQueue"
                        />
                      </q-card-actions>
                    </div>
                  </div>
                </q-card-section>
              </q-card>
            </div>
          </div>
        </div>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script>
import {
  ref,
  computed,
  onMounted,
  onUnmounted,
  watch,
  nextTick,
  onBeforeUnmount,
} from "vue";
import { $axios, $notify, Dialog } from "boot/app";
import { useQuasar } from "quasar";
import { useRouter } from "vue-router";
import { debounce } from "quasar";

import QrCode from "qrcode"; // import the qrcode package
import { BarController } from "chart.js";
import { Bar } from "vue-chartjs";

export default {
  setup() {
    const cusId = ref();
    const isServiceAvailable = ref(false);
    const queueList = ref([]);
    const router = useRouter();
    const currentServing = ref(null);
    const waiting = ref(false);
    const waitTime = ref(30);
    const tempTimer = ref();
    const prepared = ref();
    const originalWaitTime = ref(0);
    const isQueuelistEmpty = ref(false);
    let waitTimer = null;
    const noOfQueue = ref();
    const imageUrl = ref();
    const rowsCurrency = ref([]);
    const isLoading = ref(false);
    const autoServing = ref(false);
    let refreshInterval = null;
    const formError = ref({});
    const $dialog = useQuasar();
    const isCurrentlyServing = ref(false);

    const selectId = ref(null);
    const name = ref("");
    const email = ref("");
    const email_status = ref("sending_customer");
    const type_id = ref(null);
    const currencySelected = ref();
    const categoryForeignExchange = ref();
    const categoriesList = ref([]);
    const currentCiesList = ref([]);
    const indicator = ref("");
    const generatedQrValue = ref("");
    const ServiceAvail = ref("");
    const customModel = ref("no");
    const prioritySelected = ref();
    const Address = ref("");

    // Pagination
    const currentPage = ref(1);
    const itemsPerPage = 10;

    // UI Functions
    const $q = useQuasar();
    const menuOpen = ref(false);
    const toggleFullscreen = () => {
      $q.fullscreen.toggle();
    };

    const customerInfoOnline = ref(null);
    const draggedCurrency = ref(null);

    // Teller Information
    const tellerInformation = ref({
      id: "",
      tellerFirstname: "",
      tellerLastname: "",
      type_id: "",
      type_name: "",
      branch_id: "",
      referenceNumber: "",
    });

    // Fetch queue data with error handling
    const QueueListlastUpdatedAt = ref(null); // default to null
    let polling = true;
    const fetchQueue = async () => {
      if (!polling) return;
      try {
        isLoading.value = true;

        // Load locally stored queue order if available
        const storedQueue = JSON.parse(localStorage.getItem("queueList")) || [];
        const response = await $axios.post("/teller/queue-list", {
          type_id: tellerInformation.value.type_id,
          teller_id: tellerInformation.value.id,
          last_updated: QueueListlastUpdatedAt.value,
          branch_id: tellerInformation.value.branch_id,
        });

        if (response.data.updated) {
          let fetchedQueue = response.data.queue;

          // fetchedQueue = fetchedQueue.sort((a, b) => {
          //   if (a.priority_service && !b.priority_service) return -1;
          //   if (!a.priority_service && b.priority_service) return 1;
          // });
          // console.log(fetchedQueue)

          // Update and store current serving
          currentServing.value = response.data.current_serving;
          console.log(currentServing.value);
          if (currentServing.value !== null) {
            isCurrentlyServing.value = true;
          }
          localStorage.setItem(
            "currentServing",
            JSON.stringify(currentServing.value)
          );

          // Remove current serving from the queue list
          // const updatedQueue = fetchedQueue.filter(
          //   (q) => q.id !== currentServing.value?.id
          // );

          // Preserve the local order while updating new queue items
          queueList.value = fetchedQueue;
          if (currentServing.value !== null) {
            cusId.value = currentServing.value.id;
          }
          // Save updated queue order
          localStorage.setItem("queueList", JSON.stringify(queueList.value));

          noOfQueue.value = queueList.value.length;

          isQueuelistEmpty.value = queueList.value.length == 0;
          QueueListlastUpdatedAt.value = response.data.last_updated_at;
        }
      } catch (error) {
        console.error("Error fetching queue:", error);
        //   $notify("negative", "error", "Failed to fetch queue data");
      } finally {
        isLoading.value = false;
        if (noOfQueue.value > 5) {
          if (polling) setTimeout(fetchQueue, 10000);
        } else {
          if (polling) setTimeout(fetchQueue, 5000);
        }
      }
    };

    // Helper function to maintain the local order
    // const reorderQueue = (storedQueue, updatedQueue) => {
    //   const orderMap = new Map(storedQueue.map((q, index) => [q.id, index]));
    //   return updatedQueue.sort(
    //     (a, b) =>
    //       (orderMap.get(a.id) ?? Infinity) - (orderMap.get(b.id) ?? Infinity)
    //   );
    // };
    // const fetchIdLastUpdatedAt = ref(null); // last update tracker
    // let fetchIdPolling = true; // Flag to control recursive polling
    // const fetchId = async () => {
    //   if (!fetchIdPolling) return; // Prevent re-fetch if polling is stopped
    //   try {

    //     const response = await $axios.post("/teller/queue-list", {
    //       type_id: tellerInformation.value.type_id,
    //       teller_id: tellerInformation.value.id,
    //       last_updated: fetchIdLastUpdatedAt.value,
    //     });
    //     if (response.data.updated) {
    //       if (response.data.current_serving) {
    //       cusId.value = response.data.current_serving.id;
    //       console.log(cusId.value)
    //     }
    //       fetchIdLastUpdatedAt.value = response.data.last_updated_at;
    //     }

    //   } catch (error) {
    //     console.error("Error fetching customer ID:", error);
    //   }finally {
    //     if (fetchIdPolling) setTimeout(fetchId, 3000); // Poll every 3 seconds
    //   }
    // };
    // Cater customer with error handling
    const caterCustomer = async (customerId, type_id) => {
      try {
        // Update UI immediately
        const customer = queueList.value.find((q) => q.id === customerId);
        await $axios.post("/teller/cater", {
          id: customerId,
          service_id: type_id,
          teller_id: tellerInformation.value.id,
        });
        if (customer) {
          customer.status = "serving";
          currentServing.value = customer;
        }
      } catch (error) {
        console.error("Error catering customer:", error);
      }
    };

    // Cancel dialog with confirmation
    const beforeCancel = (row) => {
      $dialog
        .dialog({
          title: "Confirm",
          message: `Are you sure you want to cancel ${row.name}?`,
          cancel: true,
          persistent: true,
          ok: {
            label: "Yes",
            color: "primary",
            unelevated: true,
            style: "width: 125px;",
          },
          cancel: {
            label: "Cancel",
            color: "red-8",
            unelevated: true,
            style: "width: 125px;",
          },
          style: "border-radius: 12px; padding: 16px;",
        })
        .onOk(() => {
          cancelCustomer(row.id);
        });
    };

    // Cancel customer with error handling
    const cancelCustomer = async (customerId) => {
      try {
        await $axios.post("/teller/cancel", { id: customerId });
        $notify(
          "positive",
          "check",
          "Customer has been removed from the queue."
        );
        // stopWait();
      } catch (error) {
        console.error("Error canceling customer:", error);
        $notify("negative", "error", "Failed to cancel customer.");
      }
    };

    // Finish serving customer with error handling
    const finishCustomer = async (customerId) => {
      try {
        await $axios.post("/teller/finish", { id: customerId });
        await serveEnd();
        serveStartTime = null; // Reset
        localStorage.removeItem(
          "serveStartTime" + tellerInformation.value.id.toString()
        );
        $notify("positive", "check", "Customer has been marked as finished.");
      } catch (error) {
        console.error("Error finishing customer:", error);
        $notify("negative", "error", "Failed to finish serving.");
      }
    };

    // Start waiting process with error handling
    const startWait = async (customerId, queueNumber) => {
      try {
        if (waiting.value) return;

        await $axios.post("/waitCustomer", { id: customerId });

        waiting.value = true;

        // Fetch and store the original wait time if not set
        if (originalWaitTime.value === 0) {
          originalWaitTime.value =
            prepared.value === "Minutes" ? waitTime.value * 60 : waitTime.value;
        }

        // Set the start time in localStorage
        const startTime = Math.floor(Date.now() / 1000);
        localStorage.setItem("wait_start_time", startTime);
        localStorage.setItem("wait_duration", originalWaitTime.value);

        // Reset the wait time
        tempTimer.value = originalWaitTime.value;

        $notify("positive", "check", "Waiting for Customer");

        // Clear any existing timer
        if (waitTimer) clearInterval(waitTimer);

        startTimer(customerId);
      } catch (error) {
        console.error("Error starting wait:", error);
        $notify("negative", "error", "Failed to set waiting customer.");
      }
    };

    const resetWait = async (id) => {
      try {
        await $axios.post("/waitCustomerReset", { id: id });
      } catch (error) {
        console.error("Error resetting wait:", error);
      }
    };

    // Start the countdown timer
    const startTimer = () => {
      if (waitTimer) clearInterval(waitTimer);

      waitTimer = setInterval(() => {
        const now = Math.floor(Date.now() / 1000);
        const startTime =
          parseInt(localStorage.getItem("wait_start_time")) || 0;
        const duration = parseInt(localStorage.getItem("wait_duration")) || 0;
        const elapsed = now - startTime;
        const remaining = duration - elapsed;

        if (remaining >= 0) {
          tempTimer.value = remaining;
          if (tempTimer.value === 0) {
            resetWait(cusId.value);
          }
        } else {
          stopWait();
          originalWaitTime.value = 0;
          localStorage.removeItem("wait_start_time");
          localStorage.removeItem("wait_duration");
        }
      }, 1000);
    };

    const formatTo12Hour = (time) => {
      const [hour, minute] = time.split(":").map(Number);
      const ampm = hour >= 12 ? "PM" : "AM";
      const formattedHour = hour % 12 || 12; // Convert 0 or 12 to 12, 13 to 1, etc.
      return `${formattedHour}:${minute.toString().padStart(2, "0")} ${ampm}`;
    };

    // Fetch waiting time with error handling
    const fetchWaitingtimelastUpdatedAt = ref(null); // default to null
    let fetchWaitingtimepolling = true;
    const fetchWaitingtime = async () => {
      if (!fetchWaitingtimepolling) return;
      try {
        const { data } = await $axios.post("/admin/waiting_Time-fetch", {
          last_updated: fetchWaitingtimelastUpdatedAt.value,
          branch_id: tellerInformation.value.branch_id,
        });
        if (data.updated) {
          if (data?.dataValue?.Waiting_time) {
            waitTime.value = data.dataValue.Waiting_time;
          }

          fetchWaitingtimelastUpdatedAt.value = data.last_updated_at;
        }
      } catch (error) {
        console.error("Error fetching waiting time:", error);
      } finally {
        if (fetchWaitingtimepolling) setTimeout(fetchWaitingtime, 10000);
      }
    };

    // Format time as MM:SS
    const formatTime = (seconds) => {
      if (seconds == null) return ".....";
      if (seconds >= 60) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        return `${minutes}m ${remainingSeconds}s`;
      }
      return `${seconds}s`;
    };

    // Stop waiting process
    const stopWait = () => {
      waiting.value = false;
      if (waitTimer) {
        clearInterval(waitTimer);
        waitTimer = null;
      }
      localStorage.removeItem("wait_start_time");
      localStorage.removeItem("wait_duration");
    };

    watch(
      () => queueList.value.length,
      (newLength, oldLength) => {
        if (newLength < oldLength) {
          // Only run when someone leaves
          debouncedUpdateQueuePositions();
        }
      }
    );

    // Computed property for paginated queue list
    const paginatedQueueList = computed(() => queueList.value);

    watch(
      () => currentServing.value,
      (newValue) => {
        if (newValue !== null) {
          // If a customer is currently being served, stop the auto-serving interval
          isCurrentlyServing.value = true;
        } else {
          // If no customer is being served, start the auto-serving interval
          isCurrentlyServing.value = false;
        }
      }
    );

    const debouncedUpdateQueuePositions = debounce(async () => {
      const updatedPositions = paginatedQueueList.value.map(
        (customer, index) => ({
          id: customer.id,
          position: index + 1,
        })
      );

      try {
        await $axios.post("/update-queue-positions", {
          positions: updatedPositions,
        });
        // Consider adding success feedback
      } catch (error) {
        console.error("Error updating positions:", error);
        // Add user feedback here (e.g., toast notification)
      }
    }, 300); // Reduced debounce time

    // Watch both length and array reference
    let autoServingInterval = null; // Store the interval ID
    let serveStartTime = null;

    // Watch for changes in autoServing state
    watch(autoServing, (newValue) => {
      // Check if the user is on break first
      if (onBreak.value) {
        // Notify user that they're on break
        $notify("primary", "info", "I'm taking a break");

        // Clear the interval if it was running
        if (autoServingInterval) {
          clearInterval(autoServingInterval);
          autoServingInterval = null;
        }
      } else {
        if (newValue) {
          // Auto Serving is enabled
          $notify("positive", "check", "Automatic Serving Enabled");

          // Start the interval when autoServing is turned on
          autoServingInterval = setInterval(() => {
            if (
              queueList.value.length > 0 &&
              queueList.value[0].status === "waiting" &&
              currentServing.value == null
            ) {
              const nextCustomer = queueList.value[0];

              if (nextCustomer) {
                setTimeout(() => {
                  caterCustomer(nextCustomer.id, nextCustomer.type_id);
                  startWait(nextCustomer.id, nextCustomer.queue_number);
                  serveStartTime = new Date();
                  const startingTime = serveStartTime.toLocaleTimeString();

                  // Store serving time in localStorage
                  localStorage.setItem(
                    "serveStartTime" + tellerInformation.value.id.toString(),
                    serveStartTime
                  );
                  localStorage.setItem(
                    "startingTime" + tellerInformation.value.id.toString(),
                    startingTime
                  );
                }, 2000); // Delay for 3 seconds before serving the customer
              }
            }
          }, 5000); // Check every 2 seconds
        } else {
          // Auto Serving is disabled
          $notify("positive", "check", "Automatic Serving Disabled");

          // Clear the interval when autoServing is turned off
          if (autoServingInterval) {
            clearInterval(autoServingInterval);
            autoServingInterval = null;
          }
        }
      }
    });

    const serveEnd = async () => {
      const savedStartTimeStr = localStorage.getItem(
        "serveStartTime" + tellerInformation.value.id.toString()
      );
      const startingTime = localStorage.getItem(
        "startingTime" + tellerInformation.value.id.toString()
      );
      if (savedStartTimeStr && startingTime) {
        const savedStartTime = new Date(savedStartTimeStr); // ✅ convert to Date
        const now = new Date();
        const endingTime = now.toLocaleTimeString();
        const diffMs = now - savedStartTime; // in ms
        let minutes = Math.round(diffMs / 60000); // convert to minutes
        if (minutes < 1) minutes = 1;
        // const seconds = Math.floor((diffMs % 60000) / 1000); // if you want seconds too

        // Save to backend
        try {
          await $axios.post("/teller/save-serving-time", {
            minutes,
            startingTime,
            endingTime,
            type_id: tellerInformation.value.type_id,
            teller_id: tellerInformation.value.id,
            branch_id: tellerInformation.value.branch_id,
          });
        } catch (err) {
          console.error("Failed to save serving time", err);
        }
      }
    };

    const fromBreak = ref("");
    const toBreak = ref("");
    const formattedCurrentTime = ref("");
    const newTime = ref("");
    const newFormattedTime = ref("");
    const originalFromBreak = ref("");
    const hasNotified = ref(false);
    const onBreak = ref(false);
    const fetchBreakTime = async () => {
      try {
        const { data } = await $axios.post("/admin/fetch_break_time", {
          branch_id: tellerInformation.value.branch_id,
        });
        // Correctly assign break start & end times
        if (data?.dataValue) {
          fromBreak.value = data.dataValue.break_from.slice(0, 5); // Start of break
          toBreak.value = data.dataValue.break_to.slice(0, 5); // End of break
          //  Get current time in HH:mm format
          const currentTime = new Date();
          const currentHour = currentTime
            .getHours()
            .toString()
            .padStart(2, "0");
          const currentMinutes = currentTime
            .getMinutes()
            .toString()
            .padStart(2, "0");
          console.log("Updated ", fromBreak.value, toBreak.value);
          formattedCurrentTime.value = `${currentHour}:${currentMinutes}`;
          const totalMinutes = parseTime(fromBreak.value) - 5;
          newTime.value = formatTime2(totalMinutes);
          const OrgtotalMinutes = parseTime(fromBreak.value);
          originalFromBreak.value = formatTime2(OrgtotalMinutes);
          const totalFormatMinutes = parseTime(formattedCurrentTime.value);
          newFormattedTime.value = formatTime2(totalFormatMinutes);

          if (
            newFormattedTime.value >= newTime.value &&
            newFormattedTime.value < originalFromBreak.value &&
            hasNotified.value == false
          ) {
            hasNotified.value = true;
            $notify(
              "positive",
              "check",
              "5 minutes before break time, please consider turning off auto serving"
            );
          }

          if (
            newFormattedTime.value >= fromBreak.value &&
            formattedCurrentTime.value < toBreak.value
          ) {
            hasNotified.value = false;
            onBreak.value = true;
            if (autoServing.value == true) {
              autoServing.value = false;
              $dialog
                .dialog({
                  title: "Turn off Auto Serving",
                  message: "Auto serving will be turned off automatically",
                  cancel: false,
                  persistent: true,
                  color: "primary",
                  ok: {
                    label: "OK",
                    color: "primary", // Make confirm button red
                    unelevated: true, // Flat button style
                    style: "width: 125px;",
                  },
                  style: "border-radius: 12px; padding: 16px;",
                })
                .onOk(async () => {
                  autoServing.value = false;
                })
                .onDismiss(() => {
                  autoServing.value = false;
                });
            }
          } else {
            onBreak.value = false;
          }
        } else {
          console.warn("No break time found");
        }
      } catch (error) {
        console.error("Error fetching break time:", error);
      }
    };

    function parseTime(timeString) {
      // Make sure we're working with a string (access .value if it's a Vue ref)
      const timeStr =
        typeof timeString === "object" && "value" in timeString
          ? timeString.value
          : timeString;

      const [hours, minutes] = timeStr.split(":").map(Number);
      return hours * 60 + minutes;
    }

    function formatTime2(totalMinutes) {
      const hours = Math.floor(totalMinutes / 60) % 24;
      const minutes = totalMinutes % 60;
      return `${hours.toString().padStart(2, "0")}:${minutes
        .toString()
        .padStart(2, "0")}`;
    }

    const fetchTodayServingStats = async () => {
      try {
        const response = await $axios.post("/teller/today-serving-stats", {
          type_id: tellerInformation.value.type_id,
        });
        const updatedServingTime = Math.round(response.data.avg);
        await $axios.post("/teller/update-serving-time", {
          minutes: updatedServingTime,
          type_id: tellerInformation.value.type_id,
          branch_id: tellerInformation.value.branch_id,
        });
      } catch (error) {
        console.error("Failed to fetch today's serving stats", error);
      }
    };
    // Drag and drop improvements
    let draggedIndex = null;
    const dragOverIndex = ref(null);
    const isDragging = ref(false);
    let dragImage = null; // Track the drag image

    const onDragStart = (event, index) => {
      draggedIndex = index;
      isDragging.value = true;

      // Clean up any existing drag image
      if (dragImage && document.body.contains(dragImage)) {
        document.body.removeChild(dragImage);
      }

      dragImage = event.target.cloneNode(true);
      Object.assign(dragImage.style, {
        position: "absolute",
        top: "-9999px",
        width: `${event.target.offsetWidth}px`,
        height: `${event.target.offsetHeight}px`,
        opacity: "1",
        background: "white",
        border: "2px solid #1976d2",
        boxShadow: "0px 4px 10px rgba(0,0,0,0.3)",
      });

      document.body.appendChild(dragImage);
      event.dataTransfer.setDragImage(dragImage, 0, 0);
    };

    const cleanupDrag = () => {
      if (dragImage && document.body.contains(dragImage)) {
        document.body.removeChild(dragImage);
      }
      dragImage = null;
    };

    const onDragOver = async (index) => {
      if (dragOverIndex.value !== index) {
        dragOverIndex.value = index;
        await nextTick();
      }
    };

    const onDragLeave = () => {
      dragOverIndex.value = null;
      cleanupDrag();
    };

    const onDrop = async (targetIndex) => {
      if (draggedIndex === null || draggedIndex === targetIndex) return;

      const item = queueList.value.splice(draggedIndex, 1)[0];
      queueList.value.splice(targetIndex, 0, item);

      localStorage.setItem("queueList", JSON.stringify(queueList.value));

      draggedIndex = null;
      dragOverIndex.value = null;
      isDragging.value = false;
      cleanupDrag();

      // Immediately update positions without waiting for debounce
      await debouncedUpdateQueuePositions();
      debouncedUpdateQueuePositions.flush(); // Force immediate execution
    };

    const logout = async () => {
      try {
        let isfinishServing = currentServing.value == null;
        console.log(isfinishServing);
        if (isfinishServing == false) {
          $notify(
            "negative",
            "error",
            "Please finish serving before logging out."
          );
          return;
        } else {
          const { data } = await $axios.post("/teller/logout", {
            teller_id: tellerInformation.value.id,
            type_id: tellerInformation.value.type_id,
            branch_id: tellerInformation.value.branch_id,
          });
          if (data.message) {
            localStorage.removeItem("authTokenTeller");
            localStorage.removeItem("tellerInformation");
            polling = false;
            fetchWaitingtimepolling = false;
            // fetchIdPolling = false;
            router.push("/login");
            setTimeout(() => {
              window.location.reload();
            }, 100);
          }
        }
      } catch (error) {
        if (error.response.status === 400) {
          $notify("negative", "error", error.response.data.message);
        }
      }
    };

    // Table columns for currency
    const columns = ref([
      {
        name: "currency",
        align: "left",
        label: "Currency",
        field: "currency",
        sortable: true,
      },
      { name: "buy", align: "left", label: "Buy", field: "buy" },
      { name: "sell", align: "left", label: "Sell", field: "sell" },
    ]);

    // Fetch currency data with error handling
    const fetchCurrency = async () => {
      try {
        const { data } = await $axios.post("/currency/showData");
        rowsCurrency.value = data.rows.map((row) => ({
          id: row.id,
          currency: {
            flag: row.flag,
            symbol: row.currency_symbol,
            name: row.currency_name,
          },
          buy: `${row.buy_value}`,
          sell: `${row.sell_value}`,
        }));
      } catch (error) {
        console.error("Error fetching currency data:", error);
      }
    };

    // Fetch the name of value of type id on service type
    const fetchType_idValue = async () => {
      try {
        const { data } = await $axios.post("/teller/typeid-value", {
          type_id: tellerInformation.value.type_id,
        });
        tellerInformation.value.type_name = data.servicename;
        tellerInformation.value.indicator = data.indicator;
      } catch (error) {
        console.error("Error fetching service type:", error);
      }
    };

    const fetch_Image = async () => {
      try {
        const { data } = await $axios.post("/teller/image-teller", {
          id: tellerInformation.value.id,
        });
        imageUrl.value = data.Image;
      } catch (error) {
        console.error("Error fetching teller image:", error);
      }
    };

    // Timer references for cleanup

    let currencyInterval;
    let intervalId = null;

    onMounted(() => {
      try {
        $dialog.loading.show({
          message: "Process is in progress. Hang on...",
        });

        const storedTellerInfo = localStorage.getItem("tellerInformation");
        if (storedTellerInfo) {
          tellerInformation.value = JSON.parse(storedTellerInfo);
          fetchType_idValue();
          fetch_Image();
          // Start periodic data fetching
          fetchQueue();
          fetchWaitingtime();
          // fetchId()
          fetchTodayServingStats();
          // Start currency data fetching
          fetchCurrency();
          setInterval(() => {
            fetchCategories();
          }, 1500);
          currencyInterval = setInterval(fetchCurrency, 30000);
          fetchBreakTime();
          intervalId = setInterval(() => {
            fetchBreakTime();
          }, 30000);
          // Restore wait timer if exists
          const startTime =
            parseInt(localStorage.getItem("wait_start_time")) || 0;
          const duration = parseInt(localStorage.getItem("wait_duration")) || 0;
          if (startTime && duration) {
            waiting.value = true;
            startTimer();
          }
        } else {
          console.error("No teller information found");
          router.push("/login");
        }
      } catch (error) {
        console.error("Initialization error:", error);
        router.push("/login");
      } finally {
        setTimeout(() => {
          $dialog.loading.hide();
        }, 1200);
      }
    });

    const validateReference = async () => {
      try {
        $dialog.loading.show({
          message: "Process is in progress. Hang on...",
        });

        formError.value = {};
        const { data } = await $axios.post("/queue/ReferenceNumber", {
          referenceNumber: tellerInformation.value.referenceNumber,
          branch_id: tellerInformation.value.branch_id,
        });

        if (data.value) {
          if (data.value.status == "Booked") {
            customerInfoOnline.value = data.value;
          }
        }
      } catch (error) {
        if (error.response?.status === 422) {
          customerInfoOnline.value = null;
          $notify(
            "negative",
            "error",
            error.response.data.errors.referenceNumber
          );
          formError.value.referenceNumber =
            error.response.data.errors.referenceNumber;
        } else if (error.response?.status === 400) {
          customerInfoOnline.value = null;
          $notify("negative", "error", error.response.data.errors);
        }
      } finally {
        $dialog.loading.hide(); // ✅ Use the correct method
      }
    };

    const handleAssignedTellerClick = async () => {
      if (customerInfoOnline.value?.name === "Foreign Exchange") {
        if (customerInfoOnline.value.currencyDetails) {
          handleAssignedTeller(customerInfoOnline.value.currencyDetails.id);
        } else {
          $notify(
            "negative",
            "error",
            "Need to ask the customer what currency they prefer."
          );
        }
      } else {
        handleAssignedTeller(null);
      }
    };

    const clearReference = () => {
      customerInfoOnline.value = null;
      tellerInformation.value.referenceNumber = null;
    };

    const handleAssignedTeller = async (currencyID) => {
      try {
        $dialog.loading.show({
          message: "Process is in progress. Hang on...",
        });

        const { data } = await $axios.post("/customer-join", {
          tokenn: customerInfoOnline.value.referenceNumber,
          name: customerInfoOnline.value.fullname,
          email: customerInfoOnline.value.email,
          email_status: "",
          type_id: customerInfoOnline.value.type_id,
          currency: currencyID,
          priority_service: "Online Appointment",
          branch_idd: customerInfoOnline.value.branch_id,
        });

        if (data.window_name) {
          $dialog
            .dialog({
              title: "Window Assigned",
              message: `You are assigned to ${data.window_name}`,
              cancel: false,
              persistent: true,
              ok: {
                label: "OK",
                color: "primary",
                unelevated: true,
                style: "width: 125px;",
              },
              style: "border-radius: 12px; padding: 16px;",
            })
            .onOk(() => {
              customerInfoOnline.value = null;
              tellerInformation.value.referenceNumber = null;
            });
        }
      } catch (error) {
        if (error.response.status === 400) {
          $notify("negative", "error", error.response.data.message);
        }
      } finally {
        $dialog.loading.hide();
      }
    };

    // customer manual //
    const token = ref(); // Get token from URL

    const generateRandomString = async (length = 10) => {
      const characters =
        "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
      let result = "";
      const charactersLength = characters.length;
      // Loop to create a random string
      for (let i = 0; i < length; i++) {
        result += characters.charAt(
          Math.floor(Math.random() * charactersLength)
        );
      }
      // Assign the generated string to the token ref
      token.value = result;
    };

    const joinQueue = async () => {
      if (isServiceAvailable.value == "") {
        if (name.value == "" && email.value == "" && Address.value == "") {
          formError.value.name = "Name field is required";
          formError.value.email = "Email field is required";
          formError.value.Address = "Address field is required";
          formError.value.selectId = "Service is not available at the moment";
        }
        return;
      }

      if (
        selectId.value == "" &&
        name.value == "" &&
        email.value == "" &&
        Address.value == ""
      ) {
        formError.value.name = "Name field is required";
        formError.value.email = "Email field is required";
        formError.value.Address = "Address field is required";
        formError.value.selectId = "Service field is required";
        return;
      }

      if (name.value == "") {
        formError.value.name = "Name field is required";
        return;
      } else if (email.value == "") {
        formError.value.email = "Email field is required";
        return;
      } else if (Address.value == "") {
        formError.value.Address = "Address field is required";
        return;
      } else if (selectId.value == "") {
        formError.value.selectId = "Service field is required";
        return;
      }

      isLoading.value = true;
      formError.value = [];
      await generateRandomString(); // Generate random token before the submission

      try {
        // Check if the category is 'Foreign Exchange' and validate the currency selection
        if (categoryForeignExchange.value === 1) {
          if (currencySelected.value == null) {
            formError.value.currency = "Currency field is required";
            return;
          }
        }
        if (customModel.value == "yes") {
          if (prioritySelected.value == null) {
            formError.value.priority = "Priority service field is required";
            return;
          }
        }

        if (tellerInformation.value.type_name == "Manual Queueing") {
          $dialog.loading.show({
            message: "Process is in progress. Hang on...",
          });

          setTimeout(() => {
            $dialog.loading.hide();
          }, 2000);
        }
        // Capitalize the name properly
        name.value = name.value
          .split(" ")
          .map(
            (word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
          )
          .join(" ");

        const { data } = await $axios.post("/customer-join", {
          token: "",
          name: name.value,
          email: email.value,
          email_status: email_status.value,
          type_id: selectId.value.id,
          branch_idd: tellerInformation.value.branch_id,
          currency: currencySelected.value,
          priority_service: prioritySelected.value,
        });

        // find selected Categoriess
        const selectedCategory = categoriesList.value.find(
          (category) => category.id === selectId.value.id
        );
        indicator.value = selectedCategory.indicator;

        generatedQrValue.value = `http://192.168.0.164:8080/customer-dashboard/${token.value}`;

        // generate the qr code image
        const qrCodeDataUrl = await QrCode.toDataURL(generatedQrValue.value, {
          errorCorrectionLevel: "H",
          type: "image/png",
        });

        // Notify the user that the email was successfully sent
        //$notify('positive', 'check', response.data.message);

        const queuenumber = `${indicator.value}#-${String(
          data.queue_number
        ).padStart(3, "0")}`;

        // Trigger the print function with the queue details and QR code
        printQueueDetails(
          queuenumber,
          name.value,
          selectId.value.name,
          Address.value,
          data.window_name,
          qrCodeDataUrl
        );

        // sending email
        await $axios.post("sent-email-dashboard", {
          id: data.id,
          token: token.value,
          queue_number: `${indicator.value}#-${String(
            data.queue_number
          ).padStart(3, "0")}`,
          email: email.value,
          name: name.value,
          subject: "Queue Alert", // Email subject
          message: `Welcome to our bank! To provide you with a seamless and efficient service experience,
                        we’ve implemented a queue system that helps manage customer flow.
                        Our system is designed to prioritize your needs and minimize waiting times.
                        You are free to go about your activities, and once your turn is approaching,
                        you’ll receive an email notification with further details. Thank you for choosing us!`, // Email message body
        });

        $notify("positive", "check", "Successfull Joining Queue");
        // set the qr code value

        // Reset form values after successful submission
        name.value = "";
        email.value = "";
        selectId.value = "";
        Address.value = "";
        currencySelected.value = "";
        token.value = "";
        customModel.value = "no";
        prioritySelected.value = null;
      } catch (error) {
        if (error.response) {
          // If the error response exists, check for the status
          if (error.response.status === 422) {
            formError.value = error.response.data;
          } else if (error.response.status === 400) {
            $notify(
              "negative",
              "error",
              "No tellers assigned to this service type"
            );
          } else if (error.response.status === 500) {
            $notify(
              "negative",
              "error",
              "No tellers assigned to this service type"
            );
          } else if (error.response.status === 500) {
            $notify(
              "negative",
              "error",
              "No tellers assigned to this service type"
            );
          }
        } else {
          console.log(error);
        }
      } finally {
        isLoading.value = false;
      }
    };

    const printQueueDetails = async (
      queueNumber,
      customerName,
      serviceType,
      Address,
      window_name,
      qrCodeDataUrl
    ) => {
      try {
        const printWindow = window.open(
          "",
          "",
          "height=430,width=450, resizable=yes"
        );

        // Write the content of the print window
        printWindow.document.write("<html>");
        printWindow.document.write("<head>");
        printWindow.document.write('<meta charset="UTF-8">');
        printWindow.document.write(
          '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
        );
        printWindow.document.write("<title>Customer Queue Details</title>");
        printWindow.document.write("<style>");

        // General Body styles
        printWindow.document.write("body {");
        printWindow.document.write("font-family: Arial, sans-serif;");
        printWindow.document.write("margin: 0;");
        printWindow.document.write("padding: 0;");
        printWindow.document.write("display: flex;");
        printWindow.document.write("flex-direction: column;");
        printWindow.document.write("justify-content: center;");
        printWindow.document.write("align-items: center;");
        printWindow.document.write("text-align: center;");
        printWindow.document.write("margin-top: 20px;");
        printWindow.document.write("}");

        // Outer container styles
        printWindow.document.write(".container1 {");
        printWindow.document.write("width: 100%;");
        printWindow.document.write(
          "max-width: 400px; /* Set to 400px to fit in the 450px window width */"
        );
        printWindow.document.write("padding: 5px;");
        printWindow.document.write("box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);");
        printWindow.document.write("text-align: center;");
        printWindow.document.write("}");

        // Grid container for content
        printWindow.document.write(".container {");
        printWindow.document.write("margin-left: 15px;");
        printWindow.document.write("display: grid;");
        printWindow.document.write("grid-template-columns: auto auto;");
        printWindow.document.write("width: 100%;");
        printWindow.document.write("}");

        printWindow.document.write(".container > div {");
        printWindow.document.write("background-color: #ffffff;");
        printWindow.document.write("font-size: 12px;");
        printWindow.document.write("text-align: left;");
        printWindow.document.write("}");

        // Optional: Styling for the QR Code image
        printWindow.document.write("img {");
        printWindow.document.write("display: block;");
        printWindow.document.write("margin-left: auto;");
        printWindow.document.write("margin-right: auto;");
        printWindow.document.write("}");

        // Styling for headers (h1, h2, h3)
        printWindow.document.write("h1, h2, h3 {");
        printWindow.document.write("margin: 10px 0;");
        printWindow.document.write("}");

        printWindow.document.write("</style>");
        printWindow.document.write("</head>");

        // Body content
        printWindow.document.write("<body>");
        printWindow.document.write('<div class="container1">');
        printWindow.document.write(
          '<strong><h5">VRTSYSTEMS TECHNOLOGIES</h5></strong> <br>'
        );
        printWindow.document.write(
          '<strong> <h4">Customer Queue Details</h4> </strong>'
        );
        printWindow.document.write(
          '<h2 style="margin-top:25px;">' + queueNumber + "</h2>"
        );
        printWindow.document.write("<hr> <!-- Divider between sections -->");
        printWindow.document.write('<div class="container">');
        printWindow.document.write("<div><p><strong>Name:</strong></p></div>");
        printWindow.document.write("<div><p>" + customerName + "</p></div>");
        printWindow.document.write(
          "<div><p><strong>Address:</strong></p></div>"
        );
        printWindow.document.write("<div><p>" + Address + "</p></div>");
        printWindow.document.write(
          "<div><p><strong>Service Type:</strong></p></div>"
        );
        printWindow.document.write("<div><p>" + serviceType + "</p></div>");
        printWindow.document.write(
          "<div><p><strong>Window name:</strong></p></div>"
        );
        printWindow.document.write("<div><p>" + window_name + "</p></div>");
        printWindow.document.write("</div>");
        printWindow.document.write("<hr> <!-- Divider between sections -->");
        printWindow.document.write("<h3>QR Code for Customer Dashboard</h3>");
        printWindow.document.write(
          '<img src="' +
            qrCodeDataUrl +
            '" width="150" height="150" alt="QR Code">'
        );
        printWindow.document.write("</div>");
        printWindow.document.write("</body>");
        printWindow.document.write("</html>");

        // Close the document to render the content
        printWindow.document.close();

        // Open the print dialog
        printWindow.print();
        $notify("positive", "check", "Successfully registered");
      } catch (error) {
        console.error("Error during print process:", error);
      }
    };

    const fetchCategories = async () => {
      try {
        const { data } = await $axios.post("/types/filteredTypes", {
          branch_id: tellerInformation.value.branch_id,
        });
        const OnlineTellers = data.rows;
        const WindowsInBranch = data.types;

        // Filter OnlineTellers that exist in WindowsInBranch by matching IDs
        const NewObject = OnlineTellers.filter((teller) =>
          WindowsInBranch.some((window) => window.id === teller.id)
        );

        console.log(NewObject);

        const seenNames = new Set();
        const uniqueRows = NewObject.filter((row) => {
          if (seenNames.has(row.name)) {
            return false;
          } else {
            seenNames.add(row.name);
            return true;
          }
        });

        console.log(uniqueRows);
        const filteredRows = uniqueRows.filter(
          (row) =>
            row.type_id !== null &&
            row.name !== "Online Appointment" &&
            row.name !== "Manual Queueing"
        );

        if (filteredRows.length === 0) {
          isServiceAvailable.value = false;
        } else {
          isServiceAvailable.value = true;
        }
        // Log filtered type_id values
        // Assign only valid rows to categoriesList.value
        categoriesList.value = filteredRows;
        console.log("values", categoriesList.value);
      } catch (error) {
        console.error("Error fetching categories:", error);
      }
    };

    const fecthCurrencty = async (selectedValue) => {
      try {
        if (selectedValue.name === "Foreign Exchange") {
          const { data } = await $axios.post("/currency/showData", {
            branch_id: tellerInformation.value.branch_id,
          });

          currentCiesList.value = data.rows
            .map((row) => ({
              id: row.id,
              name: row.currency_name,
              symbol: row.currency_symbol,
              flag: row.flag,
              buy_value: row.buy_value,
              sell_value: row.sell_value,
            }))
            .sort((a, b) => a.name.localeCompare(b.name)); // Sort alphabetically by name
        } else {
          currentCiesList.value = [];
          currencySelected.value = "";
        }
      } catch (error) {
        if (error.response.status === 422) {
          console.log(error.message);
        }
      }
    };

    const onDragStartCurrency = (event, currency) => {
      if (customerInfoOnline.value?.name === "Foreign Exchange") {
        draggedCurrency.value = currency; // Store the dragged currency
      } /* else {
            event.preventDefault(); // Prevent drag if not "Foreign Exchange"
            $notify("negative", "error", "Dragging is only allowed for Foreign Exchange.");
          } */
    };

    // Handle drop to customerInfoOnline
    const onDropToCustomerInfo = () => {
      if (
        draggedCurrency.value &&
        customerInfoOnline.value?.name === "Foreign Exchange"
      ) {
        // Replace the entire object to trigger reactivity
        customerInfoOnline.value = {
          ...customerInfoOnline.value,
          currencyDetails: draggedCurrency.value,
        };
        draggedCurrency.value = null; // Clear the dragged currency
      } else {
        $notify(
          "negative",
          "error",
          "Invalid drop. Only Foreign Exchange is allowed."
        );
      }
    };

    const checkAppointment = async () => {
      try {
        await $axios.post("/appointment/index");
      } catch (error) {
        if (error.response.status === 422) {
          console.log(error.message);
        }
      }
    };

    onUnmounted(() => {
      // Cleanup all timers and intervals
      clearInterval(currencyInterval);
      if (waitTimer) clearInterval(waitTimer);
      if (refreshInterval) clearInterval(refreshInterval);
    });
    onBeforeUnmount(() => {
      if (autoServingInterval) {
        clearInterval(autoServingInterval);
      }
      if (intervalId) {
        clearInterval(intervalId);
      }
      checkAppointment();
    });

    return {
      formError,
      fetchCurrency,
      queueList,
      currentServing,
      handleAssignedTellerClick,
      caterCustomer,
      cancelCustomer,
      finishCustomer,
      startWait,
      waiting,
      waitTime,
      fetchType_idValue,
      tempTimer,
      beforeCancel,
      isQueuelistEmpty,
      prepared,
      formatTime,
      cusId,
      tellerInformation,
      noOfQueue,
      logout,
      currentPage,
      itemsPerPage,
      paginatedQueueList,
      menuOpen,
      toggleFullscreen,
      fetch_Image,
      imageUrl,
      dragOverIndex,
      isDragging,
      onDragStart,
      onDragOver,
      onDragLeave,
      onDrop,
      columns,
      rowsCurrency,
      isLoading,
      autoServing,
      newFormattedTime,
      fromBreak,
      formattedCurrentTime,
      toBreak,
      formatTo12Hour,
      validateReference,
      customerInfoOnline,
      onDragStartCurrency,
      onDropToCustomerInfo,
      handleAssignedTeller,
      isCurrentlyServing,

      prioritySelected,
      customModel,
      ServiceAvail,
      generatedQrValue,
      name,
      email,
      indicator,
      email_status,
      Address,
      joinQueue,
      generateRandomString,
      formError,
      isLoading,
      token,
      currencySelected,
      type_id,
      categoriesList,
      fecthCurrencty,
      currentCiesList,
      selectId,
      clearReference,
      isServiceAvailable,
      categoriesPriority: [
        "Elderly Customers",
        "Pregnant Women",
        "People with Disabilities",
        "Premium Customers",
        "Parents with Young Children",
        "Queue-Free Services",
      ],
    };
  },
};
</script>

<style>
@import "flag-icons/css/flag-icons.min.css";

@keyframes queueDots {
  0% {
    opacity: 0.2;
  }
  50% {
    opacity: 1;
  }
  100% {
    opacity: 0.2;
  }
}

.draggable-item {
  cursor: grab;
  user-select: none;
  transition: background 0.2s ease-in-out, transform 0.15s ease-in-out,
    border 0.15s ease-in-out;
  opacity: 1 !important;
  border: 2px solid transparent;
}

.draggable-item.drag-over {
  background: rgba(25, 118, 210, 0.3);
  border: 2px dashed #1976d2 !important;
}

.drop-hint {
  height: 10px;
  background: rgba(25, 118, 210, 0.4);
  border-top: 2px dashed #1976d2 !important;
  border-bottom: 2px dashed #1976d2 !important;
  margin: 2px 0;
  visibility: hidden;
  opacity: 0;
  transition: opacity 0.15s ease-in-out, visibility 0.15s;
}

.is-dragging .drop-hint {
  visibility: visible;
  opacity: 1;
}

.draggable-item:active,
.draggable-item.is-dragging {
  border: 2px solid #1976d2 !important;
  background: rgba(25, 118, 210, 0.1);
  padding: 6px;
  box-shadow: 0px 0px 8px rgba(25, 118, 210, 0.5);
}

.loading-dots span {
  animation: queueDots 1.5s infinite ease-in-out;
}

.loading-dots span:nth-child(1) {
  animation-delay: 0s;
}
.loading-dots span:nth-child(2) {
  animation-delay: 0.2s;
}
.loading-dots span:nth-child(3) {
  animation-delay: 0.4s;
}

.full-progress {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 0;
}
.q-item__separator {
  border-color: #ff5733;
  border-width: 50px;
  margin: 5px 0;
}

/* Responsive styles */
@media (max-width: 1024px) {
  .q-card {
    margin-bottom: 16px;
  }
}

@media (max-width: 768px) {
  .text-h4 {
    font-size: 1.5rem;
  }
  .q-btn {
    padding: 8px 12px;
  }
}

@media (max-width: 480px) {
  .q-toolbar {
    flex-wrap: wrap;
  }
  .q-img {
    max-width: 80px !important;
  }
}

@media (max-width: 320px) {
  .text-left,
  .text-right {
    width: 100%;
  }

  .action-btn {
    min-width: 80px;
    height: 45px;
    font-size: 12px;
  }
  .q-gutter-y-xs {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
}

.q-gutter-y-xs {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.q-btn.full-width {
  width: 100%;
}

.my-scroll {
  scrollbar-width: thin;
  scrollbar-color: #4caf50 #f1f1f1;
}

.my-scroll::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

.my-scroll::-webkit-scrollbar-thumb {
  background-color: #4caf50;
  border-radius: 10px;
}

.my-scroll::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.custom-badge {
  font-size: 1.2rem;
  padding: 6px 12px;
  cursor: default;
}

/* Loading state */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.q-item-section {
  background-color: #f9f9f9; /* Light background for each item */
  border-radius: 8px;
  padding: 10px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}
.q-item {
  margin-bottom: 15px; /* Add spacing between items */
}
.q-list {
  padding-left: 0; /* Remove any padding from the list to align the items better */
}

.hover-animate {
  transition: transform 0.3s ease, box-shadow 0.3s ease; /* smooth transition */
}

.hover-animate:hover {
  transform: translate(5px) /* slightly lift the item */;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); /* add shadow on hover */
  background-color: #f5f5f5; /* Optional: Change background color */
}
</style>
