import { Order, OrderItem, CreateOrderRequest, UpdateOrderRequest, UpdateOrderStatusRequest, OrderFilters, OrderStatus, OrderStatistics, OrderTotals, CreateOrderItemRequest, ApiResponse, PaginationData } from '../Types/Order';
import { OrderApiService } from './OrderApiService';
import { OrderValidationService } from './OrderValidationService';
import { OrderCalculations } from '../Utils/OrderCalculations';

export class OrderService {
    private apiService: OrderApiService;
    private validationService: OrderValidationService;
    private calculations: OrderCalculations;

    constructor() {
        this.apiService = new OrderApiService();
        this.validationService = new OrderValidationService();
        this.calculations = new OrderCalculations();
    }

    // Order management
    async createOrder(orderData: CreateOrderRequest): Promise<Order> {
        // Validate order data
        const validation = this.validationService.validateCreateOrder(orderData);
        if (!validation.isValid) {
            throw new Error(validation.errors.join(', '));
        }

        // Calculate totals
        const calculatedData = this.calculations.calculateOrderTotals(orderData.items);

        // Create order with calculated totals
        const orderWithTotals = {
            ...orderData,
            ...calculatedData
        };

        const response = await this.apiService.createOrder(orderWithTotals);
        if (!response.success || !response.data) {
            throw new Error(response.error || 'Failed to create order');
        }

        return response.data;
    }

    async updateOrder(id: number, orderData: UpdateOrderRequest): Promise<Order> {
        const response = await this.apiService.updateOrder(id, orderData);
        if (!response.success || !response.data) {
            throw new Error(response.error || 'Failed to update order');
        }

        return response.data;
    }

    async updateOrderStatus(id: number, status: OrderStatus, notes?: string): Promise<Order> {
        // Validate status transition
        const validation = this.validationService.validateStatusTransition(id, status);
        if (!validation.isValid) {
            throw new Error(validation.errors.join(', '));
        }

        const response = await this.apiService.updateOrderStatus(id, { status, notes });
        if (!response.success || !response.data) {
            throw new Error(response.error || 'Failed to update order status');
        }

        return response.data;
    }

    async cancelOrder(id: number, reason: string): Promise<Order> {
        return this.updateOrderStatus(id, 'cancelled', reason);
    }

    async deleteOrder(id: number): Promise<void> {
        const response = await this.apiService.deleteOrder(id);
        if (!response.success) {
            throw new Error(response.error || 'Failed to delete order');
        }
    }

    // Order retrieval
    async getOrders(filters?: OrderFilters): Promise<ApiResponse<Order[]>> {
        return await this.apiService.getOrders(filters);
    }

    async getOrder(id: number): Promise<Order> {
        const response = await this.apiService.getOrder(id);
        if (!response.success || !response.data) {
            throw new Error(response.error || 'Failed to fetch order');
        }

        return response.data;
    }

    async getOrderItems(orderId: number): Promise<OrderItem[]> {
        const response = await this.apiService.getOrderItems(orderId);
        if (!response.success || !response.data) {
            throw new Error(response.error || 'Failed to fetch order items');
        }

        return response.data;
    }

    async getOrderStatusHistory(orderId: number): Promise<any[]> {
        const response = await this.apiService.getOrderStatusHistory(orderId);
        if (!response.success || !response.data) {
            throw new Error(response.error || 'Failed to fetch order status history');
        }

        return response.data;
    }

    // Order calculations
    async calculateOrderTotals(items: CreateOrderItemRequest[]): Promise<OrderTotals> {
        const response = await this.apiService.calculateTotals(items);
        if (!response.success || !response.data) {
            throw new Error(response.error || 'Failed to calculate order totals');
        }

        return response.data;
    }

    async applyDiscount(orderId: number, discountAmount: number): Promise<Order> {
        const response = await this.apiService.applyDiscount(orderId, discountAmount);
        if (!response.success || !response.data) {
            throw new Error(response.error || 'Failed to apply discount');
        }

        return response.data;
    }

    // Order processing
    async processOrder(orderId: number): Promise<Order> {
        const response = await this.apiService.processOrder(orderId);
        if (!response.success || !response.data) {
            throw new Error(response.error || 'Failed to process order');
        }

        return response.data;
    }

    // Order filtering and search
    async searchOrders(query: string, filters?: OrderFilters): Promise<Order[]> {
        const searchFilters = { ...filters, search: query };
        const response = await this.getOrders(searchFilters);
        return response.data || [];
    }

    // Order statistics
    async getOrderStatistics(filters?: OrderFilters): Promise<OrderStatistics> {
        const response = await this.apiService.getOrderStatistics(filters);
        if (!response.success || !response.data) {
            throw new Error(response.error || 'Failed to fetch order statistics');
        }

        return response.data;
    }

    // Order status helpers
    getOrderStatusColor(status: OrderStatus): string {
        const colors = {
            pending: 'warning',
            confirmed: 'info',
            processing: 'primary',
            shipped: 'secondary',
            delivered: 'success',
            cancelled: 'danger',
            refunded: 'secondary'
        };
        return colors[status] || 'secondary';
    }

    getPaymentStatusColor(status: string): string {
        const colors: Record<string, string> = {
            pending: 'warning',
            paid: 'success',
            failed: 'danger',
            refunded: 'secondary'
        };
        return colors[status] || 'secondary';
    }

    // Order validation helpers
    canUpdateOrder(order: Order): boolean {
        return order.can_be_updated;
    }

    canCancelOrder(order: Order): boolean {
        return order.can_be_cancelled;
    }

    isOrderPaid(order: Order): boolean {
        return order.is_paid;
    }

    // Format helpers
    formatCurrency(amount: number): string {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    }

    formatDate(dateString: string): string {
        return new Date(dateString).toLocaleDateString();
    }

    formatDateTime(dateString: string): string {
        return new Date(dateString).toLocaleString();
    }

    // Order status transitions
    getAvailableStatusTransitions(currentStatus: OrderStatus): OrderStatus[] {
        const transitions: Record<OrderStatus, OrderStatus[]> = {
            pending: ['confirmed', 'cancelled'],
            confirmed: ['processing', 'cancelled'],
            processing: ['shipped', 'cancelled'],
            shipped: ['delivered'],
            delivered: ['refunded'],
            cancelled: [],
            refunded: [],
        };

        return transitions[currentStatus] || [];
    }
}
