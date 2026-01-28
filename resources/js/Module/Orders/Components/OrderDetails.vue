<template>
    <div class="order-details-overlay" v-if="show">
        <div class="order-details-container">
            <div class="order-details-header">
                <h2>Order Details - {{ order?.order_number }}</h2>
                <button @click="closeDetails" class="btn-close">×</button>
            </div>

            <div v-if="order" class="order-details-content">
                <!-- Order Summary -->
                <div class="order-summary">
                    <div class="summary-item">
                        <label>Order Number:</label>
                        <span>{{ order.order_number }}</span>
                    </div>
                    <div class="summary-item">
                        <label>Status:</label>
                        <span :class="getStatusClass(order.status)" class="status-badge">
                            {{ order.status }}
                        </span>
                    </div>
                    <div class="summary-item">
                        <label>Payment Status:</label>
                        <span :class="getPaymentStatusClass(order.payment_status)" class="status-badge">
                            {{ order.payment_status }}
                        </span>
                    </div>
                    <div class="summary-item">
                        <label>Payment Method:</label>
                        <span>{{ order.payment_method }}</span>
                    </div>
                    <div class="summary-item">
                        <label>Order Date:</label>
                        <span>{{ formatDateTime(order.order_date) }}</span>
                    </div>
                    <div class="summary-item" v-if="order.shipped_date">
                        <label>Shipped Date:</label>
                        <span>{{ formatDateTime(order.shipped_date) }}</span>
                    </div>
                    <div class="summary-item" v-if="order.delivered_date">
                        <label>Delivered Date:</label>
                        <span>{{ formatDateTime(order.delivered_date) }}</span>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="customer-section">
                    <h3>Customer Information</h3>
                    <div v-if="order.customer" class="customer-info">
                        <div class="info-row">
                            <label>Name:</label>
                            <span>{{ order.customer.name }}</span>
                        </div>
                        <div class="info-row">
                            <label>Email:</label>
                            <span>{{ order.customer.email }}</span>
                        </div>
                    </div>
                    <div v-else class="no-customer">
                        Customer information not available
                    </div>
                </div>

                <!-- Order Items -->
                <div class="items-section">
                    <h3>Order Items</h3>
                    <div class="items-list">
                        <div v-for="item in order.items" :key="item.id" class="item-card">
                            <div class="item-info">
                                <h4>{{ item.product?.name || 'Product not available' }}</h4>
                                <p v-if="item.product?.description" class="item-description">
                                    {{ item.product.description }}
                                </p>
                                <div class="item-details">
                                    <span class="item-quantity">Quantity: {{ item.quantity }}</span>
                                    <span class="item-price">Unit Price: {{ formatCurrency(item.unit_price) }}</span>
                                    <span class="item-total">Total: {{ formatCurrency(item.total_price) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pricing Information -->
                <div class="pricing-section">
                    <h3>Pricing Information</h3>
                    <div class="pricing-details">
                        <div class="pricing-row">
                            <label>Subtotal:</label>
                            <span>{{ formatCurrency(order.total_amount) }}</span>
                        </div>
                        <div class="pricing-row">
                            <label>Tax:</label>
                            <span>{{ formatCurrency(order.tax_amount) }}</span>
                        </div>
                        <div class="pricing-row">
                            <label>Shipping:</label>
                            <span>{{ formatCurrency(order.shipping_amount) }}</span>
                        </div>
                        <div class="pricing-row" v-if="order.discount_amount > 0">
                            <label>Discount:</label>
                            <span class="discount">-{{ formatCurrency(order.discount_amount) }}</span>
                        </div>
                        <div class="pricing-row total">
                            <label>Grand Total:</label>
                            <span>{{ formatCurrency(order.grand_total) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="address-section">
                    <h3>Shipping Address</h3>
                    <div class="address-info">
                        <div class="address-row">
                            <strong>{{ order.shipping_address.name }}</strong>
                        </div>
                        <div class="address-row">{{ order.shipping_address.email }}</div>
                        <div class="address-row">{{ order.shipping_address.phone }}</div>
                        <div class="address-row">{{ order.shipping_address.address }}</div>
                        <div class="address-row">
                            {{ order.shipping_address.city }}, {{ order.shipping_address.state }} {{ order.shipping_address.postal_code }}
                        </div>
                        <div class="address-row">{{ order.shipping_address.country }}</div>
                    </div>
                </div>

                <!-- Billing Address (if different) -->
                <div class="address-section" v-if="hasDifferentBillingAddress">
                    <h3>Billing Address</h3>
                    <div class="address-info">
                        <div class="address-row">
                            <strong>{{ order.billing_address.name }}</strong>
                        </div>
                        <div class="address-row">{{ order.billing_address.email }}</div>
                        <div class="address-row">{{ order.billing_address.phone }}</div>
                        <div class="address-row">{{ order.billing_address.address }}</div>
                        <div class="address-row">
                            {{ order.billing_address.city }}, {{ order.billing_address.state }} {{ order.billing_address.postal_code }}
                        </div>
                        <div class="address-row">{{ order.billing_address.country }}</div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="notes-section" v-if="order.notes">
                    <h3>Order Notes</h3>
                    <div class="notes-content">
                        {{ order.notes }}
                    </div>
                </div>

                <!-- Status History -->
                <div class="history-section" v-if="order.status_history && order.status_history.length > 0">
                    <h3>Status History</h3>
                    <div class="history-list">
                        <div v-for="history in order.status_history" :key="history.id" class="history-item">
                            <div class="history-header">
                                <span class="history-status">
                                    {{ history.from_status || 'Created' }} → {{ history.to_status }}
                                </span>
                                <span class="history-date">{{ formatDateTime(history.created_at) }}</span>
                            </div>
                            <div class="history-user" v-if="history.changed_by">
                                Changed by: {{ history.changed_by.name }}
                            </div>
                            <div class="history-notes" v-if="history.notes">
                                {{ history.notes }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="actions-section">
                    <div class="action-buttons">
                        <button 
                            v-if="order.can_be_updated"
                            @click="editOrder"
                            class="btn btn-warning"
                        >
                            Edit Order
                        </button>
                        <button 
                            v-if="order.status === 'confirmed' || order.status === 'processing'"
                            @click="processOrder"
                            class="btn btn-primary"
                        >
                            Process Order
                        </button>
                        <button 
                            v-if="order.status !== 'cancelled' && order.status !== 'refunded'"
                            @click="showDiscountModal"
                            class="btn btn-info"
                        >
                            Apply Discount
                        </button>
                        <button 
                            v-if="order.can_be_cancelled"
                            @click="cancelOrder"
                            class="btn btn-danger"
                        >
                            Cancel Order
                        </button>
                        <button 
                            @click="printOrder"
                            class="btn btn-secondary"
                        >
                            Print Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useToast } from 'vue-toastification';
import type { Order, OrderStatus } from '../Types/Order';

interface Props {
    order: Order;
}

interface Emits {
    (e: 'close'): void;
    (e: 'edit', order: Order): void;
    (e: 'status-change', order: Order, status: OrderStatus): void;
    (e: 'process-order', orderId: number): void;
    (e: 'apply-discount', orderId: number, amount: number): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();
const toast = useToast();

const show = ref(true);

// Computed
const hasDifferentBillingAddress = computed(() => {
    if (!props.order.billing_address) return false;
    return JSON.stringify(props.order.shipping_address) !== JSON.stringify(props.order.billing_address);
});

// Methods
const closeDetails = () => {
    emit('close');
};

const editOrder = () => {
    if (!props.order) {
        console.error('❌ No order in props for editOrder');
        return;
    }
    emit('edit', props.order);
};

const cancelOrder = () => {
    if (confirm('Are you sure you want to cancel this order?')) {
        emit('status-change', props.order, 'cancelled');
    }
};

const printOrder = () => {
    // Create a print-friendly version of the order
    const order = props.order;
    if (!order) {
        alert('No order data available to print');
        return;
    }

    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Order #${order.order_number}</title>
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
                    max-width: 800px;
                    margin: 0 auto;
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
                    color: #2c3e50;
                }
                
                .header .order-number {
                    font-size: 18px;
                    font-weight: bold;
                    color: #007bff;
                }
                
                .section {
                    margin-bottom: 25px;
                }
                
                .section h3 {
                    font-size: 16px;
                    margin-bottom: 15px;
                    color: #2c3e50;
                    border-bottom: 1px solid #ddd;
                    padding-bottom: 5px;
                }
                
                .info-grid {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 15px;
                    margin-bottom: 20px;
                }
                
                .info-item {
                    display: flex;
                    justify-content: space-between;
                    padding: 8px 0;
                    border-bottom: 1px solid #eee;
                }
                
                .info-label {
                    font-weight: 600;
                    color: #555;
                }
                
                .info-value {
                    font-weight: 500;
                }
                
                .status {
                    padding: 4px 8px;
                    border-radius: 4px;
                    font-size: 11px;
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
                    padding: 4px 8px;
                    border-radius: 4px;
                    font-size: 11px;
                    font-weight: bold;
                    text-transform: uppercase;
                }
                
                .payment-paid { background: #d4edda; color: #155724; }
                .payment-pending { background: #fff3cd; color: #856404; }
                .payment-failed { background: #f8d7da; color: #721c24; }
                
                .items-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                
                .items-table th,
                .items-table td {
                    border: 1px solid #ddd;
                    padding: 10px;
                    text-align: left;
                }
                
                .items-table th {
                    background-color: #f8f9fa;
                    font-weight: bold;
                    font-size: 12px;
                    text-transform: uppercase;
                }
                
                .items-table tr:nth-child(even) {
                    background-color: #f9f9f9;
                }
                
                .totals {
                    text-align: right;
                    margin-top: 20px;
                }
                
                .total-row {
                    display: flex;
                    justify-content: space-between;
                    padding: 8px 0;
                    border-bottom: 1px solid #eee;
                }
                
                .total-row.grand-total {
                    font-weight: bold;
                    font-size: 16px;
                    border-top: 2px solid #333;
                    border-bottom: none;
                    margin-top: 10px;
                }
                
                .notes {
                    background: #f8f9fa;
                    padding: 15px;
                    border-radius: 5px;
                    border-left: 4px solid #007bff;
                }
                
                .footer {
                    margin-top: 40px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                    text-align: center;
                    font-size: 11px;
                    color: #666;
                }
                
                @media print {
                    body { padding: 15px; }
                    .header { margin-bottom: 20px; }
                    .section { margin-bottom: 20px; }
                    .info-grid { gap: 10px; }
                    .items-table { font-size: 10px; }
                    .items-table th, .items-table td { padding: 6px; }
                    .footer { margin-top: 30px; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>ORDER DETAILS</h1>
                <div class="order-number">#${order.order_number}</div>
                <div>Generated: ${new Date().toLocaleDateString()} at ${new Date().toLocaleTimeString()}</div>
            </div>
            
            <div class="section">
                <h3>Order Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Order Number:</span>
                        <span class="info-value">${order.order_number}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status:</span>
                        <span class="status status-${order.status}">${order.status}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Payment Status:</span>
                        <span class="payment-status payment-${order.payment_status}">${order.payment_status}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Payment Method:</span>
                        <span class="info-value">${order.payment_method}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Order Date:</span>
                        <span class="info-value">${formatDateTime(order.order_date)}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Total Amount:</span>
                        <span class="info-value" style="font-weight: bold; color: #007bff;">$${parseFloat(order.total_amount || 0).toFixed(2)}</span>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h3>Customer Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Name:</span>
                        <span class="info-value">${order.customer?.name || 'N/A'}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <span class="info-value">${order.customer?.email || 'N/A'}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Phone:</span>
                        <span class="info-value">${order.shipping_address?.phone || 'N/A'}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Address:</span>
                        <span class="info-value">${order.shipping_address?.address || 'N/A'}, ${order.shipping_address?.city || 'N/A'}, ${order.shipping_address?.state || 'N/A'} ${order.shipping_address?.postal_code || 'N/A'}</span>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h3>Order Items</h3>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${order.items?.map((item: any) => `
                            <tr>
                                <td>${item.product?.name || 'Product #' + item.product_id}</td>
                                <td>${item.quantity}</td>
                                <td>$${parseFloat(item.unit_price || 0).toFixed(2)}</td>
                                <td>$${parseFloat(item.total_price || 0).toFixed(2)}</td>
                            </tr>
                        `).join('') || '<tr><td colspan="4">No items found</td></tr>'}
                    </tbody>
                </table>
                
                <div class="totals">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>$${parseFloat(order.total_amount || 0).toFixed(2)}</span>
                    </div>
                    <div class="total-row">
                        <span>Tax:</span>
                        <span>$${parseFloat(order.tax_amount || 0).toFixed(2)}</span>
                    </div>
                    <div class="total-row">
                        <span>Shipping:</span>
                        <span>$${parseFloat(order.shipping_amount || 0).toFixed(2)}</span>
                    </div>
                    <div class="total-row grand-total">
                        <span>Total:</span>
                        <span>$${parseFloat(order.total_amount || 0).toFixed(2)}</span>
                    </div>
                </div>
            </div>
            
            ${order.notes ? `
            <div class="section">
                <h3>Notes</h3>
                <div class="notes">${order.notes}</div>
            </div>
            ` : ''}
            
            <div class="footer">
                <p>This order was generated from the Enterprise Orders Management System</p>
                <p>Thank you for your business!</p>
            </div>
        </body>
        </html>
    `;

    // Create a new window for printing
    const printWindow = window.open('', '_blank');
    if (!printWindow) {
        alert('Failed to open print window. Please check your popup settings.');
        return;
    }

    printWindow.document.write(printContent);
    printWindow.document.close();
    
    // Wait for content to load before printing
    printWindow.onload = () => {
        printWindow.print();
        printWindow.close();
    };
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

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};

const formatDateTime = (dateString: string): string => {
    return new Date(dateString).toLocaleString();
};

const processOrder = async () => {
    if (!props.order) return;
    try {
        const response = await fetch(`/api/orders/${props.order.id}/process`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            }
        });
        
        if (response.ok) {
            toast.success('Order processed successfully');
            emit('process-order', props.order.id);
            emit('close');
        } else {
            const error = await response.json();
            toast.error(error.error || 'Failed to process order');
        }
    } catch (error) {
        toast.error('Failed to process order');
    }
};

const showDiscountModal = () => {
    const discountAmount = prompt('Enter discount amount:');
    if (discountAmount && !isNaN(discountAmount)) {
        const amount = parseFloat(discountAmount);
        if (amount > 0) {
            applyDiscount(props.order.id, amount);
        } else {
            toast.error('Invalid discount amount');
        }
    }
};

const applyDiscount = async (orderId: number, amount: number) => {
    try {
        const response = await fetch(`/api/orders/${orderId}/apply-discount`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ discount_amount: amount })
        });
        
        if (response.ok) {
            const result = await response.json();
            toast.success(`Discount of $${amount.toFixed(2)} applied successfully`);
            
            // Emit the updated order data to parent
            emit('apply-discount', orderId, amount);
            
            // Update the local order data with the response
            if (result.data || result) {
                const updatedOrder = result.data || result;
                // Force a re-render by updating the order prop through parent
                emit('edit', updatedOrder);
            }
        } else {
            const error = await response.json();
            toast.error(error.error || 'Failed to apply discount');
        }
    } catch (error) {
        toast.error('Failed to apply discount');
    }
};
</script>

<style scoped>
.order-details-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.order-details-container {
    background: white;
    border-radius: 8px;
    width: 90%;
    max-width: 700px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.order-details-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.order-details-header h2 {
    margin: 0;
    color: #374151;
    font-size: 18px;
    font-weight: 600;
}

.btn-close {
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: #6b7280;
    padding: 4px;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.btn-close:hover {
    background: #f3f4f6;
    color: #374151;
}

.order-details-content {
    padding: 20px;
}

.order-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 12px;
    padding: 16px;
    background: #f9fafb;
    border-radius: 6px;
    margin-bottom: 16px;
    border: 1px solid #e5e7eb;
}

.summary-item {
    display: flex;
    flex-direction: column;
    padding: 8px;
    background: white;
    border-radius: 4px;
    border: 1px solid #e5e7eb;
}

.summary-item label {
    font-weight: 500;
    color: #6b7280;
    margin-bottom: 4px;
    font-size: 11px;
    text-transform: uppercase;
}

.summary-item span {
    font-weight: 500;
    color: #374151;
    font-size: 13px;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
    text-transform: uppercase;
    display: inline-block;
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

.customer-section,
.items-section,
.pricing-section,
.address-section,
.notes-section,
.history-section,
.actions-section {
    margin-bottom: 20px;
}

.customer-section h3,
.items-section h3,
.pricing-section h3,
.address-section h3,
.notes-section h3,
.history-section h3 {
    margin-bottom: 12px;
    color: #374151;
    font-size: 16px;
    font-weight: 600;
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 4px;
}

.customer-info {
    background: #f9fafb;
    padding: 12px;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
}

.info-row {
    display: flex;
    margin-bottom: 8px;
    padding: 4px 0;
}

.info-row label {
    font-weight: 500;
    color: #6b7280;
    min-width: 120px;
    font-size: 13px;
}

.no-customer {
    color: #6b7280;
    font-style: italic;
    padding: 12px;
    background: #f9fafb;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
}

.items-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.item-card {
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 12px;
    background: white;
}

.item-info h4 {
    margin: 0 0 4px 0;
    color: #374151;
    font-size: 14px;
    font-weight: 600;
}

.item-description {
    color: #6b7280;
    font-size: 12px;
    margin: 0 0 8px 0;
    line-height: 1.4;
}

.item-details {
    display: flex;
    gap: 12px;
    font-size: 12px;
    flex-wrap: wrap;
}

.item-quantity,
.item-price,
.item-total {
    color: #6b7280;
    padding: 2px 6px;
    background: #f3f4f6;
    border-radius: 3px;
    font-weight: 500;
}

.item-total {
    font-weight: 600;
    color: #374151;
    background: #3b82f6;
    color: white;
}

.pricing-details {
    background: #f9fafb;
    padding: 12px;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
}

.pricing-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    padding: 4px 0;
    font-size: 13px;
}

.pricing-row label {
    font-weight: 500;
    color: #6b7280;
}

.pricing-row.total {
    border-top: 1px solid #e5e7eb;
    padding-top: 8px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0;
    font-size: 14px;
}

.discount {
    color: #dc3545;
}

.address-info {
    background: #f9fafb;
    padding: 12px;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
}

.address-row {
    margin-bottom: 4px;
    padding: 2px 0;
    line-height: 1.3;
}

.address-row strong {
    color: #374151;
    font-weight: 600;
}

.notes-content {
    background: #f9fafb;
    padding: 12px;
    border-radius: 6px;
    color: #374151;
    border: 1px solid #e5e7eb;
    line-height: 1.4;
    font-size: 13px;
}

.history-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.history-item {
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 12px;
    background: white;
}

.history-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 6px;
    align-items: center;
}

.history-status {
    font-weight: 500;
    color: #374151;
    font-size: 12px;
}

.history-date {
    color: #6b7280;
    font-size: 11px;
    font-weight: 500;
}

.history-user {
    color: #6b7280;
    font-size: 11px;
    margin-bottom: 4px;
    font-weight: 500;
}

.history-notes {
    color: #374151;
    font-style: italic;
    line-height: 1.3;
    font-size: 12px;
}

.action-buttons {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    flex-wrap: wrap;
}

.btn {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    transition: background-color 0.2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}

.btn-warning {
    background: #f59e0b;
    color: white;
}

.btn-warning:hover {
    background: #d97706;
}

.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-danger:hover {
    background: #dc2626;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

@media (max-width: 768px) {
    .order-summary {
        grid-template-columns: 1fr;
    }
    
    .item-details {
        flex-direction: column;
        gap: 5px;
    }
    
    .pricing-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>
