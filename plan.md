```markdown
# Detailed Implementation Plan for "Impocred" Web Application

This plan outlines the creation of a PHP, HTML, CSS, and JavaScript web application called "Impocred" – a credit management system with database integration, collector authentication, payment registration, credit terms with penalties/interest, and administration for products, clients, collectors, and expenses.

---

## 1. Project Structure and Dependent Files

Proposed folder/file structure:
- /impocred/
  - **admin/**
    - manage_clients.php  
    - manage_products.php  
    - manage_collectors.php  
    - manage_expenses.php  
  - **collector/**
    - login.php  
    - dashboard.php  
    - record_payment.php  
  - **includes/**
    - db.php  
    - functions.php  
    - header.php  
    - footer.php  
  - **assets/**
    - css/style.css  
    - js/scripts.js  
  - index.php  
  - db_schema.sql  
  - README.md  

*Note:* If any of these files or new dependencies are missed during development, re-read the overall project schema and re-plan accordingly.

---

## 2. Database Setup

- **db_schema.sql:**  
  - Create tables:  
    - `clients` (id, name, address, phone, email, etc.)  
    - `products` (id, product_name, description, price, etc.)  
    - `collectors` (id, name, email, cedula, password [hashed], etc.)  
    - `credits` (id, client_id, product_id, collector_id, purchase_date, due_date, status, etc.)  
    - `payments` (id, credit_id, payment_date, amount, penalty, interest, etc.)  
    - `expenses` (id, type, description, amount, date)
  - Include appropriate primary keys, foreign keys, and constraints.
  - Use InnoDB engine, UTF-8 charset.

---

## 3. Includes and Shared Components

### 3.1 includes/db.php
- Establish a PDO connection with error handling:
  - Use try-catch to catch PDOExceptions.
  - Set error mode to ERRMODE_EXCEPTION.
  - Export the PDO object for inclusion.

### 3.2 includes/functions.php
- Add helper functions such as:
  - `calculate_due_date($purchaseDate)` (adds 3 months)
  - `calculate_penalty($purchaseDate, $paymentDate)` to apply $2 penalty after 3 days and $3 penalty after 5 days.
  - `calculate_interest($dueDate, $currentDate, $productPrice)` to compute 7.5% interest per month past due.
- Ensure all functions validate input and handle errors.

### 3.3 includes/header.php & includes/footer.php
- Create a modern, minimal header with a navigation bar and clean typography.
- Footer contains basic copyright.
- Keep layout consistent across pages.

---

## 4. Admin Pages

Each admin page (located in `/admin/`) must include `header.php` and `footer.php`.

### 4.1 admin/manage_clients.php
- Form to add new client details (name, address, phone, email, etc.).
- Server-side validation and error messaging.
- Use prepared statements for inserts.

### 4.2 admin/manage_products.php
- Form to add new products with necessary fields (product name, description, price).
- Input validations and error handling.

### 4.3 admin/manage_collectors.php
- Form for adding collectors including: name, email, cedula (as password – stored hashed).
- Include validations and proper password handling.

### 4.4 admin/manage_expenses.php
- Form to record expenses (personal and company).
- Fields: type (dropdown: personal/company), description, amount, date.
- Validate amounts and date format.

---

## 5. Collector Pages

All collector pages also include `header.php`/`footer.php`.

### 5.1 collector/login.php
- Login form with fields: email and cedula.
- On submission, validate credentials using db.php and functions.php.
- Use PHP sessions to track login status.
- Display appropriate error messages on failure.

### 5.2 collector/dashboard.php
- After successful login, show a dashboard with:
  - Welcome message.
  - A summary list of assigned credits and pending payments.
  - Navigation to payment registration.
- Must check for active session; if not, redirect to login.php.

### 5.3 collector/record_payment.php
- Form to record client payment:
  - Fields: credit/loan identifier, payment date, amount.
  - On submission, use helper functions to calculate any penalty or interest.
  - Update the payments and credits tables accordingly.
- Include both client-side (JS) and server-side validations.

---

## 6. Frontend Assets

### 6.1 assets/css/style.css
- Define modern, responsive UI styles:
  - Use Flexbox/Grid for layout.
  - Set clear typography, spacing, and color palette.
  - Style form elements and buttons with subtle transitions.
  - Error messages displayed in a distinct (red) style.
  
### 6.2 assets/js/scripts.js
- Implement client-side form validations:
  - Validate required fields before submission.
  - Provide inline error messages.
  - Apply minimal JS enhancements; ensure graceful degradation if JS is disabled.

- *Note:* No external icons or third-party image services. If images are added (say for a landing page), use `<img src="https://placehold.co/1920x1080?text=Modern+landing+page+for+Impocred" alt="Modern landing page design for Impocred with clean typography and layout" onerror="this.onerror=null; this.src='fallback.jpg';" />`.

---

## 7. Index Page (index.php)
- Landing page introducing "Impocred" with a brief description and navigation links to the collector login and admin sections.
- Use header and footer includes.
- Modern and responsive design with clear call-to-action buttons.

---

## 8. Best Practices and Error Handling

- Use prepared statements to prevent SQL injection.
- Sanitize all user input (both client-side and server-side).
- Use try-catch blocks around database operations with user-friendly error messages.
- Implement session management securely (regenerate session IDs on login).
- Log technical errors to a file (not disclosed to the user).

---

## 9. UI/UX Considerations

- Use a clean, consistent layout across all pages.
- Modern styling with ample whitespace, consistent typography, and responsive design.
- Forms are centrally positioned with clear labels, error feedback, and modern buttons.
- Navigation is simple with clear calls-to-action for collectors and admins.

---

## Summary

• Establish a clear folder structure separating admin, collector, includes, and assets.  
• Build a MySQL database with tables for clients, products, collectors, credits, payments, and expenses via db_schema.sql.  
• Create robust includes (db.php and functions.php) for secure DB operations and financial calculations (due dates, penalties, interest).  
• Develop modern, responsive admin pages and collector pages (login, dashboard, payment registration) with consistent header and footer components.  
• Style with custom CSS to ensure a clean, professional UI while providing client- and server-side validations.  
• Emphasize error handling, security best practices, and session management throughout the application.
