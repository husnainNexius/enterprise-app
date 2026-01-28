<template>
    <div class="statistics-overlay" v-if="show" @click="closeModal">
        <div class="statistics-container" @click.stop>
            <div class="statistics-header">
                <h2>📊 Order Statistics</h2>
                <button @click="closeModal" class="btn-close">×</button>
            </div>

            <div class="statistics-content" v-if="statistics">
                <!-- Summary Cards -->
                <div class="summary-cards">
                    <div class="stat-card">
                        <div class="stat-icon">📦</div>
                        <div class="stat-info">
                            <div class="stat-value">{{ statistics.total_orders }}</div>
                            <div class="stat-label">Total Orders</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">💰</div>
                        <div class="stat-info">
                            <div class="stat-value">{{ formatCurrency(statistics.total_revenue) }}</div>
                            <div class="stat-label">Total Revenue</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">📈</div>
                        <div class="stat-info">
                            <div class="stat-value">{{ formatCurrency(statistics.average_order_value) }}</div>
                            <div class="stat-label">Average Order Value</div>
                        </div>
                    </div>
                </div>

                <!-- Status Breakdown -->
                <div class="status-breakdown">
                    <h3>Status Breakdown</h3>
                    <div class="status-grid">
                        <div 
                            v-for="status in statistics.status_breakdown" 
                            :key="status.status"
                            class="status-item"
                        >
                            <div class="status-header">
                                <span class="status-name">{{ status.status }}</span>
                                <span class="status-count">{{ status.count }}</span>
                            </div>
                            <div class="status-revenue">
                                {{ formatCurrency(status.revenue) }}
                            </div>
                            <div class="status-bar">
                                <div 
                                    class="status-fill" 
                                    :style="{ width: getStatusPercentage(status.count) + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="statistics-footer">
                <button @click="closeModal" class="btn btn-secondary">
                    Close
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';

interface StatusBreakdown {
    status: string;
    count: number;
    revenue: string;
}

interface Statistics {
    total_orders: number;
    total_revenue: string;
    average_order_value: number;
    status_breakdown: StatusBreakdown[];
}

interface Props {
    show: boolean;
    statistics: Statistics | null;
    onClose: () => void;
}

interface Emits {
    (e: 'close'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const closeModal = () => {
    props.onClose();
};

const formatCurrency = (amount: string | number): string => {
    const num = typeof amount === 'string' ? parseFloat(amount) : amount;
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(num);
};

const getStatusPercentage = (count: number): number => {
    if (!props.statistics || props.statistics.total_orders === 0) return 0;
    return Math.round((count / props.statistics.total_orders) * 100);
};
</script>

<style scoped>
.statistics-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.statistics-container {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.statistics-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 32px;
    border-bottom: 1px solid #e5e7eb;
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    border-radius: 12px 12px 0 0;
}

.statistics-header h2 {
    margin: 0;
    color: #1a1a1a;
    font-size: 24px;
    font-weight: 600;
}

.btn-close {
    background: #f8f9fa;
    border: 1px solid #e8eaed;
    font-size: 20px;
    cursor: pointer;
    color: #6c757d;
    padding: 0;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.btn-close:hover {
    color: #495057;
    background: #e9ecef;
    transform: scale(1.05);
}

.statistics-content {
    padding: 32px;
}

.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}

.stat-card {
    display: flex;
    align-items: center;
    padding: 24px;
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    border: 1px solid #e8eaed;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    font-size: 32px;
    margin-right: 16px;
}

.stat-info {
    flex: 1;
}

.stat-value {
    font-size: 28px;
    font-weight: 700;
    color: #1a1a1a;
    line-height: 1.2;
}

.stat-label {
    font-size: 14px;
    color: #6c757d;
    font-weight: 500;
    margin-top: 4px;
}

.status-breakdown h3 {
    margin: 0 0 20px 0;
    color: #1a1a1a;
    font-size: 20px;
    font-weight: 600;
    border-bottom: 2px solid #e8eaed;
    padding-bottom: 8px;
}

.status-grid {
    display: grid;
    gap: 16px;
}

.status-item {
    padding: 16px;
    background: #f8f9fa;
    border: 1px solid #e8eaed;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.status-item:hover {
    background: #e9ecef;
    border-color: #d1d5db;
}

.status-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.status-name {
    font-weight: 600;
    color: #374151;
    text-transform: capitalize;
}

.status-count {
    background: #3b82f6;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.status-revenue {
    font-size: 18px;
    font-weight: 600;
    color: #059669;
    margin-bottom: 8px;
}

.status-bar {
    height: 8px;
    background: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
}

.status-fill {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #2563eb);
    border-radius: 4px;
    transition: width 0.3s ease;
}

.statistics-footer {
    padding: 16px 32px;
    border-top: 1px solid #e5e7eb;
    background: #f8f9fa;
    border-radius: 0 0 12px 12px;
    display: flex;
    justify-content: flex-end;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-secondary {
    background: linear-gradient(135deg, #6b7280, #4b5563);
    color: white;
    box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #4b5563, #374151);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(107, 114, 128, 0.4);
}

@media (max-width: 768px) {
    .statistics-container {
        width: 95%;
        margin: 20px;
    }
    
    .summary-cards {
        grid-template-columns: 1fr;
    }
    
    .statistics-header,
    .statistics-content,
    .statistics-footer {
        padding: 20px;
    }
    
    .stat-value {
        font-size: 24px;
    }
}
</style>
