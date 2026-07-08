# 💰 Smart Expense Splitter (Splitwise Clone)

A lightweight, high-performance, and secure **Full-Stack PHP web application** designed to automatically split group expenses among friends and calculate the final settlement matrix (*“who owes whom”*) using a custom mathematical settlement engine.

---

## 🚀 Key Features

### 🔹 Dynamic Splitting Engine

Automatically calculates:

* Total group expenses
* Equal per-head share
* Optimized settlement with **minimum number of transactions**

---

### 🔹 Backend Duplicate Prevention

* Prevents duplicate friend entries using **SQL validation checks**
* Displays **real-time Bootstrap alert messages** for better UX

---

### 🔹 Automated Data Cascading

* Uses **MySQL relational constraints**
* `ON DELETE CASCADE` ensures:

  * Deleting a friend → removes all associated expenses automatically

---

### 🔹 Real-time CRUD Operations

* Add / delete friends instantly
* Add / remove expenses dynamically
* Seamless UI updates

---

### 🔹 Modern Responsive UI

* Built with **Bootstrap 5**
* Clean layout & responsive design
* Dark summary cards
* **FontAwesome icons** for interactivity

---

## 🗄️ Database Setup (MySQL)

1. Install MySQL or use any SQL client (MySQL Workbench / phpMyAdmin)
2. Create a database:

```sql
CREATE DATABASE splitter_db;
```

3. Run the following queries:

```sql
CREATE TABLE friends (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    paid_by_id INT,
    FOREIGN KEY (paid_by_id) REFERENCES friends(id) ON DELETE CASCADE
);
```

---

## ⚙️ Configuration & Installation

### 1️⃣ Database Connection (`db.php`)

Create a file named **db.php** in your root directory:

```php
<?php
$host = "localhost";
$username = "root";     
$password = "";         
$database = "splitter_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}
?>
```

⚠️ **Security Tip:**
Add `db.php` to your `.gitignore` file to avoid exposing credentials.

---

### 2️⃣ Run Project (No XAMPP Required)

#### ▶️ Using PHP Built-in Server

1. Open terminal inside your project folder:

```bash
cd expense_splitter
```

2. Start server:

```bash
php -S localhost:8000
```

3. Open browser:

```
http://localhost:8000
```

---

### 💡 Alternative Ways to Run

You can also run this project using:

* **Laragon**
* **WAMP**
* **Docker (Apache + PHP + MySQL)**
* **VS Code PHP Server Extension**

---

## 🛠️ Tech Stack

| Layer        | Technology          |
| ------------ | ------------------- |
| **Frontend** | Bootstrap 5         |
| **Backend**  | PHP (Vanilla / OOP) |
| **Database** | MySQL               |
| **Icons**    | FontAwesome v6      |

---

## 📊 How It Works

1. Add friends to the group
2. Record expenses (who paid & amount)
3. System calculates:

   * Total spent
   * Equal share per person
4. Generates optimized settlement:

   * Shows **who pays whom**
   * Minimizes number of transactions

---

## ✨ Example

| Person | Paid  | Share | Balance |
| ------ | ----- | ----- | ------- |
| A      | ₹1000 | ₹500  | +₹500   |
| B      | ₹0    | ₹500  | -₹500   |

👉 **Result:** B pays ₹500 to A

---

## 📌 Future Enhancements

* User authentication (Login/Signup)
* Group-based expense splitting
* Expense categories
* Graphical analytics (charts)
* Payment integration (UPI/Stripe)
* REST API backend support

---

## 🤝 Contribution

Feel free to fork and contribute!

```bash
git clone https://github.com/Sakshibansal027/Smart-Expense-Splitter.git
```

---

## 📜 License

This project is open-source under the **MIT License**.

---

## 💡 Author

Developed with ❤️ by **Sakshi Bansal**

---

⭐ If you like this project, don’t forget to **star the repo!**
