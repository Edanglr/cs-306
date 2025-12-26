-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: localhost
-- Üretim Zamanı: 26 Ara 2025, 20:44:10
-- Sunucu sürümü: 10.4.28-MariaDB
-- PHP Sürümü: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `cs306_project`
--

DELIMITER $$
--
-- Yordamlar
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `GenerateDailyReport` (IN `reportDate` DATE)   BEGIN
    SELECT 
        COUNT(*) AS TotalOrders,
        SUM(Total) AS TotalRevenue
    FROM Orders
    WHERE DATE(OrderDate) = reportDate;
    
    SELECT 
        Status, COUNT(*) AS OrderCount
    FROM Orders
    WHERE DATE(OrderDate) = reportDate
    GROUP BY Status;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ProcessOrder` (IN `p_orderID` INT)   BEGIN
    DECLARE currentStatus VARCHAR(20);

    -- Order statusunu al
    SELECT Status
    INTO currentStatus
    FROM Orders
    WHERE OrderID = p_orderID;

    -- Sadece Pending ise devam et
    IF currentStatus = 'Pending' THEN

        UPDATE Orders
        SET 
            Status = 'Shipped',
            AuditMsg = CONCAT('Order ', p_orderID, ' updated on ', NOW()),
            Notification = CONCAT('Order ', p_orderID, ' shipped at ', NOW())
        WHERE OrderID = p_orderID;

    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ReserveStock` (IN `inOrderID` INT, IN `inPid` INT, IN `inQuantity` INT, IN `inPrice` DECIMAL(10,2))   BEGIN
    DECLARE availableStock INT;
    
    SELECT Stock INTO availableStock
    FROM Product
    WHERE Pid = inPid;
    
    IF availableStock >= inQuantity THEN INSERT INTO OrderDetails (OrderID, Pid, Quantity, Price)VALUES (inOrderID, inPid, inQuantity, inPrice);
       UPDATE Product
       SET Stock = Stock - inQuantity
       WHERE Pid = inPid;
    ELSE
       SIGNAL SQLSTATE '45000' 
           SET MESSAGE_TEXT = 'Not enough stock available for product';
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `customer`
--

