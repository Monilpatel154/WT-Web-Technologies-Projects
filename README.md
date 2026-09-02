# Small Projects of WT

A collection of small web development projects and coursework built for the **Web Technologies** subject, ranging from plain HTML/CSS/JS exercises to full PHP + MySQL applications — including **SkillSwap**, a complete student skill-exchange platform.

## 📁 Repository Structure

```
Small-Projects-of-WT-main/
├── Calculator-html-css-js/     # Simple calculator (HTML, CSS, JS)
├── portfolio-html/             # Personal portfolio page (HTML only)
├── portfolio-html-css/         # Personal portfolio page (HTML + CSS)
├── ecommerce-html/             # E-commerce landing page (HTML only)
├── ecommerce-html-css/         # E-commerce landing page (HTML + CSS)
├── sorting-html/               # Student & Market "Smart Dashboard" (sorting/visualization demo)
├── php1/                       # PHP + MySQL shoe store demo (shoe_store DB)
├── php2/                       # PHP + MySQL portfolio backend (portfolio_db DB)
├── php3/                       # PHP + MySQL shoe store demo v2 (shoe_store3 DB)
├── php 4/                      # PHP + MySQL shoe store demo v3 (shoe_store4 DB)
├── WT Final Project/           # SkillSwap — full PHP/MySQL final project
├── WT-Web-Technologies-Projects/ # Mirror copy of the smaller projects above
└── index.html                  # Landing page linking out to the individual projects
```

> **Note:** `WT-Web-Technologies-Projects/` contains duplicates of the standalone HTML/CSS/JS and PHP mini-projects — kept for reference alongside the top-level copies.

## 🧩 Projects

| Project | Stack | Description |
|---|---|---|
| **Calculator** | HTML, CSS, JS | A basic on-screen calculator. |
| **Portfolio** | HTML (+ CSS variant) | A personal portfolio page in a plain and a styled version. |
| **E-commerce Landing Page** | HTML (+ CSS variant) | A storefront-style landing page in a plain and a styled version. |
| **Sorting / Smart Dashboard** | HTML, CSS, JS | An interactive "Student & Market Smart Dashboard" demo. |
| **php1 – php4** | PHP, MySQL (PDO) | Iterative shoe-store / portfolio backend exercises demonstrating server-side rendering, forms, and database reads/writes. |
| **WT Final Project — SkillSwap** | PHP, MySQL, PDO | A full-featured student skill-exchange platform (see below). |

### ⭐ SkillSwap (WT Final Project)

SkillSwap is a student skill-exchange web app where users can list skills they can teach, browse what others are offering, request swaps, chat, and review each other.

**Key features**
- User authentication (register/login) and profile management
- Skill listings with categories, search, and an "explore" page
- A "smart match" engine for suggesting relevant skill swaps
- Swap request workflow (request / respond / track my requests)
- In-app messaging between users
- Ratings & reviews after a completed swap
- Notifications system
- A "wanted" board for skills users are looking for
- Admin panel: dashboard, analytics, manage users/skills/categories, handle reports

**Tech stack:** PHP 8+, MySQL (PDO), vanilla JS/CSS assets, session-based auth.

## 🚀 Getting Started

### Plain HTML/CSS/JS projects
No setup required — just open the relevant `index.html` (or `sorting.html`) file in a browser.

### PHP + MySQL mini projects (`php1`–`php 4`)
Each folder includes an `index.php` and a `database.sql` file.

1. Create the corresponding MySQL database (see the DB name used in each project's `index.php`).
2. Import the matching `database.sql` file into that database.
3. Update the database credentials at the top of `index.php` to match your local MySQL setup.
4. Serve the folder with PHP's built-in server:
   ```bash
   php -S localhost:8000 -t .
   ```
5. Visit `http://localhost:8000`.

> ⚠️ These learning-exercise files have local MySQL credentials hardcoded for convenience. **Update or remove them before deploying anywhere public.**

### SkillSwap (`WT Final Project`)

1. **Requirements:** PHP ≥ 8.0 with `pdo`, `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`, `session`, and `json` extensions, plus a MySQL server.
2. Install PHP dependencies (if any are added later):
   ```bash
   composer install
   ```
3. Import the schema:
   ```bash
   mysql -u root -p < database/skillswap.sql
   ```
4. Configure the database connection via environment variables (all optional — sensible local defaults are used if unset):
   ```bash
   export SKILLSWAP_DB_HOST=localhost
   export SKILLSWAP_DB_PORT=3306
   export SKILLSWAP_DB_NAME=skillswap
   export SKILLSWAP_DB_USER=root
   export SKILLSWAP_DB_PASS=yourpassword
   ```
5. Run it locally:
   ```bash
   php -S 0.0.0.0:8000 -t .
   ```
6. Visit `http://localhost:8000`.

The project also ships with a `Procfile` and `nixpacks.toml`, so it can be deployed directly to platforms like **Railway** or **Heroku**-style buildpacks with minimal changes — just set the `SKILLSWAP_DB_*` environment variables on the platform.

## 🛠️ Tech Stack Summary

- **Frontend:** HTML5, CSS3, vanilla JavaScript
- **Backend:** PHP 8+ (PDO for database access)
- **Database:** MySQL
- **Deployment:** Nixpacks / Procfile-based (Railway-compatible)

## 👤 Author

**Monil Patel**
B.Tech Computer Science Engineering, Jain University, Bengaluru

## 📄 License

This repository is intended for educational purposes as part of Web Technologies coursework. Feel free to explore and reference the code for learning.
