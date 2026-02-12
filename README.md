# AI Price Negotiator

An e-commerce platform where customers can negotiate product prices with an AI-powered engine before purchasing. The system brings together three user roles -- customers, vendors, and administrators -- under a single platform, with a machine learning model at its core that learns from every negotiation to make smarter pricing decisions over time.

---

## What This Project Does

In most online stores, the price is fixed. You either pay it or you leave. This project changes that. Customers can make offers on any product, and an AI engine evaluates each offer in real time -- considering factors like the customer's purchase history, how long the product has been sitting in stock, current demand, and the vendor's minimum acceptable price. The AI then decides whether to accept the offer, make a counter-offer, or reject it entirely.

The negotiation engine is backed by a Random Forest classifier trained on historical negotiation data. It gets better the more it is used. When there is not enough data to train a model, the system falls back to a rule-based engine so negotiations still work from day one.

---

## How It Works

A customer browses products, picks one, and opens a negotiation. They submit an offer price. Behind the scenes, PHP collects 13 data points about the customer and the product, passes them to a Python script, and the trained ML model returns a decision with a confidence score. The customer sees whether their offer was accepted, countered, or rejected -- and can try again for up to five rounds per session.

Vendors list products with a price and a minimum negotiation threshold. They can monitor which products are being negotiated and track the outcomes. The admin oversees everything -- user management, order tracking, revenue dashboards, and the ML model training pipeline.

---

## Tech Stack

- **Backend:** PHP (procedural), MySQL
- **ML Engine:** Python (no external libraries required -- the Random Forest is implemented from scratch)
- **Frontend:** HTML, CSS, JavaScript
- **Server:** Apache (XAMPP)
- **Integration:** PHP calls Python via shell_exec, data is exchanged through JSON and CSV

---

## Project Structure

```
AI-PRICE-Negotiator/
|
|-- index.php                    Landing page
|-- config/                      Database connection and session config
|-- database/
|   |-- database.sql             Full schema (13 tables)
|   |-- seed_data.php            Fake data generator for testing
|
|-- customer/                    Customer-facing module
|   |-- register.php             Account registration
|   |-- login.php                Login page
|   |-- dashboard.php            Product browsing and search
|   |-- product_detail.php       Product page with reviews and add-to-cart
|   |-- negotiate.php            Negotiation interface (offer, counter, accept)
|   |-- cart.php                 Shopping cart with quantity management
|   |-- place_order.php          Checkout and order placement
|   |-- orders.php               Order history
|   |-- order_detail.php         Order tracking and details
|   |-- wishlist.php             Saved products
|   |-- add_review.php           Product review submission
|
|-- vendor/                      Vendor-facing module
|   |-- register.php             Vendor registration with business details
|   |-- login.php                Vendor login
|   |-- dashboard.php            Sales overview and stats
|   |-- manage_products.php      Product listing management
|   |-- add_product.php          New product creation
|   |-- edit_product.php         Product editing
|   |-- orders.php               Order management and status updates
|   |-- negotiations.php         View negotiation activity on products
|
|-- admin/                       Admin panel
|   |-- dashboard.php            Platform-wide stats and revenue overview
|   |-- manage_vendors.php       Vendor approval and management
|   |-- manage_users.php         Customer account management
|   |-- manage_products.php      Product moderation
|   |-- manage_categories.php    Product category management
|   |-- orders.php               All orders with commission tracking
|   |-- negotiation_logs.php     Full negotiation history viewer
|   |-- ml_training.php          ML model training and status page
|
|-- negotiation engine/          Machine learning pipeline
|   |-- negotiate_process.php    PHP engine class (calls Python, fallback logic)
|   |-- predict.py               Python prediction script (loads model, returns JSON)
|   |-- train_model.py           Python training script (Random Forest from scratch)
|   |-- export_training_data.php Exports DB negotiation data to CSV
|   |-- generate_fake_data.py    Generates synthetic training data
|   |-- trained_model.json       Serialized trained model
|   |-- training_data.csv        Training dataset
|
|-- assests/css/                 Stylesheets
```

---

## Database Schema

The application uses 13 tables:

| Table | Purpose |
|-------|---------|
| users | All user accounts (customers, vendors, admin) with role-based access |
| customer_addresses | Delivery addresses linked to customer accounts |
| categories | Product categories (Electronics, Clothing, Home, Books, Sports) |
| products | Product listings with price, minimum negotiation price, and stock |
| customer_carts | Shopping cart items with optional negotiated prices |
| wishlists | Customer product wishlists |
| negotiations_sessions | Negotiation sessions between customers and AI, tracking status and final price |
| negotiation_logs | Round-by-round log of every offer and counter-offer |
| orders | Completed orders with status, payment info, and admin commission |
| order_items | Individual products within each order |
| payments | Payment transaction records |
| order_tracking | Shipment status updates with location |
| reviews | Customer product reviews and ratings |

---

## ML Negotiation Engine

The negotiation engine analyzes 13 features before making a decision:

**Customer factors** -- total past orders, total amount spent, negotiation success rate, and average discount received in previous deals.

**Product factors** -- current stock level, number of days listed, total units sold, number of active negotiations on the same product, and product category.

**Offer factors** -- the offer as a percentage of the listed price, the current negotiation round, the original price, and the vendor's minimum acceptable price.

These features are fed into a Random Forest classifier (20 decision trees, each trained on a 5000-sample bootstrap from the dataset). The model outputs a decision -- accept, counter, or reject -- along with a confidence score. Counter-offer prices are calculated dynamically based on the confidence level, customer loyalty, and product demand.

The entire ML pipeline runs without any external Python libraries. The Random Forest, including Gini impurity calculation, bootstrap sampling, and quantile-based threshold selection, is implemented from scratch.

---

## Admin Commission

The platform charges a 5% commission on every completed order. This is calculated during checkout, stored in the orders table, and displayed on the admin dashboard and orders page.

---

## Setup Instructions

**Prerequisites:**
- XAMPP (Apache + MySQL)
- Python 3.x (added to system PATH)

**Steps:**

1. Clone this repository into your XAMPP htdocs folder:
   ```
   git clone https://github.com/your-username/AI-PRICE-Negotiator.git
   ```

2. Start Apache and MySQL from the XAMPP Control Panel.

3. Open phpMyAdmin and create the database:
   - Import `database/database.sql` to create all tables and default data.

4. (Optional) Populate with test data:
   - Open `http://localhost/AI-PRICE-Negotiator/database/seed_data.php` in your browser.
   - This creates 60 vendors, 1000 customers, 500 products, 10000 orders, and 3500 negotiation sessions.
   - All generated accounts use the password `password123`.

5. Train the ML model:
   - Log in as admin (admin@admin.com / admin123).
   - Go to ML Training in the sidebar.
   - Click "Export Training Data" then "Train ML Model".

6. Open the application:
   ```
   http://localhost/AI-PRICE-Negotiator/
   ```

---

## Default Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@admin.com | admin123 |

Vendor and customer accounts can be created through the registration pages, or use the seeded accounts (password: password123).

---

## Key Features

- Real-time AI-powered price negotiation with up to 5 rounds per session
- Machine learning model that improves with more negotiation data
- Multi-vendor marketplace with vendor approval workflow
- Shopping cart with negotiated price support
- Order management with tracking, payments, and status updates
- Admin dashboard with revenue analytics and commission tracking
- Product reviews and ratings
- Wishlist functionality
- Responsive design across all modules
- Database seeder for testing with realistic data

---

## License

This project is open source and available for educational and personal use.
