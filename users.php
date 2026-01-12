<?php
require_once "db.php";
session_start();

// Έλεγχος πρόσβασης
if (!isset($_SESSION["username"]) || $_SESSION["userRole"] !== "secretary") {
  header("Location: login.php");
  exit;
}

// Αν αλλάχθηκε γλώσσα
if (isset($_GET["lang"])) {
  $lang = $_GET["lang"];
  if (!in_array($lang, ["el", "en"])) $lang = "el";
  setcookie("lang", $lang, time() + (86400 * 30), "/");
  header("Location: users.php");
  exit;
}

$lang = $_COOKIE["lang"] ?? "el";
if (!in_array($lang, ["el", "en"])) $lang = "el";

// Αν ζητήθηκε διαγραφή χρήστη
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_username'])) {
  $deleteUsername = $_POST['delete_username'];
  $stmt = $conn->prepare("DELETE FROM users WHERE username = ? AND role != 'secretary'");
  $stmt->bind_param("s", $deleteUsername);
  $stmt->execute();
  $stmt->close();
  header("Location: users.php");
  exit;
}

// Ανάκτηση χρηστών
$result = $conn->query("SELECT name, username, email, role FROM users");
$users = [];
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $users[] = $row;
  }
}

$translations = [
  "el" => [
    "title" => "Διαχείριση Χρηστών",
    "brand" => "Μηχανουργείο AutoFix",
    "userMgmtTitle" => "Διαχείριση Χρηστών",
    "name" => "Ονοματεπώνυμο",
    "username" => "Όνομα Χρήστη",
    "email" => "Email",
    "role" => "Ρόλος",
    "actions" => "Ενέργειες",
    "delete" => "Διαγραφή",
    "footer" => "Μηχανουργείο Αυτοκινήτων",
  ],
  "en" => [
    "title" => "User Management",
    "brand" => "AutoFix Garage",
    "userMgmtTitle" => "User Management",
    "name" => "Full Name",
    "username" => "Username",
    "email" => "Email",
    "role" => "Role",
    "actions" => "Actions",
    "delete" => "Delete",
    "footer" => "Garage Services",
  ]
];
$t = $translations[$lang];
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $t["title"] ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="icon" type="image/png" href="image.png" />
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="dashboard.php">
        <img src="image.png" height="30" class="me-2">
        <?= $t["brand"] ?>
      </a>
      <form method="get" class="ms-3">
        <select name="lang" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
          <option value="el" <?= $lang == "el" ? "selected" : "" ?>>Ελληνικά</option>
          <option value="en" <?= $lang == "en" ? "selected" : "" ?>>English</option>
        </select>
      </form>
    </div>
  </nav>

  <!-- Πίνακας Χρηστών -->
  <div class="container mt-4">
    <h3><?= $t["userMgmtTitle"] ?></h3>
    <table class="table table-bordered mt-3">
      <thead class="table-light">
        <tr>
          <th><?= $t["name"] ?></th>
          <th><?= $t["username"] ?></th>
          <th><?= $t["email"] ?></th>
          <th><?= $t["role"] ?></th>
          <th><?= $t["actions"] ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $user): ?>
          <tr>
            <td><?= htmlspecialchars($user["name"]) ?></td>
            <td><?= htmlspecialchars($user["username"]) ?></td>
            <td><?= htmlspecialchars($user["email"]) ?></td>
            <td><?= ucfirst($user["role"]) ?></td>
            <td>
              <?php if ($user["role"] !== "secretary"): ?>
                <form method="POST" onsubmit="return confirm('Επιβεβαιώνεις διαγραφή;')">
                  <input type="hidden" name="delete_username" value="<?= $user['username'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm"><?= $t["delete"] ?></button>
                </form>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <footer class="bg-dark text-white text-center py-3 mt-5">
    <p class="mb-0">&copy; 2025 <?= $t["footer"] ?></p>
  </footer>
</body>
</html>
