<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ticketing_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userID = (int)($_COOKIE['UserID'] ?? 0);

if ($userID <= 0) {
    header('Location: LoginPage.php');
    exit;
}

$movieValue = '';
$ticketTypeValue = '';
$quantityValue = '';
$showtimeValue = '';
$fnbValue = '';
$fnbQuantityValue = 0;
$message = '';
$messageColor = 'green';

$ticketPrices = [
    "adult" => 18,
    "student" => 12,
    "child" => 10
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movieValue = (int)($_POST['movie'] ?? 0);
    $ticketTypeValue = $_POST['ticket'] ?? '';
    $quantityValue = (int)($_POST['quantity'] ?? 0);
    $showtimeValue = $_POST['showtime'] ?? '';
    $fnbValue = (int)($_POST['fnb'] ?? 0);
    $fnbQuantityValue = (int)($_POST['fnb_quantity'] ?? 0);

    $ticketPrice = $ticketPrices[$ticketTypeValue] ?? 0;
    $fnbPrice = 0;

    if ($fnbValue <= 0) {
        $fnbQuantityValue = 0;
    }

    if ($fnbValue > 0) {
        $fnbResult = $conn->query("SELECT Price FROM fnb WHERE FnbID = $fnbValue");

        if ($fnbResult && $fnbResult->num_rows > 0) {
            $fnbRow = $fnbResult->fetch_assoc();
            $fnbPrice = $fnbRow['Price'];
        } else {
            $fnbValue = 0;
            $fnbQuantityValue = 0;
        }
    }

    $totalPrice = ($ticketPrice * $quantityValue) + ($fnbPrice * $fnbQuantityValue);

    if ($movieValue <= 0 || empty($ticketTypeValue) || $quantityValue <= 0 || empty($showtimeValue)) {
        $message = "Please complete the booking form.";
        $messageColor = "red";
    } elseif ($fnbValue > 0 && $fnbQuantityValue <= 0) {
        $message = "Please enter F&B quantity.";
        $messageColor = "red";
    } else {
        if ($fnbValue <= 0) {
            $sql = "INSERT INTO sales (UserID, MovieID, TicketType, Quantity, Showtime, FnbID, FnbQuantity, TotalPrice)
                    VALUES ($userID, $movieValue, '$ticketTypeValue', $quantityValue, '$showtimeValue', NULL, 0, $totalPrice)";
        } else {
            $sql = "INSERT INTO sales (UserID, MovieID, TicketType, Quantity, Showtime, FnbID, FnbQuantity, TotalPrice)
                    VALUES ($userID, $movieValue, '$ticketTypeValue', $quantityValue, '$showtimeValue', $fnbValue, $fnbQuantityValue, $totalPrice)";
        }

        if ($conn->query($sql) === TRUE) {
            $saleID = $conn->insert_id;

            $movieValue = '';
            $ticketTypeValue = '';
            $quantityValue = '';
            $showtimeValue = '';
            $fnbValue = '';
            $fnbQuantityValue = 0;

            header("Location: CheckoutPage.php?sale_id=$saleID");
            exit;
        } else {
            $message = "Booking failed: " . $conn->error;
            $messageColor = "red";
        }
    }
}

