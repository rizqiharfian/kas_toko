-- SQL: create database and tables for Kas App
CREATE DATABASE IF NOT EXISTS kas_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kas_app;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS transaksi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tanggal DATE NOT NULL,
  jenis ENUM('masuk','keluar') NOT NULL,
  kategori VARCHAR(100) NOT NULL,
  keterangan TEXT,
  nominal INT NOT NULL,
  user_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Note: create admin account using create_admin.php or generate a password_hash and INSERT manually.
