<template>
    <div class="category-filters">
        <div class="filters-header">
            <h3>Filters</h3>
            <button @click="resetFilters" class="reset-btn">
                Reset
            </button>
        </div>

        <div class="filters-content">
            <div class="filter-group">
                <label for="search">Search</label>
                <div class="search-input">
                    <input
                        id="search"
                        v-model="localFilters.search"
                        type="text"
                        placeholder="Search categories..."
                        @input="handleSearch"
                    />
                    <span class="search-icon">🔍</span>
                </div>
            </div>

            <div class="filter-group">
                <label for="status">Status</label>
                <select
                    id="status"
                    v-model="localFilters.is_active"
                    @change="handleFilterChange"
                >
                    <option :value="null">All Status</option>
                    <option :value="true">Active</option>
                    <option :value="false">Inactive</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="parent">Parent Category</label>
                <select
                    id="parent"
                    v-model="localFilters.parent_id"
                    @change="handleFilterChange"
                >
                    <option :value="null">All Categories</option>
                    <option :value="null">None (Root Categories)</option>
                    <option 
                        v-for="category in parentCategories" 
                        :key="category.id" 
                        :value="category.id"
                    >
                        {{ category.name }}
                    </option>
                </select>
            </div>
        </div>

        <div class="filters-summary" v-if="hasActiveFilters">
            <p>Active filters: {{ activeFilterCount }}</p>
            <button @click="resetFilters" class="clear-all-btn">
                Clear All
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import type { CategoryFilters } from '../Types/Category';
import type { Category } from '../Types/Category';

interface Props {
    filters: CategoryFilters;
    categories: Category[];
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'filter-change': [filters: CategoryFilters];
    'search': [query: string];
}>();

const localFilters = ref<CategoryFilters>({ ...props.filters });

const parentCategories = computed(() => {
    return props.categories.filter(cat => !cat.parent_id);
});

const hasActiveFilters = computed(() => {
    return Object.values(localFilters.value).some(value => 
        value !== '' && value !== null && value !== undefined
    );
});

const activeFilterCount = computed(() => {
    return Object.values(localFilters.value).filter(value => 
        value !== '' && value !== null && value !== undefined
    ).length;
});

const handleFilterChange = () => {
    emit('filter-change', { ...localFilters.value });
};

const handleSearch = () => {
    emit('search', localFilters.value.search || '');
};

const resetFilters = () => {
    localFilters.value = {
        search: '',
        is_active: null,
        parent_id: null,
    };
    emit('filter-change', { ...localFilters.value });
    emit('search', '');
};

// Watch for props changes
watch(() => props.filters, (newFilters) => {
    localFilters.value = { ...newFilters };
}, { deep: true });
</script>

<style scoped>
.category-filters {
    background: white;
    border-radius: 8px;
    padding: 20px;
    border: 1px solid #e5e7eb;
    margin-bottom: 24px;
}

.filters-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.filters-header h3 {
    margin: 0;
    color: #1f2937;
    font-size: 18px;
    font-weight: 600;
}

.reset-btn {
    background: none;
    border: 1px solid #d1d5db;
    color: #6b7280;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.reset-btn:hover {
    background: #f3f4f6;
    border-color: #9ca3af;
    color: #374151;
}

.filters-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filter-group label {
    font-weight: 500;
    color: #374151;
    font-size: 14px;
}

.filter-group input,
.filter-group select {
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.2s;
}

.filter-group input:focus,
.filter-group select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.search-input {
    position: relative;
}

.search-input input {
    width: 100%;
    padding-right: 40px;
}

.search-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 14px;
}

.filters-summary {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.filters-summary p {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
}

.clear-all-btn {
    background: #ef4444;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.clear-all-btn:hover {
    background: #dc2626;
}

@media (max-width: 768px) {
    .filters-content {
        grid-template-columns: 1fr;
    }
    
    .filters-summary {
        flex-direction: column;
        gap: 12px;
        align-items: stretch;
    }
    
    .clear-all-btn {
        width: 100%;
    }
}
</style>
