import './bootstrap';
import { createApp } from 'vue';

// Toast notifications
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";

// Import Users module main component
import UsersIndex from './Module/Users/UsersIndex.vue';

// Create app
const app = createApp(UsersIndex);

// Register global plugins
app.use(Toast, {
    position: "top-right",
    timeout: 3000,
    closeOnClick: true,
    pauseOnHover: true
});

// Mount to DOM
app.mount('#app');