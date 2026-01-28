import { ref, reactive } from 'vue';
import { OrderService } from '../Services/OrderService';
import type { Order, OrderFilters, CreateOrderRequest, UpdateOrderRequest, OrderStatus, ApiResponse } from '../Types/Order';

export function useOrders() {
    // State
    const orders = ref<Order[]>([]);
    const loading = ref(false);
    const pagination = reactive({
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
        from: 0,
        to: 0
    });
    const filters = ref<OrderFilters>({});

    // Services
    const orderService = new OrderService();

    // Methods
    const fetchOrders = async (page?: number) => {
        loading.value = true;
        try {
            const response = await orderService.getOrders({
                ...filters.value,
                page: page || pagination.current_page
            });


            if (response.success && response.data) {
                orders.value = response.data;
                
                if (response.pagination) {
                    Object.assign(pagination, response.pagination);
                }
            } else {
                console.error('API response unsuccessful:', response);
            }
        } catch (error) {
            console.error('Failed to fetch orders:', error);
        } finally {
            loading.value = false;
        }
    };

    const createOrder = async (orderData: CreateOrderRequest): Promise<Order> => {
        return await orderService.createOrder(orderData);
    };

    const updateOrder = async (id: number, orderData: UpdateOrderRequest): Promise<Order> => {
        return await orderService.updateOrder(id, orderData);
    };

    const deleteOrder = async (id: number): Promise<void> => {
        return await orderService.deleteOrder(id);
    };

    const updateOrderStatus = async (id: number, status: OrderStatus, notes?: string): Promise<Order> => {
        return await orderService.updateOrderStatus(id, status, notes);
    };

    const searchOrders = async (query: string): Promise<Order[]> => {
        return await orderService.searchOrders(query, filters.value);
    };

    const getOrderStatistics = async () => {
        return await orderService.getOrderStatistics(filters.value);
    };

    const processOrder = async (id: number) => {
        return await orderService.processOrder(id);
    };

    const applyDiscount = async (id: number, discountAmount: number) => {
        return await orderService.applyDiscount(id, discountAmount);
    };

    return {
        // State
        orders,
        loading,
        pagination,
        filters,
        
        // Methods
        fetchOrders,
        createOrder,
        updateOrder,
        deleteOrder,
        updateOrderStatus,
        searchOrders,
        getOrderStatistics,
        processOrder,
        applyDiscount
    };
}
