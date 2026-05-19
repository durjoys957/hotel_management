-- Hotel Management System - Full Schema
-- Run this file once to set up the entire database

CREATE DATABASE IF NOT EXISTS hotel_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hotel_management;

-- =============================================
-- USERS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(30),
    nationality VARCHAR(100),
    id_number VARCHAR(100),
    role ENUM('guest','receptionist','housekeeping','admin') NOT NULL DEFAULT 'guest',
    profile_pic VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- ROOM TYPES TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS room_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price_per_night DECIMAL(10,2) NOT NULL,
    max_capacity INT NOT NULL DEFAULT 2,
    thumbnail_path VARCHAR(255) DEFAULT NULL,
    amenities JSON DEFAULT NULL
);

-- =============================================
-- ROOMS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_type_id INT NOT NULL,
    room_number VARCHAR(20) NOT NULL UNIQUE,
    floor INT NOT NULL DEFAULT 1,
    status ENUM('available','occupied','dirty','maintenance','blocked') DEFAULT 'available',
    notes TEXT DEFAULT NULL,
    FOREIGN KEY (room_type_id) REFERENCES room_types(id) ON DELETE CASCADE
);

-- =============================================
-- BOOKINGS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guest_id INT NOT NULL,
    room_id INT DEFAULT NULL,
    room_type_id INT NOT NULL,
    checkin_date DATE NOT NULL,
    checkout_date DATE NOT NULL,
    num_guests INT NOT NULL DEFAULT 1,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending','confirmed','checked_in','checked_out','cancelled') DEFAULT 'pending',
    source ENUM('online','walk_in') DEFAULT 'online',
    special_requests TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guest_id) REFERENCES users(id),
    FOREIGN KEY (room_id) REFERENCES rooms(id),
    FOREIGN KEY (room_type_id) REFERENCES room_types(id)
);

-- =============================================
-- BILLING TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS billing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    guest_id INT NOT NULL,
    base_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    extras_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    payment_method ENUM('cash','card','online','points') DEFAULT NULL,
    payment_status ENUM('pending','paid') DEFAULT 'pending',
    paid_at DATETIME DEFAULT NULL,
    receipt_path VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    FOREIGN KEY (guest_id) REFERENCES users(id)
);

-- =============================================
-- SERVICE REQUESTS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS service_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    guest_id INT NOT NULL,
    room_id INT NOT NULL,
    service_type ENUM('extra_bed','toiletries','laundry','room_service','other') NOT NULL,
    description TEXT,
    status ENUM('pending','in_progress','completed') DEFAULT 'pending',
    requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    FOREIGN KEY (guest_id) REFERENCES users(id),
    FOREIGN KEY (room_id) REFERENCES rooms(id)
);

-- =============================================
-- HOUSEKEEPING TASKS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS housekeeping_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    assigned_to INT NOT NULL,
    task_type ENUM('cleaning','inspection','maintenance') NOT NULL,
    priority ENUM('normal','urgent') DEFAULT 'normal',
    status ENUM('pending','in_progress','done') DEFAULT 'pending',
    notes TEXT DEFAULT NULL,
    scheduled_date DATE NOT NULL,
    completed_at DATETIME DEFAULT NULL,
    FOREIGN KEY (room_id) REFERENCES rooms(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id)
);

-- =============================================
-- MAINTENANCE REPORTS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS maintenance_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    reported_by INT NOT NULL,
    description TEXT NOT NULL,
    severity ENUM('low','medium','high') DEFAULT 'medium',
    status ENUM('open','in_progress','resolved') DEFAULT 'open',
    reported_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    resolved_at DATETIME DEFAULT NULL,
    FOREIGN KEY (room_id) REFERENCES rooms(id),
    FOREIGN KEY (reported_by) REFERENCES users(id)
);

-- =============================================
-- SEASONAL PRICING TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS seasonal_pricing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_type_id INT NOT NULL,
    label VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    price_per_night DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (room_type_id) REFERENCES room_types(id) ON DELETE CASCADE
);

-- =============================================
-- REVIEWS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL UNIQUE,
    guest_id INT NOT NULL,
    overall_rating TINYINT NOT NULL CHECK (overall_rating BETWEEN 1 AND 5),
    cleanliness_rating TINYINT NOT NULL CHECK (cleanliness_rating BETWEEN 1 AND 5),
    service_rating TINYINT NOT NULL CHECK (service_rating BETWEEN 1 AND 5),
    review_text TEXT,
    admin_reply TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    FOREIGN KEY (guest_id) REFERENCES users(id)
);

-- =============================================
-- LOYALTY POINTS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS loyalty_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guest_id INT NOT NULL,
    booking_id INT DEFAULT NULL,
    points_earned INT NOT NULL DEFAULT 0,
    points_used INT NOT NULL DEFAULT 0,
    balance INT NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guest_id) REFERENCES users(id),
    FOREIGN KEY (booking_id) REFERENCES bookings(id)
);

-- =============================================
-- ANNOUNCEMENTS TABLE (Admin Feature)
-- =============================================
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- =============================================
-- SEED DATA
-- =============================================

-- Default Admin
INSERT IGNORE INTO users (name, email, password_hash, role, is_active) VALUES
('Hotel Admin', 'admin@hotel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1),
('Front Desk 1', 'receptionist@hotel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'receptionist', 1),
('Housekeeping Manager', 'housekeeping@hotel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'housekeeping', 1),
('John Guest', 'guest@hotel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guest', 1);
-- Default password for all: "password"

-- Room Types
INSERT IGNORE INTO room_types (id, name, description, price_per_night, max_capacity, amenities) VALUES
(1, 'Standard', 'Comfortable standard room with all basic amenities. Perfect for solo travelers and couples.', 80.00, 2, '["WiFi","AC","TV","Bathroom","Wardrobe"]'),
(2, 'Deluxe', 'Spacious deluxe room with premium furnishings and a city view. Great for families and business travelers.', 150.00, 3, '["WiFi","AC","TV","Mini Bar","Bathroom","Wardrobe","City View","Desk"]'),
(3, 'Suite', 'Luxurious suite with a separate living area, king bed, and premium bath. The ultimate hotel experience.', 280.00, 4, '["WiFi","AC","Smart TV","Mini Bar","Jacuzzi","Lounge Area","King Bed","Room Service","Safe","Balcony"]');

-- Rooms
INSERT IGNORE INTO rooms (id, room_type_id, room_number, floor, status) VALUES
(1, 1, '101', 1, 'available'),
(2, 1, '102', 1, 'available'),
(3, 1, '103', 1, 'available'),
(4, 1, '104', 1, 'available'),
(5, 2, '201', 2, 'available'),
(6, 2, '202', 2, 'available'),
(7, 2, '203', 2, 'available'),
(8, 3, '301', 3, 'available'),
(9, 3, '302', 3, 'available'),
(10, 1, '105', 1, 'maintenance');

-- Seasonal Pricing
INSERT IGNORE INTO seasonal_pricing (room_type_id, label, start_date, end_date, price_per_night) VALUES
(1, 'Eid Holiday', '2025-03-28', '2025-04-05', 120.00),
(2, 'Eid Holiday', '2025-03-28', '2025-04-05', 200.00),
(3, 'Eid Holiday', '2025-03-28', '2025-04-05', 350.00),
(1, 'Summer Peak', '2025-06-01', '2025-08-31', 100.00),
(2, 'Summer Peak', '2025-06-01', '2025-08-31', 180.00),
(3, 'Summer Peak', '2025-06-01', '2025-08-31', 320.00);
