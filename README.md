# CoZpaze

**CoZpaze** is a web-based platform for booking co-working spaces, designed to help students and freelancers find the perfect spot to work or study. The system features a token-based payment method and supports multiple user roles including Users, Location Owners, and Admins.

## 🚀 Features

### for Users
- **Search & Discover**: Find co-working spaces by University, keywords, or location.
- **Booking System**: Check availability and book spaces in real-time.
- **Wallet & Tokens**: Built-in wallet system to top-up tokens and pay for services.
- **Reviews**: Rate and review places you've visited.

### for Location Owners
- **Manage Listings**: Add, edit, and manage your co-working space details and images.
- **Booking Management**: View and manage incoming bookings.
- **Dashboard**: Track earnings and space usage.

### for Admins
- **System Management**: Overview of all users, locations, and transactions.
- **Top-up Approvals**: Verify and approve token top-up requests.
- **Content Moderation**: Manage reviews and keywords.

## 🛠 Tech Stack

- **Backend**: PHP (Native)
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, Tailwind CSS, JavaScript
- **Server**: Apache (via XAMPP recommended)

## ⚙️ Installation & Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/CoZpaze.git
   ```
   Place the project folder in your web server's root directory (e.g., `C:\xampp\htdocs\CoZpaze`).

2. **Database Setup**
   - Open phpMyAdmin (usually `http://localhost/phpmyadmin`).
   - Create a new database named **`co_working_pj`**.
   - Import the database file provided in the project (e.g., `database.sql`).
   
   *(Note: If you haven't exported the database yet, you can do so via phpMyAdmin or `mysqldump`).*

3. **Configuration**
   - Open `final_use/config/db_connect.php`.
   - Ensure the database credentials match your local setup:
     ```php
     $servername = "localhost";
     $username = "root";
     $password = ""; // Default XAMPP password is empty
     $dbname = "co_working_pj";
     ```

4. **Run the Project**
   - Open your browser and navigate to:
     `http://localhost/CoZpaze`

## 📂 Project Structure

- `final_use/` - Core application files
  - `admin/` - Admin control panel
  - `booking/` - Booking logic and cart
  - `config/` - Database connection
  - `location_owner/` - Vendor dashboard
  - `login_status/` - Authentication checks
  - `style/` - CSS files
  - `tokenshop/` - Payment and token system
  - `uni/` - University assets
  - `user/` - User profile and auth
  - `userwallets/` - Wallet management

---
© 2025 CoZpaze. All rights reserved.
