<?php
require_once "db.php";
session_start();

// Γλώσσα
$lang = $_COOKIE["lang"] ?? "el";
if (!in_array($lang, ["el", "en"])) $lang = "el";

$translations = [
  "el" => [
    "title" => "Εγγραφή Χρήστη",
    "brand" => "Μηχανουργείο AutoFix",
    "registerTitle" => "Εγγραφή Χρήστη",
    "fullName" => "Ονοματεπώνυμο",
    "idNumber" => "Αριθμός Ταυτότητας",
    "username" => "Όνομα Χρήστη",
    "email" => "Email",
    "password" => "Κωδικός",
    "role" => "Ρόλος",
    "roleCustomer" => "Πελάτης",
    "roleMechanic" => "Μηχανικός",
    "roleSecretary" => "Γραμματέας",
    "afm" => "ΑΦΜ",
    "address" => "Διεύθυνση Κατοικίας",
    "specialty" => "Ειδικότητα",
    "submit" => "Ολοκλήρωση Εγγραφής",
    "footer" => "Μηχανουργείο Αυτοκινήτων. Όλα τα δικαιώματα διατηρούνται.",
    "exists" => "Το όνομα χρήστη ή email υπάρχει ήδη."
  ],
  "en" => [
    "title" => "User Registration",
    "brand" => "AutoFix Garage",
    "registerTitle" => "User Registration",
    "fullName" => "Full Name",
    "idNumber" => "ID Number",
    "username" => "Username",
    "email" => "Email",
    "password" => "Password",
    "role" => "Role",
    "roleCustomer" => "Customer",
    "roleMechanic" => "Mechanic",
    "roleSecretary" => "Secretary",
    "afm" => "VAT Number",
    "address" => "Home Address",
    "specialty" => "Specialty",
    "submit" => "Complete Registration",
    "footer" => "Garage Services. All rights reserved.",
    "exists" => "Username or email already exists."
  ]
];
$t = $translations[$lang];

// Καταχώρηση χρήστη
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = $_POST["name"] ?? "";
  $id_number = $_POST["id_number"] ?? "";
  $username = $_POST["username"] ?? "";
  $email = $_POST["email"] ?? "";
  $password = $_POST["password"] ?? "";
  $role = $_POST["role"] ?? "";
  $afm = $_POST["afm"] ?? null;
  $address = $_POST["address"] ?? null;
  $specialty = $_POST["specialty"] ?? null;

  $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
  $stmt->bind_param("ss", $username, $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->fetch_assoc()) {
    $error = $t["exists"];
  } else {
    $hashedPass = password_hash($password, PASSWORD_DEFAULT);
    $final_afm = ($role === "customer") ? $afm : null;
    $final_address = ($role === "customer") ? $address : null;
    $final_specialty = ($role === "mechanic") ? $specialty : null;

    $stmt = $conn->prepare("INSERT INTO users 
      (name, id_number, username, email, password, role, afm, address, specialty) 
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
      "sssssssss",
      $name,
      $id_number,
      $username,
      $email,
      $hashedPass,
      $role,
      $final_afm,
      $final_address,
      $final_specialty
    );
    $stmt->execute();
    $stmt->close();
    header("Location: login.php");
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $t["title"] ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="icon" type="image/png" href="image.png">
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">
      <img src="image.png" height="30">
      <?= $t["brand"] ?>
    </a>
    <select class="form-select form-select-sm w-auto ms-3" onchange="setLanguage(this.value)">
      <option value="el" <?= $lang === "el" ? "selected" : "" ?>>Ελληνικά</option>
      <option value="en" <?= $lang === "en" ? "selected" : "" ?>>English</option>
    </select>
  </div>
</nav>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <h3 class="text-center mb-4"><?= $t["registerTitle"] ?></h3>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="POST">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label"><?= $t["fullName"] ?></label>
            <input type="text" class="form-control" name="name" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label"><?= $t["idNumber"] ?></label>
            <input type="text" class="form-control" name="id_number" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label"><?= $t["username"] ?></label>
            <input type="text" class="form-control" name="username" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label"><?= $t["email"] ?></label>
            <input type="email" class="form-control" name="email" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label"><?= $t["password"] ?></label>
            <input type="password" class="form-control" name="password" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label"><?= $t["role"] ?></label>
            <select class="form-select" name="role" id="role" required>
              <option value=""><?= $lang === "el" ? "{Επιλέξτε...}" : "{Select...}" ?></option>
              <option value="customer"><?= $t["roleCustomer"] ?></option>
              <option value="mechanic"><?= $t["roleMechanic"] ?></option>
              <option value="secretary"><?= $t["roleSecretary"] ?></option>
            </select>
          </div>
        </div>

        <div id="customerFields" class="row d-none">
          <div class="col-md-6 mb-3">
            <label class="form-label"><?= $t["afm"] ?></label>
            <input type="text" class="form-control" name="afm">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label"><?= $t["address"] ?></label>
            <input type="text" class="form-control" name="address">
          </div>
        </div>

        <div id="mechanicFields" class="row d-none">
          <div class="col-md-6 mb-3">
            <label class="form-label"><?= $t["specialty"] ?></label>
            <input type="text" class="form-control" name="specialty">
          </div>
        </div>

        <button type="submit" class="btn btn-success w-100"><?= $t["submit"] ?></button>
      </form>
    </div>
  </div>
</div>

<footer class="bg-dark text-white text-center py-3 mt-5">
  <p class="mb-0">&copy; 2025 <?= $t["footer"] ?></p>
</footer>

<script>
const roleSelect = document.getElementById("role");
const customerFields = document.getElementById("customerFields");
const mechanicFields = document.getElementById("mechanicFields");

roleSelect.addEventListener("change", () => {
  const role = roleSelect.value;
  customerFields.classList.toggle("d-none", role !== "customer");
  mechanicFields.classList.toggle("d-none", role !== "mechanic");
});

function setLanguage(lang) {
  document.cookie = "lang=" + lang + "; path=/";
  location.reload();
}
</script>
</body>
</html>
