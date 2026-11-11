-- =====================================================================
-- Shared queries for the ordering system (MySQL / MariaDB)
--
-- Each query has a matching Eloquent call (see README, "Shared queries").
-- Prefer the Eloquent call in the app; use this file in phpMyAdmin, for
-- reports, or with DB::select(). "Sales" always means completed orders.
-- Change the @variables below to try other values.
-- =====================================================================

SET @user_id   = 3;             -- a customer's user_id
SET @order_id  = 1;             -- an order_id
SET @date_from = '2026-11-01';  -- start of a report period (inclusive)
SET @date_to   = '2026-11-30';  -- end of a report period (inclusive)


-- 1. TOTAL SALES (all time)                      Order::totalSales()
SELECT COALESCE(SUM(total_price), 0) AS total_sales,
       COUNT(*)                      AS completed_orders
FROM orders
WHERE order_status = 'completed';


-- 2. TOTAL SALES for a period          Order::totalSales($from, $to)
SELECT COALESCE(SUM(total_price), 0) AS total_sales,
       COUNT(*)                      AS completed_orders
FROM orders
WHERE order_status = 'completed'
  AND order_date BETWEEN @date_from AND @date_to;


-- 3. SALES PER DAY, last 7 days                 Order::dailySales(7)
SELECT order_date,
       COUNT(*)         AS orders_count,
       SUM(total_price) AS sales
FROM orders
WHERE order_status = 'completed'
  AND order_date >= CURDATE() - INTERVAL 6 DAY
GROUP BY order_date
ORDER BY order_date DESC;


-- 4. LOW STOCK                              Product::lowStock()->get()
--    Active products at or below their reorder level, emptiest first.
SELECT p.product_id,
       p.product_name,
       c.category_name,
       i.quantity_in_stock,
       p.reorder_level,
       i.last_stock_update
FROM products p
JOIN inventories i      ON i.product_id = p.product_id
LEFT JOIN categories c  ON c.category_id = p.category_id
WHERE p.status = 'active'
  AND i.quantity_in_stock <= p.reorder_level
ORDER BY i.quantity_in_stock, p.product_name;


-- 5. RECENT ORDERS    Order::recent()->with('user')->withCount('orderItems')->limit(10)->get()
SELECT o.order_id,
       o.order_date,
       u.name                   AS customer,
       o.order_status,
       o.payment_method,
       o.total_price,
       COUNT(oi.order_item_id)  AS items
FROM orders o
JOIN users u             ON u.user_id = o.user_id
LEFT JOIN order_items oi ON oi.order_id = o.order_id
GROUP BY o.order_id, o.order_date, u.name, o.order_status, o.payment_method, o.total_price
ORDER BY o.order_date DESC, o.order_id DESC
LIMIT 10;


-- 6. ONE ORDER WITH ITS ITEMS    Order::with('user', 'orderItems.product')->findOrFail($id)
SELECT o.order_id,
       o.order_date,
       o.order_status,
       o.shipping_address,
       o.contact_number,
       o.payment_method,
       p.product_name,
       oi.quantity,
       oi.item_price,
       oi.quantity * oi.item_price AS subtotal,
       o.total_price
FROM orders o
JOIN order_items oi ON oi.order_id = o.order_id
JOIN products p     ON p.product_id = oi.product_id
WHERE o.order_id = @order_id
ORDER BY oi.order_item_id;


-- 7. A CUSTOMER'S ORDER HISTORY             $user->orders()->recent()->get()
SELECT order_id, order_date, order_status, payment_method, total_price
FROM orders
WHERE user_id = @user_id
ORDER BY order_date DESC, order_id DESC;


-- 8. BEST-SELLING PRODUCTS                        Product::bestSellers(5)
SELECT p.product_id,
       p.product_name,
       SUM(oi.quantity)                 AS units_sold,
       SUM(oi.quantity * oi.item_price) AS revenue
FROM order_items oi
JOIN orders o   ON o.order_id = oi.order_id
JOIN products p ON p.product_id = oi.product_id
WHERE o.order_status = 'completed'
GROUP BY p.product_id, p.product_name
ORDER BY units_sold DESC
LIMIT 5;


-- 9. ORDERS PER STATUS                             Order::countByStatus()
SELECT order_status, COUNT(*) AS orders_count
FROM orders
GROUP BY order_status;


-- 10. PRODUCT CATALOG (what customers see)   Product::active()->with('category')->get()
SELECT p.product_id,
       p.product_name,
       c.category_name,
       p.description,
       p.price,
       p.available_quantity,
       p.image
FROM products p
LEFT JOIN categories c ON c.category_id = p.category_id
WHERE p.status = 'active'
ORDER BY c.category_name, p.product_name;


-- 11. A USER'S CART                     $user->cartItems()->with('product')->get()
SELECT ci.cart_item_id,
       p.product_name,
       p.price,
       ci.quantity,
       p.price * ci.quantity AS subtotal,
       p.available_quantity
FROM cart_items ci
JOIN products p ON p.product_id = ci.product_id
WHERE ci.user_id = @user_id
ORDER BY ci.cart_item_id;


-- 12. RECENT ACTIVITY LOG          ActivityLog::recent()->with('user')->limit(20)->get()
SELECT l.log_id, l.timestamp, u.name, u.role, l.action
FROM logs l
JOIN users u ON u.user_id = l.user_id
ORDER BY l.timestamp DESC, l.log_id DESC
LIMIT 20;


-- 13. DASHBOARD NUMBERS in one row
SELECT (SELECT COALESCE(SUM(total_price), 0) FROM orders WHERE order_status = 'completed')            AS total_sales,
       (SELECT COUNT(*) FROM orders)                                                                  AS total_orders,
       (SELECT COUNT(*) FROM orders WHERE order_status = 'pending')                                   AS pending_orders,
       (SELECT COUNT(*) FROM products WHERE status = 'active')                                        AS active_products,
       (SELECT COUNT(*) FROM products WHERE status = 'active' AND available_quantity <= reorder_level) AS low_stock_products,
       (SELECT COUNT(*) FROM users WHERE role = 'customer' AND status = 'active')                     AS active_customers;


-- 14. STOCK CHECK: products whose two stock numbers disagree.
--     This must return no rows. If it does, stock was changed with a bulk
--     query instead of the model methods (see README, "Stock").
SELECT p.product_id, p.product_name, p.available_quantity, i.quantity_in_stock
FROM products p
LEFT JOIN inventories i ON i.product_id = p.product_id
WHERE i.inventory_id IS NULL
   OR i.quantity_in_stock <> p.available_quantity;
