# Northern To-Let Hub - Installation Guide

## 🚀 Quick Start

### Prerequisites
- XAMPP (Apache, MySQL, PHP 7.4+)
- Web browser
- Google Maps API key (optional)

### Step 1: Download and Setup
1. Download the project files
2. Extract to `C:\xampp\htdocs\NUB-Tolet`
3. Start XAMPP Control Panel
4. Start Apache and MySQL services

### Step 2: Database Setup
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create a new database named `northern_tolet_hub`
3. Import the database schema:
   - Click on the database
   - Go to "Import" tab
   - Choose file: `database/schema.sql`
   - Click "Go"

### Step 3: Configuration
1. Open `http://localhost/NUB-Tolet/install.php`
2. Fill in the installation form:
   - Database Host: `localhost`
   - Database Name: `northern_tolet_hub`
   - Database Username: `root`
   - Database Password: (leave empty for default XAMPP)
   - Site URL: `http://localhost/NUB-Tolet`
   - Admin credentials (create your admin account)
3. Click "Install Application"

### Step 4: Access the Application
- **Main Site**: `http://localhost/NUB-Tolet`
- **Admin Panel**: `http://localhost/NUB-Tolet/admin/login.php`

## 🔧 Manual Configuration (Alternative)

If you prefer manual setup:

### 1. Database Configuration
Edit `config/database.php`:
```php
private $host = 'localhost';
private $db_name = 'northern_tolet_hub';
private $username = 'root';
private $password = ''; // Your MySQL password
```

### 2. Site Configuration
Edit `config/config.php`:
```php
define('SITE_URL', 'http://localhost/NUB-Tolet');
define('GOOGLE_MAPS_API_KEY', 'YOUR_API_KEY_HERE'); // Optional
```

### 3. File Permissions
Ensure these directories are writable:
- `uploads/`
- `uploads/properties/`

## 📱 Default Accounts

After installation, you can use these default accounts:

### Admin Account
- Username: `admin`
- Password: `password` (change this after first login)

### Test User Accounts
Create test accounts through the registration page:
- Property Owner account
- Student/Renter account

## 🎯 Features Overview

### For Property Owners:
- ✅ Register and verify account
- ✅ Add/edit/delete properties
- ✅ Upload multiple property images
- ✅ Manage rent requests
- ✅ View property statistics
- ✅ Toggle property availability

### For Students/Renters:
- ✅ Register and verify account
- ✅ Search and filter properties
- ✅ View detailed property information
- ✅ Bookmark favorite properties
- ✅ Send rent requests
- ✅ Review and rate properties

### Admin Features:
- ✅ User management
- ✅ Property management
- ✅ Request monitoring
- ✅ System statistics
- ✅ Reports and analytics

## 🔍 Testing the Application

### 1. Create Test Accounts
1. Go to `http://localhost/NUB-Tolet/register.php`
2. Create a property owner account
3. Create a student/renter account

### 2. Add Test Properties
1. Login as property owner
2. Go to "Add Property"
3. Fill in property details
4. Upload property images
5. Submit the property

### 3. Test Search and Booking
1. Login as student/renter
2. Browse properties
3. Use search and filters
4. Bookmark properties
5. Send rent requests

### 4. Test Admin Panel
1. Go to `http://localhost/NUB-Tolet/admin/login.php`
2. Login with admin credentials
3. View statistics and manage users

## 🛠️ Troubleshooting

### Common Issues:

**1. Database Connection Error**
- Check if MySQL is running in XAMPP
- Verify database credentials in `config/database.php`
- Ensure database `northern_tolet_hub` exists

**2. File Upload Issues**
- Check if `uploads/` directory exists and is writable
- Verify PHP upload settings in `php.ini`

**3. Page Not Found (404)**
- Ensure Apache is running
- Check if files are in correct directory
- Verify `.htaccess` file is present

**4. Permission Denied**
- Check file permissions on `uploads/` directory
- Ensure web server has write access

### Debug Mode:
To enable debug mode, edit `config/config.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📊 Database Structure

The application uses these main tables:
- `users` - User accounts
- `properties` - Property listings
- `property_images` - Property photos
- `rent_requests` - Rental requests
- `bookmarks` - User bookmarks
- `reviews` - Property reviews
- `admins` - Admin accounts

## 🔒 Security Features

- Password hashing with PHP's `password_hash()`
- SQL injection prevention with prepared statements
- Input sanitization and validation
- User verification system
- Session management
- File upload security

## 📱 Mobile Responsiveness

The application is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones
- Various screen sizes

## 🎨 Customization

### Changing University Information:
Edit `config/config.php`:
```php
define('UNIVERSITY_LAT', 23.8103);  // Your university latitude
define('UNIVERSITY_LNG', 90.4125);  // Your university longitude
```

### Styling:
Edit `assets/css/style.css` for custom styling.

### Adding Features:
- Add new pages in the root directory
- Update navigation in all pages
- Add new database tables as needed

## 📞 Support

For technical support or questions:
- Check the README.md file
- Review the code comments
- Contact the development team

## 🎉 Success!

Once installed, you should have a fully functional property rental platform for Northern University Bangladesh. The application includes all requested features and is ready for your university project submission.

**Next Steps:**
1. Test all features thoroughly
2. Add sample data for demonstration
3. Customize the design if needed
4. Prepare your project presentation
5. Document any customizations made

Good luck with your university project! 🎓
