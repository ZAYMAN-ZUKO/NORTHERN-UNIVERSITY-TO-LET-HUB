# Northern To-Let Hub

A comprehensive web application for connecting students with rental properties around Northern University Bangladesh (NUB). This project serves as a platform where property owners can list their properties and students can find suitable accommodation near the university campus.

## 🎯 Project Overview

**Northern To-Let Hub** is designed specifically for the Northern University Bangladesh community, providing a safe and reliable platform for property rental transactions between verified users.

## ✨ Features

### For Property Owners:
- **User Registration & Verification**: Sign up with Student ID or NID verification
- **Property Management**: Add, edit, and delete property listings
- **Photo Upload**: Multiple image support for property galleries
- **Request Management**: View and respond to rent requests from students
- **Dashboard**: Comprehensive overview of properties and requests
- **Location Services**: Distance calculation from NUB campus

### For Students (Renters):
- **Advanced Search**: Filter properties by type, price, distance, and amenities
- **Property Details**: Detailed property information with photos and reviews
- **Bookmark System**: Save favorite properties for easy access
- **Rent Requests**: Send requests to property owners
- **Reviews & Ratings**: Rate and review properties
- **Location-Based Search**: Find properties within walking distance from NUB

### General Features:
- **Responsive Design**: Mobile-friendly interface
- **User Authentication**: Secure login/logout system
- **Google Maps Integration**: Location display and distance calculation
- **Modern UI**: Bootstrap 5 with custom styling
- **Database Management**: MySQL database with proper relationships

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Server**: XAMPP (Apache, MySQL, PHP)
- **Additional**: Font Awesome icons

## 📁 Project Structure

```
NUB-Tolet/
├── assets/
│   └── css/
│       └── style.css          # Custom styles
├── config/
│   ├── config.php             # Site configuration
│   └── database.php           # Database connection
├── database/
│   └── schema.sql             # Database schema
├── includes/
│   └── functions.php          # Helper functions
├── uploads/                   # File upload directory
├── index.php                  # Homepage
├── login.php                  # User login
├── register.php               # User registration
├── properties.php             # Property listings
├── property-details.php       # Individual property page
├── dashboard.php              # User dashboard
├── add-property.php           # Add new property
├── bookmarks.php              # User bookmarks
├── about.php                  # About page
└── README.md                  # Project documentation
```

## 🚀 Installation & Setup

### Prerequisites
- XAMPP (Apache, MySQL, PHP)
- Web browser
- Google Maps API key (optional)

### Installation Steps

1. **Clone/Download the project**
   ```bash
   # Place the project in your XAMPP htdocs directory
   # C:\xampp\htdocs\NUB-Tolet
   ```

2. **Start XAMPP services**
   - Start Apache and MySQL from XAMPP Control Panel

3. **Create the database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the `database/schema.sql` file to create the database and tables

4. **Configure the application**
   - Update `config/config.php` with your database credentials if needed
   - Add your Google Maps API key in `config/config.php` (optional)

5. **Set up file permissions**
   - Ensure the `uploads/` directory is writable
   - Create subdirectories: `uploads/properties/`

6. **Access the application**
   - Open your browser and navigate to: `http://localhost/NUB-Tolet`

## 📊 Database Schema

The application uses the following main tables:

- **users**: User accounts (owners and renters)
- **properties**: Property listings
- **property_images**: Property photos
- **rent_requests**: Rental requests from students
- **bookmarks**: User bookmarks
- **reviews**: Property reviews and ratings
- **admins**: Admin accounts

## 🔧 Configuration

### Database Configuration
Update `config/database.php` with your database credentials:
```php
private $host = 'localhost';
private $db_name = 'northern_tolet_hub';
private $username = 'root';
private $password = '';
```

### Site Configuration
Update `config/config.php` for site settings:
```php
define('SITE_NAME', 'Northern To-Let Hub');
define('SITE_URL', 'http://localhost/NUB-Tolet');
define('GOOGLE_MAPS_API_KEY', 'YOUR_API_KEY_HERE');
```

## 👥 User Roles

### Property Owners
- Can register and verify their account
- Can add, edit, and delete property listings
- Can manage rent requests
- Can view property statistics

### Students/Renters
- Can register and verify their account
- Can search and filter properties
- Can bookmark favorite properties
- Can send rent requests
- Can review and rate properties

## 🔒 Security Features

- Password hashing using PHP's `password_hash()`
- Input sanitization and validation
- SQL injection prevention with prepared statements
- User verification system
- Session management

## 📱 Responsive Design

The application is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones
- Various screen sizes

## 🎨 UI/UX Features

- Modern Bootstrap 5 design
- Custom CSS styling
- Font Awesome icons
- Interactive elements
- User-friendly navigation
- Professional color scheme

## 🚧 Future Enhancements

- Real-time notifications
- Advanced search filters
- Property comparison feature
- Mobile app development
- Payment integration
- Advanced reporting system

## 📞 Support

For support or questions about this project, please contact:
- Email: info@northern.edu.bd
- University: Northern University Bangladesh

## 📄 License

This project is created for educational purposes as part of a university database project.

## 🤝 Contributing

This is a university project. For contributions or suggestions, please contact the development team.

---

**Note**: This project is designed specifically for Northern University Bangladesh and the surrounding community. Make sure to update the university coordinates and contact information as needed for your specific use case.
