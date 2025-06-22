# site-sistema

<div align="center">
  <p><em>Empowering Seamless Attendance and User Engagement Innovation</em></p>

  <img alt="last-commit" src="https://img.shields.io/github/last-commit/Hermes-neptune/site-sistema?style=flat&logo=git&logoColor=white&color=0080ff">
  <img alt="repo-top-language" src="https://img.shields.io/github/languages/top/Hermes-neptune/site-sistema?style=flat&color=0080ff">
  <img alt="repo-language-count" src="https://img.shields.io/github/languages/count/Hermes-neptune/site-sistema?style=flat&color=0080ff">
  
  <p><em>Built with the tools and technologies:</em></p>
  <img alt="JSON" src="https://img.shields.io/badge/JSON-000000.svg?style=flat&logo=JSON&logoColor=white">
  <img alt="Markdown" src="https://img.shields.io/badge/Markdown-000000.svg?style=flat&logo=Markdown&logoColor=white">
  <img alt="Composer" src="https://img.shields.io/badge/Composer-885630.svg?style=flat&logo=Composer&logoColor=white">
  <img alt="JavaScript" src="https://img.shields.io/badge/JavaScript-F7DF1E.svg?style=flat&logo=JavaScript&logoColor=black">
  <img alt="PHP" src="https://img.shields.io/badge/PHP-777BB4.svg?style=flat&logo=PHP&logoColor=white">
</div>

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
- [Usage](#usage)
- [Project Structure](#project-structure)
- [Security](#security)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

## Overview

site-sistema is a versatile web application framework that simplifies user authentication, attendance management, and personalized user interactions. Designed for developers, it integrates secure login, QR code-based check-ins, and dynamic content delivery into a cohesive system.

**Why site-sistema?**

This project streamlines complex workflows and enhances user engagement through features like personalized profiles, messaging, news updates, and theme customization.

## Features

- **🛠️ User Authentication**
  - Secure login and session management
  - User registration with data validation
  - Password recovery via email
  - Protection against brute force and SQL injection attacks

- **🎯 Attendance Tracking**
  - Dynamic QR code generation for events
  - Camera-based code scanning and validation
  - Immediate visual feedback on check-in
  - Attendance history in user profiles
  - Administrative reporting tools

- **📸 Profile Customization**
  - Profile photo upload and management
  - Personal information updates
  - Notification preferences
  - Activity history tracking
  - Visual theme selection

- **📰 Content Delivery**
  - Personalized messaging system
  - Updatable news feed
  - User credit balance display
  - Real-time notifications
  - Adaptive content based on user profile

- **🔒 Secure Database Connection**
  - Parameterized queries preventing SQL injection
  - Connection pooling for performance optimization
  - Atomic transactions ensuring data integrity
  - Automated backup and recovery strategies
  - Audit logging for critical operations

- **🎨 UI Enhancements**
  - Light/dark theme toggle
  - Responsive navigation for all devices
  - Visual feedback for user actions
  - Subtle animations improving interactivity
  - WCAG-compliant accessibility features

## Getting Started

### Prerequisites

- Web server supporting PHP (Apache, Nginx)
- PHP 7.4 or higher
- PostgreSQL 12 or higher
- Composer package manager
- Modern web browser with JavaScript support

### Installation

1. **Clone the repository:**
   ```sh
   git clone https://github.com/Hermes-neptune/site-sistema
   ```

2. **Navigate to the project directory:**
   ```sh
   cd site-sistema
   ```

3. **Install dependencies:**
   ```sh
   composer install
   ```

4. **Configure the database:**
   - Create a PostgreSQL database
   - Set up access credentials in the configuration file
   - Run migrations to create tables:
     ```sh
     php migrations/run.php
     ```

5. **Configure the web server:**
   - Point document root to the project's public directory
   - Set appropriate file and directory permissions
   - Enable required modules (rewrite, ssl)

## Usage

Start the application with:

```sh
php -S localhost:8000 -t public/
```

Visit `http://localhost:8000` in your browser to access the application.

### Key Operations

- **User Registration:** Create a new account via the registration form
- **Authentication:** Log in with your credentials
- **Attendance Check-in:** Scan QR codes to register attendance
- **Profile Management:** Update personal information and preferences
- **Content Access:** View messages, news, and credit balance

## Project Structure

```
site-sistema/
├── app/                  # Core application logic
│   ├── controllers/      # Request handlers
│   ├── models/           # Data models
│   └── views/            # View templates
├── config/               # Configuration files
├── public/               # Publicly accessible files
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript files
│   └── images/           # Images and visual assets
├── vendor/               # Composer dependencies
├── tests/                # Automated tests
└── composer.json         # Composer configuration
```

## Security

Security is a core focus of this application, with implementations including:

- Protection against XSS, CSRF, SQL Injection, and Session Hijacking
- Secure password storage using bcrypt hashing
- HTTPS communication
- Encrypted storage of sensitive personal data
- Optional multi-factor authentication
- Granular permission levels
- Automatic session timeout
- Suspicious activity logging and alerts

## Testing

Run the test suite with:

```sh
vendor/bin/phpunit
```

Tests cover authentication, data handling, interface functionality, database integration, and performance under load.

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

---

<div align="center">
  <p>Developed as part of a Final Year Project (TCC) by MTSmalow</p>
  <p>© 2025 All Rights Reserved</p>
</div>
