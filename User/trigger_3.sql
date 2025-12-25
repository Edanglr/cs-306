DELIMITER $$
CREATE TRIGGER trg_notify_order_shipped
BEFORE UPDATE ON Orders
FOR EACH ROW
BEGIN
    IF NEW.Status = 'Shipped' AND OLD.Status <> 'Shipped' THEN
       SET NEW.Notification = CONCAT('Order ', NEW.OrderID, ' shipped at ', NOW());
    END IF;
END$$
DELIMITER ;