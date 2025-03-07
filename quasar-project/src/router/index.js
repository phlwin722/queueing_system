import { route } from "quasar/wrappers";
import { createRouter, createWebHistory } from "vue-router";
import routes from "./routes"; // Import the routes array

export default route(function () {
  const Router = createRouter({
    history: createWebHistory(),
    routes, // Ensure routes is passed correctly as an array
  });

/*   // 🔹 Navigation Guard: Redirect if not logged in
  Router.beforeEach((to, from, next) => {
    const isAuthenticated = sessionStorage.getItem("authToken"); // Check session
    if (to.matched.some((record) => record.meta.requiresAuth) && !isAuthenticated) {
      sessionStorage.clear();
      next("/login"); // Redirect to login if not authenticated
    } else {
      next();
    }
  }); */

  return Router;
});
