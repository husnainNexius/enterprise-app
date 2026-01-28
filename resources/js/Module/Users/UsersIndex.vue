<template>
    <div class="users-container">
        <div class="header">
            <h1>User Management System</h1>
            <div class="action-buttons">
                <button @click="activeView = 'register'" :class="{ active: activeView === 'register' }"
                    class="btn btn-primary">
                    Register New User
                </button>
                <button @click="activeView = 'login'" :class="{ active: activeView === 'login' }"
                    class="btn btn-secondary">
                    Login
                </button>
                <button v-if="isAuthenticated" @click="activeView = 'list'" :class="{ active: activeView === 'list' }"
                    class="btn btn-success">
                    View Users
                </button>
                <button v-if="isAuthenticated" @click="handleLogout" class="btn btn-danger">
                    Logout
                </button>
            </div>
        </div>

        <div class="content">
            <user-form v-if="activeView === 'register'" @user-registered="handleUserRegistered" />

            <login-form v-if="activeView === 'login'" @user-logged-in="handleUserLoggedIn" />

            <users-table v-if="activeView === 'list' && isAuthenticated" :token="authToken"
                @view-details="handleViewDetails" />

            <div v-if="activeView === 'details' && selectedUser" class="user-details">
                <h2>User Details</h2>
                <div class="detail-card">
                    <p><strong>ID:</strong> {{ selectedUser.id }}</p>
                    <p><strong>Name:</strong> {{ selectedUser.name }}</p>
                    <p><strong>Email:</strong> {{ selectedUser.email }}</p>
                    <p><strong>Email Verified:</strong> {{ selectedUser.email_verified_at || 'Not verified' }}</p>
                    <p><strong>Created:</strong> {{ formatDate(selectedUser.created_at) }}</p>
                    <p><strong>Updated:</strong> {{ formatDate(selectedUser.updated_at) }}</p>
                </div>
                <button @click="activeView = 'list'" class="btn btn-secondary">
                    Back to List
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue';
import { useToast } from 'vue-toastification';
import UserForm from './Components/UserForm.vue';
import LoginForm from './Components/LoginForm.vue';
import UsersTable from './Components/UsersTable.vue';

export default {
    name: 'UsersIndex',

    components: {
        UserForm,
        LoginForm,
        UsersTable
    },

    setup() {
        const toast = useToast();

        const activeView = ref('register');
        const isAuthenticated = ref(false);
        const authToken = ref(null);
        const selectedUser = ref(null);

        const checkAuth = () => {
            const token = localStorage.getItem('auth_token');
            if (token) {
                authToken.value = token;
                isAuthenticated.value = true;
                window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            }
        };

        const handleUserRegistered = (userData) => {
            toast.success(`User ${userData.name} registered successfully!`);
            activeView.value = 'login';
        };

        const handleUserLoggedIn = (loginData) => {
            authToken.value = loginData.token;
            isAuthenticated.value = true;

            localStorage.setItem('auth_token', loginData.token);
            window.axios.defaults.headers.common['Authorization'] = `Bearer ${loginData.token}`;

            toast.success(`Welcome back, ${loginData.user.name}!`);
            activeView.value = 'list';
        };

        const handleViewDetails = (user) => {
            selectedUser.value = user;
            activeView.value = 'details';
        };

        const handleLogout = () => {
            authToken.value = null;
            isAuthenticated.value = false;
            localStorage.removeItem('auth_token');
            delete window.axios.defaults.headers.common['Authorization'];

            activeView.value = 'login';
            toast.info('Logged out successfully');
        };

        const formatDate = (dateString) => {
            if (!dateString) return 'N/A';
            return new Date(dateString).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        };

        checkAuth();

        return {
            activeView,
            isAuthenticated,
            authToken,
            selectedUser,
            handleUserRegistered,
            handleUserLoggedIn,
            handleViewDetails,
            handleLogout,
            formatDate
        };
    }
};
</script>

<style scoped>
.users-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.header {
    margin-bottom: 30px;
}

.header h1 {
    margin-bottom: 20px;
    color: #2d3748;
}

.action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s;
}

.btn-primary {
    background-color: #3490dc;
    color: white;
}

.btn-primary:hover {
    background-color: #2779bd;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

.btn-success {
    background-color: #38c172;
    color: white;
}

.btn-success:hover {
    background-color: #2fa360;
}

.btn-danger {
    background-color: #e3342f;
    color: white;
}

.btn-danger:hover {
    background-color: #cc1f1a;
}

.btn.active {
    box-shadow: 0 0 0 3px rgba(52, 144, 220, 0.3);
}

.content {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.user-details {
    max-width: 600px;
    margin: 0 auto;
}

.user-details h2 {
    margin-bottom: 20px;
    color: #2d3748;
}

.detail-card {
    background: #f7fafc;
    padding: 20px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.detail-card p {
    margin: 10px 0;
    font-size: 16px;
}

.detail-card strong {
    color: #2d3748;
    margin-right: 10px;
}
</style>