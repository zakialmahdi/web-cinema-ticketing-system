<?php
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbname = "ticketing_system";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = '';
$email = '';
$password1 = '';
$password2 = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password1 = $_POST['password1'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($password1 !== $password2) {
        $error = 'Passwords do not match.';
    } 
    else {
        $sql = "INSERT INTO user_account (Username, Email, Password) 
        VALUES ('$username', '$email', '$password1')";

        if ($conn->query($sql) === TRUE) {
            header('Location: LoginPage.php');
            exit;
        } else {
            $error = "Registration failed: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register Page</title>
  <style>
    .container {
      max-width: 700px;
      margin: 0 auto;
      padding: 25px;
      background-color: white;
      border-radius: 10px;
    }

    label {
      font-weight: bold;
    }

    input {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
      box-sizing: border-box;
    }

    button {
      padding: 8px 14px;
      margin-top: 8px;
      border: 0;
      border-radius: 5px;
      background-color: #222;
      color: white;
      cursor: pointer;
    }
  </style>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 30px; background-color: #bababa; text-align: center;">
  <div class="container">
    <h1 style="margin-bottom: 5px;">Cinema Ticketing System</h1>
    <h2 style="margin-top: 0; color: #555;">Register</h2>

    <hr>

    <form style="margin: 0 auto; max-width: 320px; padding: 40px; background-color: #fff58b; border-radius: 20px;" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
      <label for="username">Username</label><br>
      <input type="text" id="username" name="username" placeholder="Enter your username" minlength="2" pattern="[A-Za-z\s]+" title="Username must contain only letters and spaces." required value="<?php echo htmlspecialchars($username); ?>">
      <br>
      <small style="color: #555;">At least 2 characters.</small>

      <br><br>

      <label for="email">Email</label><br>
      <input type="email" id="email" name="email" placeholder="you@example.com" required value="<?php echo htmlspecialchars($email); ?>">

      <br><br>

      <label for="password1">Password</label><br>
      <input type="password" id="password1" name="password1" placeholder="Enter your password" minlength="8" required value="<?php echo htmlspecialchars($password1); ?>">
      <br>
      <small style="color: #555;">Minimum 8 characters.</small>

      <br><br>

      <label for="password2">Confirm Password</label><br>
      <input type="password" id="password2" name="password2" placeholder="Confirm your password" minlength="8" required value="<?php echo htmlspecialchars($password2); ?>">
      <br>
      <small style="color: #555;">Minimum 8 characters.</small>

      <br><br>
      <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
      <?php endif; ?>
      <button type="submit">Register</button>
    </form>

    <hr>
    <p>Already have an account? <a href="LoginPage.php" style="color: #007bff;">Login here</a></p>
  </div>
</body>
</html>
