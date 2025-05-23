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

    function createBlankDocument($pdo, $userOwner) {
        $query = "INSERT INTO document (user_owner) VALUES (?)";
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$userOwner]);

        if ($executeQuery) {
            return ["blankDocumentCreated", $pdo -> lastInsertId()];
        } else {
            return ["error"];
        }
    }

    function getCreatedDocuments($pdo, $userId) {
        // entire row info is returned except for user_owner, and document contents which can be very long
        $query = "SELECT document_id, title, date_created, last_updated FROM document WHERE user_owner = ? ORDER BY last_updated DESC"; 
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$userId]);
        
        if($executeQuery) {
            return $statement -> fetchAll();
        } else {
            return "error";
        }
    }

    function loadDocumentContents($pdo, $documentId) {
        $query = "SELECT content FROM document WHERE document_id = ?";
        $statement = $pdo -> prepare($query);
        $statement -> execute([$documentId]);

        return $statement -> fetch();
    }

    function saveDocumentTitle($pdo, $documentId, $title) {
        $query = "UPDATE document SET title = ? WHERE document_id = ?";
        $statement = $pdo -> prepare($query);
        $statement -> execute([$title, $documentId]);
    }

    function saveDocumentContents($pdo, $documentId, $content) {
        $query = "UPDATE document SET content = ? WHERE document_id = ?";
        $statement = $pdo -> prepare($query);
        $statement -> execute([$content, $documentId]);
    }

    function searchUserByName($pdo, $keyword) {
        $query = "SELECT CONCAT(firstname, ' ', lastname) AS fullname FROM users WHERE CONCAT(firstname, ' ', lastname) LIKE ? ORDER BY fullname ASC"; 
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute(["%".$keyword."%"]);
        
        if($executeQuery) {
            return $statement -> fetchAll();
        } else {
            return "error";
        }
    }

////////////////////////////////////////////////
////////////////////////////////////////////////

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

    function getUserInfoById($pdo, $userId) {
        $query = "SELECT * FROM users WHERE user_id = ?";
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$userId]);
        
        if($executeQuery) {
            return $statement -> fetch();
        } else {
            return "error";
        }
    }

    function getUserFullNameById($pdo, $userId) {
        $query = "SELECT CONCAT(firstname, ' ', lastname) AS fullname FROM users WHERE user_id = ?";
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$userId]);
        
        if($executeQuery) {
            return $statement -> fetch();
        } else {
            return "error";
        }
    }

    function getDocumentTitle($pdo, $documentId) {
        $query = "SELECT title FROM document WHERE document_id = ?";
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$documentId]);
        
        if($executeQuery) {
            return $statement -> fetch();
        } else {
            return "error";
        }
    }
?>