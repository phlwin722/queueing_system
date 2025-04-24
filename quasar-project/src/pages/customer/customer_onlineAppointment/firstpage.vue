<template>
  <q-page class="q-px-md q-pt-md q-pb-xl bg-grey-1">
    <q-container class="q-mx-auto">

      <!-- Floating CTA Badge -->
      <q-badge
        class="q-pa-sm text-white bg-positive q-hoverable floating-cta-badge"
        rounded
        multi-line
        @click="$router.push('/book-appointment')"
      >
        <q-icon name="event" class="q-mr-sm" />
        Book Appointment
      </q-badge>

      <!-- Main Hero Section -->
      <div class="row q-col-gutter-md q-mb-lg animate-fade-in">
        <!-- Image -->
        <div class="col-12 col-sm-6">
          <q-img
            :src="require('assets/appointment.jpg')"
            alt="Person booking an appointment online"
            fit="cover"
            class="full-width rounded-borders shadow-2 q-hoverable"
            style="max-height: 400px; transition: transform 0.3s ease-in-out;"
            @mouseover="event.target.style.transform='scale(1.02)'"
            @mouseleave="event.target.style.transform='scale(1)'"
          />
        </div>

        <!-- Text & CTA -->
        <div class="col-12 col-sm-6 q-pa-lg text-center text-sm-left">
          <div class="q-mt-lg">
            <p class="text-h4 text-primary q-mb-sm">Set your Appointment Online</p>
            <p class="text-subtitle1 text-grey-7 q-mb-md" style="max-width: 700px;">
              Easily schedule appointments at your nearest branch – quick, simple, and hassle-free.
            </p>
            <div class="q-mt-md">
              <q-btn
              color="warning"
              label="Appointment Now"
              icon-right="arrow_outward"
              :to="'/book-appointment'"
              v-ripple
              class="full-width full-sm-width q-hoverable"
              style="max-width: 250px; font-size: 16px; padding: 12px 24px; border-radius: 8px; transition: background-color 0.3s ease-in-out; box-shadow: 0 4px 12px rgba(0,0,0,0.1);"
            />
            </div>
          </div>
        </div>
      </div>

      <!-- Benefits Section -->
      <q-card class="q-pa-lg bg-primary text-white rounded-borders shadow-2 q-mb-lg animate-fade-in" flat bordered>
        <div class="text-h5 text-center q-mb-md">
          {{ displayText }}<span class="blinking-cursor">|</span>
        </div>

        <div class="row q-col-gutter-md justify-center text-center">
          <div class="col-12 col-sm-4">
            <q-icon name="schedule" size="36px" class="q-mb-sm" />
            <div class="text-subtitle2">Save Time</div>
            <p class="text-caption text-info">Avoid waiting in line—get instant confirmation for your preferred schedule.</p>
          </div>
          <div class="col-12 col-sm-4">
            <q-icon name="check_circle_outline" size="36px" class="q-mb-sm" />
            <div class="text-subtitle2">Easy & Secure</div>
            <p class="text-caption text-info">Your booking data is safe and encrypted with our secure system.</p>
          </div>
          <div class="col-12 col-sm-4">
            <q-icon name="phone_android" size="36px" class="q-mb-sm" />
            <div class="text-subtitle2">Mobile Friendly</div>
            <p class="text-caption text-info">Book anytime, anywhere from your phone, tablet, or computer.</p>
          </div>
        </div>
      </q-card>

      <!-- Quick Guide Section -->
      <div class="q-mb-lg animate-fade-in">
        <q-card class="bg-white q-pa-md rounded-borders shadow-2" flat bordered>
          <div class="text-h5 text-center text-secondary q-mb-md">
            <q-icon name="info" class="q-mr-sm" />
            Quick Guide
          </div>

          <!-- Carousel -->
          <q-carousel
            animated
            v-model="slide"
            arrows
            infinite
            navigation
            :height="$q.screen.lt.sm ? '200px' : '350px'"
            control-color="secondary"
          >
            <q-carousel-slide :name="1" img-src="~assets/step1.png">
              <div class="text-center text-caption text-secondary q-mt-sm">Choose Action</div>
            </q-carousel-slide>
            <q-carousel-slide :name="2" img-src="~assets/Step2.png">
              <div class="text-center text-caption text-secondary q-mt-sm">Select Branch</div>
            </q-carousel-slide>
            <q-carousel-slide :name="3" img-src="~assets/Step3.png">
              <div class="text-center text-caption text-secondary q-mt-sm">Pick Schedule</div>
            </q-carousel-slide>
            <q-carousel-slide :name="4" img-src="~assets/Step4.png">
              <div class="text-center text-caption text-secondary q-mt-sm">Confirm & Submit</div>
            </q-carousel-slide>
          </q-carousel>

          <!-- Stepper -->
          <q-stepper
            v-model="slide"
            flat
            alternative-labels
            vertical
            color="primary"
            header-nav
            class="q-mt-md"
          >
            <q-step :name="1" title="Appointment Options" icon="event">
              Select the appointment type.
            </q-step>
            <q-step :name="2" title="Select Bank Branch" icon="location_on">
              Choose the branch where you want to book your appointment.
            </q-step>
            <q-step :name="3" title="Choose Schedule" icon="schedule">
              Pick a date and time that suits your schedule.
            </q-step>
            <q-step :name="4" title="Confirm Submission" icon="check_circle">
              Review your details and submit your appointment.
            </q-step>
          </q-stepper>
        </q-card>
      </div>

      <!-- FAQs Section -->
      <div class="animate-fade-in">
        <q-card class="q-pa-md bg-white text-dark rounded-borders shadow-2" flat bordered>
          <div class="text-h5 text-center text-secondary q-mb-md">
            <q-icon name="help_outline" class="q-mr-sm" />
            Frequently Asked Questions
          </div>

          <q-expansion-item icon="help" label="How do I book an appointment?" class="text-secondary q-hoverable" expand-separator dense>
            <q-card flat bordered class="q-pa-sm q-ml-md bg-grey-2">
              <q-icon name="check" color="primary" class="q-mr-sm" />
              <span class="text-body2">
                Click on the 'Appointment Now' button or the sticky button 'Book Appointment', choose your bank and date, and confirm your booking.
              </span>
            </q-card>
          </q-expansion-item>

          <q-expansion-item icon="schedule" label="Can I reschedule my appointment?" class="text-secondary q-hoverable" expand-separator dense>
            <q-card flat bordered class="q-pa-sm q-ml-md bg-grey-2">
              <q-icon name="check" color="primary" class="q-mr-sm" />
              <span class="text-body2">
                Yes! Use the 'Edit Appointment' option and select your new preferred schedule.
              </span>
            </q-card>
          </q-expansion-item>

          <q-expansion-item icon="cancel" label="How do I cancel an appointment?" class="text-secondary q-hoverable" expand-separator dense>
            <q-card flat bordered class="q-pa-sm q-ml-md bg-grey-2">
              <q-icon name="check" color="primary" class="q-mr-sm" />
              <span class="text-body2">
                Simply go to 'Cancel Appointment' and follow the instructions.
              </span>
            </q-card>
          </q-expansion-item>
        </q-card>
      </div>

    </q-container>
  </q-page>
