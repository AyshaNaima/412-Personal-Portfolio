# Modern Resume Builder

A **beautiful, responsive, multi-step resume builder** with **photo upload**, **AJAX saving**, and **PDF export** using **TCPDF**.

Live preview → [YourApp.com](https://yourdomain.com) *(replace with your link)*

---

## Features

| Feature | Description |
|-------|-----------|
| **Multi-Step Form** | 3 clean steps: Personal → Education → Experience + Skills |
| **Photo Upload** | Drag & drop, live preview, 2MB limit, base64 storage |
| **AJAX Auto-Save** | Progress saved per step, no page reload |
| **Modern UI** | Tailwind-inspired design with gradients, cards, animations |
| **Dynamic Fields** | Add/remove education & experience entries |
| **PDF Export** | Professional 2-column resume with photo, icons, skill chips |
| **Secure Auth** | Login & Register with hashed passwords |
| **Fully Responsive** | Works on mobile, tablet, desktop |

---

## Tech Stack

- **PHP 8+** – Backend logic
- **MySQL** – Database (PDO)
- **TCPDF** – PDF generation
- **Vanilla JS** – AJAX, DOM manipulation
- **HTML5 + CSS3** – Modern, clean UI
- **Font: Inter** – Google Fonts

---

## Project Structure
resume-builder/
├── dashboard.php          ← Main resume builder
├── index.php              ← Login page
├── register.php           ← Register page
├── generate_pdf.php       ← Modern PDF export
├── save_resume.php        ← AJAX save handler
├── login.php              ← Login handler
├── register_process.php   ← Registration handler
├── logout.php             ← Logout
├── db.php                 ← DB connection
├── header.php             ← HTML header
├── footer.php             ← HTML footer
├── style.css              ← All styles (modern design)
├── tcpdf/                 ← TCPDF library (required)
└── sql-setup.sql          ← Database schema


## 2. Configuration

Edit db.php if needed:

$host = 'localhost';
$db   = 'resume_db';
$user = 'root';
$pass = '';  // Change if needed


## Usage

Register → Login
Fill Personal Details (upload photo)
Add Education & Experience
Enter Skills (comma-separated)
Click Download PDF → Get professional resume!