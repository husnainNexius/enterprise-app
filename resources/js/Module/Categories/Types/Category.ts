// ========================================
// CATEGORY MODULE - TYPE DEFINITIONS
// ========================================
// This file contains all TypeScript interfaces and types for the Category module
// These interfaces provide type safety and autocompletion for the entire module

// ========================================
// CORE CATEGORY INTERFACES
// ========================================

/**
 * Main Category interface - represents a single category entity
 * This interface defines the structure of category data throughout the application
 */
export interface Category {
    // Primary identifier from the database
    id: number;
    
    // Basic category information
    name: string;
    slug: string;                    // URL-friendly version of the name
    description: string | null;      // Optional detailed description
    
    // Hierarchy and organization
    parent_id: number | null;        // Reference to parent category (null = root level)
    sort_order: number;              // Display order within siblings
    is_active: boolean;              // Whether category is visible/usable
    
    // SEO metadata for search engines
    meta_title: string | null;       // SEO title tag
    meta_description: string | null; // SEO meta description
    
    // Timestamps from Laravel
    created_at: string;
    updated_at: string;
    
    // ========================================
    // RELATIONSHIP FIELDS (loaded conditionally)
    // ========================================
    
    // Parent category relationship (loaded when needed)
    parent?: Category | null;
    
    // Child categories array (loaded when needed)
    children?: Category[];
    
    // Count of products in this category (loaded when needed)
    products_count?: number;
    
    // Count of direct child categories (loaded when needed)
    children_count?: number;
    
    // Computed full path (e.g., "Electronics > Smartphones > iPhones")
    full_path?: string;
    
    // Boolean flag indicating if category has children
    has_children?: boolean;
}

// ========================================
// FILTER AND QUERY INTERFACES
// ========================================

/**
 * CategoryFilters interface - defines available filter options
 * Used for searching and filtering categories in the UI and API calls
 */
export interface CategoryFilters {
    search?: string;                 // Text search across name/description
    is_active?: boolean | null;      // Filter by active/inactive status
    parent_id?: number | null;       // Filter by parent category
    page?: number;                   // Pagination page number
}

// ========================================
// REQUEST/RESPONSE INTERFACES
// ========================================

/**
 * CreateCategoryRequest interface - data structure for creating new categories
 * All fields except description and SEO fields are required for creation
 */
export interface CreateCategoryRequest {
    name: string;                    // Required: Category display name
    slug: string;                    // Required: URL-friendly identifier
    description?: string;            // Optional: Detailed description
    parent_id?: number | null;       // Optional: Parent category ID
    sort_order: number;              // Required: Display order
    is_active: boolean;              // Required: Active status
    meta_title?: string;             // Optional: SEO title
    meta_description?: string;       // Optional: SEO description
}

/**
 * UpdateCategoryRequest interface - data structure for updating existing categories
 * Extends CreateCategoryRequest but makes all fields optional for partial updates
 */
export interface UpdateCategoryRequest extends Partial<CreateCategoryRequest> {}

/**
 * CategoryOrderUpdate interface - used for drag-and-drop reordering
 * Defines the structure for updating category hierarchy and sort order
 */
export interface CategoryOrderUpdate {
    id: number;                      // Category ID to update
    sort_order: number;              // New sort position
    parent_id?: number | null;       // New parent category (for moving between trees)
}

// ========================================
// API RESPONSE INTERFACES
// ========================================

/**
 * Generic API Response wrapper - standard response format for all API calls
 * Provides consistent structure for success/error handling
 */
export interface ApiResponse<T> {
    success: boolean;                // Whether the operation succeeded
    data: T;                         // The response payload (generic type)
    message?: string;                // Optional success/error message
}

/**
 * Paginated Response interface - extends ApiResponse for paginated data
 * Used for list views that require pagination controls
 */
export interface PaginatedResponse<T> extends ApiResponse<T[]> {
    pagination: {
        current_page: number;         // Current page number
        last_page: number;            // Total number of pages
        per_page: number;             // Items per page
        total: number;                // Total number of items
        from: number;                 // First item number on current page
        to: number;                   // Last item number on current page
    };
}
