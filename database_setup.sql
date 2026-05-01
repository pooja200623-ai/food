-- SQL Script for Crave Food Delivery App
-- Instructions: 
-- 1. Open phpMyAdmin (http://localhost/phpmyadmin)
-- 2. Click the "SQL" tab at the top.
-- 3. Paste this entire script and click "Go".

CREATE DATABASE IF NOT EXISTS `custom_app_db`;
USE `custom_app_db`;

-- Table for Food Items
CREATE TABLE IF NOT EXISTS `foods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `rating` decimal(3,1) DEFAULT 4.0,
  `image_url` text DEFAULT NULL,
  `delivery_time` varchar(50) DEFAULT '30-40 min',
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Food Data
INSERT INTO `foods` (`name`, `category`, `price`, `rating`, `image_url`, `delivery_time`, `description`) VALUES
('Chicken Biryani', 'Indian', 320.00, 4.9, 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?auto=format&fit=crop&w=500&q=80', '30-40 min', 'Aromatic basmati rice cooked with tender chicken and authentic spices.'),
('Paneer Butter Masala', 'Indian', 280.00, 4.7, 'https://images.unsplash.com/photo-1631452180519-c014fe946bc0?auto=format&fit=crop&w=500&q=80', '25-35 min', 'Cottage cheese cubes cooked in a rich, creamy tomato gravy.'),
('Garlic Naan', 'Indian', 60.00, 4.8, 'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=500&q=80', '15-20 min', 'Soft and fluffy flatbread baked in a tandoor with garlic butter.'),
('Steamed Momos', 'Chinese', 149.00, 4.7, 'https://images.unsplash.com/photo-1625220194771-7ebdea0b70b9?auto=format&fit=crop&w=500&q=80', '20-25 min', 'Delicate dumplings filled with minced chicken and herbs.'),
('Classic Cheeseburger', 'American', 199.00, 4.6, 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=500&q=80', '20-25 min', 'Juicy beef patty with melted cheddar, lettuce, and tomato.');

-- Table for Orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_email` varchar(255) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `delivery_address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'cod',
  `order_status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for Order Items
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `food_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
