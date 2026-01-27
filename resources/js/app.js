import './bootstrap';
import { createApp } from 'vue';

// Import components manually
import ExampleComponent from './Modules/Example/Components/ExampleComponent.vue';
import UserCard from './Modules/User/Components/UserCard.vue';

const app = createApp({});

// Register components manually
app.component('ExampleComponent', ExampleComponent);
app.component('UserCard', UserCard);

app.mount('#app');
