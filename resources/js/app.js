import './bootstrap';
import { createApp } from 'vue';

import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";

// Dynamic module loading based on path
const modules = {
    '/': () => import('./Module/Users/UsersIndex.vue'),
    '/users': () => import('./Module/Users/UsersIndex.vue'),
    '/products': () => import('./Module/Products/ProductsIndex.vue'),
    '/orders': () => import('./Module/Orders/OrdersIndex.vue')
};

const currentPath = window.location.pathname;
const moduleLoader = modules[currentPath] || modules['/'];

moduleLoader().then(component => {
    const app = createApp(component.default);
    app.use(Toast, { position: "top-right", timeout: 3000 });
    app.mount('#app');
}).catch(error => {
    console.error('Failed to load module:', error);
});