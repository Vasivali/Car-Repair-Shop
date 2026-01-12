<?php
require_once "db.php";
session_start();

// Έλεγχος πρόσβασης
if (!isset($_SESSION["username"]) || $_SESSION["userRole"] !== "secretary") {
  header("Location: login.php");
  exit;
}

// Εναλλαγή γλώσσας
if (isset($_GET["lang"])) {
  $lang = $_GET["lang"];
  if (!in_array($lang, ["el", "en"])) $lang = "el";
  setcookie("lang", $lang, time() + (86400 * 30), "/");
  header("Location: cars_admin.php");
  exit;
}

$lang = $_COOKIE["lang"] ?? "el";
if (!in_array($lang, ["el", "en"])) $lang = "el";

// Ανάκτηση αυτοκινήτων
$vehicles = [];
$result = $conn->query("SELECT * FROM vehicles");
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $vehicles[] = $row;
  }
}

$translations = [
  "el" => [
    "title" => "Διαχείριση Αυτοκινήτων",
    "brand" => "Μηχανουργείο AutoFix",
    "vehicleMgmtTitle" => "Λίστα Αυτοκινήτων",
    "plate" => "Αριθμός Κυκλοφορίας",
    "model" => "Μοντέλο",
    "owner" => "Ιδιοκτήτης",
    "footer" => "Μηχανουργείο Αυτοκινήτων",
  ],
  "en" => [
    "title" => "Vehicle Management",
    "brand" => "AutoFix Garage",
    "vehicleMgmtTitle" => "Vehicle List",
    "plate" => "Plate Number",
    "model" => "Model",
    "owner" => "Owner",
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

  <!-- Πίνακας Αυτοκινήτων -->
  <div class="container mt-4">
    <h3><?= $t["vehicleMgmtTitle"] ?></h3>
    <table class="table table-bordered mt-3">
      <thead class="table-light">
        <tr>
          <th><?= $t["plate"] ?></th>
          <th><?= $t["model"] ?></th>
          <th><?= $t["owner"] ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($vehicles as $vehicle): ?>
          <tr>
            <td><?= htmlspecialchars($vehicle["plate"]) ?></td>
            <td><?= htmlspecialchars($vehicle["model"]) ?></td>
            <td><?= htmlspecialchars($vehicle["username"]) ?></td>
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
