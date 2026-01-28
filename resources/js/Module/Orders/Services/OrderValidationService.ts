import { CreateOrderRequest, OrderStatus } from '../Types/Order';

export class OrderValidationService {
    validateCreateOrder(orderData: CreateOrderRequest): ValidationResult {
        const errors: string[] = [];

        // Validate customer
        if (!orderData.customer_id || orderData.customer_id <= 0) {
            errors.push('Valid customer is required');
        }

        // Validate items
        if (!orderData.items || orderData.items.length === 0) {
            errors.push('At least one item is required');
        } else {
            orderData.items.forEach((item, index) => {
                if (!item.product_id || item.product_id <= 0) {
                    errors.push(`Product ID is required for item ${index + 1}`);
                }
                if (!item.quantity || item.quantity <= 0) {
                    errors.push(`Valid quantity is required for item ${index + 1}`);
                }
            });
        }

        // Validate payment method
        if (!orderData.payment_method) {
            errors.push('Payment method is required');
        }

        // Validate shipping address
        if (!orderData.shipping_address) {
            errors.push('Shipping address is required');
        } else {
            const address = orderData.shipping_address;
            if (!address.name || address.name.trim() === '') {
                errors.push('Shipping name is required');
            }
            if (!address.email || !this.isValidEmail(address.email)) {
                errors.push('Valid shipping email is required');
            }
            if (!address.phone || address.phone.trim() === '') {
                errors.push('Shipping phone is required');
            }
            if (!address.address || address.address.trim() === '') {
                errors.push('Shipping address is required');
            }
            if (!address.city || address.city.trim() === '') {
                errors.push('Shipping city is required');
            }
            if (!address.state || address.state.trim() === '') {
                errors.push('Shipping state is required');
            }
            if (!address.postal_code || address.postal_code.trim() === '') {
                errors.push('Shipping postal code is required');
            }
            if (!address.country || address.country.trim() === '') {
                errors.push('Shipping country is required');
            }
        }

        return {
            isValid: errors.length === 0,
            errors
        };
    }

    validateStatusTransition(orderId: number, newStatus: OrderStatus): ValidationResult {
        // In a real implementation, you would fetch the current order status
        // For now, we'll assume basic validation
        const errors: string[] = [];

        if (!newStatus) {
            errors.push('Status is required');
        }

        // Basic status validation
        const validStatuses: OrderStatus[] = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
        if (!validStatuses.includes(newStatus)) {
            errors.push('Invalid status value');
        }

        return {
            isValid: errors.length === 0,
            errors
        };
    }

    validateUpdateOrder(orderData: any): ValidationResult {
        const errors: string[] = [];

        // Validate shipping address if provided
        if (orderData.shipping_address) {
            const address = orderData.shipping_address;
            if (!address.name || address.name.trim() === '') {
                errors.push('Shipping name is required');
            }
            if (!address.email || !this.isValidEmail(address.email)) {
                errors.push('Valid shipping email is required');
            }
            if (!address.phone || address.phone.trim() === '') {
                errors.push('Shipping phone is required');
            }
            if (!address.address || address.address.trim() === '') {
                errors.push('Shipping address is required');
            }
            if (!address.city || address.city.trim() === '') {
                errors.push('Shipping city is required');
            }
            if (!address.state || address.state.trim() === '') {
                errors.push('Shipping state is required');
            }
            if (!address.postal_code || address.postal_code.trim() === '') {
                errors.push('Shipping postal code is required');
            }
            if (!address.country || address.country.trim() === '') {
                errors.push('Shipping country is required');
            }
        }

        return {
            isValid: errors.length === 0,
            errors
        };
    }

    private isValidEmail(email: string): boolean {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
}

export interface ValidationResult {
    isValid: boolean;
    errors: string[];
}
