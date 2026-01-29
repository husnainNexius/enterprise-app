// ========================================
// USE CATEGORIES COMPOSABLE - STATE MANAGEMENT
// ========================================
// This Vue 3 composable provides centralized state management for categories
// It combines reactive state, API calls, and business logic in one reusable function
// Components can import this to get access to category data and methods

import { ref, reactive } from 'vue';
import { CategoryService } from '../Services/CategoryService';
import type { 
    Category, 
    CategoryFilters, 
    CreateCategoryRequest, 
    UpdateCategoryRequest, 
    CategoryOrderUpdate,
    ApiResponse 
} from '../Types/Category';

/**
 * useCategories composable - Main category management hook
 * Returns reactive state and methods for category operations
 * 
 * Usage in Vue components:
 * const { categories, loading, fetchCategories, createCategory } = useCategories();
 */
export function useCategories() {
    // ========================================
    // REACTIVE STATE MANAGEMENT
    // ========================================
    
    /**
     * categories - Array of categories for list view
     * ref() makes it reactive so UI updates when data changes
     * Initially empty array, populated by fetchCategories()
     */
    const categories = ref<Category[]>([]);
    
    /**
     * categoryTree - Hierarchical categories for tree view
     * Separate from categories because tree structure is different
     * Populated by fetchCategoryTree()
     */
    const categoryTree = ref<Category[]>([]);
    
    /**
     * loading - Global loading state for API operations
     * Shows spinners/loading states in UI while fetching data
     */
    const loading = ref(false);
    
    /**
     * pagination - Reactive pagination object
     * reactive() makes the entire object reactive (deep reactivity)
     * Updated automatically when API returns pagination data
     */
    const pagination = reactive({
        current_page: 1,      // Current page number
        last_page: 1,          // Total number of pages
        per_page: 15,          // Items per page
        total: 0,              // Total number of items
        from: 0,               // First item number on current page
        to: 0                  // Last item number on current page
    });
    
    /**
     * filters - Current filter state
     * ref() makes it reactive so UI can bind to filter controls
     * Used to persist filter values across API calls
     */
    const filters = ref<CategoryFilters>({});

    // ========================================
    // SERVICE LAYER
    // ========================================
    
    /**
     * categoryService - Instance of API service
     * Single instance reused across all methods
     * Handles all HTTP communication with backend
     */
    const categoryService = new CategoryService();

    // ========================================
    // DATA FETCHING METHODS
    // ========================================

    /**
     * fetchCategories - Get paginated categories with filters
     * @param page - Optional page number to fetch (defaults to current page)
     * 
     * This method:
     * 1. Sets loading state to true
     * 2. Calls API with current filters and page
     * 3. Updates categories array with response data
     * 4. Updates pagination object from response
     * 5. Handles errors gracefully
     * 6. Sets loading state to false
     */
    const fetchCategories = async (page?: number) => {
        loading.value = true;
        try {
            // Call API with current filters and specified page
            const response = await categoryService.getCategories({
                ...filters.value,
                page: page || pagination.current_page
            });

            // Update reactive state with response data
            if (response) {
                // Handle different response structures
                if (response.data && Array.isArray(response.data)) {
                    categories.value = response.data;
                } else if (Array.isArray(response)) {
                    categories.value = response;
                } else {
                    console.error('Unexpected API response structure:', response);
                    categories.value = [];
                }
                
                // Update pagination if provided
                if (response && response.pagination) {
                    Object.assign(pagination, response.pagination);
                }
            } else {
                console.error('No response received:', response);
                categories.value = [];
            }
        } catch (error) {
            console.error('Failed to fetch categories:', error);
        } finally {
            loading.value = false;
        }
    };

    /**
     * fetchCategoryTree - Get hierarchical category structure
     * 
     * This method:
     * 1. Calls tree API endpoint
     * 2. Updates categoryTree with hierarchical data
     * 3. Used for tree view component
     */
    const fetchCategoryTree = async () => {
        try {
            const response = await categoryService.getCategoryTree();
            if (response) {
                // Handle different response structures
                if (response.data && Array.isArray(response.data)) {
                    categoryTree.value = response.data;
                } else if (Array.isArray(response)) {
                    categoryTree.value = response;
                } else {
                    console.error('Unexpected tree API response structure:', response);
                    categoryTree.value = [];
                }
            } else {
                console.error('No tree response received:', response);
                categoryTree.value = [];
            }
        } catch (error) {
            console.error('Failed to fetch category tree:', error);
        }
    };

    // ========================================
    // CRUD OPERATIONS
    // ========================================

    /**
     * createCategory - Create new category
     * @param categoryData - Category data to create
     * @returns Promise<Category> - Created category
     * 
     * Usage: Called from form submission in create mode
     * Returns the complete created category with ID
     */
    const createCategory = async (categoryData: CreateCategoryRequest): Promise<Category> => {
        return await categoryService.createCategory(categoryData);
    };

    /**
     * updateCategory - Update existing category
     * @param id - Category ID to update
     * @param categoryData - Partial data to update
     * @returns Promise<Category> - Updated category
     * 
     * Usage: Called from form submission in edit mode
     * Accepts partial data for flexible updates
     */
    const updateCategory = async (id: number, categoryData: UpdateCategoryRequest): Promise<Category> => {
        return await categoryService.updateCategory(id, categoryData);
    };

    /**
     * deleteCategory - Delete category by ID
     * @param id - Category ID to delete
     * @returns Promise<void> - No return value on success
     * 
     * Usage: Called when user confirms deletion
     * Server validates deletion constraints
     */
    const deleteCategory = async (id: number): Promise<void> => {
        return await categoryService.deleteCategory(id);
    };

    /**
     * getCategory - Get single category with relationships
     * @param id - Category ID to fetch
     * @returns Promise<Category> - Category with full relationships
     * 
     * Usage: Called before opening edit/view forms
     * Ensures we have complete category data
     */
    const getCategory = async (id: number): Promise<Category> => {
        const response = await categoryService.getCategory(id);
        return response.data;
    };

    // ========================================
    // SPECIALIZED OPERATIONS
    // ========================================

    /**
     * updateCategoryOrder - Reorder categories in hierarchy
     * @param categories - Array of category position updates
     * @returns Promise<void> - No return value on success
     * 
     * Usage: Called after drag-and-drop reordering
     * Updates both sort_order and parent_id for moved categories
     */
    const updateCategoryOrder = async (categories: CategoryOrderUpdate[]): Promise<void> => {
        return await categoryService.updateCategoryOrder(categories);
    };

    /**
     * searchCategories - Search categories by text
     * @param query - Search text
     * @returns Promise<Category[]> - Matching categories
     * 
     * Usage: Called from search input/autocomplete
     * Searches across name and description fields
     */
    const searchCategories = async (query: string): Promise<Category[]> => {
        return await categoryService.searchCategories(query, filters.value);
    };

    // ========================================
    // RETURN API - COMPOSABLE INTERFACE
    // ========================================
    
    /**
     * Return object containing all reactive state and methods
     * Components can destructure what they need:
     * const { categories, loading, fetchCategories } = useCategories();
     */
    return {
        // Reactive State
        categories,           // Array of categories (list view)
        categoryTree,         // Hierarchical categories (tree view)
        loading,              // Global loading state
        pagination,           // Pagination information
        filters,              // Current filter values
        
        // Data Fetching Methods
        fetchCategories,      // Get paginated categories
        fetchCategoryTree,    // Get hierarchical categories
        
        // CRUD Methods
        createCategory,        // Create new category
        updateCategory,        // Update existing category
        deleteCategory,        // Delete category
        getCategory,          // Get single category
        
        // Specialized Methods
        updateCategoryOrder,   // Reorder categories
        searchCategories       // Search categories
    };
}
