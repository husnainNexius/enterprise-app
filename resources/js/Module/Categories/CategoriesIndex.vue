<template>
    <div class="categories-container">
        <div class="categories-header">
            <h1>Categories Management</h1>
            <div class="header-actions">
                <button @click="showCreateForm" class="btn btn-primary">
                    Create Category
                </button>
                <button @click="toggleTreeView" class="btn btn-info">
                    {{ showTree ? 'List View' : 'Tree View' }}
                </button>
                <button @click="exportCategories" class="btn btn-secondary">
                    📥 Export
                </button>
            </div>
        </div>

        <CategoryFiltersComponent 
            :filters="filters"
            :categories="categories"
            @filter-change="handleFilterChange"
            @search="handleSearch"
        />

        <div v-if="showTree" class="tree-view">
            <CategoryTree 
                :categories="categoryTree"
                :loading="loading"
                @edit="editCategory"
                @delete="handleDeleteCategory"
                @toggle-active="handleToggleActive"
                @reorder="handleReorder"
            />
        </div>
        <div v-else class="list-view">
            <CategoriesTable 
                :categories="categories"
                :loading="loading"
                :pagination="pagination"
                @view="viewCategory"
                @edit="editCategory"
                @delete="handleDeleteCategory"
                @page-change="handlePageChange"
            />
        </div>

        <CategoryForm
            v-if="showForm"
            :category="selectedCategory"
            :categories="categories"
            :is-editing="isEditing"
            @save="handleSaveCategory"
            @cancel="hideForm"
        />

        <CategoryDetails
            v-if="showDetails"
            :category="selectedCategory"
            @close="hideDetails"
            @edit="editCategory"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useCategories } from './Composables/useCategories';
import { useToast } from 'vue-toastification';
import type { Category, CategoryFilters, CategoryOrderUpdate } from './Types/Category';

// Components
import CategoryFiltersComponent from './Components/CategoryFilters.vue';
import CategoriesTable from './Components/CategoriesTable.vue';
import CategoryTree from './Components/CategoryTree.vue';
import CategoryForm from './Components/CategoryForm.vue';
import CategoryDetails from './Components/CategoryDetails.vue';

// Composables
const { 
    categories, 
    categoryTree,
    loading, 
    pagination, 
    filters,
    fetchCategories,
    fetchCategoryTree,
    createCategory,
    updateCategory,
    deleteCategory,
    updateCategoryOrder
} = useCategories();

// Local state
const showForm = ref(false);
const showDetails = ref(false);
const showTree = ref(false);
const selectedCategory = ref<Category | null>(null);
const isEditing = ref(false);
const toast = useToast();

// Initialize
onMounted(() => {
    if (showTree.value) {
        fetchCategoryTree();
    } else {
        fetchCategories();
    }
});

// Event handlers
const handleFilterChange = (newFilters: CategoryFilters) => {
    filters.value = { ...filters.value, ...newFilters };
    if (showTree.value) {
        fetchCategoryTree();
    } else {
        fetchCategories();
    }
};

const handleSearch = (query: string) => {
    filters.value = { ...filters.value, search: query };
    if (showTree.value) {
        fetchCategoryTree();
    } else {
        fetchCategories();
    }
};

const handlePageChange = (page: number) => {
    fetchCategories(page);
};

const toggleTreeView = () => {
    showTree.value = !showTree.value;
    if (showTree.value) {
        fetchCategoryTree();
    } else {
        fetchCategories();
    }
};

const showCreateForm = () => {
    selectedCategory.value = null;
    isEditing.value = false;
    showForm.value = true;
};

const editCategory = async (category: Category) => {
    if (!category) {
        console.error('❌ No category provided to editCategory');
        return;
    }
    
    try {
        // Fetch full category details with relationships
        const response = await fetch(`/api/categories/${category.id}`);
        if (!response.ok) {
            throw new Error('Failed to fetch category details');
        }
        const fullCategory = await response.json();
        
        selectedCategory.value = fullCategory.data || fullCategory;
        isEditing.value = true;
        showForm.value = true;
        showDetails.value = false;
    } catch (error) {
        console.error('❌ Error fetching category details:', error);
        toast.error('Failed to load category details for editing');
    }
};

const viewCategory = (category: Category) => {
    selectedCategory.value = category;
    showDetails.value = true;
};

