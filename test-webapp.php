<?php
// Kara David's Wish Note — local QA testing exercise.
// WARNING: root with a blank password is insecure. Use only in an isolated local lab.
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "birthday_wishnote";

$conn = new mysqli($servername, $username, $password, $dbname);
$dbError = "";
$successMessage = "";
$firstName = $lastName = $govPosition = $location = "";

if ($conn->connect_error) {
    $dbError = "Database connection failed. Check that MariaDB is running and the database exists.";
} else {
    $conn->set_charset("utf8mb4");
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && !$conn->connect_error) {
    $firstName = trim($_POST["FirstName"] ?? "");
    $lastName = trim($_POST["LastName"] ?? "");
    $govPosition = trim($_POST["GovPosition"] ?? "");
    $location = trim($_POST["Location"] ?? ""); // Captured, but intentionally not inserted.

    if ($firstName === "" || $lastName === "" || $govPosition === "") {
        $dbError = "Please complete all required fields.";
    } elseif (mb_strlen($firstName) > 30 || mb_strlen($lastName) > 30 ||
              mb_strlen($govPosition) > 20 || mb_strlen($location) > 40) {
        $dbError = "One or more fields exceed the allowed character limit.";
    } else {
        /*
         * INTENTIONAL QA BUG:
         * Location is captured from the form but omitted from this INSERT statement
         * and bind_param call. This should cause the Location column to be missing
         * or left at its default value in the database, which testers must detect.
         */
        $sql = "INSERT INTO main_corrupt_politicians (FirstName, LastName, GovPosition)
                VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            $dbError = "Unable to prepare the database query. Check the table and column names.";
        } else {
            $stmt->bind_param("sss", $firstName, $lastName, $govPosition);
            if ($stmt->execute()) {
                $successMessage = "Success! The wish note has been sent.";
                $firstName = $lastName = $govPosition = $location = "";
            } else {
                $dbError = "The wish note could not be saved. Check the database table structure.";
            }
            $stmt->close();
        }
    }
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kara David's Wish Note</title>
<style>
:root{--blue:#1769aa;--blue-dark:#104e83;--light:#eef4f8;--text:#233243;--border:#d4dee7}
*{box-sizing:border-box}
body{margin:0;padding:28px 16px;background:#f1f4f7;color:var(--text);font-family:Arial,Helvetica,sans-serif}
.card{width:100%;max-width:560px;margin:24px auto;background:#fff;border-radius:12px;padding:30px;box-shadow:0 8px 28px rgba(22,48,75,.12)}
h2{margin:0 0 8px;color:var(--blue);text-align:center}
.intro{text-align:center;color:#637386;margin:0 0 24px}
label{display:block;font-weight:700;margin:16px 0 7px}
input{width:100%;padding:12px 13px;border:1px solid var(--border);border-radius:7px;font-size:16px}
input:focus{outline:2px solid #9bc8ed;border-color:var(--blue)}
button{border:0;border-radius:7px;padding:12px 17px;font-size:15px;font-weight:700;cursor:pointer}
.primary{width:100%;margin-top:22px;background:var(--blue);color:#fff}
.primary:hover{background:var(--blue-dark)}
.secondary{background:#e8edf2;color:#243447}
.confirm{background:var(--blue);color:white}
.notice{padding:12px 14px;border-radius:7px;margin:0 0 18px}
.success{background:#e6f5ec;color:#17633a;border:1px solid #b7e1c7}
.error{background:#fff0ef;color:#8b2420;border:1px solid #f0c3bf}
.modal-backdrop{position:fixed;inset:0;background:rgba(10,25,40,.58);display:none;align-items:center;justify-content:center;padding:18px;z-index:5}
.modal{width:100%;max-width:440px;background:#fff;border-radius:12px;padding:25px;box-shadow:0 18px 55px rgba(0,0,0,.25)}
.modal h3{margin-top:0;color:var(--blue)}
.review{background:var(--light);border-radius:8px;padding:12px 14px;margin:16px 0;overflow-wrap:anywhere}
.review p{margin:8px 0}
.actions{display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap}
@media(max-width:480px){.card{padding:22px 18px}.actions button{flex:1}}
</style>
</head>
<body>
<main class="card">
  <h2>Kara David's Wish Note</h2>
  <p class="intro">Enter the target's details and verify them before sending.</p>
  <?php if ($successMessage !== ""): ?><div class="notice success" role="status"><?= h($successMessage) ?></div><?php endif; ?>
  <?php if ($dbError !== ""): ?><div class="notice error" role="alert"><?= h($dbError) ?></div><?php endif; ?>
  <form id="wishForm" method="post" action="">
    <label for="FirstName">Target's First Name</label>
    <input id="FirstName" name="FirstName" maxlength="30" required value="<?= h($firstName) ?>">
    <label for="LastName">Target's Last Name</label>
    <input id="LastName" name="LastName" maxlength="30" required value="<?= h($lastName) ?>">
    <label for="GovPosition">Target's Government Position</label>
    <input id="GovPosition" name="GovPosition" maxlength="20" required value="<?= h($govPosition) ?>">
    <label for="Location">Target's Location</label>
    <input id="Location" name="Location" maxlength="40" value="<?= h($location) ?>">
    <button class="primary" type="submit">Send Wish Note</button>
  </form>
</main>
<div class="modal-backdrop" id="verifyBackdrop" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
  <section class="modal">
    <h3 id="modalTitle">Verify Target Data</h3>
    <p>Please check the information below before confirming.</p>
    <div class="review">
      <p><strong>Target's First Name:</strong> <span id="reviewFirst"></span></p>
      <p><strong>Target's Last Name:</strong> <span id="reviewLast"></span></p>
      <p><strong>Target's Government Position:</strong> <span id="reviewPosition"></span></p>
      <p><strong>Target's Location:</strong> <span id="reviewLocation"></span></p>
    </div>
    <div class="actions">
      <button type="button" class="secondary" id="cancelBtn">Cancel</button>
      <button type="button" class="confirm" id="confirmBtn">Confirm &amp; Send</button>
    </div>
  </section>
</div>
<script>
(() => {
  const form = document.getElementById('wishForm');
  const backdrop = document.getElementById('verifyBackdrop');
  const fields = [
    ['FirstName', 'reviewFirst'],
    ['LastName', 'reviewLast'],
    ['GovPosition', 'reviewPosition'],
    ['Location', 'reviewLocation']
  ];
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    if (!form.reportValidity()) return;
    fields.forEach(([fieldId, reviewId]) => {
      // textContent safely displays user input without interpreting it as HTML.
      document.getElementById(reviewId).textContent =
        document.getElementById(fieldId).value || '(not provided)';
    });
    backdrop.style.display = 'flex';
    document.getElementById('cancelBtn').focus();
  });
  document.getElementById('cancelBtn').addEventListener('click', () => {
    backdrop.style.display = 'none';
  });
  document.getElementById('confirmBtn').addEventListener('click', () => {
    form.submit(); // Intentionally bypasses the submit listener after confirmation.
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') backdrop.style.display = 'none';
  });
})();
</script>
</body>
</html>
<?php if (isset($conn) && $conn instanceof mysqli) { $conn->close(); } ?>
