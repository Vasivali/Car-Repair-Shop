<?php
require_once "db.php";
session_start();

// Εναλλαγή γλώσσας μέσω GET
if (isset($_GET["lang"])) {
  setcookie("lang", $_GET["lang"], time() + (86400 * 30), "/");
  header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
  exit;
}

if (!isset($_SESSION["username"]) || $_SESSION["userRole"] !== "customer") {
  header("Location: login.php");
  exit;
}

$username = $_SESSION["username"];
$lang = $_COOKIE["lang"] ?? "el";
if (!in_array($lang, ["el", "en"])) $lang = "el";

// Μεταφράσεις
$translations = [
  "el" => [
    "pageTitle" => "Τα Ραντεβού Μου",
    "addAppointment" => "Νέο Ραντεβού",
    "noAppointments" => "Δεν έχετε ραντεβού ακόμα.",
    "date" => "Ημερομηνία",
    "time" => "Ώρα",
    "reason" => "Λόγος",
    "description" => "Περιγραφή",
    "status" => "Κατάσταση",
    "cancel" => "Άκυρο",
    "submit" => "Καταχώρηση",
    "vehicle" => "Αυτοκίνητο",
    "brand" => "Μηχανουργείο AutoFix",
    "service" => "Σέρβις",
    "repair" => "Επιδιόρθωση",
    "delete" => "Διαγραφή",
    "confirmDelete" => "Επιβεβαιώνεις διαγραφή;",
    "conflict" => "Δεν είναι δυνατή η κράτηση: Υπάρχει ήδη ραντεβού εντός 2 ωρών."
  ],
  "en" => [
    "pageTitle" => "My Appointments",
    "addAppointment" => "New Appointment",
    "noAppointments" => "You don't have any appointments yet.",
    "date" => "Date",
    "time" => "Time",
    "reason" => "Reason",
    "description" => "Description",
    "status" => "Status",
    "cancel" => "Cancel",
    "submit" => "Submit",
    "vehicle" => "Vehicle",
    "brand" => "AutoFix Garage",
    "service" => "Service",
    "repair" => "Repair",
    "delete" => "Delete",
    "confirmDelete" => "Confirm deletion?",
    "conflict" => "Booking not allowed: There is already an appointment within 2 hours."
  ]
];
$t = $translations[$lang];

// Υποβολή νέου ραντεβού με έλεγχο διαθεσιμότητας 2 ωρών
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["date"])) {
  date_default_timezone_set("Europe/Athens");

  $date = $_POST["date"];
  $time = $_POST["time"];
  $vehicle = $_POST["vehicle"];
  $reason = $_POST["reason"];
  $description = $_POST["description"];
  $newAppt = new DateTime("$date $time");

  $stmt = $conn->prepare("SELECT date, time FROM appointments WHERE DATE(date) = ?");
  $stmt->bind_param("s", $date);
  $stmt->execute();
  $result = $stmt->get_result();

  $conflict = false;
  while ($row = $result->fetch_assoc()) {
    $existing = new DateTime("{$row['date']} {$row['time']}");
    $diff = abs($existing->getTimestamp() - $newAppt->getTimestamp());
    if ($diff < 2 * 3600) {
      $conflict = true;
      break;
    }
  }
  $stmt->close();

  if ($conflict) {
    echo "<script>alert('{$t["conflict"]}'); window.location='appointments.php';</script>";
    exit;
  }

  $stmt = $conn->prepare("INSERT INTO appointments (username, date, time, vehicle, reason, description) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("ssssss", $username, $date, $time, $vehicle, $reason, $description);
  $stmt->execute();
  $stmt->close();
  header("Location: appointments.php");
  exit;
}

// Διαγραφή ραντεβού
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {
  $id = intval($_POST["delete_id"]);
  $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ? AND username = ?");
  $stmt->bind_param("is", $id, $username);
  $stmt->execute();
  $stmt->close();
  header("Location: appointments.php");
  exit;
}

// Λήψη ραντεβού χρήστη
$stmt = $conn->prepare("SELECT * FROM appointments WHERE username = ? ORDER BY date DESC, time DESC");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$appointments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $t["pageTitle"] ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"/>
  <link rel="icon" type="image/png" href="image.png">
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">
      <img src="image.png" height="30" class="me-2">
      <?= $t["brand"] ?>
    </a>
    <form method="get" class="ms-3">
      <select name="lang" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
        <option value="el" <?= $lang === "el" ? "selected" : "" ?>>Ελληνικά</option>
        <option value="en" <?= $lang === "en" ? "selected" : "" ?>>English</option>
      </select>
    </form>
  </div>
</nav>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3><?= $t["pageTitle"] ?></h3>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newAppointmentModal">
      + <?= $t["addAppointment"] ?>
    </button>
  </div>

  <div class="row">
    <?php if (count($appointments) === 0): ?>
      <p class="text-muted"><?= $t["noAppointments"] ?></p>
    <?php else: ?>
      <?php foreach ($appointments as $index => $appt): ?>
        <div class="col-md-6 mb-3">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">#<?= $index + 1 ?> - <?= htmlspecialchars($appt["vehicle"]) ?></h5>
              <p class="card-text">
                <strong><?= $t["date"] ?>:</strong> <?= $appt["date"] ?><br>
                <strong><?= $t["time"] ?>:</strong> <?= $appt["time"] ?><br>
                <strong><?= $t["reason"] ?>:</strong> <?= $t[$appt["reason"]] ?? $appt["reason"] ?><br>
                <strong><?= $t["description"] ?>:</strong> <?= $appt["description"] ?: "-" ?><br>
                <strong><?= $t["status"] ?>:</strong> <?= $t[$appt["status"]] ?? $appt["status"] ?>
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

<!-- Modal Νέου Ραντεβού -->
<div class="modal fade" id="newAppointmentModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="post">
      <div class="modal-header">
        <h5 class="modal-title"><?= $t["addAppointment"] ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label"><?= $t["date"] ?></label>
          <input type="date" class="form-control" name="date" required>
        </div>
        <div class="mb-3">
          <label class="form-label"><?= $t["time"] ?></label>
          <input type="time" class="form-control" name="time" required>
        </div>
        <div class="mb-3">
          <label class="form-label"><?= $t["vehicle"] ?></label>
          <input type="text" class="form-control" name="vehicle" required>
        </div>
        <div class="mb-3">
          <label class="form-label"><?= $t["reason"] ?></label>
          <select class="form-select" name="reason" required>
            <option value="">--</option>
            <option value="service"><?= $t["service"] ?></option>
            <option value="repair"><?= $t["repair"] ?></option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label"><?= $t["description"] ?></label>
          <textarea class="form-control" name="description" rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal"><?= $t["cancel"] ?></button>
        <button type="submit" class="btn btn-success"><?= $t["submit"] ?></button>
      </div>
    </form>
  </div>
</div>

<footer class="bg-dark text-white text-center py-3 mt-5">
  <p class="mb-0">&copy; 2025 <?= $t["brand"] ?></p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
