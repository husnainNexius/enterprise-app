// ========================================
// CATEGORY SERVICE - API LAYER
// ========================================
// This service class handles all HTTP communication with the Category API
// It provides a clean interface between the UI components and the backend API
// Each method corresponds to a specific API endpoint

import axios from 'axios';
import type { 
    Category, 
    CategoryFilters, 
    CreateCategoryRequest, 
    UpdateCategoryRequest, 
    CategoryOrderUpdate,
    ApiResponse,
    PaginatedResponse 
} from '../Types/Category';

// Base URL for all category API endpoints
const API_BASE_URL = '/api/categories';

/**
 * CategoryService class - Centralized API communication layer
 * This class encapsulates all HTTP requests for category management
 * Uses axios for HTTP client with proper configuration
 */
export class CategoryService {
    // ========================================
    // HTTP CLIENT CONFIGURATION
    // ========================================
    
    /**
     * Axios instance configured for category API calls
     * - baseURL: All requests will be prefixed with '/api/categories'
     * - headers: Sets JSON content type and accept headers for proper API communication
     */
    private api = axios.create({
        baseURL: API_BASE_URL,
        headers: {
            'Content-Type': 'application/json',    // Tell server we're sending JSON
            'Accept': 'application/json',           // Tell server we want JSON response
        },
    });

    // ========================================
    // READ OPERATIONS
    // ========================================

    /**
     * Get paginated list of categories with optional filtering
     * @param filters - Optional filters for search, status, parent, pagination
     * @returns Promise<PaginatedResponse<Category>> - Paginated category data
     * 
     * Usage: Used in list view to display categories with pagination and filters
     */
    async getCategories(filters?: CategoryFilters): Promise<PaginatedResponse<Category>> {
        const response = await this.api.get('', { params: filters });
        return response.data;
    }

    /**
     * Get hierarchical tree structure of categories
     * @returns Promise<ApiResponse<Category[]>> - Tree-structured categories
     * 
     * Usage: Used in tree view to display parent-child relationships
     * Returns only root categories with their children nested inside
     */
    async getCategoryTree(): Promise<ApiResponse<Category[]>> {
        const response = await this.api.get('/tree');
        return response.data;
    }

    /**
     * Get single category by ID with full relationships
     * @param id - Category ID to fetch
     * @returns Promise<ApiResponse<Category>> - Single category with relationships
     * 
     * Usage: Used in edit/view forms to get complete category details
     * Includes parent, children, and product count relationships
     */
    async getCategory(id: number): Promise<ApiResponse<Category>> {
        const response = await this.api.get(`/${id}`);
        return response.data;
    }

    /**
     * Search categories by text query with optional filters
     * @param query - Search text to match against name/description
     * @param filters - Additional filters to apply with search
     * @returns Promise<Category[]> - Array of matching categories
     * 
     * Usage: Used in autocomplete search or quick search functionality
     */
    async searchCategories(query: string, filters?: CategoryFilters): Promise<Category[]> {
        const response = await this.api.get('', { 
            params: { 
                ...filters, 
                search: query 
            } 
        });
        return response.data.data;
    }

    // ========================================
    // WRITE OPERATIONS
    // ========================================

    /**
     * Create a new category
     * @param categoryData - Category data matching CreateCategoryRequest interface
     * @returns Promise<Category> - Created category with server-generated values
     * 
     * Usage: Called when user submits the create category form
     * Returns the complete created category including ID and timestamps
     */
    async createCategory(categoryData: CreateCategoryRequest): Promise<Category> {
        const response = await this.api.post('', categoryData);
        return response.data.data;
    }

    /**
     * Update an existing category
     * @param id - Category ID to update
     * @param categoryData - Partial category data to update
     * @returns Promise<Category> - Updated category with new values
     * 
     * Usage: Called when user submits the edit category form
     * Accepts partial data to allow updating specific fields only
     */
    async updateCategory(id: number, categoryData: UpdateCategoryRequest): Promise<Category> {
        const response = await this.api.put(`/${id}`, categoryData);
        return response.data.data;
    }

    /**
     * Delete a category
     * @param id - Category ID to delete
     * @returns Promise<void> - No data returned on successful deletion
     * 
     * Usage: Called when user confirms category deletion
     * Server will validate that category has no children or products before deletion
     */
    async deleteCategory(id: number): Promise<void> {
        await this.api.delete(`/${id}`);
    }

    // ========================================
    // SPECIALIZED OPERATIONS
    // ========================================

    /**
     * Update category hierarchy and sort order
     * @param categories - Array of category updates with new positions
     * @returns Promise<void> - No data returned on successful update
     * 
     * Usage: Called after drag-and-drop reordering in tree view
     * Allows moving categories between parents and updating sort orders
     * 
     * Example input:
     * [
     *   { id: 1, sort_order: 0, parent_id: null },
     *   { id: 2, sort_order: 1, parent_id: 1 },
     *   { id: 3, sort_order: 0, parent_id: 2 }
     * ]
     */
    async updateCategoryOrder(categories: CategoryOrderUpdate[]): Promise<void> {
        await this.api.put('/update-order', { categories });
    }
}