CREATE TABLE `customer` (
  `Cid` int(11) NOT NULL,
  `Cname` char(24) DEFAULT NULL,
  `Address` char(64) DEFAULT NULL,
  `Email` char(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `customer`
--

INSERT INTO `customer` (`Cid`, `Cname`, `Address`, `Email`) VALUES
(1, 'Test User', 'Test Address', 'test@test.com'),
(2, 'Test User', 'Test Address', 'test@test.com');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `orderdetails`
--

CREATE TABLE `orderdetails` (
  `OrderID` int(11) NOT NULL,
  `Pid` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `Price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `orderdetails`
--

INSERT INTO `orderdetails` (`OrderID`, `Pid`, `Quantity`, `Price`) VALUES
(1, 101, 2, 50.00),
(2, 101, 1, 1200.99),
(3, 101, 1, 1200.99),
(4, 101, 1, 1200.99),
(5, 101, 1, 1200.99),
(6, 101, 1, 1200.99),
(7, 101, 1, 1200.99),
(8, 101, 1, 1200.99),
(9, 101, 1, 1200.99),
(10, 101, 1, 1200.99),
(11, 101, 1, 1200.99),
(12, 101, 1, 1200.99),
(13, 101, 3, 28999.99);

--
-- Tetikleyiciler `orderdetails`
--
DELIMITER $$
CREATE TRIGGER `trg_update_stock_after_insert` AFTER INSERT ON `orderdetails` FOR EACH ROW BEGIN
    UPDATE Product
    SET Stock = Stock - NEW.Quantity
    WHERE Pid = NEW.Pid;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `orders`
--

CREATE TABLE `orders` (
  `OrderID` int(11) NOT NULL,
  `Cid` int(11) NOT NULL,
  `OrderDate` datetime DEFAULT NULL,
  `Total` decimal(10,2) DEFAULT NULL,
  `Status` enum('Pending','Shipped','Delivered','Canceled') DEFAULT NULL,
  `AuditMsg` varchar(255) DEFAULT NULL,
  `Notification` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `orders`
--

INSERT INTO `orders` (`OrderID`, `Cid`, `OrderDate`, `Total`, `Status`, `AuditMsg`, `Notification`) VALUES
(1, 1, '2025-12-23 03:08:38', 100.00, 'Shipped', 'Order 1 updated on 2025-12-26 18:53:44', 'Order 1 shipped at 2025-12-23 03:16:10'),
(2, 1, '2025-12-23 03:42:19', 1200.99, 'Shipped', 'Order 2 updated on 2025-12-26 22:29:37', 'Order 2 shipped at 2025-12-26 22:29:37'),
(3, 1, '2025-12-23 03:58:55', 1200.99, 'Shipped', 'Order 3 updated on 2025-12-26 22:29:24', 'Order 3 shipped at 2025-12-26 22:29:24'),
(4, 1, '2025-12-23 04:04:55', 0.00, 'Shipped', 'Order 4 updated on 2025-12-26 22:29:51', 'Order 4 shipped at 2025-12-26 22:29:51'),
(5, 1, '2025-12-23 04:11:08', 0.00, 'Pending', NULL, NULL),
(6, 1, '2025-12-23 04:11:30', 0.00, 'Pending', NULL, NULL),
(7, 1, '2025-12-23 04:11:33', 0.00, 'Pending', NULL, NULL),
(8, 1, '2025-12-23 04:11:33', 0.00, 'Pending', NULL, NULL),
(9, 1, '2025-12-23 04:11:33', 1200.99, 'Pending', 'Order 9 updated on 2025-12-26 22:06:30', NULL),
(10, 1, '2025-12-25 02:11:21', 0.00, 'Pending', NULL, NULL),
(11, 1, '2025-12-26 18:53:21', 0.00, 'Pending', NULL, NULL),
(12, 1, '2025-12-26 19:03:08', 0.00, 'Pending', NULL, NULL),
(13, 1, '2025-12-26 19:10:12', 0.00, 'Pending', NULL, NULL);

--
-- Tetikleyiciler `orders`
--
DELIMITER $$
CREATE TRIGGER `trg_audit_order_update` BEFORE UPDATE ON `orders` FOR EACH ROW BEGIN
    SET NEW.AuditMsg = CONCAT('Order ', NEW.OrderID, ' updated on ', NOW());
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_notify_order_shipped` BEFORE UPDATE ON `orders` FOR EACH ROW BEGIN
    IF NEW.Status = 'Shipped' AND OLD.Status <> 'Shipped' THEN
       SET NEW.Notification = CONCAT('Order ', NEW.OrderID, ' shipped at ', NOW());
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `payment`
--

CREATE TABLE `payment` (
  `PayID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `PayType` enum('Credit Card','Debit Card','PayPal','Cash on Delivery') NOT NULL,
  `TransDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `product`
--

CREATE TABLE `product` (
  `Pid` int(11) NOT NULL,
  `Pname` char(32) NOT NULL,
  `Description` text DEFAULT NULL,
  `Price` decimal(10,2) NOT NULL,
  `Stock` int(11) DEFAULT NULL,
  `Weight` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `product`
--

INSERT INTO `product` (`Pid`, `Pname`, `Description`, `Price`, `Stock`, `Weight`) VALUES
(101, 'Laptop', NULL, 28999.99, 87, NULL),
(102, 'Mouse', NULL, 1500.00, 40, NULL),
(103, 'Keyboard', NULL, 2489.90, 60, NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `review`
--

CREATE TABLE `review` (
  `ReviewID` int(11) NOT NULL,
  `Cid` int(11) NOT NULL,
  `Pid` int(11) NOT NULL,
  `Rating` int(11) DEFAULT NULL,
  `Comment` char(255) DEFAULT NULL,
  `rDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `shipment`
--

CREATE TABLE `shipment` (
  `ShipID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `TrackNo` char(50) NOT NULL,
  `Estimate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`Cid`);

--
-- Tablo için indeksler `orderdetails`
--
ALTER TABLE `orderdetails`
  ADD PRIMARY KEY (`OrderID`,`Pid`),
  ADD KEY `Pid` (`Pid`);

--
-- Tablo için indeksler `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`OrderID`),
  ADD KEY `Cid` (`Cid`);

--
-- Tablo için indeksler `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`PayID`),
  ADD UNIQUE KEY `OrderID` (`OrderID`);

--
-- Tablo için indeksler `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`Pid`);

--
-- Tablo için indeksler `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`ReviewID`),
  ADD KEY `Cid` (`Cid`),
  ADD KEY `Pid` (`Pid`);

--
-- Tablo için indeksler `shipment`
--
ALTER TABLE `shipment`
  ADD PRIMARY KEY (`ShipID`),
  ADD UNIQUE KEY `OrderID` (`OrderID`),
  ADD UNIQUE KEY `TrackNo` (`TrackNo`);

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `orderdetails`
--
ALTER TABLE `orderdetails`
  ADD CONSTRAINT `orderdetails_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`) ON DELETE CASCADE,
  ADD CONSTRAINT `orderdetails_ibfk_2` FOREIGN KEY (`Pid`) REFERENCES `product` (`Pid`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`Cid`) REFERENCES `customer` (`Cid`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`Cid`) REFERENCES `customer` (`Cid`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`Pid`) REFERENCES `product` (`Pid`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `shipment`
--
ALTER TABLE `shipment`
  ADD CONSTRAINT `shipment_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
