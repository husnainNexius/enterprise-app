<template>
    <div class="modal-overlay" @click="$emit('cancel')">
        <div class="modal-content" @click.stop>
            <div class="modal-header">
                <h2>{{ isEditing ? 'Edit Product' : 'Create New Product' }}</h2>
                <button @click="$emit('cancel')" class="close-btn">&times;</button>
            </div>
            
            <form @submit.prevent="handleSubmit" class="product-form">
                <div class="form-group">
                    <label for="name">Product Name *</label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        :class="{ 'error': errors.name }"
                    />
                    <span v-if="errors.name" class="error-message">{{ errors.name }}</span>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        :class="{ 'error': errors.description }"
                    ></textarea>
                    <span v-if="errors.description" class="error-message">{{ errors.description }}</span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price *</label>
                        <input
                            id="price"
                            v-model="form.price"
                            type="number"
                            step="0.01"
                            min="0"
                            required
                            :class="{ 'error': errors.price }"
                        />
                        <span v-if="errors.price" class="error-message">{{ errors.price }}</span>
                    </div>

                    <div class="form-group">
                        <label for="quantity">Quantity *</label>
                        <input
                            id="quantity"
                            v-model="form.quantity"
                            type="number"
                            min="0"
                            required
                            :class="{ 'error': errors.quantity }"
                        />
                        <span v-if="errors.quantity" class="error-message">{{ errors.quantity }}</span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select
                            id="category"
                            v-model="form.category"
                            required
                            :class="{ 'error': errors.category }"
                        >
                            <option value="">Select a category</option>
                            <option value="Electronics">Electronics</option>
                            <option value="Clothing">Clothing</option>
                            <option value="Food">Food</option>
                            <option value="Books">Books</option>
                            <option value="Toys">Toys</option>
                            <option value="Home">Home</option>
                            <option value="Sports">Sports</option>
                            <option value="Other">Other</option>
                        </select>
                        <span v-if="errors.category" class="error-message">{{ errors.category }}</span>
                    </div>

                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select
                            id="status"
                            v-model="form.status"
                            required
                            :class="{ 'error': errors.status }"
                        >
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="discontinued">Discontinued</option>
                        </select>
                        <span v-if="errors.status" class="error-message">{{ errors.status }}</span>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" @click="$emit('cancel')" class="btn btn-secondary">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" :disabled="submitting">
                        {{ submitting ? 'Saving...' : (isEditing ? 'Update Product' : 'Create Product') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ProductForm',
    props: {
        product: {
            type: Object,
            default: () => ({})
        },
        isEditing: {
            type: Boolean,
            default: false
        }
    },
    emits: ['save', 'cancel'],
    data() {
        return {
            form: {
                name: '',
                description: '',
                price: '',
                quantity: '',
                category: '',
                status: 'active'
            },
            errors: {},
            submitting: false
        };
    },
    watch: {
        product: {
            immediate: true,
            handler(newProduct) {
                if (newProduct && Object.keys(newProduct).length > 0) {
                    this.form = { ...newProduct };
                } else {
                    this.resetForm();
                }
            }
        }
    },
    methods: {
        resetForm() {
            this.form = {
                name: '',
                description: '',
                price: '',
                quantity: '',
                category: '',
                status: 'active'
            };
            this.errors = {};
        },
        validateForm() {
            this.errors = {};
            
            if (!this.form.name.trim()) {
                this.errors.name = 'Product name is required';
            }
            
            if (!this.form.price || this.form.price < 0) {
                this.errors.price = 'Price must be a positive number';
            }
            
            if (!this.form.quantity || this.form.quantity < 0 || !Number.isInteger(Number(this.form.quantity))) {
                this.errors.quantity = 'Quantity must be a non-negative integer';
            }
            
            if (!this.form.category) {
                this.errors.category = 'Category is required';
            }
            
            if (!this.form.status) {
                this.errors.status = 'Status is required';
            }
            
            if (this.form.description && this.form.description.length > 1000) {
                this.errors.description = 'Description must not exceed 1000 characters';
            }
            
            return Object.keys(this.errors).length === 0;
        },
        async handleSubmit() {
            if (!this.validateForm()) {
                return;
            }
            
            this.submitting = true;
            
            try {
                const productData = {
                    ...this.form,
                    price: parseFloat(this.form.price),
                    quantity: parseInt(this.form.quantity)
                };
                
                if (this.isEditing) {
                    productData.id = this.product.id;
                }
                
                this.$emit('save', productData);
            } catch (error) {
                console.error('Error submitting form:', error);
            } finally {
                this.submitting = false;
            }
        }
    }
};
</script>

<style scoped>
.modal-overlay {
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

.modal-content {
    background: white;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.modal-header h2 {
    margin: 0;
    color: #374151;
    font-size: 18px;
    font-weight: 600;
}

.close-btn {
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

.close-btn:hover {
    background: #f3f4f6;
    color: #374151;
}

.product-form {
    padding: 20px;
}

.form-group {
    margin-bottom: 16px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.form-group label {
    display: block;
    margin-bottom: 4px;
    font-weight: 500;
    color: #374151;
    font-size: 13px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 13px;
    transition: border-color 0.2s;
    background: #ffffff;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 1px #3b82f6;
}

.form-group input:hover,
.form-group select:hover,
.form-group textarea:hover {
    border-color: #9ca3af;
}

.form-group input.error,
.form-group select.error,
.form-group textarea.error {
    border-color: #ef4444;
    box-shadow: 0 0 0 1px #ef4444;
}

.error-message {
    color: #ef4444;
    font-size: 12px;
    margin-top: 4px;
    display: block;
    font-weight: 500;
}

.form-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid #e5e7eb;
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

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .modal-content {
        width: 95%;
        margin: 10px;
    }
}
</style>
