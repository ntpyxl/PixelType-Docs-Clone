<?php 
    require_once "dbConfig.php";

    function registerAccount($pdo, $username, $password, $firstname, $lastname){
        $uacQuery = "INSERT INTO user_accounts (username, userpass) VALUES (?, ?)";
        $uacStatement = $pdo -> prepare($uacQuery);
        $execute_uacQuery = $uacStatement -> execute([$username, $password]);

        $accQuery = "INSERT INTO users (firstname, lastname) VALUES (?, ?)";
        $accStatement = $pdo -> prepare($accQuery);
        $execute_accQuery = $accStatement -> execute([$firstname, $lastname]);

        if($execute_uacQuery && $execute_accQuery) {
            return "accountRegistered";
        } else {
            return "error";
        }
    }

    function loginUser($pdo, $username, $password) {
        $userExistence = checkUsernameExistence($pdo, $username);
        if($userExistence == "usernameNotExisting") {
            return "usernameNotExisting";
        } else if ($userExistence == "error") {
            return "error";
        }

        $userAccountData = getUserAccByUsername($pdo, $username);
        if(password_verify($password, $userAccountData['userpass'])) {
            $_SESSION['user_id'] = $userAccountData['user_id'];
            return "loginSuccess";
        } else {
            return "incorrectPassword";
        }
    }

    ///////////////////////////
    ///////////////////////////
    ///////////////////////////

    function checkUsernameExistence($pdo, $username){
        $query = "SELECT * FROM user_accounts WHERE username = ?";
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$username]);

        if($executeQuery && $statement -> rowCount() >= 1) {
            return "usernameExists";
        } else if($executeQuery && $statement -> rowCount() == 0) {
            return "usernameNotExisting";
        } else {
            return "error";
        }
    }

    function getUserAccByUsername($pdo, $username) {
        $query = "SELECT * FROM user_accounts WHERE username = ?";
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$username]);
        
        if($executeQuery && $statement -> rowCount() == 1) {
            return $statement -> fetch();
        } else if($executeQuery && $statement -> rowCount() == 0) {
            return "usernameNotExisting";
        } else {
            return "error";
        }
    }

    function getUserInfoById($pdo, $user_id) {
        $query = "SELECT * FROM users WHERE user_id = ?";
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$user_id]);
        
        if($executeQuery) {
            return $statement -> fetch();
        } else {
            return "failed";
        }
    }
?>