<template>
    <div class="products-table-container">
        <div v-if="loading" class="loading">
            Loading products...
        </div>
        
        <table v-else class="products-table">
            <thead>
                <tr>
                    <th @click="sortBy('name')" class="sortable">
                        Name
                        <span v-if="sortColumn === 'name'">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
                    </th>
                    <th @click="sortBy('category')" class="sortable">
                        Category
                        <span v-if="sortColumn === 'category'">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
                    </th>
                    <th @click="sortBy('price')" class="sortable">
                        Price
                        <span v-if="sortColumn === 'price'">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
                    </th>
                    <th @click="sortBy('quantity')" class="sortable">
                        Quantity
                        <span v-if="sortColumn === 'quantity'">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
                    </th>
                    <th @click="sortBy('status')" class="sortable">
                        Status
                        <span v-if="sortColumn === 'status'">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
                    </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="sortedProducts.length === 0">
                    <td colspan="6" class="no-data">No products found</td>
                </tr>
                <tr v-for="product in sortedProducts" :key="product.id">
                    <td>
                        <div class="product-name">
                            <strong>{{ product.name }}</strong>
                            <div class="product-description">{{ product.description }}</div>
                        </div>
                    </td>
                    <td>
                        <span class="category-badge">{{ product.category }}</span>
                    </td>
                    <td class="price">${{ product.price }}</td>
                    <td>
                        <span :class="['quantity', getQuantityClass(product.quantity)]">
                            {{ product.quantity }}
                        </span>
                    </td>
                    <td>
                        <span :class="['status-badge', getStatusClass(product.status)]">
                            {{ product.status }}
                        </span>
                    </td>
                    <td>
                        <div class="actions">
                            <button @click="$emit('edit', product)" class="btn-edit">
                                Edit
                            </button>
                            <button @click="$emit('delete', product.id)" class="btn-delete">
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
export default {
    name: 'ProductsTable',
    props: {
        products: {
            type: Array,
            default: () => []
        },
        loading: {
            type: Boolean,
            default: false
        }
    },
    emits: ['edit', 'delete', 'refresh'],
    data() {
        return {
            sortColumn: 'name',
            sortOrder: 'asc'
        };
    },
    computed: {
        sortedProducts() {
            const sorted = [...this.products];
            sorted.sort((a, b) => {
                let aVal = a[this.sortColumn];
                let bVal = b[this.sortColumn];
                
                if (typeof aVal === 'string') {
                    aVal = aVal.toLowerCase();
                    bVal = bVal.toLowerCase();
                }
                
                if (this.sortOrder === 'asc') {
                    return aVal > bVal ? 1 : -1;
                } else {
                    return aVal < bVal ? 1 : -1;
                }
            });
            return sorted;
        }
    },
    methods: {
        sortBy(column) {
            if (this.sortColumn === column) {
                this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortOrder = 'asc';
            }
        },
        getStatusClass(status) {
            return {
                'status-active': status === 'active',
                'status-inactive': status === 'inactive',
                'status-discontinued': status === 'discontinued'
            };
        },
        getQuantityClass(quantity) {
            if (quantity === 0) return 'quantity-out';
            if (quantity < 10) return 'quantity-low';
            return 'quantity-normal';
        }
    }
};
</script>

<style scoped>
.products-table-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.loading {
    text-align: center;
    padding: 40px;
    color: #666;
}

.products-table {
    width: 100%;
    border-collapse: collapse;
}

.products-table th {
    background: #f8f9fa;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    color: #333;
    border-bottom: 2px solid #dee2e6;
}

.products-table th.sortable {
    cursor: pointer;
    user-select: none;
}

.products-table th.sortable:hover {
    background: #e9ecef;
}

.products-table td {
    padding: 12px;
    border-bottom: 1px solid #dee2e6;
}

.products-table tr:hover {
    background: #f8f9fa;
}

.product-name strong {
    display: block;
    color: #333;
}

.product-description {
    font-size: 12px;
    color: #666;
    margin-top: 4px;
}

.category-badge {
    background: #e9ecef;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.price {
    font-weight: 600;
    color: #28a745;
}

.quantity {
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 4px;
}

.quantity-normal {
    background: #d4edda;
    color: #155724;
}

.quantity-low {
    background: #fff3cd;
    color: #856404;
}

.quantity-out {
    background: #f8d7da;
    color: #721c24;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    text-transform: capitalize;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-inactive {
    background: #f8d7da;
    color: #721c24;
}

.status-discontinued {
    background: #e2e3e5;
    color: #383d41;
}

.actions {
    display: flex;
    gap: 8px;
}

.btn-edit, .btn-delete {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    transition: background-color 0.3s;
}

.btn-edit {
    background: #007bff;
    color: white;
}

.btn-edit:hover {
    background: #0056b3;
}

.btn-delete {
    background: #dc3545;
    color: white;
}

.btn-delete:hover {
    background: #c82333;
}

.no-data {
    text-align: center;
    padding: 40px;
    color: #666;
    font-style: italic;
}
</style>
