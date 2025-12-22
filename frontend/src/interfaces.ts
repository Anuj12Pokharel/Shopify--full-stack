export interface Product {
    id: number;
    shopify_product_id: string;
    title: string;
    description: string;
    status: 'active' | 'draft' | 'archived';
    price: string;
    variants: any[];
    images: any[];
    created_at: string;
}

export interface Collection {
    id: number;
    title: string;
    products_count: number;
}

export interface Order {
    id: number;
    order_number: string;
    customer: any;
    total_price: string;
    financial_status: string;
    created_at: string;
}

export interface DashboardStats {
    total_products: number;
    total_collections: number;
    total_orders: number;
    last_sync_at: string | null;
    collections_with_products: number;
}
