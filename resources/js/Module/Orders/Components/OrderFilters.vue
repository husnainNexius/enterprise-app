<template>
    <div class="order-filters">
        <div class="filters-header">
            <h3>Filter Orders</h3>
            <button @click="clearFilters" class="btn btn-outline-secondary btn-sm">
                Clear All
            </button>
        </div>
        
        <div class="filters-content">
            <!-- Single Row: All Filters -->
            <div class="filter-row">
                <div class="filter-group filter-search">
                    <label for="search">Search Orders</label>
                    <div class="input-group">
                        <span class="input-icon">🔍</span>
                        <input
                            id="search"
                            v-model="localSearch"
                            type="text"
                            placeholder="Search by order number or customer..."
                            @input="handleSearch"
                            class="form-control"
                        />
                    </div>
                </div>

                <div class="filter-group filter-status">
                    <label for="status">Order Status</label>
                    <div class="select-wrapper">
                        <select
                            id="status"
                            v-model="localStatus"
                            @change="handleFilterChange"
                            class="form-control custom-select"
                        >
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>
                </div>

                <div class="filter-group filter-payment-status">
                    <label for="paymentStatus">Payment Status</label>
                    <div class="select-wrapper">
                        <select
                            id="paymentStatus"
                            v-model="localPaymentStatus"
                            @change="handleFilterChange"
                            class="form-control custom-select"
                        >
                            <option value="">All Payment Status</option>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="failed">Failed</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>
                </div>

                <div class="filter-group filter-date">
                    <label for="dateFrom">From Date</label>
                    <div class="input-group">
                        <span class="input-icon">📅</span>
                        <input
                            id="dateFrom"
                            v-model="localDateFrom"
                            type="date"
                            @change="handleFilterChange"
                            class="form-control"
                        />
                    </div>
                </div>

                <div class="filter-group filter-date">
                    <label for="dateTo">To Date</label>
                    <div class="input-group">
                        <span class="input-icon">📅</span>
                        <input
                            id="dateTo"
                            v-model="localDateTo"
                            type="date"
                            @change="handleFilterChange"
                            class="form-control"
                        />
                    </div>
                </div>
            </div>
        </div>

        <div class="active-filters" v-if="hasActiveFilters">
            <h4>Active Filters:</h4>
            <div class="filter-tags">
                <span v-if="localSearch" class="filter-tag">
                    Search: {{ localSearch }}
                    <button @click="clearSearch" class="tag-remove">×</button>
                </span>
                <span v-if="localStatus" class="filter-tag">
                    Status: {{ localStatus }}
                    <button @click="clearStatus" class="tag-remove">×</button>
                </span>
                <span v-if="localPaymentStatus" class="filter-tag">
                    Payment Status: {{ localPaymentStatus }}
                    <button @click="clearPaymentStatus" class="tag-remove">×</button>
                </span>
                <span v-if="localDateFrom" class="filter-tag">
                    From: {{ formatDate(localDateFrom) }}
                    <button @click="clearDateFrom" class="tag-remove">×</button>
                </span>
                <span v-if="localDateTo" class="filter-tag">
                    To: {{ formatDate(localDateTo) }}
                    <button @click="clearDateTo" class="tag-remove">×</button>
                </span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import type { OrderFilters, OrderStatus, PaymentStatus } from '../Types/Order';

interface Props {
    filters: OrderFilters;
}

