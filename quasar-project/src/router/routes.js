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
    path: "/customer/customer_QrCode", // The login page path
    component: () => import("pages/custumer_ScanQrCode/index.vue"), // Dynamically load the login component
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
        component: () => import("pages/admin_Dashboard/index.vue"), // Load the dashboard page
      },
      {
        path: "/admin/qr_code", // Path for the QR Code management page
        component: () => import("pages/admin_Qrcode/index.vue"), // Load the QR code page
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
