<?php
$servername = "localhost";
$username   = "root";
$password   = "9wE!l@vnydz2sJ*Z";
$dbname     = "test";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$venue_id = filter_input(INPUT_POST, 'venue_id');
$result = null;
if ($venue_id) {
    // If id is INT, use "i"; if VARCHAR, use "s"
    $stmt = $conn->prepare("SELECT name, address FROM venues WHERE id = ?");
    $stmt->bind_param("i", $venue_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Venue</title>
</head>
<body>
  <form action="index.php" method="POST">
    <label for="venue_id">Enter Venue ID:</label>
    <input type="text" id="venue_id" name="venue_id" required>
    <button type="submit">Search</button>
  </form>

  <?php
  if ($venue_id) {
      if ($result && $result->num_rows > 0) {
          $row = $result->fetch_assoc();
          echo "<h2>Venue Name: " . htmlspecialchars($row['name']) . "</h2>";
          echo "<p>Address: " . htmlspecialchars($row['address']) . "</p>";
      } else {
          echo "<p>No venue found with ID: " . htmlspecialchars($venue_id) . "</p>";
      }
  }
  $conn->close();
  ?>
</body>
</html>
