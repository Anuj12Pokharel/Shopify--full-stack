import React from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { Icon } from '@shopify/polaris';
import { HomeIcon, ProductIcon } from '@shopify/polaris-icons';
import '../App.css'; // Make sure styles are imported

interface LayoutProps {
    children: React.ReactNode;
}

const Layout: React.FC<LayoutProps> = ({ children }) => {
    const location = useLocation();
    const navigate = useNavigate();

    const menuItems = [
        { path: '/', label: 'Dashboard', icon: HomeIcon },
        { path: '/products', label: 'Products', icon: ProductIcon },
    ];

    return (
        <div className="app-layout">
            <aside className="sidebar glass-panel">
                <div className="sidebar-header">
                    <h1 className="text-gradient">Shopify App</h1>
                </div>
                <nav className="sidebar-nav">
                    {menuItems.map((item) => {
                        const isActive = location.pathname === item.path;
                        return (
                            <button
                                key={item.path}
                                className={`nav-item ${isActive ? 'active' : ''}`}
                                onClick={() => navigate(item.path)}
                            >
                                <span className="icon">
                                    <Icon source={item.icon} tone={isActive ? 'base' : 'subdued'} />
                                </span>
                                <span className="label">{item.label}</span>
                            </button>
                        );
                    })}
                </nav>
            </aside>
            <main className="main-content animate-fade-in">
                <div className="content-wrapper">
                    {children}
                </div>
            </main>
        </div>
    );
}

export default Layout;
