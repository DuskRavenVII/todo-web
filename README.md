# ToDo-Web 📝

A secure and responsive To-Do List web application built with PHP, MySQL, Bootstrap, and JavaScript. Features user authentication, task management, and a clean modern interface.

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?style=flat-square&logo=bootstrap)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=flat-square&logo=javascript)

---

## ✨ Features

- 🔐 **User Authentication**
  - Sign In / Sign Up system
  - Password hashing with `password_hash()`
  - Session management

- 📋 **Task Management**
  - ✅ Add new tasks with title, description, and due date
  - ✏️ Edit existing tasks
  - ❌ Delete tasks with confirmation
  - 📅 Due date tracking with visual indicators

- 🎨 **User Interface**
  - Responsive design (mobile, tablet, desktop)
  - Color-coded task status (Overdue, Today, Soon, Upcoming)
  - Bootstrap 5 components
  - Clean and modern UI

- 🔒 **Security Features**
  - SQL injection prevention (prepared statements)
  - XSS protection (htmlspecialchars)
  - User authorization (tasks belong to users)
  - Password verification

---

## 🛠️ Technologies Used

| Category       | Technology          |
| -------------- | ------------------- |
| **Backend**    | PHP 7.4+            |
| **Database**   | MySQL / MariaDB     |
| **Frontend**   | HTML5, CSS3         |
| **Framework**  | Bootstrap 5         |
| **JavaScript** | Vanilla JS          |
| **Server**     | Apache (XAMPP/WAMP) |

---

## 📋 Project Structure

todo-web/
├── index.php # Main application file
├── db.php # Database configuration and setup
├── static/
│ ├── css/
│ │ └── style.css # Custom styles
│ ├── js/
│ │ └── script.js # Client-side JavaScript
│ └── bootstrap/ # Bootstrap files
│ ├── css/
│ └── js/
├── .gitignore # Git ignore file
└── README.md # This file

---

## 🚀 Setup Instructions

### Prerequisites

- ✅ XAMPP, WAMP, or MAMP installed
- ✅ PHP 7.4 or higher
- ✅ MySQL 5.7 or higher
- ✅ Web browser (Chrome, Firefox, Edge)

---

### Step 1: Clone the Repository

git clone https://github.com/DuskRavenVII/todo-web.git

cd todo-web


### Step 2: Set Up the Database
Option A: Automatic Setup (Recommended)
Start XAMPP/WAMP
Open your browser and go to http://localhost/phpmyadmin
The db.php file will automatically create:
Database: todo_db
Table: users
Table: tasks
Option B: Manual Setup

```
CREATE DATABASE todo_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE todo_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    due_date DATE NOT NULL,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Step 3: Configure Database Connection
Edit db.php with your database credentials:

<?php
$servername = "localhost";
$username = "username";         // Your MySQL username
$password = "password";        // Your MySQL password
$dbname = "todo_db";

// ... rest of the file
?>

Step 4: Run the Application
Copy the todo-web folder to your web server directory:
XAMPP: C:\xampp\htdocs\todo-web
WAMP: C:\wamp64\www\todo-web
MAMP: /Applications/MAMP/htdocs/todo-web
Open your browser and navigate to:

http://localhost/todo-web/

Create an account and start managing your tasks! 🎉
