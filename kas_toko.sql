SET FOREIGN_KEY_CHECKS = 0;

-- ======================
-- TABLE: users
-- ======================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` VALUES
(1,'admin','$2y$10$g5MexszZoij9zdrHL7Iu2eWz4q6rtrmGxiW39zpVKWfXJN7hPgoWa','$2y$10$bOEcDKnmkPiGPX9hN3nT6uNZPSgBDC8pssLaUTGMQ4ZCRPn2ueUHa','Administrator');

-- ======================
-- TABLE: transaksi
-- ======================
DROP TABLE IF EXISTS `transaksi`;
CREATE TABLE `transaksi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `jenis` enum('masuk','keluar') NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `keterangan` text,
  `nominal` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_user_transaksi` (`user_id`),
  CONSTRAINT `fk_user_transaksi`
      FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
      ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `transaksi` VALUES
(1,'2025-11-14','keluar','makan','makan ube',15000,1,'2025-11-14 14:45:16'),
(2,'2025-11-14','masuk','pembelian_unit','COD toko Asus X200MA',1500000,1,'2025-11-14 14:58:33'),
(3,'2025-11-14','keluar','sparepart','Batre asus x200ma',120000,1,'2025-11-14 15:03:29'),
(4,'2025-11-14','keluar','atk','Pulpen ',12000,1,'2025-11-14 15:07:08'),
(5,'2025-11-14','keluar','makan','makan dawani',15000,1,'2025-11-14 15:29:04'),
(7,'2025-11-15','keluar','perlengkapan','Sunlight',10000,1,'2025-11-14 16:02:08'),
(8,'2025-10-31','masuk','pembelian_unit','Cod ilham AST Celeron',2500000,1,'2025-11-14 16:17:57'),
(9,'2025-11-15','keluar','makan','Makan Ilham',15000,1,'2025-11-14 16:19:30');

SET FOREIGN_KEY_CHECKS = 1;
