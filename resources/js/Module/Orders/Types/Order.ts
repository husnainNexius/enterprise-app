export interface Order {
    id: number;
    order_number: string;
    customer_id: number;
    customer?: Customer;
    status: OrderStatus;
    total_amount: number;
    tax_amount: number;
    shipping_amount: number;
    discount_amount: number;
    payment_method: PaymentMethod;
    payment_status: PaymentStatus;
    shipping_address: Address;
    billing_address: Address;
    notes?: string;
    order_date: string;
    shipped_date?: string;
    delivered_date?: string;
    created_at: string;
    updated_at: string;
    
    // Computed fields
    total_with_tax: number;
    grand_total: number;
    can_be_updated: boolean;
    can_be_cancelled: boolean;
    is_paid: boolean;
    
    // Relationships
    items?: OrderItem[];
    status_history?: OrderStatusHistory[];
    payments?: OrderPayment[];
}

export interface OrderItem {
    id: number;
    order_id: number;
    product_id: number;
    product?: Product;
    quantity: number;
    unit_price: number;
    total_price: number;
    product_snapshot: ProductSnapshot;
    created_at: string;
    updated_at: string;
}

export interface OrderStatusHistory {
    id: number;
    order_id: number;
    from_status: string;
    to_status: string;
    notes?: string;
    changed_by?: User;
    created_at: string;
}

export interface OrderPayment {
    id: number;
    order_id: number;
    payment_method: string;
    amount: number;
    status: PaymentStatus;
    transaction_id?: string;
    created_at: string;
}

export interface CreateOrderRequest {
    customer_id: number;
    items: CreateOrderItemRequest[];
    payment_method: PaymentMethod;
    shipping_address: Address;
    billing_address?: Address;
    notes?: string;
}

export interface CreateOrderItemRequest {
    product_id: number;
    quantity: number;
}

export interface UpdateOrderRequest {
    notes?: string;
    shipping_address?: Address;
}

export interface UpdateOrderStatusRequest {
    status: OrderStatus;
    notes?: string;
}

export interface OrderFilters {
    status?: OrderStatus;
    customer_id?: number;
    date_from?: string;
    date_to?: string;
    payment_method?: PaymentMethod[];
    payment_status?: PaymentStatus;
    search?: string;
    per_page?: number;
    page?: number;
}

export interface OrderStatistics {
    total_orders: number;
    total_revenue: number;
    average_order_value: number;
    status_breakdown: OrderStatusBreakdown[];
}

export interface OrderStatusBreakdown {
    status: string;
    count: number;
    revenue: number;
}

export interface OrderTotals {
    subtotal: number;
    tax_amount: number;
    shipping_amount: number;
    total_amount: number;
}

// Enums
export type OrderStatus = 'pending' | 'confirmed' | 'processing' | 'shipped' | 'delivered' | 'cancelled' | 'refunded';
export type PaymentMethod = 'cash' | 'card' | 'paypal' | 'stripe';
export type PaymentStatus = 'pending' | 'paid' | 'failed' | 'refunded';

// Supporting types
export interface Customer {
    id: number;
    name: string;
    email: string;
}

export interface Product {
    id: number;
    name: string;
    description: string;
    price: number;
    category: string;
    status: string;
}

export interface ProductSnapshot {
    name: string;
    description: string;
    price: number;
    category: string;
}

export interface Address {
    name: string;
    email: string;
    phone: string;
    address: string;
    city: string;
    state: string;
    postal_code: string;
    country: string;
}

export interface User {
    id: number;
    name: string;
}

// API Response types
export interface ApiResponse<T> {
    success: boolean;
    data?: T;
    message?: string;
    pagination?: PaginationData;
    error?: string;
}

export interface PaginationData {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}
