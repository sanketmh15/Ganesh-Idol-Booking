GANESH IDOL BOOKING SYSTEM (PHP + MySQL + XAMPP)

SETUP
1. Copy the folder "ganesh-idol-booking" into C:\xampp\htdocs\
2. Start Apache and MySQL in XAMPP.
3. Open http://localhost/phpmyadmin
4. Import setup.sql.
5. Open http://localhost/ganesh-idol-booking/setup-admin.php and create your first admin account.
6. Delete setup-admin.php after creating the admin account.
7. Open http://localhost/ganesh-idol-booking/

Default XAMPP database settings are in db.php (root user, blank password).
If your MySQL root account has a password, update db.php.

FEATURES
- Customer registration and login
- Idol catalogue and stock display
- Home delivery booking with address, PIN code, mobile number and delivery date
- Configurable delivery charge from admin settings
- Server-side validation, prepared statements and CSRF token
- Booking transaction with stock checking and decrement
- Admin booking status management
- Admin can add idols and update stock
- Customer booking history
- Cancelling a confirmed/pending booking restores reserved stock

IMPORTANT
- This is a starter project for local development. Before public deployment, add HTTPS,
  rate limiting, email/mobile verification, secure hosting configuration and backups.
- Change admin password after setup.
- Booking dates cannot be in the past. Delivery charges are stored on each booking so
  changing the current charge does not alter older booking totals.
