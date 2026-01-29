<template>
    <div 
        class="tree-node"
        :style="{ paddingLeft: `${level * 24}px` }"
        draggable
        @dragstart="handleDragStart"
        @dragover="handleDragOver"
        @drop="handleDrop"
        @dragend="handleDragEnd"
    >
        <div class="node-content">
            <div class="node-info">
                <div class="node-main">
                    <span class="drag-handle">⋮⋮</span>
                    <span class="expand-icon" @click="toggleExpanded">
                        {{ isExpanded ? '▼' : '▶' }}
                    </span>
                    <span class="category-name">{{ category.name }}</span>
                    <span :class="['status-badge', category.is_active ? 'active' : 'inactive']">
                        {{ category.is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <span v-if="category.products_count" class="products-count">
                        ({{ category.products_count }} products)
                    </span>
                </div>
                <div class="node-meta">
                    <code class="slug">{{ category.slug }}</code>
                    <span class="sort-order">Order: {{ category.sort_order }}</span>
                </div>
            </div>
            
            <div class="node-actions">
                <button 
                    @click="$emit('toggle-active', category)"
                    :class="['action-btn', category.is_active ? 'deactivate-btn' : 'activate-btn']"
                    :title="category.is_active ? 'Deactivate' : 'Activate'"
                >
                    {{ category.is_active ? '🔴' : '🟢' }}
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
        </div>

        <div v-if="isExpanded && category.children && category.children.length > 0" class="children">
            <CategoryTreeNode
                v-for="child in category.children"
                :key="child.id"
                :category="child"
                :level="level + 1"
                @edit="$emit('edit', $event)"
                @delete="$emit('delete', $event)"
                @toggle-active="$emit('toggle-active', $event)"
                @reorder="$emit('reorder', $event)"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import type { Category, CategoryOrderUpdate } from '../Types/Category';

interface Props {
    category: Category;
    level: number;
}

const props = defineProps<Props>();
defineEmits<{
    edit: [category: Category];
    delete: [category: Category];
    'toggle-active': [category: Category];
    reorder: [categories: CategoryOrderUpdate[]];
}>();

const isExpanded = ref(true);
const isDragging = ref(false);

const toggleExpanded = () => {
    isExpanded.value = !isExpanded.value;
};

const handleDragStart = (event: DragEvent) => {
    isDragging.value = true;
    event.dataTransfer?.setData('text/plain', props.category.id.toString());
    event.dataTransfer!.effectAllowed = 'move';
    (event.target as HTMLElement).classList.add('dragging');
};

const handleDragOver = (event: DragEvent) => {
    event.preventDefault();
    event.dataTransfer!.dropEffect = 'move';
    (event.target as HTMLElement).classList.add('drag-over');
};

const handleDrop = (event: DragEvent) => {
    event.preventDefault();
    const draggedId = parseInt(event.dataTransfer?.getData('text/plain') || '0');
    
    if (draggedId !== props.category.id) {
        // Emit reorder event with the new order
        // This is a simplified version - in a real implementation, you'd need to
        // track the full tree structure and emit a complete reorder array
        console.log(`Moving category ${draggedId} to position of ${props.category.id}`);
    }
    
    (event.target as HTMLElement).classList.remove('drag-over');
};

const handleDragEnd = (event: DragEvent) => {
    isDragging.value = false;
    (event.target as HTMLElement).classList.remove('dragging');
    document.querySelectorAll('.drag-over').forEach(el => {
        el.classList.remove('drag-over');
    });
};
</script>

<style scoped>
.tree-node {
    border: 1px solid transparent;
    border-radius: 6px;
    margin-bottom: 2px;
    transition: all 0.2s;
}

.tree-node:hover {
    background: #f9fafb;
    border-color: #e5e7eb;
}

.tree-node.dragging {
    opacity: 0.5;
}

.tree-node.drag-over {
    background: #eff6ff;
    border-color: #3b82f6;
}

.node-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    min-height: 48px;
}

.node-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1;
}

.node-main {
    display: flex;
    align-items: center;
    gap: 8px;
}

.drag-handle {
    color: #9ca3af;
    cursor: grab;
    font-size: 12px;
    user-select: none;
}

.drag-handle:active {
    cursor: grabbing;
}

.expand-icon {
    color: #6b7280;
    cursor: pointer;
    font-size: 10px;
    width: 16px;
    text-align: center;
    user-select: none;
}

.expand-icon:hover {
    color: #374151;
}

.category-name {
    font-weight: 500;
    color: #1f2937;
}

.status-badge {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 10px;
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

.products-count {
    color: #6b7280;
    font-size: 12px;
}

.node-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 12px;
    color: #6b7280;
}

.slug {
    background: #f3f4f6;
    padding: 1px 4px;
    border-radius: 2px;
    font-family: monospace;
}

.sort-order {
    color: #9ca3af;
}

.node-actions {
    display: flex;
    gap: 4px;
}

.action-btn {
    background: none;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    padding: 4px 6px;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 28px;
}

.action-btn:hover {
    transform: translateY(-1px);
}

.activate-btn:hover {
    background: #10b981;
    border-color: #10b981;
}

.deactivate-btn:hover {
    background: #ef4444;
    border-color: #ef4444;
}

.edit-btn:hover {
    background: #f59e0b;
    border-color: #f59e0b;
}

.delete-btn:hover {
    background: #ef4444;
    border-color: #ef4444;
}

.children {
    margin-top: 2px;
}
</style>
