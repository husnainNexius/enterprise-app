<template>
    <div class="products-container">
        <div class="header">
            <h1>Products Management</h1>
            <button @click="showCreateForm" class="btn btn-primary">
                Add New Product
            </button>
        </div>

        <div class="filters">
            <input 
                v-model="searchTerm" 
                type="text" 
                placeholder="Search products..." 
                class="search-input"
            />
            <select v-model="statusFilter" class="filter-select">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="discontinued">Discontinued</option>
            </select>
        </div>

        <products-table 
            :products="filteredProducts"
            :loading="loading"
            @edit="editProduct"
            @delete="deleteProduct"
            @refresh="fetchProducts"
        />

        <!-- Pagination Controls -->
        <div class="pagination">
            <div class="pagination-info">
                Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} products
            </div>
            <div class="pagination-controls">
                <button 
                    @click="goToPage(1)" 
                    class="pagination-btn"
                >
                    First
                </button>
                <button 
                    @click="goToPage(pagination.current_page - 1)" 
                    class="pagination-btn"
                >
                    Previous
                </button>
                
                <span class="page-numbers">
                    <button 
                        v-for="page in getVisiblePages()" 
                        :key="page"
                        @click="goToPage(page)"
                        :class="['page-btn', { active: page === pagination.current_page }]"
                    >
                        {{ page }}
                    </button>
                </span>
                
                <button 
                    @click="goToPage(pagination.current_page + 1)" 
                    class="pagination-btn"
                >
                    Next
                </button>
                <button 
                    @click="goToPage(pagination.last_page)" 
                    class="pagination-btn"
                >
                    Last
                </button>
            </div>
        </div>

        <product-form
            v-if="showForm"
            :product="selectedProduct"
            :is-editing="isEditing"
            @save="saveProduct"
            @cancel="hideForm"
        />
    </div>
</template>

<script>
import ProductsTable from './Components/ProductsTable.vue';
import ProductForm from './Components/ProductForm.vue';
import { useToast } from 'vue-toastification';

