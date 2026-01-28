<template>
    <div class="users-table">
        <h2>Registered Users</h2>

        <div v-if="loading" class="loading">
            <p>Loading users...</p>
        </div>

        <div v-else-if="users.length > 0" class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users" :key="user.id">
                        <td>{{ user.id }}</td>
                        <td>{{ user.name }}</td>
                        <td>{{ user.email }}</td>
                        <td>{{ formatDate(user.created_at) }}</td>
                        <td>
                            <button @click="viewDetails(user.id)" class="btn btn-view">
                                View Details
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-else class="empty-state">
            <p>No users found</p>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useToast } from 'vue-toastification';

export default {
    name: 'UsersTable',

    props: {
        token: {
            type: String,
            required: true
        }
    },

    emits: ['view-details'],

    setup(props, { emit }) {
        const toast = useToast();

        const users = ref([]);
        const loading = ref(false);

        const fetchUsers = async () => {
            loading.value = true;

            try {
                // Simulate fetching users - replace with actual endpoint when ready
                // For now, we'll create dummy data
                await new Promise(resolve => setTimeout(resolve, 1000));

                users.value = [
                    {
                        id: 1,
                        name: 'John Doe',
                        email: 'john@example.com',
                        created_at: '2024-01-15T10:30:00Z'
                    },
                    {
                        id: 2,
                        name: 'Jane Smith',
                        email: 'jane@example.com',
                        created_at: '2024-01-16T14:20:00Z'
                    }
                ];

                // Uncomment this when you have the endpoint:
                // const response = await window.axios.get('/api/users', {
                //   headers: {
                //     'Authorization': `Bearer ${props.token}`
                //   }
                // });
                // users.value = response.data.data || response.data;

            } catch (error) {
                toast.error('Failed to load users');
                console.error('Fetch users error:', error);
            } finally {
                loading.value = false;
            }
        };

        const viewDetails = async (userId) => {
            try {
                const response = await window.axios.get(`/api/user/${userId}`, {
                    headers: {
                        'Authorization': `Bearer ${props.token}`
                    }
                });

                emit('view-details', response.data);
            } catch (error) {
                toast.error('Failed to load user details');
            }
        };

        const formatDate = (dateString) => {
            if (!dateString) return 'N/A';
            return new Date(dateString).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        };

        onMounted(() => {
            fetchUsers();
        });

        return {
            users,
            loading,
            viewDetails,
            formatDate
        };
    }
};
</script>

<style scoped>
.users-table {
    width: 100%;
}

.users-table h2 {
    margin-bottom: 20px;
    color: #2d3748;
}

.loading,
.empty-state {
    text-align: center;
    padding: 40px;
    color: #718096;
}

.table-container {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

thead {
    background-color: #f7fafc;
}

th {
    padding: 12px;
    text-align: left;
    font-weight: 600;
    color: #2d3748;
    border-bottom: 2px solid #e2e8f0;
}

td {
    padding: 12px;
    border-bottom: 1px solid #e2e8f0;
}

tbody tr:hover {
    background-color: #f7fafc;
}

.btn-view {
    padding: 6px 12px;
    background-color: #3490dc;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    transition: background-color 0.3s;
}

.btn-view:hover {
    background-color: #2779bd;
}
</style>