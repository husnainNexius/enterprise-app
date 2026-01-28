import { Order, OrderItem, CreateOrderRequest, UpdateOrderRequest, UpdateOrderStatusRequest, OrderFilters, ApiResponse, OrderStatistics, OrderTotals, PaginationData, CreateOrderItemRequest } from '../Types/Order';

export class OrderApiService {
    private baseUrl: string;

    constructor() {
        this.baseUrl = '/api/orders';
    }

    private async request<T>(url: string, options: RequestInit = {}): Promise<ApiResponse<T>> {
        try {
            const response = await fetch(`${this.baseUrl}${url}`, {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    ...options.headers,
                },
                ...options,
            });

            const data = await response.json();

            if (!response.ok) {
                return {
                    success: false,
                    error: data.error || data.message || 'Request failed',
                    message: data.message || 'Request failed'
                };
            }

            return {
                success: true,
                data: data.data || data,
                pagination: data.pagination || data.meta,
                message: data.message
            };
        } catch (error) {
            return {
                success: false,
                error: error instanceof Error ? error.message : 'Network error',
                message: 'Network error occurred'
            };
        }
    }

    // Order CRUD operations
    async getOrders(filters?: OrderFilters): Promise<ApiResponse<Order[]>> {
        const params = new URLSearchParams();
        
        if (filters) {
            Object.entries(filters).forEach(([key, value]) => {
                if (value === undefined || value === null || value === '') {
                    return;
                }
                if (Array.isArray(value)) {
                    value
                        .filter(v => v !== undefined && v !== null && v !== '')
                        .forEach(v => params.append(`${key}[]`, v.toString()));
                    return;
                }
                params.append(key, value.toString());
            });
        }

        const queryString = params.toString();
        const url = queryString ? `?${queryString}` : '';
        
        return this.request<Order[]>(url);
    }

    async getOrder(id: number): Promise<ApiResponse<Order>> {
        return this.request<Order>(`/${id}`);
    }

    async createOrder(orderData: CreateOrderRequest): Promise<ApiResponse<Order>> {
        return this.request<Order>('', {
            method: 'POST',
            body: JSON.stringify(orderData),
        });
    }

    async updateOrder(id: number, orderData: UpdateOrderRequest): Promise<ApiResponse<Order>> {
        return this.request<Order>(`/${id}`, {
            method: 'PUT',
            body: JSON.stringify(orderData),
        });
    }

    async deleteOrder(id: number): Promise<ApiResponse<void>> {
        return this.request<void>(`/${id}`, {
            method: 'DELETE',
        });
    }

    // Order items operations
    async getOrderItems(orderId: number): Promise<ApiResponse<OrderItem[]>> {
        return this.request<OrderItem[]>(`/${orderId}/items`);
    }

    async addOrderItem(orderId: number, itemData: CreateOrderItemRequest): Promise<ApiResponse<OrderItem>> {
        return this.request<OrderItem>(`/${orderId}/items`, {
            method: 'POST',
            body: JSON.stringify(itemData),
        });
    }

    async updateOrderItem(orderId: number, itemId: number, itemData: Partial<OrderItem>): Promise<ApiResponse<OrderItem>> {
        return this.request<OrderItem>(`/${orderId}/items/${itemId}`, {
            method: 'PUT',
            body: JSON.stringify(itemData),
        });
    }

    async removeOrderItem(orderId: number, itemId: number): Promise<ApiResponse<void>> {
        return this.request<void>(`/${orderId}/items/${itemId}`, {
            method: 'DELETE',
        });
    }

    // Order status operations
    async updateOrderStatus(orderId: number, statusData: UpdateOrderStatusRequest): Promise<ApiResponse<Order>> {
        return this.request<Order>(`/${orderId}/status`, {
            method: 'PUT',
            body: JSON.stringify(statusData),
        });
    }

    async getOrderStatusHistory(orderId: number): Promise<ApiResponse<any[]>> {
        return this.request<any[]>(`/${orderId}/history`);
    }

    // Order calculations
    async calculateTotals(items: CreateOrderItemRequest[]): Promise<ApiResponse<OrderTotals>> {
        return this.request<OrderTotals>('/calculate-totals', {
            method: 'POST',
            body: JSON.stringify({ items }),
        });
    }

    async applyDiscount(orderId: number, discountAmount: number): Promise<ApiResponse<Order>> {
        return this.request<Order>(`/${orderId}/apply-discount`, {
            method: 'PUT',
            body: JSON.stringify({ discount_amount: discountAmount }),
        });
    }

    // Order processing
    async processOrder(orderId: number): Promise<ApiResponse<Order>> {
        return this.request<Order>(`/${orderId}/process`, {
            method: 'POST',
        });
    }

    // Statistics and reports
    async getOrderStatistics(filters?: OrderFilters): Promise<ApiResponse<OrderStatistics>> {
        const params = new URLSearchParams();
        
        if (filters) {
            Object.entries(filters).forEach(([key, value]) => {
                if (value === undefined || value === null || value === '') {
                    return;
                }
                if (Array.isArray(value)) {
                    value
                        .filter(v => v !== undefined && v !== null && v !== '')
                        .forEach(v => params.append(`${key}[]`, v.toString()));
                    return;
                }
                params.append(key, value.toString());
            });
        }

        const queryString = params.toString();
        const url = queryString ? `/statistics?${queryString}` : '/statistics';
        
        return this.request<OrderStatistics>(url);
    }
}