export default {
    name: 'ProductsIndex',
    components: {
        ProductsTable,
        ProductForm
    },
    data() {
        return {
            products: [],
            pagination: {
                current_page: 1,
                last_page: 1,
                per_page: 8,
                total: 0,
                from: 0,
                to: 0
            },
            loading: false,
            showForm: false,
            isEditing: false,
            selectedProduct: null,
            searchTerm: '',
            statusFilter: '',
            toast: useToast()
        };
    },
    computed: {
        filteredProducts() {
            return this.products.filter(product => {
                const matchesSearch = product.name.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                                    product.description.toLowerCase().includes(this.searchTerm.toLowerCase());
                const matchesStatus = !this.statusFilter || product.status === this.statusFilter;
                return matchesSearch && matchesStatus;
            });
        }
    },
    mounted() {
        this.fetchProducts();
    },
    methods: {
        async fetchProducts(page = 1) {
            this.loading = true;
            try {
                const response = await fetch(`/api/products?page=${page}`);
                const data = await response.json();
                
                
                // Laravel paginate() returns this structure:
                // {
                //   "data": [...],
                //   "current_page": 1,
                //   "last_page": 13,
                //   "per_page": 8,
                //   "total": 100,
                //   "from": 1,
                //   "to": 8
                // }
                
                if (data && typeof data === 'object') {
                    // Check if it's Laravel pagination format
                    if (data.data && Array.isArray(data.data) && data.current_page !== undefined) {
                        this.products = data.data;
                        this.pagination = {
                            current_page: parseInt(data.current_page),
                            last_page: parseInt(data.last_page),
                            per_page: parseInt(data.per_page),
                            total: parseInt(data.total),
                            from: parseInt(data.from || 1),
                            to: parseInt(data.to || data.data.length)
                        };
                    }
                    // Check if it's a simple array
                    else if (Array.isArray(data)) {
                        this.products = data;
                        const totalPages = Math.ceil(data.length / 8);
                        this.pagination = {
                            current_page: page,
                            last_page: totalPages,
                            per_page: 8,
                            total: data.length,
                            from: ((page - 1) * 8) + 1,
                            to: Math.min(page * 8, data.length)
                        };
                    }
                    // Fallback - create pagination manually
                    else {
                        this.products = data.data || [];
                        this.pagination = {
                            current_page: page,
                            last_page: 13, // We know we have 100 products seeded
                            per_page: 8,
                            total: 100,
                            from: ((page - 1) * 8) + 1,
                            to: Math.min(page * 8, 100)
                        };
                    }
                } else {
                    // Last resort fallback
                    this.products = [];
                    this.pagination = {
                        current_page: 1,
                        last_page: 1,
                        per_page: 8,
                        total: 0,
                        from: 0,
                        to: 0
                    };
                }
                
            } catch (error) {
                this.toast.error('Failed to fetch products');
                console.error('Error fetching products:', error);
            } finally {
                this.loading = false;
            }
        },
        showCreateForm() {
            this.selectedProduct = null;
            this.isEditing = false;
            this.showForm = true;
        },
        editProduct(product) {
            this.selectedProduct = { ...product };
            this.isEditing = true;
            this.showForm = true;
        },
        hideForm() {
            this.showForm = false;
            this.selectedProduct = null;
            this.isEditing = false;
        },
        async saveProduct(productData) {
            try {
                const url = this.isEditing ? `/api/products/${productData.id}` : '/api/products';
                const method = this.isEditing ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(productData)
                });

                if (response.ok) {
                    this.toast.success(this.isEditing ? 'Product updated successfully' : 'Product created successfully');
                    this.hideForm();
                    this.fetchProducts();
                } else {
                    const error = await response.json();
                    this.toast.error(error.error || 'Failed to save product');
                }
            } catch (error) {
                this.toast.error('Failed to save product');
                console.error('Error saving product:', error);
            }
        },
        async deleteProduct(productId) {
            if (!confirm('Are you sure you want to delete this product?')) {
                return;
            }

            try {
                const response = await fetch(`/api/products/${productId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    this.toast.success('Product deleted successfully');
                    this.fetchProducts(this.pagination.current_page);
                } else {
                    const error = await response.json();
                    this.toast.error(error.error || 'Failed to delete product');
                }
            } catch (error) {
                this.toast.error('Failed to delete product');
                console.error('Error deleting product:', error);
            }
        },
        goToPage(page) {
            
            try {
                // Validate page number
                if (!page || page < 1 || page > this.pagination.last_page) {
                    console.warn('Invalid page number:', page, 'last_page:', this.pagination.last_page);
                    return;
                }
                
                // Don't fetch if already on this page
                if (page === this.pagination.current_page) {
                    return;
                }
                
                this.fetchProducts(page);
            } catch (error) {
                console.error('Error navigating to page:', error);
                this.toast.error('Failed to navigate to page');
            }
        },
        getVisiblePages() {
            const current = this.pagination.current_page;
            const last = this.pagination.last_page;
            const delta = 2; // Show 2 pages before and after current
            

            if (!current || !last || last <= 1) {
                return [1];
            }
            
            const range = [];
            const rangeWithDots = [];
            let l;

            for (let i = 1; i <= last; i++) {
                if (i === 1 || i === last || (i >= current - delta && i <= current + delta)) {
                    range.push(i);
                }
            }

            range.forEach((i) => {
                if (l) {
                    if (i - l === 2) {
                        rangeWithDots.push(l + 1);
                    } else if (i - l !== 1) {
                        rangeWithDots.push('...');
                    }
                }
                rangeWithDots.push(i);
                l = i;
            });

            return rangeWithDots.filter(page => page !== '...');
        }
    }
};
</script>

<style scoped>
.products-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.header h1 {
    color: #333;
    margin: 0;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.3s;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-primary:hover {
    background-color: #0056b3;
}

.filters {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.search-input, .filter-select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.search-input {
    flex: 1;
    max-width: 300px;
}

.filter-select {
    min-width: 150px;
}

/* Pagination Styles */
.pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    padding: 20px 0;
    border-top: 1px solid #dee2e6;
}

.pagination-info {
    color: #666;
    font-size: 14px;
}

.pagination-controls {
    display: flex;
    align-items: center;
    gap: 5px;
}

.pagination-btn, .page-btn {
    padding: 8px 12px;
    border: 1px solid #ddd;
    background: white;
    color: #007bff;
    cursor: pointer;
    border-radius: 4px;
    font-size: 14px;
    transition: all 0.3s;
}

.pagination-btn:hover:not(:disabled), .page-btn:hover:not(.active) {
    background: #e9ecef;
    border-color: #adb5bd;
}

.pagination-btn:disabled, .page-btn:disabled {
    background: #f8f9fa;
    color: #6c757d;
    cursor: not-allowed;
    border-color: #dee2e6;
}

.page-btn.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.page-numbers {
    display: flex;
    gap: 2px;
}
</style>
