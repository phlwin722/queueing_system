// src/boot/pusher.js
import Pusher from 'pusher-js';  // Import the Pusher JavaScript library

export default async ({ app }) => {
  // Create a new Pusher instance with your app key and the cluster (us2 in this case)
  const pusher = new Pusher('8d3b62bc5d67d22d3605', {
    cluster: 'us2',  // Specify the Pusher cluster
  });

  // Make the pusher instance globally accessible in the Vue app via `$pusher`
  app.config.globalProperties.$pusher = pusher;
};