</template>

<script>
import { defineComponent, ref, onMounted } from 'vue';

export default defineComponent({
  name: 'IndexPage',
  setup() {
    const slide = ref(1);

    const fullText = 'Why Book Online?'
    const displayText = ref('')
    let index = 0

    onMounted(() => {
      const observer = new IntersectionObserver(
        entries => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              entry.target.classList.add('fade-in-visible');
              observer.unobserve(entry.target);
            }
          });
        },
        { threshold: 0.2 }
      );

      document.querySelectorAll('.animate-fade-in').forEach(el => {
        observer.observe(el);
      });

      const interval = setInterval(() => {
    if (index < fullText.length) {
      displayText.value += fullText[index++]
    } else {
      clearInterval(interval)
    }
  }, 100) // typing speed (ms)
    });

    return { slide,fullText,displayText };
  }
});
</script>


<style scoped>

.blinking-cursor {
  animation: blink 1s step-end infinite;
  color: #1976d2;
}

@keyframes blink {
  from, to {
    opacity: 0;
  }
  50% {
    opacity: 1;
  }
}

.animate-fade-in {
  opacity: 0;
  transform: translateY(20px);
  transition: all 0.6s ease;
}

.fade-in-visible {
  opacity: 1;
  transform: translateY(0);
}

.floating-cta-badge {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 9999;
  cursor: pointer;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
  font-size: 14px;
  padding: 12px 16px;
  transition: transform 0.2s ease;
}

.floating-cta-badge:hover {
  transform: scale(1.05);
}


@media (max-width: 600px) {
  .q-tabs {
    flex-wrap: wrap;
  }

  .q-carousel {
    height: 300px !important;
  }

  .text-subtitle1 {
    font-size: 16px;
  }

  .text-caption {
    font-size: 14px;
  }

  .text-h4 {
    font-size: 22px;
  }

  .q-btn {
    font-size: 15px !important;
    padding: 10px 16px !important;
  }
}

</style>
