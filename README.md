<div align="center">
  <h1>🏆 SportsX Management System</h1>
  <p><strong>A comprehensive, high-performance web platform to manage sports events, teams, and players efficiently.</strong></p>
  <img src="https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Badge" />
  <img src="https://img.shields.io/badge/MySQL-5.7+-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL Badge" />
  <img src="https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind Badge" />
</div>

<br>

Welcome to the **Sports Management System**! This platform empowers administrators and players with role-based portals, rich real-time analytics, dynamic schedules, and a beautiful UI built on Tailwind CSS.

## ✨ Features

- 👑 **Admin Panel:** Manage your organization, seamlessly monitor events, and oversee all operational analytics.
- 🏃‍♂️ **Player Portal:** Dedicated dashboards for athletes to track participation, register for upcoming events, and view team rosters.
- 🔐 **Role-Based Authentication:** Ironclad access control tailored specifically for `admin` and `player` privileges.
- 🎨 **Modern & Responsive UI:** Minimal, glassmorphic interfaces designed for maximum usability across all devices.

## 🛠 Prerequisites

Make sure your environment meets the following requirements:
- 🐘 **PHP:** `>= 7.4` (8.x highly recommended)
- 🗄️ **MySQL:** `>= 5.7`
- 🌐 **Server Environment:** XAMPP, MAMP, or run directly via PHP's built-in development server.

## 🚀 Installation & Setup

**1. Clone the Repository**
```bash
git clone https://github.com/Isumit7781/sportsx.git
cd sportsx
```

**2. Database Setup**
Create a MySQL database named `sports_management`, and import our starter schema and mock data:
```bash
mysql -u root -p sports_management < "sports_management (3).sql"
```

**3. Configure Credentials**
Navigate to the `includes/` folder, duplicate our template file, and update your DB credentials (if they differ from default username `root` with a blank password):
```bash
cd includes
cp config.example.php config.php
```

**4. Run Live**
Spin up a local PHP server right from the root directory!
```bash
php -S localhost:8080
```
Then jump to 👉 [http://localhost:8080](http://localhost:8080) to see it live!

## 🤝 Contributing

We love community energy! Contributions, feature requests, and bug reports are all deeply welcome. Feel free to open a pull request or add suggestions in the issues page!

## 🌟 Credits & Core Team

This project is meticulously crafted and brought to life by:
- 👨‍💻 **Sumit Singh** ([@Isumit7781](https://github.com/Isumit7781)) - Lead Developer & Maintainer
- 👨‍💻 **OM Patil** ([@OmPatil078](https://github.com/OmPatil078)) - Lead Developer & Maintainer

🚀 *Engineered utilizing the powerhouse trio of PHP, MySQL, and Tailwind CSS.*

## 📄 License

This open-source code is provided under the terms of the [MIT License](LICENSE).
