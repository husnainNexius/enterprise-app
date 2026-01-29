<template>
    <div class="category-form-overlay" @click.self="$emit('cancel')">
        <div class="category-form">
            <div class="form-header">
                <h2>{{ isEditing ? 'Edit Category' : 'Create Category' }}</h2>
                <button @click="$emit('cancel')" class="close-btn">&times;</button>
            </div>

            <form @submit.prevent="handleSubmit" class="form-content">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Category Name *</label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            :class="{ 'error': errors.name }"
                            @blur="generateSlug"
                        />
                        <span v-if="errors.name" class="error-message">{{ errors.name }}</span>
                    </div>

                    <div class="form-group">
                        <label for="slug">Slug *</label>
                        <input
                            id="slug"
                            v-model="form.slug"
                            type="text"
                            required
                            :class="{ 'error': errors.slug }"
                        />
                        <span v-if="errors.slug" class="error-message">{{ errors.slug }}</span>
                    </div>
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
                        <label for="parent_id">Parent Category</label>
                        <select
                            id="parent_id"
                            v-model="form.parent_id"
                            :class="{ 'error': errors.parent_id }"
                        >
                            <option value="">None (Root Category)</option>
                            <option 
                                v-for="cat in parentCategories" 
                                :key="cat.id" 
                                :value="cat.id"
                                :disabled="isEditing && cat.id === category?.id"
                            >
                                {{ cat.name }}
                            </option>
                        </select>
                        <span v-if="errors.parent_id" class="error-message">{{ errors.parent_id }}</span>
                    </div>

                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input
                            id="sort_order"
                            v-model.number="form.sort_order"
                            type="number"
                            min="0"
                            :class="{ 'error': errors.sort_order }"
                        />
                        <span v-if="errors.sort_order" class="error-message">{{ errors.sort_order }}</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                        />
                        <span>Active</span>
                    </label>
                </div>

                <div class="form-section">
                    <h3>SEO Settings</h3>
                    <div class="form-group">
                        <label for="meta_title">Meta Title</label>
                        <input
                            id="meta_title"
                            v-model="form.meta_title"
                            type="text"
                            maxlength="255"
                            :class="{ 'error': errors.meta_title }"
                        />
                        <span v-if="errors.meta_title" class="error-message">{{ errors.meta_title }}</span>
                    </div>

                    <div class="form-group">
                        <label for="meta_description">Meta Description</label>
                        <textarea
                            id="meta_description"
                            v-model="form.meta_description"
                            rows="2"
                            maxlength="500"
                            :class="{ 'error': errors.meta_description }"
                        ></textarea>
                        <span v-if="errors.meta_description" class="error-message">{{ errors.meta_description }}</span>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" @click="$emit('cancel')" class="btn btn-secondary">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" :disabled="loading">
                        {{ loading ? 'Saving...' : (isEditing ? 'Update Category' : 'Create Category') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import type { Category, CreateCategoryRequest, UpdateCategoryRequest } from '../Types/Category';

interface Props {
    category?: Category | null;
    categories: Category[];
    isEditing: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    save: [data: CreateCategoryRequest | UpdateCategoryRequest];
    cancel: [];
}>();

const loading = ref(false);
const errors = ref<Record<string, string>>({});

const form = ref<CreateCategoryRequest>({
    name: '',
    slug: '',
    description: '',
    parent_id: null,
    sort_order: 0,
    is_active: true,
    meta_title: '',
    meta_description: '',
});

const parentCategories = computed(() => {
    return props.categories.filter(cat => 
        !props.isEditing || cat.id !== props.category?.id
    );
});

const generateSlug = () => {
    if (form.value.name && !form.value.slug) {
        form.value.slug = form.value.name
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }
};

const validateForm = (): boolean => {
    errors.value = {};

    if (!form.value.name.trim()) {
        errors.value.name = 'Category name is required';
    }

    if (!form.value.slug.trim()) {
        errors.value.slug = 'Slug is required';
    }

    if (form.value.sort_order < 0) {
        errors.value.sort_order = 'Sort order must be 0 or greater';
    }

    return Object.keys(errors.value).length === 0;
};

const handleSubmit = () => {
    if (!validateForm()) {
        return;
    }

    const submitData: CreateCategoryRequest | UpdateCategoryRequest = {
        name: form.value.name,
        slug: form.value.slug,
        description: form.value.description || undefined,
        parent_id: form.value.parent_id || null,
        sort_order: form.value.sort_order,
        is_active: form.value.is_active,
        meta_title: form.value.meta_title || undefined,
        meta_description: form.value.meta_description || undefined,
    };

    emit('save', submitData);
};

// Initialize form when editing
watch(() => props.category, (newCategory) => {
    if (newCategory && props.isEditing) {
        form.value = {
            name: newCategory.name,
            slug: newCategory.slug,
            description: newCategory.description || '',
            parent_id: newCategory.parent_id || null,
            sort_order: newCategory.sort_order,
            is_active: newCategory.is_active,
            meta_title: newCategory.meta_title || '',
            meta_description: newCategory.meta_description || '',
        };
    } else if (!props.isEditing) {
        // Reset form for creating new category
        form.value = {
            name: '',
            slug: '',
            description: '',
            parent_id: null,
            sort_order: 0,
            is_active: true,
            meta_title: '',
            meta_description: '',
        };
    }
}, { immediate: true });

onMounted(() => {
    if (!props.isEditing) {
        generateSlug();
    }
});
</script>

<style scoped>
.category-form-overlay {
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

.category-form {
    background: white;
    border-radius: 12px;
    width: 100%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 24px 0;
    margin-bottom: 24px;
}

.form-header h2 {
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

.form-content {
    padding: 0 24px 24px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    color: #374151;
    font-weight: 500;
    font-size: 14px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.2s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group input.error,
.form-group select.error,
.form-group textarea.error {
    border-color: #ef4444;
}

.error-message {
    display: block;
    color: #ef4444;
    font-size: 12px;
    margin-top: 4px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: auto;
    margin: 0;
}

.form-section {
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid #e5e7eb;
}

.form-section h3 {
    margin: 0 0 20px 0;
    color: #1f2937;
    font-size: 18px;
    font-weight: 600;
}

.form-actions {
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

.btn-primary:hover:not(:disabled) {
    background: #2563eb;
}

.btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

@media (max-width: 640px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
}
</style>
