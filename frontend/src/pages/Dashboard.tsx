import React, { useState, useEffect } from 'react';
import {
    Spinner,
    Banner,
    Icon,
    Button,
} from '@shopify/polaris';
import {
    ProductIcon,
    CollectionIcon,
    ClockIcon,
    RefreshIcon
} from '@shopify/polaris-icons';
import axios from 'axios';
import type { DashboardStats } from '../interfaces';

const Dashboard: React.FC = () => {
    const [stats, setStats] = useState<DashboardStats | null>(null);
    const [loading, setLoading] = useState<boolean>(true);
    const [syncing, setSyncing] = useState<boolean>(false);
    const [message, setMessage] = useState<{ type: 'success' | 'warning' | 'critical', content: string } | null>(null);

    useEffect(() => {
        fetchStats();
    }, []);

    const fetchStats = async () => {
        try {
            setLoading(true);
            const response = await axios.get<DashboardStats>('/api/dashboard/stats');
            setStats(response.data);
        } catch (error) {
            console.error('Error fetching stats:', error);
            setMessage({ type: 'critical', content: 'Failed to load dashboard stats' });
        } finally {
            setLoading(false);
        }
    };

    const handleSync = async () => {
        try {
            setSyncing(true);
            setMessage(null);
            const response = await axios.post('/api/sync/products');

            if (response.data.success) {
                setMessage({ type: 'success', content: response.data.message });
                await fetchStats();
            } else {
                setMessage({ type: 'warning', content: response.data.message });
            }
        } catch (error) {
            console.error('Sync error:', error);
            setMessage({ type: 'critical', content: 'Failed to sync products' });
        } finally {
            setSyncing(false);
        }
    };

    if (loading) {
        return (
            <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100%', minHeight: '400px' }}>
                <Spinner size="large" />
            </div>
        );
    }

    return (
        <div className="dashboard-container animate-slide-up">
            <div className="page-header" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '32px' }}>
                <div>
                    <h1 style={{ fontSize: '2rem', fontWeight: 700, margin: 0 }}>Dashboard</h1>
                    <p style={{ color: 'var(--text-muted)', marginTop: '4px' }}>Overview of your store's performance</p>
                </div>
                <Button variant="primary" onClick={handleSync} loading={syncing} icon={RefreshIcon}>
                    Sync Products
                </Button>
            </div>

            {message && (
                <div style={{ marginBottom: '24px' }}>
                    <Banner tone={message.type} onDismiss={() => setMessage(null)}>
                        <p>{message.content}</p>
                    </Banner>
                </div>
            )}

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))', gap: '24px' }}>
                {/* Products Card */}
                <div className="glass-panel" style={{ padding: '24px', display: 'flex', flexDirection: 'column', gap: '16px' }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'start' }}>
                        <div style={{ padding: '12px', borderRadius: '12px', background: 'rgba(99, 102, 241, 0.1)', color: 'var(--primary)' }}>
                            <Icon source={ProductIcon} tone="inherit" />
                        </div>
                        <span style={{ fontSize: '0.875rem', color: 'var(--success)', fontWeight: 500 }}>+12%</span>
                    </div>
                    <div>
                        <h3 style={{ color: 'var(--text-muted)', fontSize: '0.875rem', fontWeight: 500, margin: 0 }}>Total Products</h3>
                        <p style={{ fontSize: '2rem', fontWeight: 700, margin: '4px 0 0 0', color: 'var(--text-main)' }}>
                            {stats?.total_products || 0}
                        </p>
                    </div>
                </div>

                {/* Collections Card */}
                <div className="glass-panel" style={{ padding: '24px', display: 'flex', flexDirection: 'column', gap: '16px' }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'start' }}>
                        <div style={{ padding: '12px', borderRadius: '12px', background: 'rgba(236, 72, 153, 0.1)', color: 'var(--secondary)' }}>
                            <Icon source={CollectionIcon} tone="inherit" />
                        </div>
                    </div>
                    <div>
                        <h3 style={{ color: 'var(--text-muted)', fontSize: '0.875rem', fontWeight: 500, margin: 0 }}>Collections</h3>
                        <p style={{ fontSize: '2rem', fontWeight: 700, margin: '4px 0 0 0', color: 'var(--text-main)' }}>
                            {stats?.total_collections || 0}
                        </p>
                        <p style={{ fontSize: '0.875rem', color: 'var(--text-muted)', marginTop: '4px' }}>
                            {stats?.collections_with_products || 0} with products
                        </p>
                    </div>
                </div>

                {/* Last Sync Card */}
                <div className="glass-panel" style={{ padding: '24px', display: 'flex', flexDirection: 'column', gap: '16px' }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'start' }}>
                        <div style={{ padding: '12px', borderRadius: '12px', background: 'rgba(16, 185, 129, 0.1)', color: 'var(--success)' }}>
                            <Icon source={ClockIcon} tone="inherit" />
                        </div>
                    </div>
                    <div>
                        <h3 style={{ color: 'var(--text-muted)', fontSize: '0.875rem', fontWeight: 500, margin: 0 }}>Last Sync</h3>
                        <p style={{ fontSize: '1.25rem', fontWeight: 600, margin: '8px 0 0 0', color: 'var(--text-main)' }}>
                            {stats?.last_sync_at
                                ? new Date(stats.last_sync_at).toLocaleDateString()
                                : 'Never'}
                        </p>
                        <p style={{ fontSize: '0.875rem', color: 'var(--text-muted)' }}>
                            {stats?.last_sync_at
                                ? new Date(stats.last_sync_at).toLocaleTimeString()
                                : ''}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default Dashboard;
