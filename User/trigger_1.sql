DELIMITER $$
CREATE TRIGGER trg_update_stock_after_insert
AFTER INSERT ON OrderDetailsOrderDetails
FOR EACH ROW
BEGIN
    UPDATE Product
    SET Stock = Stock - NEW.Quantity
    WHERE Pid = NEW.Pid;
END$$
DELIMITER ;
