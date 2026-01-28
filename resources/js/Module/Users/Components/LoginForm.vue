<template>
    <div class="login-form">
        <h2>User Login</h2>

        <form @submit.prevent="handleLogin">
            <div class="form-group">
                <label for="login-email">Email:</label>
                <input id="login-email" v-model="credentials.email" type="email"
                    :class="{ 'error-input': errors.email }" placeholder="Enter your email">
                <span v-if="errors.email" class="error-message">
                    {{ errors.email[0] }}
                </span>
            </div>

            <div class="form-group">
                <label for="login-password">Password:</label>
                <input id="login-password" v-model="credentials.password" type="password"
                    :class="{ 'error-input': errors.password }" placeholder="Enter your password">
                <span v-if="errors.password" class="error-message">
                    {{ errors.password[0] }}
                </span>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-login" :disabled="submitting">
                    {{ submitting ? 'Logging in...' : 'Login' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script>
import { reactive, ref } from 'vue';
import { useToast } from 'vue-toastification';

export default {
    name: 'LoginForm',

    emits: ['user-logged-in'],

    setup(props, { emit }) {
        const toast = useToast();

        const credentials = reactive({
            email: '',
            password: ''
        });

        const errors = ref({});
        const submitting = ref(false);

        const handleLogin = async () => {
            errors.value = {};
            submitting.value = true;

            try {
                const response = await window.axios.post('/api/login', credentials);

                const { access_token, user } = response.data;

                toast.success(`Welcome back, ${user.name}!`);

                emit('user-logged-in', {
                    token: access_token,
                    user: user
                });

                credentials.email = '';
                credentials.password = '';

            } catch (error) {
                if (error.response?.status === 422) {
                    errors.value = error.response.data.errors;
                } else if (error.response?.status === 401) {
                    toast.error('Invalid email or password');
                } else {
                    toast.error(error.response?.data?.error || 'Login failed');
                }
            } finally {
                submitting.value = false;
            }
        };

        return {
            credentials,
            errors,
            submitting,
            handleLogin
        };
    }
};
</script>

<style scoped>
.login-form {
    max-width: 400px;
    margin: 0 auto;
}

.login-form h2 {
    margin-bottom: 25px;
    color: #2d3748;
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #4a5568;
}

input {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid #e2e8f0;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.3s;
}

input:focus {
    outline: none;
    border-color: #38c172;
}

.error-input {
    border-color: #e3342f !important;
}

.error-message {
    display: block;
    margin-top: 5px;
    color: #e3342f;
    font-size: 13px;
}

.form-actions {
    margin-top: 25px;
}

.btn-login {
    width: 100%;
    padding: 12px;
    background-color: #38c172;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-login:hover:not(:disabled) {
    background-color: #2fa360;
}

.btn-login:disabled {
    background-color: #cbd5e0;
    cursor: not-allowed;
}
</style>