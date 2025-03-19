import { route } from "quasar/wrappers"; // Import the Quasar router wrapper
import { createRouter, createWebHistory } from "vue-router"; // Import Vue Router functions
import routes from "./routes"; // Import the routes array from another file

export default route(function () {
  // Create a new Vue Router instance with a history mode
  const Router = createRouter({
    history: createWebHistory(), // Use web history mode for routing (no hashes in URL)
    routes, // Pass the array of routes to the router
  });

  // 🔹 Navigation Guard: This runs before each route change
  Router.beforeEach((to, from, next) => {
    // Check if the user is authenticated as an admin (get token from session storage)
    const isAuthenticatedAdmin = sessionStorage.getItem("authTokenAdmin");
    
    // Check if the user is authenticated as a teller (get token from session storage)
    const isAuthenticatedTeller = sessionStorage.getItem("authTokenTeller");

    // Check if the route requires authentication (meta field 'requiresAuth')
    if (to.matched.some((record) => record.meta.requiresAuth)) {
      // If the route requires authentication, check if neither admin nor teller is authenticated
      if (!isAuthenticatedAdmin && !isAuthenticatedTeller) {
        sessionStorage.clear(); // Clear session storage if neither is authenticated
        next("/login"); // Redirect to login page
      } else {
        next(); // Proceed to the requested route if the user is authenticated (admin or teller)
      }
    } else {
      next(); // If the route does not require authentication, proceed to the route
    }
  });

  // Return the router instance
  return Router;
});
