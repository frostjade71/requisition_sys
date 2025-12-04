# LEYECO III Requisition Management System

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net/)
[![Docker](https://img.shields.io/badge/Docker-✓-blue.svg)](https://www.docker.com/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-blue.svg)](https://www.mysql.com/)

A comprehensive web-based requisition management system with a 5-level sequential approval workflow for LEYECO III Electric Company.

## 📋 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Project Structure](#-project-structure)
- [Getting Started](#-getting-started)
- [Docker Setup & Installation](#-docker-setup--installation)
- [Database Information](#-database-information)
- [Usage Guide](#-usage-guide)
- [Approval Workflow](#-approval-workflow-rules)
- [Development](#-development)
- [Troubleshooting](#-troubleshooting)
- [Contributing](#-contributing)
- [Support](#-support)
- [License](#-license)

## 🚀 Features

- **Public Request Submission** - Employees can submit requisition requests without login
- **Unique RF Control Numbers** - Auto-generated tracking numbers (Format: RF-YYYYMMDD-XXXX)
- **Request Tracking** - Public tracking system with visual approval timeline
- **5-Level Sequential Approval** - Structured approval workflow
- **Approver Dashboard** - Role-based dashboards for approvers
- **Real-time Status Updates** - Track requests through all approval stages
- **Responsive Design** - Mobile-friendly interface
- **Email Notifications** - Automatic email alerts at each approval stage
- **Documentation** - Comprehensive documentation and API references
- **Audit Trail** - Complete history of all actions taken on each request

## 📋 Tech Stack

- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Backend**: PHP 8.2
- **Database**: MySQL 8.0
- **Environment**: Docker & Docker Compose

## 🏗️ Project Structure

> **Note**: This project follows a modular structure for better code organization and maintainability.

```
requisition_sys/
├── api/                    # API endpoints
│   ├── authenticate.php    # User authentication
│   ├── get_request_status.php
│   ├── process_approval.php
│   └── submit_request.php
├── approver/              # Approver pages
│   ├── dashboard.php
│   ├── login.php
│   ├── logout.php
│   └── view_request.php
├── assets/                # Static assets
│   ├── css/
│   ├── js/
│   └── images/
├── config/                # Configuration
│   ├── config.php
│   └── database.php
├── database/              # Database files
│   ├── schema.sql
│   └── seed.sql
├── includes/              # Common includes
│   ├── header.php
│   ├── footer.php
│   └── functions.php
├── middleware/            # Middleware
│   └── auth.php
├── public/                # Public pages
│   ├── request_form.php
│   └── track_request.php
├── .env                   # Environment variables
├── .gitignore
├── docker-compose.yml
├── Dockerfile
└── index.php             # Homepage
```

## 🐳 Docker Setup & Installation

### Prerequisites

- Docker Desktop installed and running
- Ports 8080, 3306, and 8081 available

### Step 1: Start Docker Services

```bash
# Navigate to project directory
cd c:\xampp\htdocs\requisition_sys

# Start all services
docker-compose up -d
```

### Step 2: Verify Services

```bash
# Check running containers
docker-compose ps

# You should see 3 services running:
# - requisition_web (PHP-Apache)
# - requisition_db (MySQL)
# - requisition_phpmyadmin
```

### Step 3: Access the Application

- **Main Application**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
  - Server: mysql
  - Username: requisition_user
  - Password: requisition_pass_2024

## 📊 Database Information

### Default Credentials

**Database**:
- Host: mysql (or localhost:3306)
- Database: requisition_db
- Username: requisition_user
- Password: requisition_pass_2024
- Root Password: root_pass_2024

### Sample Approver Accounts

All accounts use password: `password123`

| Level | Email | Role |
|-------|-------|------|
| 1 | juan.delacruz@leyeco3.com | Section Head |
| 2 | maria.santos@leyeco3.com | Warehouse Section Head |
| 3 | pedro.reyes@leyeco3.com | Budget Officer |
| 4 | ana.garcia@leyeco3.com | Internal Auditor |
| 5 | roberto.fernandez@leyeco3.com | General Manager |
| Admin | admin@leyeco3.com | System Administrator |

## 🎯 Usage Guide

### For Employees (Public Access)

1. **Submit a Request**:
   - Go to http://localhost:8080
   - Click "Submit New Request"
   - Fill in requester information
   - Add items using the "+ Add Item" button
   - Submit the form
   - Save the generated RF Control Number

2. **Track a Request**:
   - Click "Track Request"
   - Enter your RF Control Number
   - View request status and approval timeline

### For Approvers

1. **Login**:
   - Click "Approver Login"
   - Enter email and password
   - You'll be redirected to your dashboard

2. **Review Requests**:
   - View pending requests at your approval level
   - Click "Review" to see full details
   - Add remarks (optional)
   - Click "Approve" or "Reject"

3. **Approval Workflow**:
   - Level 1: Recommending Approval
   - Level 2: Inventory Checked
   - Level 3: Budget Approval
   - Level 4: Checked By
   - Level 5: Final Approval

## 🔄 Approval Workflow Rules

1. **Sequential Processing**: Each level must approve before the next level can act
2. **Rejection**: Any rejection at any level marks the entire request as REJECTED
3. **Level Access**: Approvers only see requests at their current level
4. **Audit Trail**: All approvals are logged with approver name, timestamp, and remarks

## 🛠️ Development

### Prerequisites

- Docker and Docker Compose
- Git
- Composer (for PHP dependencies)

### Setup Development Environment

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/requisition-system.git
   cd requisition-system
   ```

2. Copy the environment file:
   ```bash
   cp .env.example .env
   ```

3. Start the development environment:
   ```bash
   docker-compose up -d
   ```

4. Install PHP dependencies:
   ```bash
   docker-compose exec php-apache composer install
   ```

5. Generate application key:
   ```bash
   docker-compose exec php-apache php artisan key:generate
   ```

### Development Commands

```bash
# Stop all services
docker-compose down

# Restart services
docker-compose restart

# View logs
docker-compose logs -f

# Access MySQL CLI
docker-compose exec mysql mysql -u requisition_user -p requisition_db

# Access PHP container
docker-compose exec php-apache bash

# Run tests
docker-compose exec php-apache php vendor/bin/phpunit

# Rebuild containers
docker-compose up -d --build
```

### Coding Standards

This project follows PSR-12 coding standards. To check and fix code style:

```bash
docker-compose exec php-apache composer cs-check
docker-compose exec php-apache composer cs-fix
```

## 🔧 Troubleshooting

### Common Issues

1. **Port Conflicts**
   - If you encounter port conflicts, you can change the ports in the `.env` file.

2. **Database Connection Issues**
   - Ensure the MySQL service is running: `docker-compose ps`
   - Check database credentials in `.env`
   - Try rebuilding the containers: `docker-compose up -d --build`

3. **Permission Issues**
   - Ensure the storage directory is writable:
     ```bash
     docker-compose exec php-apache chmod -R 777 storage/
     ```

### Debugging

- View PHP error logs:
  ```bash
  docker-compose logs php-apache
  ```

- Check MySQL logs:
  ```bash
  docker-compose logs mysql
  ```

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. Fork the repository
2. Create a new branch: `git checkout -b feature/your-feature`
3. Make your changes and commit them: `git commit -m 'Add some feature'`
4. Push to the branch: `git push origin feature/your-feature`
5. Submit a pull request

### Code of Conduct

Please read our [Code of Conduct](CODE_OF_CONDUCT.md) before contributing.

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Support

If you encounter any issues or have questions, please:

1. Check the [issues](https://github.com/yourusername/requisition-system/issues) page
2. Create a new issue if your problem isn't listed
3. Email support@leyeco3.com for urgent matters

## 📜 Changelog

Detailed changes for each release are documented in the [CHANGELOG.md](CHANGELOG.md).

## 🚀 Roadmap

- [ ] Mobile application for request submission
- [ ] Integration with inventory management system
- [ ] Advanced reporting and analytics
- [ ] Multi-language support
- [ ] Two-factor authentication for approvers

## 👥 Contributors

- [Your Name](https://github.com/yourusername)
- [Contributor Name](https://github.com/contributor)

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

<div align="center">
  <sub>Built with ❤️ by LEYECO III IT Team</sub>
</div>

# Re-run schema and seed files
SOURCE /docker-entrypoint-initdb.d/01-schema.sql;
SOURCE /docker-entrypoint-initdb.d/02-seed.sql;
```

### Backup Database

```bash
docker-compose exec mysql mysqldump -u requisition_user -p requisition_db > backup.sql
```

### Restore Database

```bash
docker-compose exec -T mysql mysql -u requisition_user -p requisition_db < backup.sql
```

## 🔒 Security Features

- **Password Hashing**: bcrypt for all passwords
- **Prepared Statements**: PDO with parameterized queries
- **Input Sanitization**: All user inputs are sanitized
- **Session Management**: Secure session handling
- **CSRF Protection**: Token-based CSRF prevention
- **SQL Injection Prevention**: Prepared statements throughout

## 📱 Responsive Design

The system is fully responsive and works on:
- Desktop (1920px+)
- Tablet (768px - 1919px)
- Mobile (< 768px)

## 🐛 Troubleshooting

### Port Already in Use

```bash
# Check what's using the port
netstat -ano | findstr :8080

# Change ports in docker-compose.yml if needed
```

### Database Connection Failed

```bash
# Check MySQL is running
docker-compose ps

# Check logs
docker-compose logs mysql

# Restart MySQL
docker-compose restart mysql
```

### Permission Issues

```bash
# Fix permissions
docker-compose exec php-apache chown -R www-data:www-data /var/www/html
docker-compose exec php-apache chmod -R 755 /var/www/html
```

## 📝 Sample Data

The system includes 3 sample requisition requests:

1. **RF-20251203-0001**: At Level 3 (Budget Approval)
2. **RF-20251203-0002**: At Level 1 (Recommending Approval)
3. **RF-20251203-0003**: Fully Approved (All 5 levels)

## 🎨 Customization

### Change Company Name

Edit `config/config.php`:
```php
define('APP_NAME', 'Your Company Name');
```

### Add Departments

Edit `config/config.php`:
```php
define('DEPARTMENTS', [
    'Your Department 1',
    'Your Department 2',
    // ...
]);
```

### Modify Approval Levels

Edit `config/config.php`:
```php
define('APPROVAL_LEVELS', [
    1 => 'Your Level 1 Description',
    // ...
]);
```

## 📞 Support

For issues or questions:
- Check the troubleshooting section
- Review Docker logs: `docker-compose logs`
- Verify database connection in phpMyAdmin

## 📄 License

Copyright © 2024 LEYECO III Electric Company. All rights reserved.

---

**Version**: 1.0.0  
**Last Updated**: December 3, 2024
