# Student Feedback Management System

A comprehensive web-based feedback management system for educational institutions, built with PHP and MySQL.

## Features

### 🔐 Multi-User Authentication
- **Admin Portal** - Complete system management
- **Faculty Portal** - View feedback and manage courses
- **Student Portal** - Submit feedback for courses
- **OTP Login** - Secure login via email OTP for all user types

### 📊 Core Functionality
- Student and faculty management
- Course assignment and tracking
- Feedback submission and analysis
- Real-time feedback reports
- Email notifications via SMTP

### 🎨 Modern UI/UX
- Responsive design for all devices
- Split-screen login pages with background images
- Animated components and smooth transitions
- Clean and intuitive interface

## Technology Stack

- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Email:** PHPMailer with Gmail SMTP
- **Server:** Apache (XAMPP/WAMP)

## Installation

### Prerequisites
- XAMPP/WAMP/LAMP installed
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Gmail account for SMTP

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/feedback-system.git
   cd feedback-system
   ```

2. **Database Setup**
   - Open phpMyAdmin
   - Create a new database named `feedback_system`
   - Import `database/schema.sql`
   - Create your admin account manually or through SQL

3. **Configure Email**
   - Open `config/email_config.php`
   - Update Gmail credentials with your own:
     ```php
     define('SMTP_USERNAME', 'your-email@gmail.com');
     define('SMTP_PASSWORD', 'your-app-password');
     ```
   - Get Gmail App Password: [Google Account Security](https://myaccount.google.com/apppasswords)
   - **Important:** Never commit real credentials to GitHub!

4. **Configure Database**
   - Open `config/database.php`
   - Update if needed (default works with XAMPP):
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'feedback_system');
     ```

5. **Start the Application**
   - Start Apache and MySQL in XAMPP
   - Open browser: `http://localhost/feedback_system/`

## Default Login Credentials

After setting up the database, you'll need to create your own admin, faculty, and student accounts through the admin panel or by inserting data directly into the database.

**Security Note:** Never commit real passwords or sensitive credentials to GitHub. Always change default passwords in production.

## Project Structure

```
feedback_system/
├── api/                    # API endpoints
│   ├── admin/             # Admin APIs
│   ├── faculty/           # Faculty APIs
│   ├── student/           # Student APIs
│   └── common/            # Shared APIs
├── config/                # Configuration files
│   ├── database.php       # Database config
│   ├── email_config.php   # Email config
│   └── email_sender.php   # Email functions
├── database/              # SQL files
│   ├── schema.sql         # Database schema
│   └── sample_data.sql    # Sample data
├── vendor/                # PHPMailer library
├── *.html                 # Frontend pages
└── README.md             # This file
```

## Key Features Explained

### OTP Login System
- Users can login using email OTP instead of password
- 6-digit OTP valid for 10 minutes
- Sent to registered email address
- Available for Admin, Faculty, and Students

### Password Reset
- Forgot password functionality
- OTP-based verification
- Secure password reset flow

### Feedback System
- Students submit feedback for courses
- Multiple rating parameters (Teaching Quality, Communication, etc.)
- Anonymous feedback option
- Faculty can view their feedback
- Admin can view all feedback

## Security Features

- Password hashing using bcrypt
- Session management
- SQL injection prevention (prepared statements)
- XSS protection
- CSRF token validation
- Secure OTP generation
- **Configuration files with credentials are gitignored**

## ⚠️ Security Best Practices

**Before deploying or pushing to GitHub:**

1. **Never commit real credentials** - The config files with actual credentials are excluded via .gitignore
2. **Use example files** - Copy `config/email_config.example.php` to `config/email_config.php` and add your credentials
3. **Change default passwords** - Always use strong, unique passwords in production
4. **Enable HTTPS** - Use SSL/TLS certificates in production
5. **Regular updates** - Keep PHP, MySQL, and libraries updated
6. **Backup regularly** - Maintain regular database backups

## Browser Support

- Chrome (recommended)
- Firefox
- Safari
- Edge
- Opera

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is open source and available under the MIT License.

## Support

For issues and questions, please open an issue on GitHub.

## Screenshots

### Login Pages
- Modern split-screen design
- Background images for each user type
- OTP login option

### Dashboards
- Admin: Complete system overview
- Faculty: Course and feedback management
- Student: Course enrollment and feedback submission

## Future Enhancements

- [ ] Mobile app
- [ ] Advanced analytics
- [ ] Export reports to PDF
- [ ] Multi-language support
- [ ] Dark mode
- [ ] Real-time notifications

---

**Developed with ❤️ for Educational Institutions**
