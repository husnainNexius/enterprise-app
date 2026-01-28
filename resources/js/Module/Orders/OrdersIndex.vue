<template>
    <div class="orders-container">
        <div class="orders-header">
            <h1>Orders Management</h1>
            <div class="header-actions">
                <button @click="showCreateForm" class="btn btn-primary">
                    Create Order
                </button>
                <button @click="showStatistics" class="btn btn-info">
                    📊 Statistics
                </button>
                <button @click="exportOrders" class="btn btn-secondary">
                    📥 Export
                </button>
                <button @click="printOrders" class="btn btn-info">
                    🖨️ Print
                </button>
            </div>
        </div>

        <OrderFiltersComponent 
            :filters="filters"
            @filter-change="handleFilterChange"
            @search="handleSearch"
        />

        <OrdersTable 
            :orders="orders"
            :loading="loading"
            :pagination="pagination"
            @edit="editOrder"
            @view="viewOrder"
            @delete="handleDeleteOrder"
            @status-change="handleStatusChange"
            @page-change="handlePageChange"
        />

        <OrderForm
            v-if="showForm"
            :order="selectedOrder"
            :is-editing="isEditing"
            @save="handleSaveOrder"
            @cancel="hideForm"
        />

        <OrderDetails
            v-if="showDetails"
            :order="selectedOrder"
            @close="hideDetails"
            @edit="editOrder"
            @status-change="handleStatusChange"
            @process-order="handleProcessOrder"
            @apply-discount="handleApplyDiscount"
        />

        <StatisticsModal
            :show="showStatisticsModal"
            :statistics="statisticsData"
            :onClose="hideStatisticsModal"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useOrders } from './Composables/useOrders';
import { useToast } from 'vue-toastification';
import type { Order, OrderFilters, OrderStatus } from './Types/Order';
import StatisticsModal from './Components/StatisticsModal.vue';

// Components
import OrderFiltersComponent from './Components/OrderFilters.vue';
import OrdersTable from './Components/OrdersTable.vue';
import OrderForm from './Components/OrderForm.vue';
import OrderDetails from './Components/OrderDetails.vue';

// Composables
const { 
    orders, 
    loading, 
    pagination, 
    filters,
    fetchOrders,
    createOrder,
    updateOrder,
    deleteOrder,
    updateOrderStatus,
    searchOrders,
    getOrderStatistics,
    processOrder,
    applyDiscount
} = useOrders();

// Local state
const showForm = ref(false);
const showDetails = ref(false);
const showStatisticsModal = ref(false);
const statisticsData = ref(null);
const selectedOrder = ref<Order | null>(null);
const isEditing = ref(false);
const toast = useToast();

// Initialize
onMounted(() => {
    fetchOrders();
});

// Event handlers
const handleFilterChange = (newFilters: OrderFilters) => {
    filters.value = { ...filters.value, ...newFilters };
    fetchOrders();
};

const handleSearch = (query: string) => {
    filters.value = { ...filters.value, search: query };
    fetchOrders();
};

const handlePageChange = (page: number) => {
    fetchOrders(page);
};

const showCreateForm = () => {
    selectedOrder.value = null;
    isEditing.value = false;
    showForm.value = true;
};

const editOrder = async (order: Order) => {
    if (!order) {
        console.error('❌ No order provided to editOrder');
        return;
    }
    
    try {
        // Fetch full order details with items
        const response = await fetch(`/api/orders/${order.id}`);
        if (!response.ok) {
            throw new Error('Failed to fetch order details');
        }
        const fullOrder = await response.json();
        
        selectedOrder.value = fullOrder.data || fullOrder;
        isEditing.value = true;
        showForm.value = true;
        showDetails.value = false; // Hide details modal when editing
    } catch (error) {
        console.error('❌ Error fetching order details:', error);
        toast.error('Failed to load order details for editing');
    }
};

const viewOrder = (order: Order) => {
    selectedOrder.value = order;
    showDetails.value = true;
};

const handleSaveOrder = async (orderData: any) => {
    try {
        if (isEditing.value && selectedOrder.value) {
            await updateOrder(selectedOrder.value.id, orderData);
            toast.success('Order updated successfully');
        } else {
            await createOrder(orderData);
            toast.success('Order created successfully');
        }
        hideForm();
        fetchOrders();
    } catch (error) {
        toast.error(error.message);
    }
};

const handleDeleteOrder = async (order: Order) => {
    if (!confirm(`Are you sure you want to delete order ${order.order_number}?`)) {
        return;
    }

    try {
        await deleteOrder(order.id);
        toast.success('Order deleted successfully');
        fetchOrders();
    } catch (error) {
        toast.error(error.message);
    }
};

