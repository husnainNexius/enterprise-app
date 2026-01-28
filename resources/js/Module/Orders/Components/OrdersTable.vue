<template>
    <div class="orders-table-container">
        <div class="table-responsive">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading && orders.length === 0">
                        <td colspan="7" class="text-center">
                            <div class="loading-spinner">Loading...</div>
                        </td>
                    </tr>
                    <tr v-else-if="orders.length === 0">
                        <td colspan="7" class="text-center no-data">
                            No orders found
                        </td>
                    </tr>
                    <tr v-for="order in orders" :key="order.id">
                        <td>
                            <span class="order-number">{{ order.order_number }}</span>
                        </td>
                        <td>
                            <div v-if="order.customer" class="customer-info">
                                <div class="customer-name">{{ order.customer.name }}</div>
                                <div class="customer-email">{{ order.customer.email }}</div>
                            </div>
                            <span v-else class="text-muted">N/A</span>
                        </td>
                        <td>
                            <span :class="getStatusClass(order.status)" class="status-badge">
                                {{ order.status }}
                            </span>
                        </td>
                        <td class="text-right">
                            <span class="amount">{{ formatCurrency(order.grand_total) }}</span>
                        </td>
                        <td>
                            <span :class="getPaymentStatusClass(order.payment_status)" class="status-badge">
                                {{ order.payment_status }}
                            </span>
                        </td>
                        <td>
                            {{ formatDate(order.created_at) }}
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button 
                                    @click="viewOrder(order)"
                                    class="btn btn-sm btn-info"
                                    title="View Details"
                                >
                                    <i class="icon">👁️</i>
                                </button>
                                <button 
                                    v-if="order.can_be_updated"
                                    @click="editOrder(order)"
                                    class="btn btn-sm btn-warning"
                                    title="Edit Order"
                                >
                                    <i class="icon">✏️</i>
                                </button>
                                <button 
                                    v-if="order.can_be_cancelled"
                                    @click="deleteOrder(order)"
                                    class="btn btn-sm btn-danger"
                                    title="Cancel Order"
                                >
                                    <i class="icon">🗑️</i>
                                </button>
                                <div class="status-dropdown">
                                    <select 
                                        @change="handleStatusChange(order, $event.target.value)"
                                        class="form-control form-control-sm"
                                        title="Change Status"
                                    >
                                        <option value="">Change Status</option>
                                        <option 
                                            v-for="status in getAvailableStatuses(order.status)"
                                            :key="status"
                                            :value="status"
                                        >
                                            {{ status }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination && pagination.last_page > 1" class="pagination-container">
            <div class="pagination-info">
                Showing {{ pagination.from || 0 }} to {{ pagination.to || 0 }} of {{ pagination.total }} orders
            </div>
            <div class="pagination-controls">
                <button 
                    @click="goToPage(1)"
                    :disabled="pagination.current_page === 1"
                    class="btn btn-sm"
                >
                    First
                </button>
                <button 
                    @click="goToPage(pagination.current_page - 1)"
                    :disabled="pagination.current_page === 1"
                    class="btn btn-sm"
                >
                    Previous
                </button>
                <span class="page-info">
                    Page {{ pagination.current_page }} of {{ pagination.last_page }}
                </span>
                <button 
                    @click="goToPage(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.last_page"
                    class="btn btn-sm"
                >
                    Next
                </button>
                <button 
                    @click="goToPage(pagination.last_page)"
                    :disabled="pagination.current_page === pagination.last_page"
                    class="btn btn-sm"
                >
                    Last
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { Order, OrderStatus } from '../Types/Order';

interface Props {
    orders: Order[];
    loading: boolean;
    pagination: any;
}

interface Emits {
    (e: 'view', order: Order): void;
    (e: 'edit', order: Order): void;
    (e: 'delete', order: Order): void;
    (e: 'status-change', order: Order, status: OrderStatus): void;
    (e: 'page-change', page: number): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// Methods
const viewOrder = (order: Order) => {
    emit('view', order);
};

const editOrder = (order: Order) => {
    emit('edit', order);
};

const deleteOrder = (order: Order) => {
    emit('delete', order);
};

const handleStatusChange = (order: Order, status: string) => {
    if (status) {
        emit('status-change', order, status as OrderStatus);
    }
};

const goToPage = (page: number) => {
    emit('page-change', page);
};

const getStatusClass = (status: string): string => {
    const classes = {
        pending: 'status-warning',
        confirmed: 'status-info',
        processing: 'status-primary',
        shipped: 'status-secondary',
        delivered: 'status-success',
        cancelled: 'status-danger',
        refunded: 'status-secondary'
    };
    return classes[status] || 'status-secondary';
};

const getPaymentStatusClass = (status: string): string => {
    const classes = {
        pending: 'status-warning',
        paid: 'status-success',
        failed: 'status-danger',
        refunded: 'status-secondary'
    };
    return classes[status] || 'status-secondary';
};

const getAvailableStatuses = (currentStatus: OrderStatus): OrderStatus[] => {
    const transitions = {
        pending: ['confirmed', 'cancelled'],
        confirmed: ['processing', 'cancelled'],
        processing: ['shipped', 'cancelled'],
        shipped: ['delivered'],
        delivered: ['refunded'],
        cancelled: [],
        refunded: [],
    };
    return transitions[currentStatus] || [];
};

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString();
};
</script>

<style scoped>
.orders-table-container {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.table-responsive {
    overflow-x: auto;
}

.orders-table {
    width: 100%;
    border-collapse: collapse;
}

.orders-table th {
    background: #f8f9fa;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
    color: #495057;
}

.orders-table td {
    padding: 12px;
    border-bottom: 1px solid #dee2e6;
    vertical-align: middle;
}

.orders-table tr:hover {
    background-color: #f8f9fa;
}

.order-number {
    font-family: monospace;
    font-weight: 600;
    color: #007bff;
}

.customer-info {
    line-height: 1.2;
}

.customer-name {
    font-weight: 600;
    color: #333;
}

.customer-email {
    font-size: 12px;
    color: #666;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-warning {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.status-info {
    background-color: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.status-primary {
    background-color: #cce5ff;
    color: #004085;
    border: 1px solid #b8daff;
}

.status-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.status-secondary {
    background-color: #e2e3e5;
    color: #383d41;
    border: 1px solid #d6d8db;
}

.amount {
    font-weight: 600;
    color: #28a745;
}

.action-buttons {
    display: flex;
    gap: 5px;
    align-items: center;
}

.btn {
    padding: 4px 8px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.3s;
}

.btn-sm {
    padding: 2px 6px;
    font-size: 11px;
}

.btn-info {
    background-color: #17a2b8;
    color: white;
}

.btn-info:hover {
    background-color: #138496;
}

.btn-warning {
    background-color: #ffc107;
    color: #212529;
}

.btn-warning:hover {
    background-color: #e0a800;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn-danger:hover {
    background-color: #c82333;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.status-dropdown select {
    width: auto;
    min-width: 120px;
}

.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

.pagination-info {
    font-size: 14px;
    color: #666;
}

.pagination-controls {
    display: flex;
    gap: 10px;
    align-items: center;
}

.page-info {
    padding: 0 10px;
    font-size: 14px;
    color: #666;
}

.loading-spinner {
    padding: 20px;
    color: #666;
}

.no-data {
    padding: 40px;
    color: #666;
    font-style: italic;
}

.text-center {
    text-align: center;
}

.text-right {
    text-align: right;
}

.text-muted {
    color: #6c757d;
}

@media (max-width: 768px) {
    .orders-table {
        font-size: 12px;
    }
    
    .orders-table th,
    .orders-table td {
        padding: 8px 4px;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 2px;
    }
    
    .pagination-container {
        flex-direction: column;
        gap: 10px;
    }
    
    .pagination-controls {
        flex-wrap: wrap;
        justify-content: center;
    }
}
</style>
