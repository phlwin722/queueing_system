const routes = [
  {
    path: "/", // The root URL ("/") of the application
    redirect: "/login", // Automatically redirect to "/login" when accessing "/"
  },
  {
    path: "/login", // The login page path
    component: () => import("pages/LoginInterface/index.vue"), // Dynamically load the login component
  },
  {
    path: '/queue-qr', 
    component: () => import('pages/generated_Qr/index.vue') 
  },
  {
    path: "/customer/customer_QrCode", // The login page path
    component: () => import("pages/customer/custumer_ScanQrCode/index.vue"), // Dynamically load the login component
  },
  {
    path: "/customer-dashboard/:token", // Path for the QR Code management page
    component: () => import("pages/customer/customer_Dashboard/index.vue"), // Load the QR code page
  },
  {
    path: '/customer-register/:token' ,
    component: () => import('pages/customer/customer_Register/index.vue')
  },
  {
    path: '/customer-register-queueing' ,
    component: () => import('pages/customer/customer_Register_Manual/index.vue')
  },
  {
    path: '/customer-thankyou' ,
    component: () => import('pages/customer/customer_Thankyou/index.vue')
  },
  {
    path: "/teller/Layout", // The login page path
    component: () => import("pages/Teller/index.vue"), // Dynamically load the login component
  },
  {
    path: "/vrtsystem/onlineAppointment", 
    component: ()=> import ('layouts/OnlineAppointment.vue'),

    children: [
      {
        path: "/vrtsystem/onlineAppointment",
        component: ()=> import ('pages/customer/customer_onlineAppointment/firstpage.vue')
      }
    ]
  },
  {
    path: "/admin/dashboard", // The main path for the admin dashboard
    component: () => import("layouts/MainLayout.vue"), // Load the MainLayout which contains a sidebar or header

    children: [
      {
        path: "/admin/dashboard", // Path for the admin dashboard content
        component: () => import("pages/admin/admin_Dashboard/index.vue"), // Load the dashboard page
      },
      {
        path: "/admin/teller/tellers", // Path for the QR Code management page
        component: () => import("pages/admin/admin_Tellerteller/index.vue"), // Load the QR code page
      },
      {
        path: "/admin/teller/types", // Path for the QR Code management page
        component: () => import("pages/admin/admin_Tellertype/index.vue"), // Load the QR code page
      },
      {
        path: "/admin/teller/window", // Path for the QR Code management page
        component: () => import("pages/admin/admin_Tellerwindow/index.vue"), // Load the QR code page
      },
      {
        path: "/admin/archive", // Path for the QR Code management page
        component: () => import("pages/admin/admin_Archieve/index.vue"), // Load the QR code page
      },
      {
        path: "/admin/admin_Queue", // Path for the QR Code management page
        component: () => import("pages/admin/admin_Queue/index.vue"), // Load the QR code page
      },
      {
        path: '/admin/bank_manager',
        component: () => import ('pages/admin/bank_manager/index.vue'),
      },
      {
        path: '/admin/branch',
        component: () => import ('pages/admin/branch/index.vue')
      },
      {
        path: "/admin/currency_conversion", // Path for the QR Code management page
        component: () => import("pages/admin/admin_Currency_Conversion/index.vue"), // Load the QR code page
      },
      {
        path: "/admin/customer-logs", // Path for the QR Code management page
        component: () => import("pages/admin/customer_Logs/index.vue"), // Load the QR code page
      },
      {
        path: "/admin/teller-customer-logs",
        component: () => import("pages/admin/admin_teller_customer_logs/index.vue"),
      },
      {
        path: "/admin/window-logs", // Path for the QR Code management page
        component: () => import("pages/admin/window_Logs/index.vue"), // Load the QR code page
      },
      {
        path: "/admin/serving-time-logs", // Path for the QR Code management page
        component: () => import("pages/admin/serving_time_logs/index.vue"), // Load the QR code page
      },
      {
        path: "/admin/reports", // Path for the QR Code management page
        component: () => import("pages/admin/reports/index.vue"), // Load the QR code page
      },
      {
        path: "/admin/waiting-time", // Path for the QR Code management page
        component: () => import("pages/admin/waiting_Time/index.vue"), // Load the QR code page
      },
      {
        path: "/admin/reset-window", // Path for the QR Code management page
        component: () => import("pages/admin/reset_Window/index.vue"), // Load the QR code page
      },
      {
        path: "/admin/settings", // Path for the QR Code management page
        component: () => import("pages/admin/admin_Settings/index.vue"), // Load the QR code page
      },
    ],

    meta: { requiresAuth: true }, // Protect this route (Navigation Guard will check for authentication)
  },
  {
    path: "/:catchAll(.*)*", // This is a wildcard route to catch any undefined paths
    component: () => import("pages/ErrorNotFound.vue"), // Load the "Page Not Found" component
  },
];

// Ensure routes is correctly exported as an array for use in the router
export default routes;
