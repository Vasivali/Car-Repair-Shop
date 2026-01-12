<?php
require_once "db.php";
session_start();

if (!isset($_SESSION["username"]) || $_SESSION["userRole"] !== "customer") {
  header("Location: login.php");
  exit;
}

// Χειρισμός αλλαγής γλώσσας
if (isset($_GET["lang"])) {
  setcookie("lang", $_GET["lang"], time() + (86400 * 30), "/");
  header("Location: vehicles.php");
  exit;
}

$lang = $_COOKIE["lang"] ?? "el";
if (!in_array($lang, ["el", "en"])) $lang = "el";

$translations = [
  "el" => [
    "title" => "Τα Αυτοκίνητά Μου",
    "brand" => "Μηχανουργείο AutoFix",
    "addVehicle" => "+ Προσθήκη Οχήματος",
    "plate" => "Πινακίδα",
    "manufacturer" => "Κατασκευαστής",
    "model" => "Μοντέλο",
    "year" => "Έτος",
    "delete" => "Διαγραφή",
    "confirmDelete" => "Επιβεβαιώνεις διαγραφή;",
    "cancel" => "Άκυρο",
    "submit" => "Καταχώρηση",
    "modalTitle" => "Προσθήκη Οχήματος",
    "footer" => "Μηχανουργείο Αυτοκινήτων. Όλα τα δικαιώματα διατηρούνται."
  ],
  "en" => [
    "title" => "My Vehicles",
    "brand" => "AutoFix Garage",
    "addVehicle" => "+ Add Vehicle",
    "plate" => "Plate",
    "manufacturer" => "Manufacturer",
    "model" => "Model",
    "year" => "Year",
    "delete" => "Delete",
    "confirmDelete" => "Confirm deletion?",
    "cancel" => "Cancel",
    "submit" => "Submit",
    "modalTitle" => "Add Vehicle",
    "footer" => "Garage Services. All rights reserved."
  ]
];

$t = $translations[$lang];
$username = $_SESSION["username"];

// Προσθήκη νέου οχήματος
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $plate = $_POST["plate"];
  $manufacturer = $_POST["manufacturer"];
  $model = $_POST["model"];
  $year = $_POST["year"];
  $stmt = $conn->prepare("INSERT INTO vehicles (username, plate, manufacturer, model, year) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("ssssi", $username, $plate, $manufacturer, $model, $year);
  $stmt->execute();
  $stmt->close();
  header("Location: vehicles.php");
  exit;
}

// Διαγραφή οχήματος
if (isset($_GET["delete"])) {
  $id = intval($_GET["delete"]);
  $stmt = $conn->prepare("DELETE FROM vehicles WHERE id = ? AND username = ?");
  $stmt->bind_param("is", $id, $username);
  $stmt->execute();
  $stmt->close();
  header("Location: vehicles.php");
  exit;
}

// Λήψη οχημάτων
$stmt = $conn->prepare("SELECT * FROM vehicles WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$vehicles = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $t["title"] ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="icon" href="image.png">
</head>
<body>
  <nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="dashboard.php">
        <img src="image.png" height="30" class="me-2">
        <?= $t["brand"] ?>
      </a>
      <form method="get">
        <select name="lang" class="form-select form-select-sm w-auto ms-3" onchange="this.form.submit()">
          <option value="el" <?= $lang === "el" ? "selected" : "" ?>>Ελληνικά</option>
          <option value="en" <?= $lang === "en" ? "selected" : "" ?>>English</option>
        </select>
      </form>
    </div>
  </nav>

  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3><?= $t["title"] ?></h3>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newVehicleModal"><?= $t["addVehicle"] ?></button>
    </div>

    <div class="row">
      <?php foreach ($vehicles as $vehicle): ?>
        <div class="col-md-6 mb-3">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($vehicle["plate"]) ?></h5>
              <p class="card-text">
                <strong><?= $t["manufacturer"] ?>:</strong> <?= htmlspecialchars($vehicle["manufacturer"]) ?><br>
                <strong><?= $t["model"] ?>:</strong> <?= htmlspecialchars($vehicle["model"]) ?><br>
                <strong><?= $t["year"] ?>:</strong> <?= htmlspecialchars($vehicle["year"]) ?>
              </p>
              <a href="vehicles.php?delete=<?= $vehicle["id"] ?>" class="btn btn-danger btn-sm"
                 onclick="return confirm('<?= $t["confirmDelete"] ?>')"><?= $t["delete"] ?></a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="newVehicleModal" tabindex="-1">
    <div class="modal-dialog">
      <form method="POST" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><?= $t["modalTitle"] ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label"><?= $t["plate"] ?></label><input name="plate" class="form-control" required></div>
          <div class="mb-3"><label class="form-label"><?= $t["manufacturer"] ?></label><input name="manufacturer" class="form-control" required></div>
          <div class="mb-3"><label class="form-label"><?= $t["model"] ?></label><input name="model" class="form-control" required></div>
          <div class="mb-3"><label class="form-label"><?= $t["year"] ?></label><input type="number" name="year" class="form-control" min="1900" max="2099" required></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= $t["cancel"] ?></button>
          <button type="submit" class="btn btn-success"><?= $t["submit"] ?></button>
        </div>
      </form>
    </div>
  </div>

  <footer class="bg-dark text-white text-center py-3 mt-5">
    <p class="mb-0">&copy; 2025 <?= $t["footer"] ?></p>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