interface Emits {
    (e: 'filter-change', filters: OrderFilters): void;
    (e: 'search', query: string): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// Local state
const localSearch = ref(props.filters.search || '');
const localStatus = ref<OrderStatus | ''>(props.filters.status || '');
const localPaymentStatus = ref<string>(props.filters.payment_status || '');
const localDateFrom = ref(props.filters.date_from || '');
const localDateTo = ref(props.filters.date_to || '');

// Computed
const hasActiveFilters = computed(() => {
    return localSearch.value || 
           localStatus.value || 
           localPaymentStatus.value ||
           localDateFrom.value || 
           localDateTo.value;
});

// Watch for prop changes
watch(() => props.filters, (newFilters) => {
    localSearch.value = newFilters.search || '';
    localStatus.value = newFilters.status || '';
    localPaymentStatus.value = newFilters.payment_status || '';
    localDateFrom.value = newFilters.date_from || '';
    localDateTo.value = newFilters.date_to || '';
}, { deep: true });

// Methods
const handleSearch = () => {
    emit('search', localSearch.value);
};

const handleFilterChange = () => {
    const filters: OrderFilters = {
        search: localSearch.value,
        status: localStatus.value || undefined,
        payment_status: localPaymentStatus.value as PaymentStatus || undefined,
        date_from: localDateFrom.value,
        date_to: localDateTo.value,
    };
    
   
    
    emit('filter-change', filters);
};

const clearFilters = () => {
    localSearch.value = '';
    localStatus.value = '';
    localPaymentStatus.value = '';
    localDateFrom.value = '';
    localDateTo.value = '';
    handleFilterChange();
};

const clearSearch = () => {
    localSearch.value = '';
    handleFilterChange();
};

const clearStatus = () => {
    localStatus.value = '';
    handleFilterChange();
};

const clearPaymentStatus = () => {
    localPaymentStatus.value = '';
    handleFilterChange();
};

const clearDateFrom = () => {
    localDateFrom.value = '';
    handleFilterChange();
};

const clearDateTo = () => {
    localDateTo.value = '';
    handleFilterChange();
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString();
};
</script>

<style scoped>
.order-filters {
    background: #ffffff;
    border: 1px solid #e1e5e9;
    border-radius: 10px;
    padding: 12px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    margin-bottom: 12px;
}

.filters-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.filters-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 14px;
    font-weight: 600;
}

.filters-content {
    display: flex;
    flex-direction: row;
    gap: 16px;
    align-items: flex-end;
    flex-wrap: wrap;
}

.filters-content .filter-row {
    flex: 1;
    display: flex;
    gap: 16px;
    align-items: flex-end;
    margin-bottom: 0;
}

.filters-content .filter-group {
    flex: 1;
    min-width: 0;
}

.filters-content .filter-group.filter-search {
    flex: 1;
    min-width: 140px;
}

.filters-content .filter-group.filter-status,
.filters-content .filter-group.filter-payment-status,
.filters-content .filter-group.filter-date {
    flex: 1;
    min-width: 140px;
}

.filter-group {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0; /* Allow flex items to shrink */
}

.filter-group label {
    font-weight: 600;
    color: #495057;
    font-size: 12px;
    white-space: nowrap; /* Prevent label wrapping */
}

@media (max-width: 768px) {
    .filters-content {
        flex-direction: column;
        gap: 12px;
        align-items: stretch;
    }

    .filters-content .filter-row {
        flex-direction: column;
        gap: 12px;
    }

    .filters-content .filter-group.filter-search,
    .filters-content .filter-group.filter-status,
    .filters-content .filter-group.filter-payment-status,
    .filters-content .filter-group.filter-date {
        flex: 1;
        min-width: auto;
    }
}

.input-group {
    position: relative;
    display: flex;
    align-items: center;
}

.input-icon {
    position: absolute;
    left: 12px;
    z-index: 1;
    font-size: 14px;
    display: none;
}

.form-control {
    width: 100%;
    padding: 8px 10px;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    font-size: 13px;
    line-height: 1.2;
    transition: all 0.3s ease;
    background: #ffffff;
}

.form-control:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.select-wrapper {
    position: relative;
}

.custom-select {
    padding: 8px 10px;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    font-size: 13px;
    background: #ffffff;
    transition: all 0.3s ease;
}

.custom-select:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.btn {
    padding: 6px 10px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-outline-secondary {
    background: transparent;
    border: 2px solid #6c757d;
    color: #6c757d;
}

.btn-outline-secondary:hover {
    background: #6c757d;
    color: white;
}

.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
}

.active-filters {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #e1e5e9;
}

.active-filters h4 {
    margin: 0 0 12px 0;
    color: #495057;
    font-size: 12px;
    font-weight: 600;
}

.filter-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.filter-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 8px;
    background: #e3f2fd;
    color: #1976d2;
    border-radius: 16px;
    font-size: 12px;
    font-weight: 500;
}

.tag-remove {
    background: none;
    border: none;
    color: #1976d2;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    padding: 0;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s ease;
}

.tag-remove:hover {
    background: rgba(25, 118, 210, 0.1);
}

/* Multiple select styling */
.custom-select option {
    padding: 8px;
}

.custom-select option:checked {
    background: #007bff;
    color: white;
}
</style>
