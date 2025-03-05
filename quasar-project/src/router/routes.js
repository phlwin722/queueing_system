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
    path: "/customer-dashboard", // Path for the QR Code management page
    component: () => import("pages/customer/customer_Dashboard/index.vue"), // Load the QR code page
  },
  {
    path: '/customer-register/:token' ,
    component: () => import('pages/customer/customer_Register/index.vue')
  },
  {
    path: "/teller/Interface", // The login page path
    component: () => import("pages/teller_Interface/index.vue"), // Dynamically load the login component
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
        path: "/admin/teller/manage", // Path for the QR Code management page
        component: () => import("pages/admin/admin_TellerManage/index.vue"), // Load the QR code page
      },
      {
        path: "/admin/teller/servicetype", // Path for the QR Code management page
        component: () => import("pages/admin/admin_TellerServicetype/index.vue"), // Load the QR code page
      },
      {
        path: "/admin/archive", // Path for the QR Code management page
        component: () => import("pages/admin/admin_Archieve/index.vue"), // Load the QR code page
      },
      {
        path: "/admin/admin_Queue", // Path for the QR Code management page
        component: () => import("pages/admin/admin_Queue/index.vue"), // Load the QR code page
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
