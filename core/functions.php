<?php 
    require_once "dbConfig.php";
    require_once "phpDiff.php";

    function registerAccount($pdo, $username, $password, $firstname, $lastname){ // logged
        $uacQuery = "INSERT INTO user_accounts (username, userpass) VALUES (?, ?)";
        $uacStatement = $pdo -> prepare($uacQuery);
        $execute_uacQuery = $uacStatement -> execute([$username, $password]);

        $accQuery = "INSERT INTO users (firstname, lastname) VALUES (?, ?)";
        $accStatement = $pdo -> prepare($accQuery);
        $execute_accQuery = $accStatement -> execute([$firstname, $lastname]);

        if($execute_uacQuery && $execute_accQuery) {
            logAction($pdo, "CREATED", $pdo -> lastInsertId(), $pdo -> lastInsertId(), "ACCOUNT", $pdo -> lastInsertId(), "Created an account");
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
        $userInfoData = getUserInfoById($pdo, $userAccountData['user_id']);
        if($userInfoData['is_suspended']) {
            return "userSuspended";
        } else if(password_verify($password, $userAccountData['userpass'])) {
            $_SESSION['user_id'] = $userAccountData['user_id'];
            $_SESSION['user_role'] = $userInfoData['user_role'];
            return "loginSuccess";
        } else {
            return "incorrectPassword";
        }
    }

    function getAllUsers($pdo) {
        $query = "SELECT user_id, CONCAT(firstname, ' ', lastname) AS fullname, user_role, is_suspended, date_registered FROM users ORDER BY (user_role = 'ADMIN') DESC, date_registered DESC"; 
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute();
        
        if($executeQuery) {
            return $statement -> fetchAll();
        } else {
            return "error";
        }
    }

    function updateUserRole($pdo, $userId, $userRole) { // logged
        $query = "UPDATE users SET user_role = ? WHERE user_id = ?";
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$userRole, $userId]);

        if($executeQuery) {
            logAction($pdo, "UPDATED", $_SESSION['user_id'], $userId, "ACCOUNT", $userId, "Changed user role to " . ($userRole == "ADMIN" ? "ADMIN" : "REGULAR"));
        }
    }

    function updateUserStatus($pdo, $userId, $userStatus) { // logged
        $query = "UPDATE users SET is_suspended = ? WHERE user_id = ?";
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$userStatus, $userId]);

        if($executeQuery) {
            logAction($pdo, "UPDATED", $_SESSION['user_id'], $userId, "ACCOUNT", $userId, "Changed user status to " . ($userStatus == 1 ? "SUSPENDED" : "ACTIVE"));
        }
    }

    function createBlankDocument($pdo, $userOwner) { // logged
        $query = "INSERT INTO document (user_owner) VALUES (?)";
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$userOwner]);

        if ($executeQuery) {
            logAction($pdo, "CREATED", $_SESSION['user_id'], $pdo -> lastInsertId(), "DOCUMENT", $_SESSION['user_id'], "Created a new blank document");
            return ["blankDocumentCreated", $pdo -> lastInsertId()];
        } else {
            return ["error"];
        }
    }

    function getAllDocuments($pdo) {
        // entire row info is returned except for document contents which can be very long
        $query = "SELECT document.document_id, document.title, CONCAT(users.firstname, ' ', users.lastname) AS owner_name, document.date_created, document.last_updated FROM document INNER JOIN users ON document.user_owner = users.user_id ORDER BY last_updated DESC"; 
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute();
        
        if($executeQuery) {
            return $statement -> fetchAll();
        } else {
            return "error";
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

    function saveDocumentTitle($pdo, $documentId, $title) { // logged
        $query = "UPDATE document SET title = ? WHERE document_id = ?";
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$title, $documentId]);

        if($executeQuery) {
            logAction($pdo, "UPDATED", $_SESSION['user_id'], $documentId, "DOCUMENT", getDocumentOwner($pdo, $documentId)['user_owner'], "Updated document title.");
        }
    }

    function extractInsertedAndDeletedHTML($diffHtml) {
        $inserted = [];
        $deleted = [];

        // Extract inserted content
        if (preg_match_all('/<ins>(.*?)<\/ins>/s', $diffHtml, $matches)) {
            $inserted = $matches[1];
        }

        if (preg_match_all('/<del>(.*?)<\/del>/s', $diffHtml, $matches)) {
            $deleted = $matches[1];
        }

        return [
            'inserted' => $inserted,
            'deleted' => $deleted
        ];
    }

    function saveDocumentContents($pdo, $documentId, $newContent) {
        $oldContent = loadDocumentContents($pdo, $documentId)['content'];
        $extractedDiffHTML = extractInsertedAndDeletedHTML(getDiffHTML($oldContent, $newContent));

        $logParts = [];
        if (!empty($extractedDiffHTML['inserted'])) {
            $logParts[] = "<div class='text-green-500'><strong>Inserted:</strong><br>" . implode("<br>", $extractedDiffHTML['inserted']) . "</div>";
        }
        if (!empty($extractedDiffHTML['deleted'])) {
            $logParts[] = "<div class='text-red-500 mt-2'><strong>Deleted:</strong><br>" . implode("<br>", $extractedDiffHTML['deleted']) . "</div>";
        }

        $query = "UPDATE document SET content = ? WHERE document_id = ?";
        $statement = $pdo->prepare($query);
        $executeQuery = $statement->execute([$newContent, $documentId]);

        if ($executeQuery) {
            $logHTML = "<span class='text-left'>" . implode("<br><br>", $logParts) . "</span>";
            logDocEdit($pdo, $_SESSION['user_id'], $documentId, implode("<br>", $extractedDiffHTML['inserted']), implode("<br>", $extractedDiffHTML['deleted']));
            logAction($pdo, "UPDATED", $_SESSION['user_id'], $documentId, "DOCUMENT", getDocumentOwner($pdo, $documentId)['user_owner'], "Edited document: " . $logHTML);
        }
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

    function searchUserToShareByName($pdo, $documentId, $keyword) {
        $query = "SELECT users.user_id, CONCAT(users.firstname, ' ', users.lastname) AS fullname FROM users LEFT JOIN user_shared_access AS usa ON users.user_id = usa.user_id AND usa.document_id = ? WHERE CONCAT(users.firstname, ' ', users.lastname) LIKE ? AND usa.user_id IS NULL ORDER BY fullname ASC"; 
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$documentId, "%".$keyword."%"]);
        
        if($executeQuery) {
            return $statement -> fetchAll();
        } else {
            return "error";
        }
    }
    
    function shareDocumentToUser($pdo, $userId, $documentId) { // logged
        $query = "INSERT INTO user_shared_access (user_id, document_id) VALUES (?, ?)";
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$userId, $documentId]);

        if($executeQuery) {
            logAction($pdo, "CREATED", $_SESSION['user_id'], $documentId, "ACCESS", $userId, "Shared access to document");
            return "documentShared";
        } else {
            return "error";
        }
    }

    function revokeDocumentToUser($pdo, $userId, $documentId) { // logged
        $query = "DELETE FROM user_shared_access WHERE user_id = ? AND document_id = ?";
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$userId, $documentId]);

        if($executeQuery) {
            logAction($pdo, "DELETED", $_SESSION['user_id'], $documentId, "ACCESS", $userId, "Revoked access to document");
            return "documentRevoked";
        } else {
            return "error";
        }
    }

    function updateUserAccessLevel($pdo, $userId, $documentId, $accessLevel) { // logged
        $query = "UPDATE user_shared_access SET can_edit = ? WHERE user_id = ? AND document_id = ?";
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$accessLevel, $userId, $documentId]);

        if($executeQuery) {
            logAction($pdo, "UPDATED", $_SESSION['user_id'], $documentId, "ACCESS", $userId, "Changed document access to " . ($accessLevel == 0 ? "VIEWER" : "EDITOR"));
        }
    }

    function sendMessage($pdo, $userId, $documentId, $message) { // logged
        $query = "INSERT INTO document_messages (on_document_id, sender_id, content) VALUES (?, ?, ?)";
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$documentId, $userId, $message]);

        if($executeQuery) {
            logAction($pdo, "CREATED", $_SESSION['user_id'], $pdo -> lastInsertId(), "MESSAGE", $_SESSION['user_id'], 'Sent "' . $message . '"');
            return "messageSent";
        } else {
            return "error";
        }
    }

    function getDocumentMessages($pdo, $documentId) {
        $query = "SELECT dm.sender_id, CONCAT(users.firstname, ' ', users.lastname) AS fullname, dm.content, dm.date_sent FROM document_messages AS dm INNER JOIN users ON dm.sender_id = users.user_id WHERE on_document_id = ? ORDER BY date_sent ASC";
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$documentId]);

        if($executeQuery) {
            return $statement -> fetchAll();
        } else {
            return "error";
        }
    }

    // TYPE: ACCOUNT = AFFECTED AND OWNER IS ACCOUNT ID
    // TYPE: DOCUMENT = AFFECTED IS DOCUMENT ID, OWNER IS DOCUMENT OWNER ID
    // TYPE: ACCESS = AFFECTED IS DOCUMENT ID, OWNER IS SHARED USER
    // TYPE: MESSAGE = AFFECTED IS DOCUMENT ID, OWNER IS SESSION USER ID
    function logAction($pdo, $action, $suspect, $contentVictim, $contentType, $userVictim, $remarks) {
        $query = "INSERT INTO logs (action_name, done_by, content_affected, content_type, content_owner, remarks) VALUES (?, ?, ?, ?, ?, ?)";
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$action, $suspect, $contentVictim, $contentType, $userVictim, $remarks]);

        if($executeQuery) {
            return "actionLogged";
        } else {
            return "error";
        }
    }

    function getLogData($pdo) {
        $query = "SELECT logs.action_name, CONCAT(suspect.firstname, ' ', suspect.lastname) AS suspect_name, logs.content_affected, logs.content_type, CONCAT(victim.firstname, ' ', victim.lastname) AS victim_name, logs.remarks, logs.date_logged FROM logs LEFT JOIN users AS suspect ON logs.done_by = suspect.user_id LEFT JOIN users AS victim ON logs.content_owner = victim.user_id ORDER BY date_logged DESC";
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([]);

        if($executeQuery) {
            return $statement -> fetchAll();
        } else {
            return "error";
        }
    }

    function logDocEdit($pdo, $doneBy, $documentId, $insContent, $delContent) {
        $query = "INSERT INTO doc_logs (done_by, document_id, inserted_content, deleted_content) VALUES (?, ?, ?, ?)";
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$doneBy, $documentId, $insContent, $delContent]);

        if($executeQuery) {
            return "docEditLogged";
        } else {
            return "error";
        }
    }

    function getDocLogData($pdo, $documentId) {
        $query = "SELECT CONCAT(users.firstname, ' ', users.lastname) AS suspect_name, dl.document_id, dl.inserted_content, dl.deleted_content, dl.date_logged FROM doc_logs AS dl LEFT JOIN users ON dl.done_by = users.user_id WHERE dl.document_id = ? ORDER BY date_logged DESC";
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$documentId]);

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
    
    function getUsersNameSharedDoc($pdo, $documentId) {
        $query = "SELECT CONCAT(users.firstname, ' ', users.lastname) AS fullname, usa.can_edit FROM user_shared_access AS usa INNER JOIN users ON usa.user_id = users.user_id WHERE usa.document_id = ? ORDER BY date_shared DESC";
        $statement = $pdo -> prepare($query);
        $executeQuery = $statement -> execute([$documentId]);
        
        if($executeQuery) {
            return $statement -> fetchAll();
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
            // elvis operator because this will return null if docs isn't shared to user in the first place
            return $statement -> fetch() ?: ['can_edit' => 0];
        } else {
            return "error";
        }
    }
?>