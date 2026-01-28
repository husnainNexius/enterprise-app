<template>
    <div class="user-form">
        <h2>Register New User</h2>

        <form @submit.prevent="handleSubmit">
            <div class="form-group">
                <label for="name">Name:</label>
                <input id="name" v-model="form.name" type="text" :class="{ 'error-input': errors.name }"
                    placeholder="Enter full name">
                <span v-if="errors.name" class="error-message">
                    {{ errors.name[0] }}
                </span>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input id="email" v-model="form.email" type="email" :class="{ 'error-input': errors.email }"
                    placeholder="Enter email address">
                <span v-if="errors.email" class="error-message">
                    {{ errors.email[0] }}
                </span>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input id="password" v-model="form.password" type="password" :class="{ 'error-input': errors.password }"
                    placeholder="Minimum 8 characters">
                <span v-if="errors.password" class="error-message">
                    {{ errors.password[0] }}
                </span>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password:</label>
                <input id="password_confirmation" v-model="form.password_confirmation" type="password"
                    placeholder="Re-enter password">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-submit" :disabled="submitting">
                    {{ submitting ? 'Registering...' : 'Register User' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script>
import { reactive, ref } from 'vue';
import { useToast } from 'vue-toastification';

export default {
    name: 'UserForm',

    emits: ['user-registered'],

    setup(props, { emit }) {
        const toast = useToast();

        const form = reactive({
            name: '',
            email: '',
            password: '',
            password_confirmation: ''
        });

        const errors = ref({});
        const submitting = ref(false);

        const handleSubmit = async () => {
            errors.value = {};
            submitting.value = true;

            try {
                const response = await window.axios.post('/api/register', form);

                const userData = response.data;

                toast.success('User registered successfully!');

                emit('user-registered', userData);

                form.name = '';
                form.email = '';
                form.password = '';
                form.password_confirmation = '';

            } catch (error) {
                if (error.response?.status === 422) {
                    errors.value = error.response.data.errors;
                    toast.error('Please fix the validation errors');
                } else {
                    toast.error(error.response?.data?.error || 'Registration failed');
                }
            } finally {
                submitting.value = false;
            }
        };

        return {
            form,
            errors,
            submitting,
            handleSubmit
        };
    }
};
</script>

<style scoped>
.user-form {
    max-width: 500px;
    margin: 0 auto;
}

.user-form h2 {
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
    border-color: #3490dc;
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

.btn-submit {
    width: 100%;
    padding: 12px;
    background-color: #3490dc;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-submit:hover:not(:disabled) {
    background-color: #2779bd;
}

.btn-submit:disabled {
    background-color: #cbd5e0;
    cursor: not-allowed;
}
</style>