# Ordering System

Laravel 13 + React (Vite) ordering system. This README covers how to get the project running and how the database is laid out, so every member works against the same tables, rules and queries.

## Requirements

| Tool | Version |
| --- | --- |
| PHP | 8.4 or newer (the project was created on 8.5), with the `pdo_mysql` extension |
| Composer | 2.x |
| Node.js + npm | Node 20.19+ or 22.12+ |
| Database | MySQL 8.0.16+ or MariaDB 10.4+ (XAMPP's MariaDB is fine) |

## Setup

All commands run inside the `laravel-react-app` folder.

```bash
composer install
npm install
cp .env.example .env        # Windows CMD: copy .env.example .env
php artisan key:generate
```

Create an empty database named `ordering_system` (phpMyAdmin → New, collation `utf8mb4_unicode_ci`), or:

```sql
CREATE DATABASE ordering_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

`.env.example` already points at it (`DB_DATABASE=ordering_system`, user `root`, empty password). Change `DB_USERNAME` / `DB_PASSWORD` in your `.env` if your MySQL is set up differently.

Then fill the database using **one** of these two options. Both give the same tables and the same sample data; the only difference is that Option A dates the sample orders relative to the day you run it, while the export has fixed dates around 11 November 2026.

**Option A – migrations and seeders (recommended)**

```bash
php artisan migrate:fresh --seed
```

**Option B – import the exported database**

Import `database/ordering_system.sql` into the empty `ordering_system` database (phpMyAdmin → Import), or:

```bash
mysql -u root -p ordering_system < database/ordering_system.sql
```

The export already includes the `migrations` table, so `php artisan migrate` afterwards reports nothing to migrate.

Finally, start the app:

```bash
npm run build               # or `npm run dev` in a second terminal while developing
php artisan serve           # http://localhost:8000
```

### If you pulled these changes into an existing copy

The original five migrations were edited (columns renamed and added), so `php artisan migrate` alone is not enough. Rebuild your local database with `php artisan migrate:fresh --seed`. This deletes whatever is in your local `ordering_system` database.

## Login accounts

All seeded accounts use the password `password`.

| Role | Email | Notes |
| --- | --- | --- |
| Admin | `admin@example.com` | Starter admin for the first login |
| Employee | `employee@example.com` | |
| Customer | `customer1@example.com` | Has orders and a cart |
| Customer | `customer2@example.com` | Has orders |
| Customer | `inactive@example.com` | `status = inactive`, for testing blocked logins |

To create only the admin account on an empty database (no sample data):

```bash
php artisan migrate
php artisan db:seed --class=AdminSeeder
```

Change the admin password before the system is shown or deployed anywhere public.

## Database

### Tables

| Table | Primary key | Main columns |
| --- | --- | --- |
| `users` | `user_id` | `name`, `email` (unique), `password` (bcrypt hash), `role` (admin / employee / customer), `status` (active / inactive), `phone_number`, `address`, `created_at`, `updated_at` |
| `categories` | `category_id` | `category_name` (unique), `description` |
| `products` | `product_id` | `category_id`, `product_name`, `description`, `price`, `available_quantity`, `reorder_level`, `image`, `status` (active / inactive) |
| `inventories` | `inventory_id` | `product_id` (unique – one row per product), `quantity_in_stock`, `last_stock_update` |
| `orders` | `order_id` | `user_id`, `order_date`, `total_price`, `order_status` (pending / processing / completed / cancelled), `shipping_address`, `contact_number`, `payment_method` (cash_on_delivery / gcash / bank_transfer / card) |
| `order_items` | `order_item_id` | `order_id`, `product_id`, `quantity`, `item_price` (price at the time of the order) |
| `cart_items` | `cart_item_id` | `user_id`, `product_id`, `quantity` (one row per product per user) |
| `logs` | `log_id` | `user_id`, `action`, `timestamp` |
| `sessions`, `cache`, `cache_locks`, `jobs` | | Laravel's own tables, needed because `.env.example` uses the database for sessions, cache and queues |

Column names changed from the first version of the migrations: `users.user_role` is now `users.role`, and `users.is_active` (true/false) is now `users.status` (`active` / `inactive`). `products.category` (text) was replaced by `products.category_id`.

### Design decisions

- **Category is its own table.** Products point to it with `category_id` (nullable = uncategorised). This gives the admin a category list to manage and the product form a dropdown, and avoids the same category being typed three different ways.
- **The cart is a table (`cart_items`).** A logged-in customer keeps their cart across devices and logins. If Member 3 prefers a session cart, the table can simply stay unused – nothing else depends on it.
- **`image` stores a file path**, for example `products/mouse.jpg` on the `public` disk. It is empty in the sample data.
- **`products.status = inactive`** hides a product from customers without deleting it.
- **`products.reorder_level`** (default 10) is the low-stock threshold: a product is low on stock when its quantity is at or below this number.

### Foreign keys and delete rules

| Child → parent | On delete | What it means |
| --- | --- | --- |
| `orders.user_id` → `users` | **Block** | A user who has orders can't be deleted |
| `logs.user_id` → `users` | **Block** | A user who appears in the logs can't be deleted |
| `order_items.product_id` → `products` | **Block** | A product that has been ordered can't be deleted |
| `products.category_id` → `categories` | **Block** | A category that still has products can't be deleted |
| `order_items.order_id` → `orders` | **Cascade** | Deleting an order deletes its items |
| `inventories.product_id` → `products` | **Cascade** | Deleting a product deletes its stock row |
| `cart_items.user_id` → `users` | **Cascade** | Deleting a user empties their cart |
| `cart_items.product_id` → `products` | **Cascade** | Deleting a product removes it from all carts |

**Archive instead of delete.** Because of the blocks above, records with history are never removed:

- User: set `status` to `inactive` (and refuse the login) instead of deleting.
- Product: set `status` to `inactive` instead of deleting.
- Order: set `order_status` to `cancelled` instead of deleting.

A blocked delete throws `Illuminate\Database\QueryException`. Check first (for example `$product->orderItems()->exists()`) or catch it and show a message.

### Input rules

Enforced by the database:

- `users.email` and `categories.category_name` are unique.
- `price`, `total_price` and `item_price` can't be negative (CHECK constraints).
- `quantity` in `order_items` and `cart_items` must be at least 1 (CHECK constraints).
- `available_quantity`, `reorder_level` and `quantity_in_stock` can't be negative (unsigned columns).
- `role`, `status`, `order_status` and `payment_method` only accept the listed values (ENUM).

The same rules as Laravel validation, so forms show a friendly error before the database is reached. Each model has a `rules()` method:

```php
$data = $request->validate(Product::rules());
$data = $request->validate(User::rules());                 // registration: email must be unique
$data = $request->validate(User::rules($user->user_id));   // editing: the user may keep their own email
$data = $request->validate(Order::rules());                // shipping_address, contact_number, payment_method
$data = $request->validate(CartItem::rules());             // product_id, quantity >= 1
$data = $request->validate(Category::rules());
$data = $request->validate(Inventory::rules());            // quantity_in_stock >= 0
```

On a public registration form, don't accept `role` or `status` from the request – leave them out so new accounts get the defaults (`customer`, `active`).

Passwords are hashed automatically by the `User` model. Never store or compare plain text; use `Auth::attempt()` or `Hash::check()`.

### Stock

Stock is stored in two places from the original design: `products.available_quantity` and `inventories.quantity_in_stock`. The models keep them equal automatically, as long as stock is changed through a model:

```php
$product->adjustStock(-2);                       // checkout: take 2 (throws RuntimeException if not enough stock)
$product->adjustStock(+2);                       // cancelled order: put 2 back
$product->inventory->updateStock(50);            // admin sets the count to 50
$product->update(['available_quantity' => 50]);  // also fine
```

A new product gets its inventory row automatically. Don't change stock with bulk queries such as `Product::where(...)->update([...])` or `DB::table(...)` – they skip the sync. Query 14 in `database/queries.sql` lists any product whose two numbers disagree.

### Shared queries

"Sales" means orders with `order_status = completed`.

| Need | Eloquent | SQL in `database/queries.sql` |
| --- | --- | --- |
| Total sales | `Order::totalSales()` | 1 |
| Total sales for a period | `Order::totalSales('2026-11-01', '2026-11-30')` | 2 |
| Sales per day | `Order::dailySales(7)` | 3 |
| Low stock | `Product::lowStock()->get()` | 4 |
| Recent orders | `Order::recent()->with('user')->limit(10)->get()` | 5 |
| One order with items | `Order::with('user', 'orderItems.product')->findOrFail($id)` | 6 |
| A customer's orders | `$user->orders()->recent()->get()` | 7 |
| Best sellers | `Product::bestSellers(5)` | 8 |
| Orders per status | `Order::countByStatus()` | 9 |
| Product catalog | `Product::active()->with('category')->get()` | 10 |
| A user's cart | `$user->cartItems()->with('product')->get()` | 11 |
| Recent activity log | `ActivityLog::recent()->with('user')->limit(20)->get()` | 12 |
| Dashboard numbers | | 13 |
| Stock mismatch check | | 14 |

Other helpers:

```php
ActivityLog::record($user, 'Logged in');   // write a log entry
$order->recalculateTotal();                // set total_price from the order's items
$user->isAdmin();  $user->isActive();  $product->isLowStock();
```

`order_date` defaults to today when an order is created without one.

Dates and times are saved in the app timezone, which is currently UTC (`'timezone'` in `config/app.php`). Until that is changed to `Asia/Manila`, an order placed before 8:00 AM Philippine time gets the previous day's date.

## Updating the exported database

After changing a migration or seeder, rebuild and re-export so Option B stays in step with Option A:

```bash
php artisan migrate:fresh --seed
mysqldump -u root -p --no-tablespaces --skip-comments ordering_system > database/ordering_system.sql
```

## Tests

```bash
php artisan test
```

The tests run on an in-memory SQLite database, so they never touch `ordering_system`. They cover the relationships, delete rules, input rules, seeders and the shared queries.