const handleStatusChange = async (order: Order, status: OrderStatus, notes?: string) => {
    try {
        await updateOrderStatus(order.id, status, notes);
        toast.success(`Order status updated to ${status}`);
        fetchOrders();
    } catch (error) {
        toast.error(error.message);
    }
};

const hideForm = () => {
    showForm.value = false;
    selectedOrder.value = null;
    isEditing.value = false;
};

const hideDetails = () => {
    showDetails.value = false;
    selectedOrder.value = null;
};

const exportOrders = async () => {
    try {
        // Get current filtered orders
        let ordersToExport = orders.value;
        
        // If no orders, show message
        if (ordersToExport.length === 0) {
            toast.warning('No orders to export');
            return;
        }

        // Create CSV content
        const headers = [
            'Order Number',
            'Customer Name',
            'Customer Email',
            'Status',
            'Total Amount',
            'Payment Method',
            'Payment Status',
            'Order Date',
            'Items Count'
        ];

        const csvContent = [
            headers.join(','),
            ...ordersToExport.map(order => [
                order.order_number,
                `"${order.customer?.name || 'N/A'}"`,
                `"${order.customer?.email || 'N/A'}"`,
                order.status,
                order.total_amount,
                order.payment_method,
                order.payment_status,
                order.order_date ? new Date(order.order_date).toLocaleDateString() : 'N/A',
                order.items?.length || 0
            ].join(','))
        ].join('\n');

        // Create blob and download
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        link.setAttribute('href', url);
        link.setAttribute('download', `orders_export_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        toast.success(`Exported ${ordersToExport.length} orders successfully`);
    } catch (error) {
        console.error('Export error:', error);
        toast.error('Failed to export orders');
    }
};

const printOrders = () => {
    try {
        if (orders.value.length === 0) {
            toast.warning('No orders to print');
            return;
        }

        // Create print-friendly HTML
        const printWindow = window.open('', '_blank');
        if (!printWindow) {
            toast.error('Failed to open print window');
            return;
        }

        const printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Orders Report</title>
                <style>
                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                    }
                    
                    body {
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                        font-size: 12px;
                        line-height: 1.4;
                        color: #333;
                        padding: 20px;
                    }
                    
                    .header {
                        text-align: center;
                        margin-bottom: 30px;
                        border-bottom: 2px solid #333;
                        padding-bottom: 20px;
                    }
                    
                    .header h1 {
                        font-size: 24px;
                        margin-bottom: 10px;
                    }
                    
                    .header .date {
                        font-size: 14px;
                        color: #666;
                    }
                    
                    .summary {
                        margin-bottom: 20px;
                        padding: 15px;
                        background: #f5f5f5;
                        border-radius: 5px;
                    }
                    
                    .summary-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                        gap: 15px;
                    }
                    
                    .summary-item {
                        text-align: center;
                    }
                    
                    .summary-item .value {
                        font-size: 18px;
                        font-weight: bold;
                        color: #007bff;
                    }
                    
                    .summary-item .label {
                        font-size: 11px;
                        color: #666;
                        text-transform: uppercase;
                    }
                    
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 20px;
                    }
                    
                    th, td {
                        border: 1px solid #ddd;
                        padding: 8px;
                        text-align: left;
                    }
                    
                    th {
                        background-color: #f8f9fa;
                        font-weight: bold;
                        font-size: 11px;
                        text-transform: uppercase;
                    }
                    
                    tr:nth-child(even) {
                        background-color: #f9f9f9;
                    }
                    
                    .status {
                        padding: 2px 6px;
                        border-radius: 3px;
                        font-size: 10px;
                        font-weight: bold;
                        text-transform: uppercase;
                    }
                    
                    .status-pending { background: #fff3cd; color: #856404; }
                    .status-confirmed { background: #d1ecf1; color: #0c5460; }
                    .status-processing { background: #cce5ff; color: #004085; }
                    .status-shipped { background: #ffeaa7; color: #6c757d; }
                    .status-delivered { background: #d4edda; color: #155724; }
                    .status-cancelled { background: #f8d7da; color: #721c24; }
                    .status-refunded { background: #f8d7da; color: #721c24; }
                    
                    .payment-status {
                        padding: 2px 6px;
                        border-radius: 3px;
                        font-size: 10px;
                        font-weight: bold;
                        text-transform: uppercase;
                    }
                    
                    .payment-paid { background: #d4edda; color: #155724; }
                    .payment-pending { background: #fff3cd; color: #856404; }
                    .payment-failed { background: #f8d7da; color: #721c24; }
                    
                    .amount {
                        text-align: right;
                        font-weight: bold;
                    }
                    
                    .footer {
                        margin-top: 30px;
                        padding-top: 20px;
                        border-top: 1px solid #ddd;
                        text-align: center;
                        font-size: 11px;
                        color: #666;
                    }
                    
                    @media print {
                        body { padding: 10px; }
                        .header { margin-bottom: 20px; }
                        .summary { margin-bottom: 15px; }
                        table { font-size: 10px; }
                        th, td { padding: 6px; }
                        .footer { margin-top: 20px; }
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>Orders Report</h1>
                    <div class="date">Generated on ${new Date().toLocaleDateString()} at ${new Date().toLocaleTimeString()}</div>
                </div>
                
                <div class="summary">
                    <div class="summary-grid">
                        <div class="summary-item">
                            <div class="value">${orders.value.length}</div>
                            <div class="label">Total Orders</div>
                        </div>
                        <div class="summary-item">
                            <div class="value">$${orders.value.reduce((sum, order) => sum + parseFloat(order.total_amount || 0), 0).toFixed(2)}</div>
                            <div class="label">Total Revenue</div>
                        </div>
                        <div class="summary-item">
                            <div class="value">${orders.value.filter(o => o.status === 'pending').length}</div>
                            <div class="label">Pending</div>
                        </div>
                        <div class="summary-item">
                            <div class="value">${orders.value.filter(o => o.status === 'delivered').length}</div>
                            <div class="label">Delivered</div>
                        </div>
                    </div>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th>Items</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${orders.value.map(order => `
                            <tr>
                                <td>${order.order_number}</td>
                                <td>${order.customer?.name || 'N/A'}</td>
                                <td>${order.customer?.email || 'N/A'}</td>
                                <td><span class="status status-${order.status}">${order.status}</span></td>
                                <td><span class="payment-status payment-${order.payment_status}">${order.payment_status}</span></td>
                                <td class="amount">$${parseFloat(order.total_amount || 0).toFixed(2)}</td>
                                <td>${order.order_date ? new Date(order.order_date).toLocaleDateString() : 'N/A'}</td>
                                <td>${order.items?.length || 0}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                
                <div class="footer">
                    <p>This report was generated from the Enterprise Orders Management System</p>
                    <p>Page 1 of 1</p>
                </div>
            </body>
            </html>
        `;

        printWindow.document.write(printContent);
        printWindow.document.close();
        
        // Wait for content to load before printing
        printWindow.onload = () => {
            printWindow.print();
            printWindow.close();
        };

        toast.success('Print dialog opened');
    } catch (error) {
        console.error('Print error:', error);
        toast.error('Failed to generate print view');
    }
};

const showStatistics = async () => {
    try {
        const response = await getOrderStatistics();
        
        // Handle both response structures: direct data or wrapped in data property
        let stats = null;
        if (response && response.data) {
            stats = response.data;
        } else if (response) {
            stats = response;
        }
        
        if (stats) {
            statisticsData.value = stats;
            showStatisticsModal.value = true;
        } else {
            toast.warning('No statistics data available');
        }
    } catch (error) {
        console.error('Statistics error:', error);
        toast.error('Failed to fetch statistics');
    }
};

const hideStatisticsModal = () => {
    showStatisticsModal.value = false;
    statisticsData.value = null;
};

const handleProcessOrder = async (orderId: number) => {
    try {
        await processOrder(orderId);
        toast.success('Order processed successfully');
        fetchOrders();
    } catch (error) {
        toast.error('Failed to process order');
    }
};

const handleApplyDiscount = async (orderId: number, discountAmount: number) => {
    try {
        await applyDiscount(orderId, discountAmount);
        toast.success('Discount applied successfully');
        
        // Update the selected order if it's currently being viewed
        if (selectedOrder.value && selectedOrder.value.id === orderId) {
            // Fetch the updated order details
            const response = await fetch(`/api/orders/${orderId}`);
            if (response.ok) {
                const updatedOrder = await response.json();
                selectedOrder.value = updatedOrder.data || updatedOrder;
            }
        }
        
        // Refresh the orders list
        fetchOrders();
    } catch (error) {
        toast.error('Failed to apply discount');
    }
};
</script>

<style scoped>
.orders-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.orders-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.btn-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0056b3, #004085);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.4);
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d, #545b62);
    color: white;
    box-shadow: 0 2px 4px rgba(108, 117, 125, 0.3);
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #545b62, #3d4142);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(108, 117, 125, 0.4);
}

.btn-info {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
    box-shadow: 0 2px 4px rgba(23, 162, 184, 0.3);
}

.btn-info:hover {
    background: linear-gradient(135deg, #138496, #0f6674);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(23, 162, 184, 0.4);
}

h1 {
    margin: 0;
    color: #2c3e50;
    font-size: 28px;
    font-weight: 700;
}
</style>
