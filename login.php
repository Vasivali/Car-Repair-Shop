<?php
require_once "db.php";
session_start();

$translations = [
  "el" => [
    "title" => "Σύνδεση Χρήστη",
    "brand" => "Μηχανουργείο AutoFix",
    "login" => "Σύνδεση",
    "username" => "Όνομα Χρήστη",
    "password" => "Κωδικός",
    "submit" => "Είσοδος",
    "error" => "Λάθος στοιχεία σύνδεσης",
    "footer" => "Μηχανουργείο Αυτοκινήτων. Όλα τα δικαιώματα διατηρούνται."
  ],
  "en" => [
    "title" => "User Login",
    "brand" => "AutoFix Garage",
    "login" => "Login",
    "username" => "Username",
    "password" => "Password",
    "submit" => "Submit",
    "error" => "Invalid login credentials",
    "footer" => "Garage Services. All rights reserved."
  ]
];

$lang = $_GET["lang"] ?? $_COOKIE["lang"] ?? "el";
if (!in_array($lang, ["el", "en"])) $lang = "el";
setcookie("lang", $lang, time() + (86400 * 30), "/");

$t = $translations[$lang];
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $username = $_POST["username"] ?? "";
  $password = $_POST["password"] ?? "";

  $stmt = $conn->prepare("SELECT password, role FROM users WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows === 1) {
    $stmt->bind_result($db_pass, $role);
    $stmt->fetch();

    if (password_verify($password, $db_pass)) {
      $_SESSION["username"] = $username;
      $_SESSION["userRole"] = $role;
      header("Location: dashboard.php");
      exit;
    }
  }
  $error = $t["error"];
}

?>

<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $t["title"] ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="icon" href="image.png" type="image/png">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="login.php">
        <img src="image.png" height="30" class="me-2">
        <?= $t["brand"] ?>
      </a>
      <div class="ms-3">
        <form method="get" action="login.php">
          <select name="lang" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
            <option value="el" <?= $lang == "el" ? "selected" : "" ?>>Ελληνικά</option>
            <option value="en" <?= $lang == "en" ? "selected" : "" ?>>English</option>
          </select>
        </form>
      </div>
    </div>
  </nav>

  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <h3 class="text-center mb-4"><?= $t["login"] ?></h3>
        <form method="POST">
          <div class="mb-3">
            <label for="username" class="form-label"><?= $t["username"] ?></label>
            <input type="text" class="form-control" id="username" name="username" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label"><?= $t["password"] ?></label>
            <input type="password" class="form-control" id="password" name="password" required>
          </div>
          <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
          <?php endif; ?>
          <button type="submit" class="btn btn-primary w-100"><?= $t["submit"] ?></button>
        </form>
      </div>
    </div>
  </div>

  <footer class="bg-dark text-white text-center py-3 mt-5">
    <p class="mb-0">&copy; 2025 <?= $t["footer"] ?></p>
  </footer>
</body>
</html>
