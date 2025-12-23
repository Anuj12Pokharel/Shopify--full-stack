# Shopify Embedded App - Technical Assignment

A full-stack Shopify embedded application that connects to merchant stores, fetches products, collections, and orders using Shopify Admin API (GraphQL), stores them in a local MySQL database, and displays them in a beautiful Polaris-powered dashboard.

## Tech Stack

- **Backend**: Laravel 10 (PHP 8.2)
- **Frontend**: React 18 + Shopify Polaris
- **Database**: MySQL 8.0
- **Integration**: Shopify Admin API (GraphQL 2024-01)
- **Dev Tools**: Vite, Docker, Composer, NPM

##  Features

### Core Features
- ✅ **OAuth 2.0 Authentication** - Secure Shopify app installation
- ✅ **Product Sync** - Manual sync with pagination support
- ✅ **Dashboard** - Summary cards showing total products, collections, and last sync time
- ✅ **Products Page** - List products with search, filter by status, and pagination (10 per page)
- ✅ **GraphQL Integration** - All data fetching via Shopify Admin API

### Bonus Features
- ✅ **Webhooks** - Auto-update on product create/update/delete
- ✅ **Collections Sync** - Fetch and store collections
- ✅ **Orders Sync** - Fetch and store orders

## Prerequisites

Choose **Option A** (Manual) OR **Option B** (Docker):

### Option A: Manual Installation
- PHP 8.1 or higher
- Composer
- MySQL 8.0
- Node.js 18+ and npm
- Shopify Partner Account

### Option B: Docker Installation
- Docker Desktop for Windows
- Docker Compose
- Shopify Partner Account

## 🔧 Setup Instructions

### 1. Clone the Repository

```bash
git clone <repository-url>
cd shopify-app
```

### 2. Shopify App Configuration

