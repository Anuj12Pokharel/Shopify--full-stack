import React, { useState, useEffect, useCallback } from 'react';
import {
    DataTable,
    Pagination,
    Spinner,
    Banner,
    Badge,
    Icon,
    TextField,
    Select
} from '@shopify/polaris';
import { SearchIcon } from '@shopify/polaris-icons';
import axios from 'axios';
import type { Product } from '../interfaces';

interface ProductsResponse {
    data: Product[];
    last_page: number;
    current_page: number;
    total: number;
}

const Products: React.FC = () => {
    const [products, setProducts] = useState<Product[]>([]);
    const [loading, setLoading] = useState<boolean>(true);
    const [search, setSearch] = useState<string>('');
    const [status, setStatus] = useState<string>('');
    const [currentPage, setCurrentPage] = useState<number>(1);
    const [totalPages, setTotalPages] = useState<number>(1);
    const [error, setError] = useState<string | null>(null);

    const fetchProducts = useCallback(async () => {
        try {
            setLoading(true);
            setError(null);

            const params: any = {
                page: currentPage,
                per_page: 10,
            };

            if (search) params.search = search;
            if (status) params.status = status;

            const response = await axios.get<ProductsResponse>('/api/products', { params });

            setProducts(response.data.data || []);
            setTotalPages(response.data.last_page || 1);
        } catch (err) {
            console.error('Error fetching products:', err);
            setError('Failed to load products');
        } finally {
            setLoading(false);
        }
    }, [currentPage, search, status]);

    useEffect(() => {
        fetchProducts();
    }, [fetchProducts]);

    const handleSearchChange = (value: string) => {
        setSearch(value);
        setCurrentPage(1); // Reset to first page on search
    };

    const handleStatusChange = (value: string) => {
        setStatus(value);
        setCurrentPage(1); // Reset to first page on filter change
    };

    const getStatusBadge = (productStatus: string) => {
        const toneMap: Record<string, 'success' | 'info' | 'warning'> = {
            active: 'success',
            draft: 'info',
            archived: 'warning',
        };
        const tone = toneMap[productStatus] || 'critical';

        return (
            <Badge tone={tone}>
                {productStatus.toUpperCase()}
            </Badge>
        );
    };

    const rows = products.map((product) => [
        product.title,
        (product as any).vendor || '-',
        (product as any).product_type || '-',
        getStatusBadge(product.status),
        new Date(product.created_at).toLocaleDateString(),
    ]);

    return (
        <div className="products-container animate-slide-up">
            <div className="page-header" style={{ marginBottom: '32px' }}>
                <h1 style={{ fontSize: '2rem', fontWeight: 700, margin: 0 }}>Products</h1>
                <p style={{ color: 'var(--text-muted)', marginTop: '4px' }}>Manage your product inventory</p>
            </div>

            {error && (
                <div style={{ marginBottom: '24px' }}>
                    <Banner tone="critical" onDismiss={() => setError(null)}>
                        <p>{error}</p>
                    </Banner>
                </div>
            )}

            <div className="glass-panel" style={{ padding: '24px', marginBottom: '24px' }}>
                <div style={{ display: 'flex', gap: '16px', flexWrap: 'wrap' }}>
                    <div style={{ flex: 1, minWidth: '200px' }}>
                        <TextField
                            label="Search"
                            labelHidden
                            value={search}
                            onChange={handleSearchChange}
                            placeholder="Search by title..."
                            clearButton
                            onClearButtonClick={() => handleSearchChange('')}
                            autoComplete="off"
                            prefix={<Icon source={SearchIcon} />}
                        />
                    </div>
                    <div style={{ width: '200px' }}>
                        <Select
                            label="Status"
                            labelHidden
                            value={status}
                            onChange={handleStatusChange}
                            options={[
                                { label: 'All Statuses', value: '' },
                                { label: 'Active', value: 'active' },
                                { label: 'Draft', value: 'draft' },
                                { label: 'Archived', value: 'archived' },
                            ]}
                        />
                    </div>
                </div>
            </div>

            <div className="glass-panel" style={{ padding: '0', overflow: 'hidden' }}>
                {loading ? (
                    <div style={{ textAlign: 'center', padding: '50px' }}>
                        <Spinner size="large" />
                    </div>
                ) : (
                    <>
                        <div style={{ overflowX: 'auto' }}>
                            <DataTable
                                columnContentTypes={['text', 'text', 'text', 'text', 'text']}
                                headings={['Title', 'Vendor', 'Type', 'Status', 'Created']}
                                rows={rows}
                            />
                        </div>

                        {totalPages > 1 && (
                            <div style={{ display: 'flex', justifyContent: 'center', padding: '24px', borderTop: '1px solid var(--border)' }}>
                                <Pagination
                                    hasPrevious={currentPage > 1}
                                    hasNext={currentPage < totalPages}
                                    onPrevious={() => setCurrentPage(currentPage - 1)}
                                    onNext={() => setCurrentPage(currentPage + 1)}
                                />
                            </div>
                        )}

                        {products.length === 0 && !loading && (
                            <div style={{ textAlign: 'center', padding: '48px', color: 'var(--text-muted)' }}>
                                <p>No products found.</p>
                            </div>
                        )}
                    </>
                )}
            </div>
        </div>
    );
}

export default Products;