$movies = $conn->query("SELECT MovieID, MovieName FROM movies");
$foods = $conn->query("SELECT FnbID, FnbName, Price FROM fnb");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cinema Menu</title>
  <style>
    .container {
      max-width: 700px;
      margin: 0 auto;
      padding: 25px;
      background-color: white;
      border-radius: 10px;
    }

    td {
      padding: 10px;
    }

    th {
      background-color: #fff58b;
    }

    select,
    input {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
      box-sizing: border-box;
    }

    button {
      padding: 8px 14px;
      margin: 5px;
      border: 0;
      border-radius: 5px;
      cursor: pointer;
    }
  </style>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 30px; background-color: #bababa; text-align: center;">
  <div class="container">
    <h1 style="margin-bottom: 5px;">Cinema Ticketing System</h1>
    <h2 style="margin-top: 0; color: #555;">Main Menu</h2>

    <hr>

    <h3 style="margin-top: 28px; color: #222;">Now Showing</h3>
    <?php $movieList = $conn->query("SELECT MovieName FROM movies"); ?>
    <p>
      <?php while ($movieItem = $movieList->fetch_assoc()): ?>
      <?php echo htmlspecialchars($movieItem['MovieName']); ?><br>
      <?php endwhile; ?>
    </p>

    <h3 style="margin-top: 28px; color: #222;">Ticket Prices</h3>
    <table style="margin: 0 auto; border-collapse: collapse; width: 80%;" border="1" cellpadding="8" cellspacing="0">
      <tr>
        <th>Ticket Type</th>
        <th>Price</th>
      </tr>
      <tr>
        <td>Adult</td>
        <td>RM 18</td>
      </tr>
      <tr>
        <td>Student</td>
        <td>RM 12</td>
      </tr>
      <tr>
        <td>Child</td>
        <td>RM 10</td>
      </tr>
    </table><br>

    <h3 style="margin-top: 28px; color: #222;">Food and Drinks</h3>
    <?php $fnbList = $conn->query("SELECT FnbName, Price FROM fnb"); ?>
    <p>
      <?php while ($fnbItem = $fnbList->fetch_assoc()): ?>
      <?php echo htmlspecialchars($fnbItem['FnbName']); ?> - RM <?php echo number_format($fnbItem['Price'], 2); ?><br>
      <?php endwhile; ?>
    </p>

    <h3 style="margin-top: 28px; color: #222;">Book a Ticket</h3>
    <?php if (!empty($message)): ?>
    <p style="color: <?php echo $messageColor; ?>; font-weight: bold;">
      <?php echo htmlspecialchars($message); ?>
    </p>
    <?php endif; ?>
    <form style="margin: 0 auto; max-width: 520px; padding: 40px; background-color: #fff58b; border-radius: 10px;" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
      <label for="movie">Choose Movie:</label><br>
      <select id="movie" name="movie" required>
        <option value="" disabled <?php echo ($movieValue === '') ? 'selected' : ''; ?>>Select a movie</option>
        <?php while ($movie = $movies->fetch_assoc()): ?>
        <option value="<?php echo $movie['MovieID']; ?>" <?php echo ($movieValue == $movie['MovieID']) ? 'selected' : ''; ?>>
        <?php echo htmlspecialchars($movie['MovieName']); ?>
        </option>
        <?php endwhile; ?>
      </select>

      <br><br>

      <label for="ticket">Ticket Type:</label><br>
      <select id="ticket" name="ticket" required>
        <option value="" disabled <?php echo ($ticketTypeValue === '') ? 'selected' : ''; ?>>Select ticket type</option>
        <option value="adult" <?php echo ($ticketTypeValue === 'adult') ? 'selected' : ''; ?>>Adult</option>
        <option value="student" <?php echo ($ticketTypeValue === 'student') ? 'selected' : ''; ?>>Student</option>
        <option value="child" <?php echo ($ticketTypeValue === 'child') ? 'selected' : ''; ?>>Child</option>
      </select>

      <br><br>

      <label for="quantity">Quantity:</label><br>
      <input type="number" id="quantity" name="quantity" min="1" max="10" required value="<?php echo htmlspecialchars($quantityValue ?? ''); ?>">

      <br><br>

      <label for="showtime">Showtime:</label><br>
      <select id="showtime" name="showtime" required>
        <option value="" disabled <?php echo ($showtimeValue === '') ? 'selected' : ''; ?>>Select showtime</option>
        <option value="11am" <?php echo ($showtimeValue === '11am') ? 'selected' : ''; ?>>11:00 AM</option>
        <option value="2pm" <?php echo ($showtimeValue === '2pm') ? 'selected' : ''; ?>>2:00 PM</option>
        <option value="6pm" <?php echo ($showtimeValue === '6pm') ? 'selected' : ''; ?>>6:00 PM</option>
        <option value="9pm" <?php echo ($showtimeValue === '9pm') ? 'selected' : ''; ?>>9:00 PM</option>
      </select>

      <br><br>

      <label for="fnb">Food and Drinks:</label><br>
      <select id="fnb" name="fnb">
        <option value="">No food or drink</option>
        <?php while ($food = $foods->fetch_assoc()): ?>
        <option value="<?php echo $food['FnbID']; ?>" <?php echo ($fnbValue == $food['FnbID']) ? 'selected' : ''; ?>>
        <?php echo htmlspecialchars($food['FnbName']); ?> - RM <?php echo number_format($food['Price'], 2); ?>
        </option>
        <?php endwhile; ?>
      </select>

      <br><br>

      <label for="fnb_quantity">F&B Quantity:</label><br>
      <input type="number" id="fnb_quantity" name="fnb_quantity" min="0" max="10" value="<?php echo htmlspecialchars($fnbQuantityValue); ?>">

      <br><br>
      <button type="submit" style="background-color: #222; color: white;">Book Now</button>
      <button type="reset">Clear</button>
    </form>

    <hr>

    <p><a href="LoginPage.php" style="color: #007bff;">Back to Login</a></p>
  </div>
</body>
</html>
