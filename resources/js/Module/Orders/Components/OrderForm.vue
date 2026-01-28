<template>
    <div class="order-form-overlay" v-if="show">
        <div class="order-form-container">
            <div class="order-form-header">
                <h2>{{ isEditing ? 'Edit Order' : 'Create Order' }}</h2>
                <button @click="closeForm" class="btn-close">×</button>
            </div>

            <form @submit.prevent="handleSubmit" class="order-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="customer_id">Customer *</label>
                        <div class="select-wrapper">
                            <select
                                id="customer_id"
                                v-model="form.customer_id"
                                required
                                class="form-control custom-select"
                            >
                                <option value="">👤 Select Customer</option>
                                <option 
                                    v-for="customer in customers" 
                                    :key="customer.id"
                                    :value="customer.id"
                                >
                                    {{ customer.name }} ({{ customer.email }})
                                </option>
                            </select>
                        </div>
                        <span v-if="errors.customer_id" class="error">{{ errors.customer_id }}</span>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Order Items</h3>
                    <div class="items-container">
                        <div v-for="(item, index) in form.items" :key="index" class="item-row">
                            <div class="form-group">
                                <label>Product *</label>
                                <div class="select-wrapper">
                                    <select
                                        v-model="item.product_id"
                                        required
                                        @change="updateItemTotal(index)"
                                        class="form-control custom-select"
                                    >
                                        <option value="">📦 Select Product</option>
                                        <option 
                                            v-for="product in products" 
                                            :key="product.id"
                                            :value="product.id"
                                        >
                                            {{ product.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <span v-if="errors[`items.${index}.product_id`]" class="error">
                                {{ errors[`items.${index}.product_id`] }}
                            </span>

                            <div class="form-group">
                                <label>Quantity *</label>
                                <input
                                    type="number"
                                    v-model.number="item.quantity"
                                    min="1"
                                    required
                                    @input="updateItemTotal(index)"
                                    class="form-control"
                                />
                                <span v-if="errors[`items.${index}.quantity`]" class="error">
                                    {{ errors[`items.${index}.quantity`] }}
                                </span>
                            </div>

                            <div class="form-group">
                                <label>Total</label>
                                <input
                                    type="text"
                                    :value="formatCurrency(item.total_price)"
                                    readonly
                                    class="form-control"
                                />
                            </div>

                            <div class="form-group">
                                <button 
                                    type="button"
                                    @click="removeItem(index)"
                                    class="btn btn-danger btn-sm"
                                    v-if="form.items.length > 1"
                                >
                                    Remove
                                </button>
                            </div>
                        </div>

                        <button 
                            type="button"
                            @click="addItem"
                            class="btn btn-secondary"
                        >
                            Add Item
                        </button>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="payment_method">Payment Method *</label>
                        <select
                            id="payment_method"
                            v-model="form.payment_method"
                            required
                            class="form-control"
                        >
                            <option value="">Select Payment Method</option>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="paypal">PayPal</option>
                            <option value="stripe">Stripe</option>
                        </select>
                        <span v-if="errors.payment_method" class="error">{{ errors.payment_method }}</span>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Shipping Address *</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="shipping_name">Name *</label>
                            <input
                                id="shipping_name"
                                v-model="form.shipping_address.name"
                                required
                                class="form-control"
                            />
                            <span v-if="errors['shipping_address.name']" class="error">
                                {{ errors['shipping_address.name'] }}
                            </span>
                        </div>

                        <div class="form-group">
                            <label for="shipping_email">Email *</label>
                            <input
                                id="shipping_email"
                                type="email"
                                v-model="form.shipping_address.email"
                                required
                                class="form-control"
                            />
                            <span v-if="errors['shipping_address.email']" class="error">
                                {{ errors['shipping_address.email'] }}
                            </span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="shipping_phone">Phone *</label>
                            <input
                                id="shipping_phone"
                                v-model="form.shipping_address.phone"
                                required
                                class="form-control"
                            />
                            <span v-if="errors['shipping_address.phone']" class="error">
                                {{ errors['shipping_address.phone'] }}
                            </span>
                        </div>

                        <div class="form-group">
                            <label for="shipping_address">Address *</label>
                            <input
                                id="shipping_address"
                                v-model="form.shipping_address.address"
                                required
                                class="form-control"
                            />
                            <span v-if="errors['shipping_address.address']" class="error">
                                {{ errors['shipping_address.address'] }}
                            </span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="shipping_city">City *</label>
                            <input
                                id="shipping_city"
                                v-model="form.shipping_address.city"
                                required
                                class="form-control"
                            />
                            <span v-if="errors['shipping_address.city']" class="error">
                                {{ errors['shipping_address.city'] }}
                            </span>
                        </div>

                        <div class="form-group">
                            <label for="shipping_state">State *</label>
                            <input
                                id="shipping_state"
                                v-model="form.shipping_address.state"
                                required
                                class="form-control"
                            />
                            <span v-if="errors['shipping_address.state']" class="error">
                                {{ errors['shipping_address.state'] }}
                            </span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="shipping_postal_code">Postal Code *</label>
                            <input
                                id="shipping_postal_code"
                                v-model="form.shipping_address.postal_code"
                                required
                                class="form-control"
                            />
                            <span v-if="errors['shipping_address.postal_code']" class="error">
                                {{ errors['shipping_address.postal_code'] }}
                            </span>
                        </div>

                        <div class="form-group">
                            <label for="shipping_country">Country *</label>
                            <input
                                id="shipping_country"
                                v-model="form.shipping_address.country"
                                required
                                class="form-control"
                            />
                            <span v-if="errors['shipping_address.country']" class="error">
                                {{ errors['shipping_address.country'] }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea
                        id="notes"
                        v-model="form.notes"
                        rows="3"
                        class="form-control"
                    ></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" :disabled="submitting" class="btn btn-primary">
                        {{ submitting ? 'Saving...' : (isEditing ? 'Update Order' : 'Create Order') }}
                    </button>
                    <button type="button" @click="closeForm" class="btn btn-secondary">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, watch, computed } from 'vue';
import { useToast } from 'vue-toastification';
import type { Order, CreateOrderRequest } from '../Types/Order';

interface Props {
    order?: Order | null;
    isEditing: boolean;
}

interface Emits {
    (e: 'save', orderData: CreateOrderRequest): void;
    (e: 'cancel'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const toast = useToast();

// State
const show = ref(true);
const submitting = ref(false);
const errors = ref<Record<string, string>>({});
const customers = ref<any[]>([]);
const products = ref<any[]>([]);

// Form data
const form = reactive({
    customer_id: props.order?.customer_id || '',
    items: props.order?.items?.map(item => ({
        id: item.id, // Include the order item ID for updates
        product_id: Number(item.product_id), // Convert to number to match products list
        quantity: item.quantity,
        total_price: item.total_price,
        product: item.product || null // Include product data from OrderItemResource
    })) || [{ product_id: '', quantity: 1, total_price: 0, product: null, id: undefined }],
    payment_method: props.order?.payment_method || '',
    shipping_address: props.order?.shipping_address || {
        name: '',
        email: '',
        phone: '',
        address: '',
        city: '',
        state: '',
        postal_code: '',
        country: ''
    },
    notes: props.order?.notes || ''
});



// Computed properties for debugging
const productsCount = computed(() => products.value.length);
const hasProducts = computed(() => products.value.length > 0);

// Watch for order prop changes (when editing)
watch(() => props.order, (newOrder) => {
    if (newOrder && props.isEditing) {
        // Update form with new order data
        form.customer_id = newOrder.customer_id || '';
        form.items = newOrder.items?.map(item => ({
            product_id: Number(item.product_id), // Convert to number to match products list
            quantity: item.quantity,
            total_price: item.total_price,
            product: item.product || null // Include product data from OrderItemResource
        })) || [{ product_id: '', quantity: 1, total_price: 0, product: null }];
        form.payment_method = newOrder.payment_method || '';
        form.shipping_address = newOrder.shipping_address || {
            name: '',
            email: '',
            phone: '',
            address: '',
            city: '',
            state: '',
            postal_code: '',
            country: ''
        };
        form.notes = newOrder.notes || '';
        
    }
}, { deep: true });

// Methods
const closeForm = () => {
    emit('cancel');
};

const addItem = () => {
    form.items.push({ product_id: '', quantity: 1, total_price: 0 });
};

const removeItem = (index: number) => {
    form.items.splice(index, 1);
};

const updateItemTotal = (index: number) => {
    const item = form.items[index];
    const product = products.value.find(p => p.id === item.product_id);
    if (product) {
        item.total_price = item.quantity * product.price;
    }
};

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};

const handleSubmit = async () => {
    try {
        submitting.value = true;
        errors.value = {};

        // Validation
        if (!form.customer_id) {
            errors.value.customer_id = 'Customer is required';
        }

        if (form.items.some(item => !item.product_id || item.quantity < 1)) {
            errors.value.items = 'All items must have a valid product and quantity';
        }

        if (!form.payment_method) {
            errors.value.payment_method = 'Payment method is required';
        }

        // Validate shipping address
        const requiredShippingFields = ['name', 'email', 'phone', 'address', 'city', 'state', 'postal_code', 'country'];
        requiredShippingFields.forEach(field => {
            if (!form.shipping_address[field]) {
                errors.value[`shipping_address.${field}`] = `${field.charAt(0).toUpperCase() + field.slice(1)} is required`;
            }
        });

        if (Object.keys(errors.value).length > 0) {
            return;
        }

        // Handle create vs update differently
        if (props.isEditing && props.order) {
            // Update existing order - send items with IDs
            const updateData = {
                notes: form.notes,
                shipping_address: form.shipping_address,
                items: form.items.map(item => ({
                    id: item.id, // Include the order item ID for updates
                    quantity: item.quantity,
                    unit_price: item.total_price / item.quantity // Calculate unit price
                }))
            };
            emit('save', updateData);
        } else {
            // Create new order
            const orderData = {
                customer_id: form.customer_id,
                items: form.items.map(item => ({
                    product_id: item.product_id,
                    quantity: item.quantity
                })),
                payment_method: form.payment_method,
                shipping_address: form.shipping_address,
                notes: form.notes
            };
            emit('save', orderData);
        }
    } catch (error) {
        toast.error('Failed to save order');
    } finally {
        submitting.value = false;
    }
};

// Load customers and products
onMounted(async () => {
    try {
        // Load customers
        const customersResponse = await fetch('/api/users');
        if (customersResponse.ok) {
            const customersData = await customersResponse.json();
            customers.value = Array.isArray(customersData) ? customersData : customersData.data || [];
        }

        // Load products
        const productsResponse = await fetch('/api/products');
        if (productsResponse.ok) {
            const productsData = await productsResponse.json();
            products.value = productsData.data || productsData || [];
        }
        
    } catch (error) {
        console.error('Failed to load data:', error);
        toast.error('Failed to load data');
    }
});
</script>

<style scoped>
.order-form-overlay {
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

.order-form-container {
    background: white;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.order-form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.order-form-header h2 {
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

.order-form {
    padding: 20px;
}

.form-section {
    margin-bottom: 20px;
}

.form-section h3 {
    margin-bottom: 12px;
    color: #374151;
    font-size: 14px;
    font-weight: 600;
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 4px;
}

.form-row {
    display: flex;
    gap: 12px;
    margin-bottom: 12px;
    flex-wrap: wrap;
}

.form-group {
    flex: 1;
    min-width: 200px;
}

.form-group label {
    display: block;
    margin-bottom: 4px;
    font-weight: 500;
    color: #374151;
    font-size: 13px;
}

.form-control {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 13px;
    transition: border-color 0.2s;
    background: #ffffff;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 1px #3b82f6;
}

.form-control:hover {
    border-color: #9ca3af;
}

.select-wrapper {
    position: relative;
}

.custom-select {
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%2024%2024'%20fill='none'%20stroke='%236b7280'%20stroke-width='2'%20stroke-linecap='round'%20stroke-linejoin='round'%3e%3cpolyline%20points='6%209%2012%2015%2018%209'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 8px center;
    background-size: 16px;
}

.error {
    color: #ef4444;
    font-size: 12px;
    margin-top: 4px;
    display: block;
    font-weight: 500;
}

.items-container {
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 12px;
    background: #f9fafb;
}

.item-row {
    display: flex;
    gap: 8px;
    align-items: end;
    margin-bottom: 8px;
    padding: 8px;
    background: white;
    border-radius: 4px;
    border: 1px solid #e5e7eb;
}

.item-row .form-group {
    margin-bottom: 0;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    transition: background-color 0.2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #2563eb;
}

.btn-primary:disabled {
    background: #9ca3af;
    cursor: not-allowed;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-danger:hover {
    background: #dc2626;
}

.btn-sm {
    padding: 4px 8px;
    font-size: 11px;
}

.form-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid #e5e7eb;
}

@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
    }
    
    .form-group {
        min-width: auto;
    }
    
    .item-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>
