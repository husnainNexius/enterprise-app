import { CreateOrderRequest, OrderTotals, CreateOrderItemRequest } from '../Types/Order';

export class OrderCalculations {
    calculateOrderTotals(items: CreateOrderItemRequest[]): OrderTotals {
        let subtotal = 0;
        let taxAmount = 0;
        let shippingAmount = 0;

        // Calculate subtotal
        items.forEach(item => {
            subtotal += item.quantity * 100; // Assuming $100 per item for now
        });

        // Calculate tax (10% for example)
        taxAmount = subtotal * 0.1;
        
        // Calculate shipping (flat rate for example)
        shippingAmount = subtotal > 100 ? 0 : 10;

        const totalAmount = subtotal + taxAmount + shippingAmount;

        return {
            subtotal,
            tax_amount: taxAmount,
            shipping_amount: shippingAmount,
            total_amount: totalAmount,
        };
    }

    calculateGrandTotal(orderTotals: OrderTotals): number {
        return orderTotals.total_amount + orderTotals.tax_amount + orderTotals.shipping_amount;
    }

    calculateDiscountAmount(originalTotal: number, discountPercentage: number): number {
        return originalTotal * (discountPercentage / 100);
    }

    calculateFinalPrice(originalPrice: number, discountAmount: number): number {
        return Math.max(0, originalPrice - discountAmount);
    }

    formatCurrency(amount: number): string {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    }

    roundToTwoDecimals(value: number): number {
        return Math.round(value * 100) / 100;
    }
}