const handleSaveCategory = async (categoryData: any) => {
    try {
        if (isEditing.value && selectedCategory.value) {
            await updateCategory(selectedCategory.value.id, categoryData);
            toast.success('Category updated successfully');
        } else {
            await createCategory(categoryData);
            toast.success('Category created successfully');
        }
        hideForm();
        if (showTree.value) {
            fetchCategoryTree();
        } else {
            fetchCategories();
        }
    } catch (error) {
        toast.error((error as Error).message || 'Failed to save category');
    }
};

const handleDeleteCategory = async (category: Category) => {
    if (!confirm(`Are you sure you want to delete category "${category.name}"?`)) {
        return;
    }

    try {
        await deleteCategory(category.id);
        toast.success('Category deleted successfully');
        if (showTree.value) {
            fetchCategoryTree();
        } else {
            fetchCategories();
        }
    } catch (error: any) {
        // Handle validation errors (422)
        if (error.response?.status === 422) {
            const errorMessage = error.response.data?.error || error.response.data?.message || 'Cannot delete category';
            toast.error(errorMessage);
        } else {
            toast.error((error as Error).message || 'Failed to delete category');
        }
    }
};

const handleToggleActive = async (category: Category) => {
    try {
        await updateCategory(category.id, { is_active: !category.is_active });
        toast.success(`Category ${category.is_active ? 'deactivated' : 'activated'} successfully`);
        if (showTree.value) {
            fetchCategoryTree();
        } else {
            fetchCategories();
        }
    } catch (error: any) {
        // Handle validation errors (422)
        if (error.response?.status === 422) {
            const errorMessage = error.response.data?.error || error.response.data?.message || 'Cannot update category status';
            toast.error(errorMessage);
        } else {
            toast.error((error as Error).message || 'Failed to toggle category status');
        }
    }
};

const handleReorder = async (reorderedCategories: CategoryOrderUpdate[]) => {
    try {
        await updateCategoryOrder(reorderedCategories);
        toast.success('Category order updated successfully');
        fetchCategoryTree();
    } catch (error) {
        toast.error((error as Error).message || 'Failed to update category order');
    }
};

const hideForm = () => {
    showForm.value = false;
    selectedCategory.value = null;
    isEditing.value = false;
};

const hideDetails = () => {
    showDetails.value = false;
    selectedCategory.value = null;
};

const exportCategories = async () => {
    try {
        let categoriesToExport = showTree.value ? categoryTree.value : categories.value;
        
        if (categoriesToExport.length === 0) {
            toast.warning('No categories to export');
            return;
        }

        const headers = [
            'ID',
            'Name',
            'Slug',
            'Description',
            'Parent',
            'Sort Order',
            'Status',
            'Products Count',
            'Created At'
        ];

        const flattenCategories = (cats: Category[]): Category[] => {
            const result: Category[] = [];
            cats.forEach(cat => {
                result.push(cat);
                if (cat.children && cat.children.length > 0) {
                    result.push(...flattenCategories(cat.children));
                }
            });
            return result;
        };

        const flatCategories = showTree.value ? flattenCategories(categoriesToExport) : categoriesToExport;

        const csvContent = [
            headers.join(','),
            ...flatCategories.map(category => [
                category.id,
                `"${category.name}"`,
                `"${category.slug}"`,
                `"${category.description || ''}"`,
                `"${category.parent?.name || ''}"`,
                category.sort_order,
                category.is_active ? 'Active' : 'Inactive',
                category.products_count || 0,
                category.created_at ? new Date(category.created_at).toLocaleDateString() : 'N/A'
            ].join(','))
        ].join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        link.setAttribute('href', url);
        link.setAttribute('download', `categories_export_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        toast.success(`Exported ${flatCategories.length} categories successfully`);
    } catch (error) {
        console.error('Export error:', error);
        toast.error('Failed to export categories');
    }
};
</script>

<style scoped>
.categories-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.categories-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.btn-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0056b3, #004085);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.4);
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d, #545b62);
    color: white;
    box-shadow: 0 2px 4px rgba(108, 117, 125, 0.3);
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #545b62, #3d4142);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(108, 117, 125, 0.4);
}

.btn-info {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
    box-shadow: 0 2px 4px rgba(23, 162, 184, 0.3);
}

.btn-info:hover {
    background: linear-gradient(135deg, #138496, #0f6674);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(23, 162, 184, 0.4);
}

h1 {
    margin: 0;
    color: #2c3e50;
    font-size: 28px;
    font-weight: 700;
}

.tree-view, .list-view {
    margin-top: 20px;
}
</style>
