<?php
require_once "db.php";
session_start();

// Εναλλαγή γλώσσας
if (isset($_GET["lang"])) {
  $lang = $_GET["lang"];
  if (!in_array($lang, ["el", "en"])) $lang = "el";
  setcookie("lang", $lang, time() + (86400 * 30), "/");
  header("Location: dashboard.php");
  exit;
}

// Στοιχεία χρήστη από session
$username = $_SESSION["username"] ?? null;
$role = $_SESSION["userRole"] ?? null;

// Αν δεν έχει γίνει login
if (!$username || !$role) {
  header("Location: login.php");
  exit;
}

// Γλώσσα διεπαφής
$lang = $_COOKIE["lang"] ?? "el";
if (!in_array($lang, ["el", "en"])) $lang = "el";

// Μεταφράσεις
$translations = [
  "el" => [
    "brand" => "Μηχανουργείο AutoFix",
    "dashboard" => "Πίνακας Ελέγχου",
    "welcome" => "Καλώς ήρθες",
    "logout" => "Αποσύνδεση",
    "myAppointments" => "Τα Ραντεβού Μου",
    "myVehicles" => "Τα Αυτοκίνητά Μου",
    "userManagement" => "Διαχείριση Χρηστών",
    "carManagement" => "Διαχείριση Αυτοκινήτων",
    "appointmentManagement" => "Διαχείριση Ραντεβού",
    "assignAppointments" => "Ραντεβού Ανάθεσης",
    "footer" => "Μηχανουργείο Αυτοκινήτων. Όλα τα δικαιώματα διατηρούνται."
  ],
  "en" => [
    "brand" => "AutoFix Garage",
    "dashboard" => "Dashboard",
    "welcome" => "Welcome",
    "logout" => "Logout",
    "myAppointments" => "My Appointments",
    "myVehicles" => "My Vehicles",
    "userManagement" => "User Management",
    "carManagement" => "Vehicle Management",
    "appointmentManagement" => "Appointment Management",
    "assignAppointments" => "Assign Appointments",
    "footer" => "Garage Services. All rights reserved."
  ]
];
$t = $translations[$lang];

// Λήψη στατιστικών (αν είσαι πελάτης)
$apptCount = 0;
$vehicleCount = 0;

if ($role === "customer") {
  $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $stmt->bind_result($apptCount);
  $stmt->fetch();
  $stmt->close();

  $stmt2 = $conn->prepare("SELECT COUNT(*) FROM vehicles WHERE username = ?");
  $stmt2->bind_param("s", $username);
  $stmt2->execute();
  $stmt2->bind_result($vehicleCount);
  $stmt2->fetch();
  $stmt2->close();
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $t["dashboard"] ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="icon" href="image.png" type="image/png">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="index.php">
        <img src="image.png" height="30" class="me-2">
        <?= $t["brand"] ?>
      </a>
      <div class="ms-3">
        <form method="get" action="dashboard.php">
          <select name="lang" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
            <option value="el" <?= $lang == "el" ? "selected" : "" ?>>Ελληνικά</option>
            <option value="en" <?= $lang == "en" ? "selected" : "" ?>>English</option>
          </select>
        </form>
      </div>
      <form method="post" class="ms-auto">
        <button name="logout" class="btn btn-outline-light"><?= $t["logout"] ?></button>
      </form>
    </div>
  </nav>

  <div class="container mt-5 text-center">
    <h1><?= $t["dashboard"] ?></h1>
    <h4><?= $t["welcome"] ?>, <strong><?= htmlspecialchars($username) ?></strong> (<?= htmlspecialchars($role) ?>)</h4>

    <?php if ($role === "customer"): ?>
      <div class="row justify-content-center mt-4">
        <div class="col-md-4 mb-3">
          <a href="appointments.php" class="btn btn-outline-primary w-100"><?= $t["myAppointments"] ?> (<?= $apptCount ?>)</a>
        </div>
        <div class="col-md-4 mb-3">
          <a href="vehicles.php" class="btn btn-outline-primary w-100"><?= $t["myVehicles"] ?> (<?= $vehicleCount ?>)</a>
        </div>
      </div>
    <?php elseif ($role === "mechanic"): ?>
      <div class="col-md-4 mb-3 mx-auto">
        <a href="mechanic_dashboard.php" class="btn btn-outline-secondary w-100"><?= $t["assignAppointments"] ?></a>
      </div>
    <?php elseif ($role === "secretary"): ?>
      <div class="row justify-content-center mt-4">
        <div class="col-md-4 mb-3">
          <a href="users.php" class="btn btn-outline-success w-100"><?= $t["userManagement"] ?></a>
        </div>
        <div class="col-md-4 mb-3">
          <a href="cars_admin.php" class="btn btn-outline-success w-100"><?= $t["carManagement"] ?></a>
        </div>
        <div class="col-md-4 mb-3">
          <a href="appointments_admin.php" class="btn btn-outline-success w-100"><?= $t["appointmentManagement"] ?></a>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <footer class="bg-dark text-white text-center py-3 mt-5">
    <p class="mb-0">&copy; 2025 <?= $t["footer"] ?></p>
  </footer>
</body>
</html>

<?php
if (isset($_POST["logout"])) {
  session_destroy();
  header("Location: login.php");
  exit;
}
?>
