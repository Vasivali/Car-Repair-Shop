<?php
require_once "db.php";

 session_start();
 ?>
<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title data-i18n="title">Μηχανουργείο</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"/>
  <link rel="stylesheet" href="style.css" />
  <link rel="icon" type="image/png" href="image.png">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="index.php">
        <img src="image.png" height="30">
        <span data-i18n="brand">Μηχανουργείο AutoFix</span>
      </a>

      <select class="form-select form-select-sm w-auto ms-3" onchange="setLanguage(this.value)">
        <option value="el">Ελληνικά</option>
        <option value="en">English</option>
      </select>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto" id="navLinks">
          <!-- Γεμίζει δυναμικά -->
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero section -->
  <header class="bg-light text-center p-5">
    <h1 class="display-4" data-i18n="welcome">Καλώς ήρθατε στο Μηχανουργείο</h1>
    <p class="lead" data-i18n="subtitle">Διαχειριστείτε ραντεβού, αυτοκίνητα και χρήστες εύκολα και γρήγορα.</p>
    <a href="login.php" class="btn btn-primary btn-lg" data-i18n="login">Σύνδεση</a>
  </header>

  <footer class="bg-dark text-white text-center py-3">
    <p class="mb-0" style="color: aliceblue;">&copy; 2025 <span data-i18n="copyright">Μηχανουργείο Αυτοκινήτων</span></p>
  </footer>

  <script>
    const translations = {
      el: {
        title: "Μηχανουργείο",
        brand: "Μηχανουργείο AutoFix",
        welcome: "Καλώς ήρθατε στο Μηχανουργείο",
        subtitle: "Διαχειριστείτε ραντεβού, αυτοκίνητα και χρήστες εύκολα και γρήγορα.",
        login: "Σύνδεση",
        register: "Εγγραφή",
        logout: "Αποσύνδεση",
        hello: "Καλώς ήρθες",
        copyright: "Μηχανουργείο Αυτοκινήτων"
      },
      en: {
        title: "Garage",
        brand: "AutoFix Garage",
        welcome: "Welcome to the Garage",
        subtitle: "Manage appointments, vehicles, and users easily and quickly.",
        login: "Login",
        register: "Register",
        logout: "Logout",
        hello: "Welcome",
        copyright: "Garage Services"
      }
    };

    function setLanguage(lang) {
      const elements = document.querySelectorAll("[data-i18n]");
      elements.forEach(el => {
        const key = el.getAttribute("data-i18n");
        el.textContent = translations[lang][key] || key;
      });
      localStorage.setItem("lang", lang);
    }

    const savedLang = localStorage.getItem("lang") || "el";
    setLanguage(savedLang);

    const navLinks = document.getElementById("navLinks");
    const username = sessionStorage.getItem("username");
    const role = sessionStorage.getItem("userRole");

    if (username && role) {
      navLinks.innerHTML = `
        <li class="nav-item">
          <span class="nav-link disabled">${translations[savedLang]["hello"]}, ${username}</span>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" onclick="logout()">${translations[savedLang]["logout"]}</a>
        </li>`;
    } else {
      navLinks.innerHTML = `
        <li class="nav-item">
          <a class="nav-link" href="login.php" data-i18n="login">Σύνδεση</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="register.php" data-i18n="register">Εγγραφή</a>
        </li>`;
    }

    function logout() {
      sessionStorage.clear();
      window.location.href = "index.php";
    }
  </script>
</body>
</html>
