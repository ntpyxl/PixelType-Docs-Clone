<?php 
    require_once "dbConfig.php";
    require_once "functions.php";

    if(isset($_POST['accountRegistrationRequest'])) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $verifyPassword = $_POST['verifyPassword']; 
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];

        if(checkUsernameExistence($pdo, $username) == "usernameNotExisting") {
            if($_POST['password'] == $_POST['verifyPassword']) {
                $function = registerAccount($pdo, $username, $password, $firstname, $lastname);
                echo $function;
            } else {
                echo "passwordNotVerified";
            }
        } else {
            echo "usernameExists";
        }
    }

    if(isset($_POST['userLoginRequest'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $function = loginUser($pdo, $username, $password);
        echo $function;
    }

    if(isset($_POST['newBlankDocumentRequest'])) {
        $userOwner = $_POST['owner'];

        $function = createBlankDocument($pdo, $userOwner);
        echo json_encode($function);
    }
?>