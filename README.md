# Inventory Management System

A simple PHP & MySQL inventory management system with user registration, login, product browsing, cart, order placement, and admin low-stock notifications.

## Features

- User registration and login
- Product listing with search
- Add to cart and checkout
- Orders history
- Low stock email notification to admin

## Setup Instructions

1. **Clone or copy the project files to your XAMPP `htdocs` directory.**

2. **Database Setup:**
   - Open phpMyAdmin.
   - Run the SQL script `inventory_schema.sql` to create all tables and insert sample data.

3. **Configure Database Connection:**
   - Edit `db.php` if your MySQL username, password, or database name are different.

4. **Email Notifications:**
   - For low stock notifications, set the admin email in `customer.php`.
   - For production, use SMTP with PHPMailer for reliable email delivery.

5. **Usage:**
   - Register a new user or use the sample admin user.
   - Browse products, add to cart, and checkout.
   - Orders and cart are managed per user.

## Default Admin User

- **Email:** admin@example.com
- **Password:** admin123 (hashed, you may need to reset this)

## Notes

- Images should be placed in the `images/` directory or update the image paths in the products table.
- For local email testing, use tools like MailHog or Mailtrap.

---

**Enjoy your inventory system!**
