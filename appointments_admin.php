<?php
require_once "db.php";
session_start();

if (!isset($_SESSION["username"]) || $_SESSION["userRole"] !== "secretary") {
  header("Location: login.php");
  exit;
}

$lang = $_COOKIE["lang"] ?? "el";
if (!in_array($lang, ["el", "en"])) $lang = "el";

$translations = [
  "el" => [
    "allAppointments" => "Όλα τα Ραντεβού",
    "noAppointments" => "Δεν υπάρχουν ραντεβού.",
    "date" => "Ημερομηνία",
    "time" => "Ώρα",
    "vehicle" => "Όχημα",
    "reason" => "Λόγος",
    "description" => "Περιγραφή",
    "status" => "Κατάσταση",
    "delete" => "Διαγραφή",
    "confirmDelete" => "Είστε σίγουροι;",
    "footer" => "Μηχανουργείο Αυτοκινήτων"
  ],
  "en" => [
    "allAppointments" => "All Appointments",
    "noAppointments" => "No appointments available.",
    "date" => "Date",
    "time" => "Time",
    "vehicle" => "Vehicle",
    "reason" => "Reason",
    "description" => "Description",
    "status" => "Status",
    "delete" => "Delete",
    "confirmDelete" => "Are you sure?",
    "footer" => "Garage Services"
  ]
];
$t = $translations[$lang];

// Φόρτωση όλων των ραντεβού από τη βάση
$sql = "SELECT * FROM appointments ORDER BY date, time";
$result = $conn->query($sql);
$appointments = [];

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
  }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $t["allAppointments"] ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">
      <img src="image.png" height="30" class="me-2">
      AutoFix
    </a>
    <form method="get" action="">
      <select name="lang" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
        <option value="el" <?= $lang == "el" ? "selected" : "" ?>>Ελληνικά</option>
        <option value="en" <?= $lang == "en" ? "selected" : "" ?>>English</option>
      </select>
    </form>
  </div>
</nav>

<?php
if (isset($_GET["lang"])) {
  setcookie("lang", $_GET["lang"], time() + (86400 * 30), "/");
  header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
  exit;
}
?>

<div class="container mt-4">
  <h3><?= $t["allAppointments"] ?></h3>
  <div class="row mt-3">
    <?php if (count($appointments) === 0): ?>
      <p><?= $t["noAppointments"] ?></p>
    <?php else: ?>
      <?php foreach ($appointments as $appt): ?>
        <div class="col-md-6 mb-3">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($appt["username"]) ?></h5>
              <p class="card-text">
                <strong><?= $t["date"] ?>:</strong> <?= htmlspecialchars($appt["date"]) ?><br>
                <strong><?= $t["time"] ?>:</strong> <?= htmlspecialchars($appt["time"]) ?><br>
                <strong><?= $t["vehicle"] ?>:</strong> <?= htmlspecialchars($appt["vehicle"]) ?><br>
                <strong><?= $t["reason"] ?>:</strong> <?= htmlspecialchars($appt["reason"]) ?><br>
                <strong><?= $t["description"] ?>:</strong> <?= htmlspecialchars($appt["description"]) ?><br>
                <strong><?= $t["status"] ?>:</strong> <?= htmlspecialchars($appt["status"]) ?>
              </p>
              <form method="post" onsubmit="return confirm('<?= $t["confirmDelete"] ?>');">
                <input type="hidden" name="delete_id" value="<?= $appt["id"] ?>">
                <button type="submit" class="btn btn-danger btn-sm"><?= $t["delete"] ?></button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<footer class="bg-dark text-white text-center py-3 mt-5">
  <p class="mb-0">&copy; 2025 <?= $t["footer"] ?></p>
</footer>
</body>
</html>

<?php
// Χειρισμός διαγραφής
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {
  $id = intval($_POST["delete_id"]);
  $conn->query("DELETE FROM appointments WHERE id = $id");
  header("Location: appointments_admin.php");
  exit;
}
?>
