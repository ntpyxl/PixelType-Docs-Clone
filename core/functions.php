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

    function getSharedDocuments($pdo, $userId) {
        // entire row info is returned except for  document contents which can be very long
        $query = "SELECT document.document_id, document.title, CONCAT(users.firstname, ' ', users.lastname) AS owner_name, document.date_created, document.last_updated FROM document INNER JOIN user_shared_access AS usa ON document.document_id = usa.document_id INNER JOIN users ON document.user_owner = users.user_id WHERE usa.user_id = ? ORDER BY last_updated DESC"; 
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$userId]);
        
        if($executeQuery) {
            return $statement -> fetchAll();
        } else {
            return "error";
        }
    }

    function getUserIdsWithDocAccess($pdo, $documentId) {
        $query = "SELECT user_id FROM user_shared_access WHERE document_id = ?"; 
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$documentId]);
        
        if($executeQuery) {
            return array_column($statement->fetchAll(), 'user_id');
        } else {
            return [];
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

    function getUsersWithDocAccess($pdo, $documentId) {
        $query = "SELECT usa.user_id, CONCAT(users.firstname, ' ', users.lastname) AS fullname, usa.can_edit, usa.date_shared FROM user_shared_access AS usa INNER JOIN users ON usa.user_id = users.user_id WHERE document_id = ? ORDER BY date_shared DESC"; 
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$documentId]);
        
        if($executeQuery) {
            return $statement -> fetchAll();
        } else {
            return "error";
        }
    }

    function searchUserToShareByName($pdo, $keyword) {
        $query = "SELECT users.user_id, CONCAT(users.firstname, ' ', users.lastname) AS fullname FROM users LEFT JOIN user_shared_access AS usa ON users.user_id = usa.user_id WHERE CONCAT(users.firstname, ' ', users.lastname) LIKE ? AND usa.user_id IS NULL ORDER BY fullname ASC"; 
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute(["%".$keyword."%"]);
        
        if($executeQuery) {
            return $statement -> fetchAll();
        } else {
            return "error";
        }
    }
    
    function shareDocumentToUser($pdo, $userId, $documentId) {
        $query = "INSERT INTO user_shared_access (user_id, document_id) VALUES (?, ?)";
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$userId, $documentId]);

        if($executeQuery) {
            return "documentShared";
        } else {
            return "error";
        }
    }

    function revokeDocumentToUser($pdo, $userId, $documentId) {
        $query = "DELETE FROM user_shared_access WHERE user_id = ? AND document_id = ?";
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$userId, $documentId]);

        if($executeQuery) {
            return "documentRevoked";
        } else {
            return "error";
        }
    }

    function updateUserAccessLevel($pdo, $userId, $documentId, $accessLevel) {
        $query = "UPDATE user_shared_access SET can_edit = ? WHERE user_id = ? AND document_id = ?";
        $statement = $pdo -> prepare($query);
        $statement -> execute([$accessLevel, $userId, $documentId]);
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

    function getDocumentOwner($pdo, $documentId) {
        $query = "SELECT user_owner FROM document WHERE document_id = ?";
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$documentId]);
        
        if($executeQuery) {
            return $statement -> fetch();
        } else {
            return "error";
        }
    }

    function getUserDocAccessLevel($pdo, $userId, $documentId) {
        $query = "SELECT can_edit FROM user_shared_access WHERE user_id = ? AND document_id = ?";
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$userId, $documentId]);
        
        if($executeQuery) {
            return $statement -> fetch();
        } else {
            return "error";
        }
    }
?>