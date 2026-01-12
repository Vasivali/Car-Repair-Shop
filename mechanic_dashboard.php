<?php
require_once "db.php";
session_start();

if (!isset($_SESSION["username"]) || $_SESSION["userRole"] !== "mechanic") {
  header("Location: login.php");
  exit;
}

$lang = $_GET["lang"] ?? $_COOKIE["lang"] ?? "el";
if (!in_array($lang, ["el", "en"])) $lang = "el";
setcookie("lang", $lang, time() + (86400 * 30), "/");

$translations = [
  "el" => [
    "title" => "Ραντεβού προς Διαχείριση",
    "brand" => "Μηχανουργείο AutoFix",
    "date" => "Ημερομηνία",
    "time" => "Ώρα",
    "vehicle" => "Αυτοκίνητο",
    "reason" => "Λόγος",
    "status" => "Κατάσταση",
    "service" => "Σέρβις",
    "repair" => "Επιδιόρθωση",
    "created" => "δημιουργήθηκε",
    "accepted" => "εγκρίθηκε",
    "rejected" => "απορρίφθηκε",
    "completed" => "ολοκληρώθηκε",
    "accept" => "Αποδοχή",
    "reject" => "Απόρριψη",
    "complete" => "Ολοκλήρωση",
    "footer" => "Μηχανουργείο Αυτοκινήτων"
  ],
  "en" => [
    "title" => "Appointments to Manage",
    "brand" => "AutoFix Garage",
    "date" => "Date",
    "time" => "Time",
    "vehicle" => "Vehicle",
    "reason" => "Reason",
    "status" => "Status",
    "service" => "Service",
    "repair" => "Repair",
    "created" => "created",
    "accepted" => "accepted",
    "rejected" => "rejected",
    "completed" => "completed",
    "accept" => "Accept",
    "reject" => "Reject",
    "complete" => "Complete",
    "footer" => "Garage Services"
  ]
];
$t = $translations[$lang];

// Αλλαγή κατάστασης με POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"], $_POST["newStatus"])) {
  $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
  $stmt->bind_param("si", $_POST["newStatus"], $_POST["id"]);
  $stmt->execute();
  $stmt->close();
  header("Location: mechanic_dashboard.php");
  exit;
}

// Ανάκτηση ραντεβού
$appointments = [];
$result = $conn->query("SELECT * FROM appointments ORDER BY date, time");
if ($result) {
  $appointments = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $t["title"] ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="icon" href="image.png" type="image/png">
</head>
<body>
  <nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="dashboard.php">
        <img src="image.png" height="30" class="me-2">
        <?= $t["brand"] ?>
      </a>
      <form method="get" action="mechanic_dashboard.php" class="ms-3">
        <select name="lang" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
          <option value="el" <?= $lang == "el" ? "selected" : "" ?>>Ελληνικά</option>
          <option value="en" <?= $lang == "en" ? "selected" : "" ?>>English</option>
        </select>
      </form>
    </div>
  </nav>

  <div class="container mt-4">
    <h3><?= $t["title"] ?></h3>
    <div class="row mt-3">
      <?php foreach ($appointments as $appt): ?>
        <div class="col-md-6 mb-3">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($appt["username"] ?? 'Χρήστης') ?></h5>
              <p class="card-text">
                <strong><?= $t["date"] ?>:</strong> <?= $appt["date"] ?><br>
                <strong><?= $t["time"] ?>:</strong> <?= $appt["time"] ?><br>
                <strong><?= $t["vehicle"] ?>:</strong> <?= htmlspecialchars($appt["vehicle"]) ?><br>
                <strong><?= $t["reason"] ?>:</strong> <?= $t[$appt["reason"]] ?? $appt["reason"] ?><br>
                <strong><?= $t["status"] ?>:</strong> <?= $t[$appt["status"]] ?? $appt["status"] ?>
              </p>
              <form method="post" class="d-flex gap-2">
                <input type="hidden" name="id" value="<?= $appt["id"] ?>">
                <button name="newStatus" value="accepted" class="btn btn-success btn-sm"><?= $t["accept"] ?></button>
                <button name="newStatus" value="rejected" class="btn btn-warning btn-sm"><?= $t["reject"] ?></button>
                <button name="newStatus" value="completed" class="btn btn-secondary btn-sm"><?= $t["complete"] ?></button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <footer class="bg-dark text-white text-center py-3 mt-5">
    <p class="mb-0">&copy; 2025 <?= $t["footer"] ?></p>
  </footer>
</body>
</html>
