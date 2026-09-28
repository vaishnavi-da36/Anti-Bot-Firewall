# 🛡️ Anti-Bot Firewall

A PHP-based web security system designed to detect, monitor, and prevent suspicious or automated form submissions.

## 📌 Project Overview

**Anti-Bot Firewall** is a web security and monitoring system developed using **PHP and MySQL**.

The system helps protect web applications from automated bot submissions by using multiple security mechanisms such as honeypot detection, rate limiting, IP monitoring, CAPTCHA validation, and security logging.

It also provides an administrative dashboard for monitoring suspicious activities and reviewing security events.

## 🚀 Features

* 🔐 Secure User & Admin Authentication
* 🤖 Bot Detection
* 🍯 Honeypot Protection
* 🧩 CAPTCHA Validation
* 🚦 Rate Limiting
* 🌐 IP Address Monitoring
* 🚫 Blocked IP Management
* 📋 Security Logs
* 📊 Security Reports
* ⚙️ Security Settings
* 🔎 Threat Activity Monitoring
* 📝 Protected Form Submission
* 👤 User Dashboard
* 🛡️ Admin Security Dashboard

## 🛠️ Technologies Used

* **Frontend:** HTML, CSS, JavaScript
* **Backend:** PHP
* **Database:** MySQL / MariaDB
* **Server:** Apache
* **Environment:** XAMPP
* **Database Management:** phpMyAdmin
* **Version Control:** Git & GitHub

## 🔒 Security Mechanisms

### Honeypot Protection

Uses hidden form fields to identify automated bots that fill fields that normal users cannot see.

### CAPTCHA

Helps distinguish human users from automated requests.

### Rate Limiting

Restricts excessive requests from the same source within a specific period.

### IP Monitoring

Tracks IP addresses associated with suspicious activities.

### Security Logging

Records security-related events for monitoring and analysis.

### Blocked IP Management

Allows administrators to manage IP addresses identified as suspicious or malicious.

## 📂 Project Structure

```text
AntiBotFirewall/
│
├── config/
│   └── db.php
│
├── admin_login.php
├── dashboard.php
├── index.php
├── logout.php
├── protected_form.php
├── request_audit.php
├── security_logs.php
├── security_reports.php
├── security_settings.php
├── threat_activity.php
├── blocked_ips.php
├── user_dashboard.php
│
├── README.md
└── .gitignore
```

> **Note:** Database configuration files containing credentials are excluded from the GitHub repository using `.gitignore`.

## ⚙️ Installation

### 1. Install XAMPP

Install XAMPP with:

* Apache
* MySQL / MariaDB
* PHP
* phpMyAdmin

### 2. Copy the Project

Place the project inside the XAMPP `htdocs` directory:

```text
xampp/htdocs/AntiBotFirewall
```

### 3. Create the Database

Open phpMyAdmin and create the required database.

Import the project's SQL database structure if an SQL file is provided.

### 4. Configure Database

Update the local database configuration in:

```text
config/db.php
```

Do not upload database passwords or sensitive credentials to GitHub.

### 5. Start XAMPP

Start:

```text
Apache
MySQL
```

### 6. Open the Application

Open:

```text
http://localhost/AntiBotFirewall/
```

## 👨‍💻 Author

**Vaishnavi**

B.Tech Artificial Intelligence and Data Science Student

GitHub: [@vaishnavi-da36](https://github.com/vaishnavi-da36)

## 📄 License

This project is created for educational and project-development purposes.
