# Cool Kids Network Plugin - Documentation

## 📌 Problem Statement
The **Cool Kids Network** plugin is designed to **manage user roles and authentication** in a WordPress environment. The goal is to allow users to **generate a character by registration and log in.** **Their roles can be updated** based on an administrator’s actions.

### **Challenges Addressed:**
1. **Managing user roles efficiently** within WordPress.
2. **Providing an admin panel** to assign roles easily.
3. **Creating a user-friendly login and registration system**.
4. **Ensuring security** through nonce verification and sanitization.
5. **Making sure the plugin works across all themes and page builders.**

---

## 🔧 **Technical Specification**

### **1️⃣ User Roles**
The plugin registers three custom user roles:
- **Cool Kid** - Basic role with limited access.
- **Cooler Kid** - Can view basic user info.
- **Coolest Kid** - Can view all users, including emails and roles.

### **2️⃣ Registration & Login**
- Registration uses the **RandomUser API** to generate fake user data.
- Login is handled via **AJAX** to prevent conflicts with themes.
- **Nonces** are used to secure login and registration.

### **3️⃣ Admin Role Management UI**
- Admins can assign roles using a custom **WordPress Admin Page**.
- Only admins can update roles via a **secure REST API (`cool-kids/v1/update-role`)**.

### **4️⃣ REST API Implementation**
- The API allows **secure role updates**.
- API requests are **validated and checked for permissions**.
- API responds with JSON messages.

### **5️⃣ Front-End Design Improvements**
- The plugin includes **custom CSS styles** to prevent theme conflicts.
- Forms are **styled properly** and remain responsive.
- **AJAX login prevents plugins and other themes from breaking authentication.**

### **6️⃣ Security Measures**
- **Nonce verification** prevents CSRF attacks.
- **Sanitization of user input** prevents SQL injection.
- **Escaping output** prevents XSS vulnerabilities.
- **Restricted access** to API endpoints using WordPress permissions.

---

## 📌 **Technical Decisions & Justifications**

| **Decision**                      | **Reason** |
|-----------------------------------|-----------|
| **Used OOP (Class-Based Plugin)** | Keeps the plugin modular, reusable, and maintainable. |
| **Implemented AJAX login** | Prevents page reloads and avoids conflicts with Elementor. |
| **Used REST API for role updates** | Provides a clean, structured way for role management. |
| **Added custom styles** | Ensures consistent UI across all themes. |
| **WordPress Hooks (`add_action`)** | Follows best practices for extendability. |

---

## 🎯 **How This Plugin Achieves the Admin’s Goals**

| **Admin Requirement** | **Implementation** |
|------------------------|--------------------------|
| **Assign roles easily** | Admin panel UI allows selecting users and updating their roles. |
| **Secure role updates** | REST API enforces permission checks. |
| **Easy login & registration** | AJAX-based login prevents conflicts with themes. |
| **User-friendly UI** | Custom CSS ensures styling consistency. |

---

# Cool Kids Network - Shortcodes Guide

This document provides a list of all available shortcodes in the **Cool Kids Network** plugin and how to use them.

## 1. `[cool_kids_registration]` - User Registration Form
**Description:** Displays the user registration form, allowing users to register with an email.

**Usage:**
```html
[cool_kids_registration]
```

---

## 2. `[cool_kids_login]` - User Login Form
**Description:** Displays the login form for users to log in using their email.

**Usage:**
```html
[cool_kids_login]
```

---

## 3. `[cool_kids_character]` - User Character Information
**Description:** Displays the logged-in user's character data, including name, country, email, and role.

**Usage:**
```html
[cool_kids_character]
```

**Output Example:**
```
Name: John Doe
Country: USA
Email: john@example.com
Role: Cool Kid
```

---

## 4. `[cool_kids_all_characters]` - View All User Data
**Description:** Displays a table of all users in the system. Users with `cool_kid` role **cannot** view this data. Users with `coolest_kid` role can see emails and roles.

**Usage:**
```html
[cool_kids_all_characters]
```

**Output Example:**
```
| Name       | Country  | Email            | Role       |
|------------|---------|------------------|-----------|
| Jane Doe   | Canada  | jane@example.com | Cooler Kid |
| John Smith | UK      | john@example.com | Coolest Kid |
```

---

## Notes:
- All shortcodes can be added to **pages, posts, or widgets**.
- The plugin ensures **proper security and role restrictions** for each shortcode.

---
## 🛠️ **Future Improvements**
🔹 Improve the **admin UI** by integrating WordPress UI components.  
🔹 Add **more tests** for better reliability.  
🔹 Enhance **database query optimizations** to scale better.  

---

## 🚀 Conclusion
The **Cool Kids Network** plugin is a **secure, scalable, and user-friendly** WordPress solution for managing custom user roles. It follows best practices in WordPress development, ensuring reliability, security, and compatibility with different themes and page builders.

This documentation outlines the thought process, architecture, and reasoning behind the plugin's design, showcasing a structured approach to solving the problem efficiently.