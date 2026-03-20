# Security Policy

## Reporting Security Issues

If you discover a security vulnerability in this project, please report it by emailing the maintainer. Please do not create public GitHub issues for security vulnerabilities.

## Security Best Practices

### For Developers

1. **Never Commit Credentials**
   - Real email passwords
   - Database passwords
   - API keys
   - Any sensitive information

2. **Use Example Config Files**
   - Copy `.example.php` files to actual config files
   - Add your credentials locally
   - These files are gitignored automatically

3. **Password Security**
   - All passwords are hashed using bcrypt
   - Never store plain text passwords
   - Use strong password policies

4. **Input Validation**
   - All user inputs are sanitized
   - SQL injection prevention via prepared statements
   - XSS protection enabled

### For Deployment

1. **HTTPS Only**
   - Always use SSL/TLS certificates
   - Never transmit credentials over HTTP

2. **Database Security**
   - Use strong database passwords
   - Restrict database access
   - Regular backups

3. **File Permissions**
   - Restrict write permissions
   - Protect config files
   - Secure upload directories

4. **Regular Updates**
   - Keep PHP updated
   - Update MySQL regularly
   - Update PHPMailer library

## Configuration Files

### Files with Credentials (Gitignored)
- `config/email_config.php`
- `config/email_sender.php`

### Example Files (Committed to Git)
- `config/email_config.example.php`

## Known Security Measures

✅ Password hashing (bcrypt)  
✅ Prepared statements (SQL injection prevention)  
✅ Session management  
✅ XSS protection  
✅ CSRF token validation  
✅ Secure OTP generation  
✅ Email validation  
✅ Input sanitization  

## Recommendations

- Change all default credentials
- Use environment variables for production
- Enable error logging (not display)
- Implement rate limiting for login attempts
- Add CAPTCHA for public forms
- Regular security audits

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |

## License

This security policy is part of the project and follows the same license.
