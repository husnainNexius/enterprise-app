<template>
    <div class="category-tree-container">
        <div v-if="loading" class="loading-state">
            <p>Loading category tree...</p>
        </div>
        
        <div v-else-if="categories.length === 0" class="empty-state">
            <p>No categories found</p>
        </div>

        <div v-else class="tree-content">
            <div class="tree-instructions">
                <p>📌 Drag and drop categories to reorder them. Click on actions to manage categories.</p>
            </div>
            
            <div class="tree-list">
                <CategoryTreeNode
                    v-for="category in categories"
                    :key="category.id"
                    :category="category"
                    :level="0"
                    @edit="$emit('edit', $event)"
                    @delete="$emit('delete', $event)"
                    @toggle-active="$emit('toggle-active', $event)"
                    @reorder="$emit('reorder', $event)"
                />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import type { Category, CategoryOrderUpdate } from '../Types/Category';
import CategoryTreeNode from './CategoryTreeNode.vue';

interface Props {
    categories: Category[];
    loading: boolean;
}

defineProps<Props>();
defineEmits<{
    edit: [category: Category];
    delete: [category: Category];
    'toggle-active': [category: Category];
    reorder: [categories: CategoryOrderUpdate[]];
}>();
</script>

<style scoped>
.category-tree-container {
    background: white;
    border-radius: 8px;
    padding: 20px;
    border: 1px solid #e5e7eb;
}

.loading-state,
.empty-state {
    text-align: center;
    padding: 40px;
    color: #6b7280;
}

.tree-instructions {
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 6px;
    padding: 12px 16px;
    margin-bottom: 20px;
}

.tree-instructions p {
    margin: 0;
    color: #0369a1;
    font-size: 14px;
}

.tree-list {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
</style>
