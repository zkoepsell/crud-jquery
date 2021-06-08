<?php // Do not put any HTML above this line
require_once "pdo.php";
require_once "util.php";
session_start();
unset($_SESSION['name']); // to log the user out
unset($_SESSION['user_id']); // to log the user out

if (isset($_POST['cancel'])) {
    // Redirect the browser to iindex.php
    unset($_SESSION['name']);
    session_destroy();
    header('Location: index.php');
    return;
}

$salt = 'XyZzy12*_';
// $stored_hash = '1a52e17fa899cf40fb04cfc42e6352f1';  // Pw is php123

if (isset($_POST['email']) || isset($_POST['pass'])) {
    if (strlen($_POST['email']) < 1 || strlen($_POST['pass']) < 1) {
        $_SESSION['error'] = "Email and password are required"; //  changed from $failure
        header("Location: login.php");
        return;
    }

    $check = hash('md5', $salt . $_POST['pass']);
    $stmt = $pdo->prepare("SELECT user_id, name FROM users
         WHERE email = :em AND password = :pw");
    $stmt->execute(array(':em' => $_POST['email'], ':pw' => $check));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row !== false) {
        $_SESSION['name'] = $row['name'];
        $_SESSION['user_id'] = $row['user_id'];
        // Redirect the browser to index.php
        header("Location: index.php");
        return;
    } else {
        $_SESSION['error'] = "Incorrect password";
        error_log("Login fail " . $_POST['email'] . " $check");
        header("Location: login.php");
        return;
    }
    // Check to see if we have some POST data, if we do process it
    if (strpos($_POST['email'], '@') !== false) {
        $_SESSION['error'] = "Email must have an at-sign @";
        header("Location: login.php");
        return;
    }
}
// finished silently handling any incoming POST data
// now it is time to produce output for the page
// Fall through into the View
?>
<!DOCTYPE html>
<html>

<head>
    <?php require_once "bootstrap.php"; ?>
    <title>Zachary Koepsell 's Login Page</title>
</head>

<body>
    <div class="container">
        <h1>Please Log In</h1>
        <?php
        flashMessages()
        ?>
        <form method="POST" action="login.php">
            <label for="nam">User Name</label>
            <input type="text" name="email" id="nam"><br />
            <label for="id_1723">Password</label>
            <input type="password" name="pass" id="id_1723"><br />
            <input type="submit" onclick="doValidate();" value="Log In">
            <a href="index.php">Cancel</a>
        </form>
        <p>
            For a password hint, view source and find a password hint
            in the HTML comments.
            <!-- Hint: The password is the four character sound a cat
makes (all lower case) followed by 123. -->
        </p>
        <script>
            function doValidate() {
                console.log('Validating...');
                try {
                    addr = document.getElementById('email').value;
                    pw = document.getElementById('id_1723').value;
                    console.log("Validating addr=" + addr + " pw=" + pw);
                    if (addr == null || addr == "" || pw == null || pw == "") {
                        alert("Both fields must be filled out");
                        return false;
                    }
                    if (addr.indexOf('0') == -1) {
                        alert("Invalid email address");
                        return false;
                    }
                    return true;
                } catch (e) {
                    return false;
                }
                return false;
            }
        </script>
    </div>
</body>