# Online Study Group Coordination Tool (PHP)

🚀 **Live Demo**: (https://studygroup.42web.io) 

## ✨ Features
- **User authentication** (login/signup)
- **Study group management** (create/join groups)
- **Real-time chat** *(if applicable)*
- **Task scheduling** *(if applicable)*

## 🛠️ Setup Guide
1. **Clone the repository** to your `htdocs` folder:
   ```bash
   git clone https://github.com/stankigwa/online-study-group-php.git
   ```
2. **Database setup**:
   - Import `database.sql` to MySQL (via phpMyAdmin)
   - Configure `includes/config.php` with your credentials:
     ```php
     <?php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'your_username');
     // ...
     ?>
     ```
3. **Access the site**:
   - Open `http://localhost/online-study-group-php` in XAMPP
   - Or visit your Infinity Free live URL

## 📦 Dependencies
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx

*(Add more sections like "Screenshots" or "Contributing" if needed)*
