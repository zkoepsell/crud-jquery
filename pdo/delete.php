<?php // Do not put any HTML above this line
require_once "pdo.php";
require_once "util.php";
require_once "head.php";

session_start();

// Demand a GET parameter
if (!isset($_SESSION['user_id'])) {    // Demand a GET parameter
    die('ACCESS DENIED');
}

if (!isset($_REQUEST['profile_id'])) {
    $_SESSION['error'] = "Missing profile_id";
    header("Location: index.php");
    return;
}

if (isset($_POST['cancel'])) {
    // Redirect the browser to game.php
    header("Location: index.php");
    return;
}

// load up the profile in question
$stmt = $pdo->prepare('SELECT * FROM Profile WHERE profile_id = :pid AND user_id = :uid');
$stmt->execute(array(
    ':pid' => $_REQUEST['profile_id'],
    ':uid' => $_SESSION['user_id']
));
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
if ($profile === false) {
    $_SESSION['error'] = "Could not load profile edit";
    header("Location: index.php");
    return;
}

// load up the position rows
$positions = loadPos($pdo, $_REQUEST['profile_id']);


if (isset($_POST['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM Profile WHERE profile_id=:pid AND user_id =:uid');
    $stmt->execute(array(
        ':pid' => $_REQUEST['profile_id'],
    ':uid' => $_SESSION['user_id']
));
    $_SESSION['success'] = "Record deleted";
    header("Location: index.php?name=");
    return;
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Zachary Koepsell 's Automobiles Database</title>
    <?php require_once "head.php"; ?>
</head>

<body>
    <p>
        Confirm: Deleting <?= htmlentities($_REQUEST['profile_id']) ?>
    </p>
    <form method="POST">
        <input type="hidden" name="profile_id" value="<?= $_REQUEST['profile_id'] ?>">
        <input type="submit" value="Delete" name="delete">
        <a href="index.php" name="cancel">Cancel</a>
    </form>
    <script>
        countPos = <?= $pos ?>;

        // http://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
        $(document).ready(function() {
            window.console && console.log('Document ready called');
            $('#addPos').click(function(event) {
                // http://api.jquery.com/event.preventdefault/
                event.preventDefault();
                if (countPos >= 9) {
                    alert("Maximum of nine position entries exceeded");
                    return;
                }
                countPos++;
                window.console && console.log("Adding position " + countPos);
                $('#position_fields').append(
                    '<div id ="position' + countPos + '"> \
            <p>Year: <input type="text" name="year' + countPos + '" value="" /> \
            <input type="button" value="-" \
            onclick="$(\'#position' + countPos + '\').remove(); return false;"></p> \
            <textarea name="desc' + countPos + '" rows="8" cols="80"></textarea>\
            </div>');
            });
        });
    </script>
</body>

</html>