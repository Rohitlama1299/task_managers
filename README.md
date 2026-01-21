#Task Manager Web Application

A modern, role-based web application for managing tasks within an organization. Built with PHP, MySQL, HTML, and styled with Tailwind CSS.

##🚀 Features

- **🔐 Role-Based Access Control (RBAC)**
    - **Admin**: Full system control. Can create/manage users, assign tasks to Managers and Users.
    - **Manager**: Can view assigned tasks and delegate them to Users.
    - **User**: Can view and update the status of tasks assigned to them.

- **📝 Modern Task Management**
    - Create, assign, update, and track tasks.
    - Intuitive dashboard for each user role.

- **🛡️ Secure Authentication**
    - Secure login and session management.
    - Protected routes based on user roles.

- **🎨 Modern & Responsive UI**
    - Clean interface built with Tailwind CSS.
    - Fully responsive design that works on all devices.

## 🛠️ Tech Stack

- **Backend**: PHP
- **Database**: MySQL
- **Frontend**: HTML, Tailwind CSS
- **Server**: XAMPP / WAMP / LAMP or equivalent


## ⚙️ Installation & Setup

Follow these steps to set up the project locally:

1.  **Prerequisites**
    - Install [XAMPP](https://www.apachefriends.org/) (includes Apache, PHP, MySQL) or a similar stack.

2.  **Clone the Repository**
    ```bash
    git clone https://github.com/Rohitlama1299/task_managers.git
    cd task_managers
    ```

3.  **Database Setup**
    - Start Apache and MySQL from your XAMPP Control Panel.
    - Open **phpMyAdmin** (usually at `http://localhost/phpmyadmin`).
    - Create a new database (e.g., `task_manager_db`).
    - Import the `database.sql` file (if provided in the project) into your new database.
    - If no `.sql` file is provided, look for database configuration in `includes/config.php` or similar and update credentials.

4.  **Configure Database Connection**
    - Locate the database configuration file (e.g., `includes/db_connection.php`).
    - Update the following details to match your local setup:
    ```php
    <?php
    $host = 'localhost';
    $dbname = 'task_manager_db'; // Your database name
    $username = 'root';           // Default XAMPP username
    $password = '';               // Default XAMPP password is empty
    ?>
    ```

5.  **Run the Application**
    - Move the entire `task_managers` folder to your XAMPP `htdocs` directory (e.g., `C:\xampp\htdocs\` on Windows).
    - Open your browser and go to: `http://localhost/task_managers`

## 👥 Default Login Credentials

*You may need to register the first admin user via a signup page or insert one directly into the database. If pre-configured, credentials might be:*

- **Admin Panel**: `http://localhost/task_managers/auth/login.php`
    - Username: `admin@example.com`
    - Password: `admin123`
- **Manager Panel**: Same login page (role detected automatically).
- **User Panel**: Same login page (role detected automatically).

*(**Important:** Change these default credentials in a production environment!)*

## 📸 Screenshots

*(You can add screenshots of your dashboard here)*
> Example:
> ![Admin Dashboard](screenshots/admin-dashboard.png)
> *Caption: The admin dashboard showing user management and task overview.*

## 📄 License

This project is licensed for personal and educational use. Please contact the repository owner for other licensing inquiries.

---

## 🤝 Contributing

Contributions, issues, and feature requests are welcome. Feel free to check the [issues page](#) if you want to contribute.

## 👤 Author

**Rohit Lama**
- GitHub: [@Rohitlama1299](https://github.com/Rohitlama1299)

## 📊 Project Statistics

- **Languages**: PHP 95.0%, Hack 4.0%, CSS 1.0%
- **Last Commit**: May 13, 2025 - "manage your task"
- **Repository**: https://github.com/Rohitlama1299/task_managers