1. Go to [Shopify Partners Dashboard](https://partners.shopify.com/)
2. Create a new app
3. Configure App URLs:
   - **App URL**: `http://localhost:8000`
   - **Allowed redirection URL(s)**: `http://localhost:8000/auth/callback`
4. Note your **API Key** and **API Secret**

### 3A. Manual Setup

#### Backend Setup

```bash
cd backend

# Install dependencies
composer install

# Copy environment file
copy .env.example .env

# Generate app key
php artisan key:generate

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shopify_app
DB_USERNAME=root
DB_PASSWORD=your_password

# Add Shopify credentials in .env
SHOPIFY_API_KEY=your_api_key_here
SHOPIFY_API_SECRET=your_api_secret_here
SHOPIFY_API_VERSION=2024-01
SHOPIFY_SCOPES=read_products,write_products,read_orders,read_customers

# Run migrations
php artisan migrate

# Start Laravel server
php artisan serve
```

#### Frontend Setup

```bash
cd frontend

# Install dependencies
npm install

# Start development server
npm run dev
```

### 3B. Docker Setup

```bash
# Build and start containers
docker-compose up -d

# Install Laravel dependencies
docker-compose exec app composer install

# Copy environment file
docker-compose exec app cp .env.example .env

# Generate app key
docker-compose exec app php artisan key:generate

# Edit .env file with Shopify credentials
# DB_HOST should be 'mysql' (container name)

# Run migrations
docker-compose exec app php artisan migrate

# Install frontend dependencies
docker-compose exec frontend npm install
```

### 4. Environment Configuration

Create/edit `.env` in backend directory:

```env
APP_NAME="Shopify Embedded App"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1  # Use 'mysql' for Docker
DB_PORT=3306
DB_DATABASE=shopify_app
DB_USERNAME=root
DB_PASSWORD=

# Shopify API Credentials
SHOPIFY_API_KEY=your_api_key_here
SHOPIFY_API_SECRET=your_api_secret_here
SHOPIFY_API_VERSION=2024-01
SHOPIFY_SCOPES=read_products,write_products,read_orders,read_customers

FRONTEND_URL=http://localhost:3000
BACKEND_URL=http://localhost:8000
```

##  Shopify API Scopes Used

- `read_products` - Read product data
- `write_products` - Required for webhook registration
- `read_orders` - Read order data
- `read_customers` - Read customer data in orders

## Usage

### Installing the App

1. Start both backend and frontend servers
2. Navigate to: `http://localhost:8000/install?shop=your-store.myshopify.com`
3. Authorize the app in Shopify
4. You'll be redirected to the dashboard

### Syncing Data

1. Click **"Sync Products"** button on the dashboard
2. Wait for the sync to complete
3. Navigate to **Products** page to see synced products
4. Use search and filters to find specific products

### Setting Up Webhooks (Optional)

After installation, webhooks can be automatically registered for:
- `products/create` → `http://your-domain/webhooks/products/create`
- `products/update` → `http://your-domain/webhooks/products/update`
- `products/delete` → `http://your-domain/webhooks/products/delete`

Note: For local development, use tools like ngrok to expose your localhost.

##  Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     Shopify Store                           │
│                   (Merchant's Shop)                         │
└────────────┬────────────────────────────┬───────────────────┘
             │                            │
             │ OAuth Flow                 │ Webhooks
             │                            │
             ▼                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    Laravel Backend                          │
│  ┌──────────────┐   ┌──────────────┐   ┌──────────────┐   │
│  │    Auth      │   │    Sync      │   │   Webhook    │   │
│  │  Controller  │   │  Controller  │   │  Controller  │   │
│  └──────┬───────┘   └──────┬───────┘   └──────┬───────┘   │
│         │                  │                   │            │
│         ▼                  ▼                   ▼            │
│  ┌────────────────────────────────────────────────────┐    │
│  │              Shopify Service                       │    │
│  │           (GraphQL API Client)                     │    │
│  └─────────────────────┬──────────────────────────────┘    │
│                        │                                    │
│                        ▼                                    │
│  ┌─────────────────────────────────────────────────────┐   │
│  │         MySQL Database                              │   │
│  │  ┌──────┐ ┌──────────┐ ┌────────────┐ ┌─────────┐  │   │
│  │  │Shops │ │ Products │ │Collections │ │ Orders  │  │   │
│  │  └──────┘ └──────────┘ └────────────┘ └─────────┘  │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │              API Endpoints                          │   │
│  │  GET  /api/dashboard/stats                         │   │
│  │  GET  /api/products (search, filter, paginate)     │   │
│  │  POST /api/sync/products                           │   │
│  └─────────────────────────────────────────────────────┘   │
└────────────┬────────────────────────────────────────────────┘
             │
             │ REST API
             │
             ▼
┌─────────────────────────────────────────────────────────────┐
│                   React Frontend                            │
│  ┌─────────────────────────────────────────────────────┐   │
│  │           Shopify Polaris Components                │   │
│  └─────────────────────────────────────────────────────┘   │
│  ┌──────────────┐              ┌──────────────┐           │
│  │  Dashboard   │              │   Products   │           │
│  │              │              │              │           │
│  │ - Stats Cards│              │ - DataTable  │           │
│  │ - Sync Button│              │ - Search     │           │
│  │              │              │ - Filters    │           │
│  │              │              │ - Pagination │           │
│  └──────────────┘              └──────────────┘           │
└─────────────────────────────────────────────────────────────┘
```

### Data Flow

1. **Installation**: Merchant installs app → OAuth flow → Store access token
2. **Sync**: User clicks sync → Laravel fetches from Shopify GraphQL API → Store in MySQL
3. **Display**: React fetches from Laravel API → Display in Polaris table
4. **Webhooks**: Shopify sends webhook → Laravel updates database → Real-time sync

##  Project Structure

```
shopify-app/
├── backend/                    # Laravel backend
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── ProductController.php
│   │   │   │   ├── SyncController.php
│   │   │   │   └── WebhookController.php
│   │   │   └── Middleware/
│   │   ├── Models/
│   │   │   ├── Shop.php
│   │   │   ├── Product.php
│   │   │   ├── Collection.php
│   │   │   └── Order.php
│   │   └── Services/
│   │       ├── ShopifyService.php
│   │       └── SyncService.php
│   ├── config/
│   │   └── shopify.php
│   ├── database/
│   │   └── migrations/
│   ├── routes/
│   │   └── web.php
│   └── public/
│       └── index.php
│
├── frontend/                   # React frontend
│   ├── src/
│   │   ├── components/
│   │   │   └── Layout.jsx
│   │   ├── pages/
│   │   │   ├── Dashboard.jsx
│   │   │   └── Products.jsx
│   │   ├── App.jsx
│   │   └── main.jsx
│   ├── package.json
│   └── vite.config.js
│
├── docker-compose.yml
├── .env.example
└── README.md
```

## Testing

### Manual Testing Checklist

- [ ] App installation flow works
- [ ] OAuth callback redirects correctly
- [ ] Dashboard displays correct stats
- [ ] Product sync fetches data from Shopify
- [ ] Products page shows paginated results
- [ ] Search filters products by title
- [ ] Status filter works (Active/Draft/Archived)
- [ ] Pagination navigates correctly
- [ ] Webhooks update database in real-time

### API Testing

```bash
# Get dashboard stats
curl http://localhost:8000/api/dashboard/stats

# Get products with search
curl "http://localhost:8000/api/products?search=shirt"

# Get products with filter
curl "http://localhost:8000/api/products?status=active&page=1"

# Trigger product sync
curl -X POST http://localhost:8000/api/sync/products
```

##  Security Considerations

- ✅ HMAC verification on OAuth callback
- ✅ Access tokens encrypted in database
- ✅ Session-based authentication
- ✅ CSRF protection on all POST requests
- ✅ Input validation and sanitization
- ✅ SQL injection prevention via Eloquent ORM

##  Deployment

### Production Checklist

1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false`
3. Update `APP_URL` to production domain
4. Use HTTPS for all URLs
5. Set up webhook URLs with public domain
6. Configure database with strong credentials
7. Run `php artisan config:cache`
8. Run `php artisan route:cache`
9. Build frontend: `npm run build`

##  Development Notes

### Key Design Decisions

1. **GraphQL over REST**: Used Shopify Admin GraphQL API for better performance and flexibility
2. **Session authentication**: Simple session-based auth for embedded app context
3. **Pagination**: Server-side pagination for better performance with large datasets
4. **JSON columns**: Store variants, images, and line items as JSON for flexibility
5. **Soft updates**: `updateOrCreate` prevents duplicate entries during sync

### Known Limitations

- Single shop support (multi-tenancy not implemented)
- No background job queue (uses synchronous API calls)
- Basic error handling (production needs comprehensive logging)

### Future Enhancements

- [ ] Background jobs for sync operations
- [ ] Multi-shop support
- [ ] Advanced analytics dashboard
- [ ] Bulk product operations
- [ ] Export functionality
- [ ] Real-time updates with WebSockets

##  Contributing

This is a technical assignment submission. For production use, consider implementing:
- Unit and integration tests
- CI/CD pipeline
- Monitoring and logging
- Rate limiting
- Caching layer

##  License

MIT

##  Author

Anuj Pokharel 

---

**Note**: This is a development setup. For production deployment, ensure proper security measures, HTTPS, environment variable management, and monitoring are in place.
