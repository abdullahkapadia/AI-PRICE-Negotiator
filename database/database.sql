CREATE DATABASE IF NOT EXISTS ai_negotiation_db;
USE ai_negotiation_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(15) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'customer',
    business_name VARCHAR(255),
    gst_number VARCHAR(50),
    approval_status VARCHAR(20) DEFAULT 'Approved',
    active_status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE customer_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(150) NOT NULL,
    category_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT NOT NULL,
    category_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_description TEXT,
    price DECIMAL(10,2) NOT NULL,
    min_negotiation_price DECIMAL(10,2) NOT NULL,
    stock_quantity INT NOT NULL,
    product_image VARCHAR(255),
    product_status VARCHAR(50) NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB;

CREATE TABLE customer_carts (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_reference INT NOT NULL,
    product_reference INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    negotiated_price DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_reference) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_reference) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE wishlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE negotiations_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    vendor_id INT NOT NULL,
    product_id INT NOT NULL,
    original_price DECIMAL(10,2) NOT NULL,
    final_price DECIMAL(10,2),
    status VARCHAR(50) NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiry_time TIMESTAMP NULL,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vendor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE negotiation_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    sender_id INT,
    offer_source VARCHAR(50) NOT NULL,
    offered_price DECIMAL(10,2) NOT NULL,
    round_number INT NOT NULL,
    decision_status VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES negotiations_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    vendor_id INT NOT NULL,
    negotiation_session_id INT,
    total_amount DECIMAL(10,2) NOT NULL,
    admin_commission DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    order_status VARCHAR(50) NOT NULL DEFAULT 'Order Confirmed',
    payment_status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    payment_method VARCHAR(50) DEFAULT 'COD',
    tracking_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (vendor_id) REFERENCES users(id),
    FOREIGN KEY (negotiation_session_id) REFERENCES negotiations_sessions(id)
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price_per_unit DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(150),
    amount DECIMAL(10,2) NOT NULL,
    payment_status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE order_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    tracking_status VARCHAR(100) NOT NULL,
    location VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    product_id INT NOT NULL,
    rating INT NOT NULL,
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO users (username, email, phone, password_hash, role, approval_status)
VALUES ('admin', 'admin@admin.com', '0000000000', '$2y$10$hZuYWXuof74W3HQRDQNGiuNvLr3JgetwFnMRD3vWWrZ2f3gIQMVnq', 'admin', 'Approved');

INSERT INTO categories (category_name, category_description) VALUES
('Electronics', 'Electronic gadgets and devices'),
('Clothing', 'Apparel and fashion accessories'),
('Home & Kitchen', 'Home appliances and kitchen items'),
('Books', 'Books and educational material'),
('Sports', 'Sports equipment and accessories');
