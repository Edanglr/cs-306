
DELIMITER $$
CREATE TRIGGER trg_audit_order_update
BEFORE UPDATE ON Orders
FOR EACH ROW
BEGIN
    SET NEW.AuditMsg = CONCAT('Order ', NEW.OrderID, ' updated on ', NOW());
END$$
DELIMITER ;
























