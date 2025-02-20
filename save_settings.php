<?php

@include 'db_config.php';

// Establish database connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $siteName = $conn->real_escape_string($_POST['siteName']);
    $timezone = $conn->real_escape_string($_POST['timezone']);
    $language = $conn->real_escape_string($_POST['language']);
    $theme = $conn->real_escape_string($_POST['theme']);
    $minPasswordLength = intval($_POST['minPasswordLength']);
    $accountLockout = intval($_POST['accountLockout']);
    $smtpHost = $conn->real_escape_string($_POST['smtpHost']);
    $smtpPort = intval($_POST['smtpPort']);
    $emailFrom = $conn->real_escape_string($_POST['emailFrom']);

    // Update query
    $sql = "UPDATE settings SET 
                site_name = '$siteName',
                timezone = '$timezone',
                language = '$language',
                theme = '$theme',
                min_password_length = $minPasswordLength,
                account_lockout = $accountLockout,
                smtp_host = '$smtpHost',
                smtp_port = $smtpPort,
                email_from = '$emailFrom'
            WHERE id = 1";

    if ($conn->query($sql) === TRUE) {
        header("Location: settings.php?success=1"); // Redirect with success flag
    } else {
        header("Location: settings.php?error=1"); // Redirect with error flag
    }
}

// Close connection
$conn->close();
?>
<script>
document.getElementById("settingsForm").addEventListener("submit", function (e) {
    const siteName = document.getElementById("siteName").value;
    const emailFrom = document.getElementById("emailFrom").value;

    if (!siteName || !emailFrom) {
        alert("Site Name and Email From fields are required!");
        e.preventDefault(); // Prevent form submission
        return;
    }

    // Confirmation dialog
    if (!confirm("Are you sure you want to save these settings?")) {
        e.preventDefault();
    }
});
</script>
