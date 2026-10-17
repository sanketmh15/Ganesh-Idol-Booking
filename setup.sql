CREATE DATABASE IF NOT EXISTS ganesh_booking_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ganesh_booking_db;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  mobile VARCHAR(15) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS idols (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  description VARCHAR(600) NOT NULL,
  image VARCHAR(255) DEFAULT NULL,
  stock INT NOT NULL DEFAULT 0,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  idol_id INT NOT NULL,
  quantity INT NOT NULL,
  booking_date DATE NOT NULL,
  delivery_address VARCHAR(500) NOT NULL,
  delivery_area VARCHAR(120) NOT NULL,
  delivery_city VARCHAR(100) NOT NULL,
  delivery_pincode VARCHAR(10) NOT NULL,
  delivery_mobile VARCHAR(15) NOT NULL,
  delivery_charge DECIMAL(10,2) NOT NULL DEFAULT 0,
  idol_total DECIMAL(10,2) NOT NULL,
  grand_total DECIMAL(10,2) NOT NULL,
  status ENUM('Pending','Confirmed','Out for Delivery','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
  stock_released TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_booking_user FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT fk_booking_idol FOREIGN KEY (idol_id) REFERENCES idols(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS settings (
  setting_key VARCHAR(80) PRIMARY KEY,
  setting_value VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

INSERT IGNORE INTO settings (setting_key, setting_value)
VALUES ('delivery_charge', '100.00');

INSERT INTO idols (name, price, description, image, stock)
SELECT 'Small Ganesh Idol', 500.00, 'A beautiful small Ganesh idol for home celebrations.', 'small-ganesh.jpg', 20
WHERE NOT EXISTS (SELECT 1 FROM idols WHERE name='Small Ganesh Idol');

INSERT INTO idols (name, price, description, image, stock)
SELECT 'Medium Ganesh Idol', 1000.00, 'A traditional medium-sized Ganesh idol for your home.', 'medium-ganesh.jpg', 15
WHERE NOT EXISTS (SELECT 1 FROM idols WHERE name='Medium Ganesh Idol');

INSERT INTO idols (name, price, description, image, stock)
SELECT 'Large Ganesh Idol', 2000.00, 'A grand Ganesh idol for festive celebrations.', 'large-ganesh.jpg', 10
WHERE NOT EXISTS (SELECT 1 FROM idols WHERE name='Large Ganesh Idol');
