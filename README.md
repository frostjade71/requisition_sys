<div align="center">

# <img src="assets/images/logoL3iii.webp" alt="LEYECO III Logo" height="50" style="vertical-align: middle; margin-bottom: 5px;"> **LEYECO III REQUISITION MANAGEMENT SYSTEM**

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net/)
[![Docker](https://img.shields.io/badge/Docker-✓-blue.svg)](https://www.docker.com/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-blue.svg)](https://www.mysql.com/)

A comprehensive web-based requisition management system developed by Computer Science Seniors at Holy Cross College of Carigara Incorporated for LEYECO III (Leyte III Electric Cooperative) featuring a 5-level sequential approval workflow for efficient procurement and resource management.

</div>

## Project Overview

This system streamlines the requisition process for LEYECO III, enabling employees to submit material and supply requests through an accessible online platform. It provides a structured 5-level approval workflow ensuring proper authorization, budget compliance, and inventory management while maintaining complete audit trails for all transactions.

## Key Features

### Public Access
- Submit requisition requests without login
- Track requests using RF Control Numbers
- Real-time status updates and approval timeline
- Auto-generated tracking numbers (Format: RF-YYYYMMDD-XXXX)

### Approver Tools
- Role-based dashboards for each approval level
- Request review and approval interface
- Status workflow: PENDING → LEVEL 1-5 APPROVAL → APPROVED/REJECTED
- Full activity history and audit trail
- Inventory and budget management fields

### Admin Console
- User and role management
- System analytics and reporting
- Approval level configuration
- Department and unit management
- Complete system oversight

## Quick Start

### With Docker (Recommended)
```bash
git clone https://github.com/frostjade71/requisition_sys requisition_sys
cd requisition_sys
docker-compose up -d
```

Access the application:
- **Web Interface**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081

### Default Logins
- **Admin**: admin@leyeco3.com / password123
- **Approvers**: approver@leyeco3.com / password123

> **Security Note**: Change default passwords immediately after first login!

## Approval Workflow

The system implements a 5-level sequential approval process:

1. **Level 1**: Recommending Approval - Section Head/Div. Head/Department Head
2. **Level 2**: Inventory Checked - Warehouse Section Head
3. **Level 3**: Budget Approval - Div. Supervisor/Budget Officer
4. **Level 4**: Checked By - Internal Auditor
5. **Level 5**: Approved By - General Manager

### Workflow Rules
- **Sequential Processing**: Each level must approve before the next level can review
- **Rejection Handling**: Any rejection at any level marks the entire request as REJECTED
- **Level-Based Access**: Approvers only see requests pending at their approval level
- **Complete Audit Trail**: All actions logged with approver name, timestamp, and remarks

## Departments

The system supports the following LEYECO III departments:
- Finance Services Department
- Institutional Services Department
- Technical Services Department
- Office of the General Manager

## Security Features

- **Password Hashing**: bcrypt encryption for all passwords
- **Prepared Statements**: PDO with parameterized queries
- **Input Sanitization**: All user inputs are sanitized and validated
- **Session Management**: Secure session handling with HTTP-only cookies
- **CSRF Protection**: Token-based CSRF prevention
- **SQL Injection Prevention**: Prepared statements throughout the application

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

---

## **Credits** <img src="assets/images/HCCCI.webp" alt="LEYECO III Logo" height="24" style="vertical-align: middle;"> HCCCI Computer Science Seniors

> #### Documentation & QA/Testers:
- Sophia Caridad
- Loren Mae Pascual
- Fauna Dea Opetina
- Agnes Osip
- Zxyrah Mae Indemne

> #### **Developer**
- **Jaderby Peñaranda**

  [![Website](https://img.shields.io/badge/🌏-jaderbypenaranda.link-1e88e5)](https://jaderbypenaranda.link/) [![Email](https://img.shields.io/badge/📩-Contact-4caf50)](mailto:jaderbypenaranda@gmail.com)

---

<div align="left">
<span><b>Built for</b></span>
  <img src="assets/images/logo_leyeco3.webp" alt="LEYECO III Logo" height="40" style="vertical-align: middle; margin-right: 12px;">
  <span><i>Lighting Houses, Lighting Homes, Lighting Hopes</i></span>
</div>

---

**Version**: 1.0.0
**Last Updated**: December 5, 2025
