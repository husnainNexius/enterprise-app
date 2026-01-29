<template>
    <div class="category-details-overlay" @click.self="$emit('close')">
        <div class="category-details">
            <div class="details-header">
                <h2>Category Details</h2>
                <button @click="$emit('close')" class="close-btn">&times;</button>
            </div>

            <div v-if="category" class="details-content">
                <div class="category-info">
                    <div class="info-section">
                        <h3>Basic Information</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Name:</label>
                                <span>{{ category.name }}</span>
                            </div>
                            <div class="info-item">
                                <label>Slug:</label>
                                <span>{{ category.slug }}</span>
                            </div>
                            <div class="info-item">
                                <label>Status:</label>
                                <span :class="['status-badge', category.is_active ? 'active' : 'inactive']">
                                    {{ category.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="info-item">
                                <label>Sort Order:</label>
                                <span>{{ category.sort_order }}</span>
                            </div>
                            <div class="info-item full-width">
                                <label>Description:</label>
                                <span>{{ category.description || 'No description provided' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="info-section">
                        <h3>Hierarchy</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Parent Category:</label>
                                <span>{{ category.parent?.name || 'None (Root Category)' }}</span>
                            </div>
                            <div class="info-item">
                                <label>Full Path:</label>
                                <span>{{ category.full_path || category.name }}</span>
                            </div>
                            <div class="info-item">
                                <label>Children Count:</label>
                                <span>{{ category.children_count || 0 }}</span>
                            </div>
                            <div class="info-item">
                                <label>Has Children:</label>
                                <span>{{ category.has_children ? 'Yes' : 'No' }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="category.children && category.children.length > 0" class="info-section">
                        <h3>Subcategories</h3>
                        <div class="children-list">
                            <div 
                                v-for="child in category.children" 
                                :key="child.id"
                                class="child-item"
                            >
                                <div class="child-info">
                                    <span class="child-name">{{ child.name }}</span>
                                    <span :class="['status-badge', child.is_active ? 'active' : 'inactive']">
                                        {{ child.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <div class="child-meta">
                                    <span>Sort Order: {{ child.sort_order }}</span>
                                    <span>Products: {{ child.products_count || 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="info-section">
                        <h3>Statistics</h3>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="stat-value">{{ category.products_count || 0 }}</div>
                                <div class="stat-label">Products</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">{{ category.children_count || 0 }}</div>
                                <div class="stat-label">Subcategories</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">{{ category.is_active ? 'Active' : 'Inactive' }}</div>
                                <div class="stat-label">Status</div>
                            </div>
                        </div>
                    </div>

                    <div class="info-section">
                        <h3>SEO Settings</h3>
                        <div class="info-grid">
                            <div class="info-item full-width">
                                <label>Meta Title:</label>
                                <span>{{ category.meta_title || 'Not set' }}</span>
                            </div>
                            <div class="info-item full-width">
                                <label>Meta Description:</label>
                                <span>{{ category.meta_description || 'Not set' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="info-section">
                        <h3>Timestamps</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Created:</label>
                                <span>{{ formatDate(category.created_at) }}</span>
                            </div>
                            <div class="info-item">
                                <label>Updated:</label>
                                <span>{{ formatDate(category.updated_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="details-actions">
                    <button @click="$emit('edit', category)" class="btn btn-primary">
                        Edit Category
                    </button>
                    <button @click="$emit('close')" class="btn btn-secondary">
                        Close
                    </button>
                </div>
            </div>

            <div v-else class="loading-state">
                <p>Loading category details...</p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import type { Category } from '../Types/Category';

interface Props {
    category?: Category | null;
}

defineProps<Props>();
defineEmits<{
    close: [];
    edit: [category: Category];
}>();

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString();
};
</script>

<style scoped>
.category-details-overlay {
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
    padding: 20px;
}

.category-details {
    background: white;
    border-radius: 12px;
    width: 100%;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.details-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 24px 0;
    margin-bottom: 24px;
}

.details-header h2 {
    margin: 0;
    color: #1f2937;
    font-size: 24px;
    font-weight: 600;
}

.close-btn {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #6b7280;
    padding: 4px;
    border-radius: 4px;
    transition: all 0.2s;
}

.close-btn:hover {
    background: #f3f4f6;
    color: #374151;
}

.details-content {
    padding: 0 24px 24px;
}

.category-info {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.info-section {
    background: #f9fafb;
    border-radius: 8px;
    padding: 20px;
    border: 1px solid #e5e7eb;
}

.info-section h3 {
    margin: 0 0 16px 0;
    color: #1f2937;
    font-size: 18px;
    font-weight: 600;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.info-item.full-width {
    grid-column: 1 / -1;
}

.info-item label {
    font-weight: 500;
    color: #374151;
    font-size: 14px;
}

.info-item span {
    color: #1f2937;
    font-size: 14px;
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

.children-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.child-item {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 12px;
}

.child-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.child-name {
    font-weight: 500;
    color: #1f2937;
}

.child-meta {
    display: flex;
    gap: 16px;
    font-size: 12px;
    color: #6b7280;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.stat-item {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 16px;
    text-align: center;
}

.stat-value {
    font-size: 24px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 12px;
    color: #6b7280;
    text-transform: uppercase;
}

.details-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid #e5e7eb;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

.loading-state {
    text-align: center;
    padding: 40px;
    color: #6b7280;
}

@media (max-width: 640px) {
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .details-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
}
</style>
