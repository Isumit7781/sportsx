# Sports Management System

A comprehensive web-based platform built with PHP and MySQL to manage sports events, players, and registrations efficiently. It includes role-based access for both Administrators and Players.

## Features

- **Admin Panel:** Manage users, monitor events, and oversee overall operations.
- **Player Portal:** Allow players to register, log in, and view available events or past participation.
- **Role-Based Authentication:** Distinct access control for Admin (`admin`) and Player (`player`) roles.
- **Responsive UI:** Minimal and clean interface optimized for usability.

## Requirements

- **PHP** ( >= 7.4 or 8.x recommended)
- **MySQL** ( >= 5.7 or 8.x )
- A local server environment like XAMPP, MAMP, or run using PHP's built-in server.

## Installation & Setup

1. **Clone the Repository**
   ```bash
   git clone https://github.com/yourusername/sports-management.git
   cd sports-management
   ```

2. **Database Setup**
   - Create a MySQL database named `sports_management`.
   - Import the included SQL file `sports_management (3).sql` into your database to create the required tables and initial mock data:
     ```bash
     mysql -u root -p sports_management < "sports_management (3).sql"
     ```

3. **Configuration**
   - Navigate to the `includes` folder.
   - Rename `config.example.php` to `config.php`.
   - Open `config.php` and update the database credentials (username, password, etc.) if they differ from the default (`root` / empty password).
   - *Note: Ensure the `BASE_URL` in `config.php` matches your directory structure (e.g., `/` if running directly from this directory, or `/sports-management/` if in a subfolder of your web root).*

4. **Run the Application**
   - If using PHP built-in server, start it inside the project root:
     ```bash
     php -S localhost:8000
     ```
   - Open your browser and navigate to `http://localhost:8000`.

## Contributing

Contributions, issues, and feature requests are welcome. Feel free to check the issues page if you want to contribute.

## License

This project is open-sourced under the [MIT License](LICENSE).
