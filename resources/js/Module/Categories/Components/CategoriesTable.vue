<template>
    <div class="categories-table-container">
        <div v-if="loading" class="loading-state">
            <p>Loading categories...</p>
        </div>
        
        <div v-else-if="categories.length === 0" class="empty-state">
            <p>No categories found</p>
        </div>

        <div v-else class="table-responsive">
            <table class="categories-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Parent</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th>Products</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="category in categories" :key="category.id">
                        <td>
                            <div class="category-name">
                                <span class="name">{{ category.name }}</span>
                                <span v-if="category.has_children" class="children-indicator">
                                    ({{ category.children_count }})
                                </span>
                            </div>
                        </td>
                        <td>
                            <code class="slug">{{ category.slug }}</code>
                        </td>
                        <td>
                            <span v-if="category.parent" class="parent-category">
                                {{ category.parent.name }}
                            </span>
                            <span v-else class="no-parent">Root</span>
                        </td>
                        <td>
                            <span :class="['status-badge', category.is_active ? 'active' : 'inactive']">
                                {{ category.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <span class="sort-order">{{ category.sort_order }}</span>
                        </td>
                        <td>
                            <span class="products-count">{{ category.products_count || 0 }}</span>
                        </td>
                        <td>
                            <span class="created-date">{{ formatDate(category.created_at) }}</span>
                        </td>
                        <td>
                            <div class="actions">
                                <button 
                                    @click="$emit('view', category)"
                                    class="action-btn view-btn"
                                    title="View details"
                                >
                                    👁️
                                </button>
                                <button 
                                    @click="$emit('edit', category)"
                                    class="action-btn edit-btn"
                                    title="Edit category"
                                >
                                    ✏️
                                </button>
                                <button 
                                    @click="$emit('delete', category)"
                                    class="action-btn delete-btn"
                                    title="Delete category"
                                >
                                    🗑️
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="pagination" class="pagination">
            <div class="pagination-info">
                <span>
                    Showing {{ pagination.from || 0 }} to {{ pagination.to || 0 }} 
                    of {{ pagination.total }} categories
                </span>
            </div>
            <div class="pagination-controls">
                <button 
                    @click="$emit('page-change', pagination.current_page - 1)"
                    :disabled="pagination.current_page <= 1"
                    class="pagination-btn"
                >
                    Previous
                </button>
                <span class="page-info">
                    Page {{ pagination.current_page }} of {{ pagination.last_page }}
                </span>
                <button 
                    @click="$emit('page-change', pagination.current_page + 1)"
                    :disabled="pagination.current_page >= pagination.last_page"
                    class="pagination-btn"
                >
                    Next
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import type { Category } from '../Types/Category';

interface Props {
    categories: Category[];
    loading: boolean;
    pagination: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
}

defineProps<Props>();
defineEmits<{
    view: [category: Category];
    edit: [category: Category];
    delete: [category: Category];
    'page-change': [page: number];
}>();

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
};
</script>

<style scoped>
.categories-table-container {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
}

.loading-state,
.empty-state {
    text-align: center;
    padding: 40px;
    color: #6b7280;
}

.table-responsive {
    overflow-x: auto;
}

.categories-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.categories-table th {
    background: #f9fafb;
    padding: 12px 16px;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 1px solid #e5e7eb;
    white-space: nowrap;
}

.categories-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.categories-table tr:hover {
    background: #f9fafb;
}

.category-name {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.name {
    font-weight: 500;
    color: #1f2937;
}

.children-indicator {
    font-size: 12px;
    color: #6b7280;
}

.slug {
    background: #f3f4f6;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 12px;
    color: #6b7280;
    font-family: monospace;
}

.parent-category {
    color: #3b82f6;
    font-weight: 500;
}

.no-parent {
    color: #9ca3af;
    font-style: italic;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
}

.status-badge.active {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.inactive {
    background: #fee2e2;
    color: #991b1b;
}

.sort-order {
    font-family: monospace;
    color: #6b7280;
}

.products-count {
    font-weight: 500;
    color: #1f2937;
}

.created-date {
    color: #6b7280;
    font-size: 12px;
}

.actions {
    display: flex;
    gap: 4px;
}

.action-btn {
    background: none;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    padding: 6px 8px;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
}

.action-btn:hover {
    transform: translateY(-1px);
}

.view-btn:hover {
    background: #3b82f6;
    border-color: #3b82f6;
}

.edit-btn:hover {
    background: #f59e0b;
    border-color: #f59e0b;
}

.delete-btn:hover {
    background: #ef4444;
    border-color: #ef4444;
}

.pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
}

.pagination-info {
    color: #6b7280;
    font-size: 14px;
}

.pagination-controls {
    display: flex;
    align-items: center;
    gap: 12px;
}

.pagination-btn {
    background: white;
    border: 1px solid #d1d5db;
    color: #374151;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
}

.pagination-btn:hover:not(:disabled) {
    background: #f3f4f6;
    border-color: #9ca3af;
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.page-info {
    color: #6b7280;
    font-size: 14px;
}

@media (max-width: 768px) {
    .categories-table {
        font-size: 12px;
    }
    
    .categories-table th,
    .categories-table td {
        padding: 8px 12px;
    }
    
    .pagination {
        flex-direction: column;
        gap: 12px;
        text-align: center;
    }
    
    .actions {
        flex-direction: column;
    }
}
</style>
