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

    if(isset($_POST['updateUserStatusRequest'])) {
        $userId = $_POST['user_id'];
        $userStatus = $_POST['user_status'] == 'active' ? 0 : 1;

        updateUserStatus($pdo, $userId, $userStatus);
    }

    if(isset($_POST['updateUserRoleRequest'])) {
        $userId = $_POST['user_id'];
        $userRole = $_POST['user_role'] == 'admin' ? 'ADMIN' : 'REGULAR';

        updateUserRole($pdo, $userId, $userRole);
    }

    if(isset($_POST['adminLogsTableRequest'])) {
        foreach(getLogData($pdo) as $log) {
            $actionName = $log['action_name'];
            $suspectName = $log['suspect_name'];
            $contentAffected = $log['content_affected'];
            $contentType = $log['content_type'];
            $victimName = $log['victim_name'];
            $remarks = $log['remarks'];
            $dateLogged = $log['date_logged'];

            switch($contentType) {
                case 'ACCOUNT':
                    $contentAffected_string = 'USER ID #' . $contentAffected;
                    break;
                case 'DOCUMENT':
                    $contentAffected_string = 'DOCUMENT ID #' . $contentAffected;
                    break;
                case 'ACCESS':
                    $contentAffected_string = 'ON DOCUMENT ID #' . $contentAffected;
                    break;
                case 'MESSAGE':
                    $contentAffected_string = 'ON DOCUMENT ID #' . $contentAffected;
                    break;
            }

            echo "
                <tr class='border group relative'>
                    <th class='border border-white group-hover:border-blue-500 px-2 py-1'>$actionName</th>
                    <th class='border border-white group-hover:border-blue-500 px-2 py-1'>$suspectName</th>
                    <th class='border border-white group-hover:border-blue-500 px-2 py-1'>$contentAffected_string</th>
                    <th class='border border-white group-hover:border-blue-500 px-2 py-1'>$contentType</th>
                    <th class='border border-white group-hover:border-blue-500 px-2 py-1'>$victimName</th>
                    <th class='border border-white group-hover:border-blue-500 px-2 py-1'>$remarks</th>
                    <th class='border border-white group-hover:border-blue-500 px-2 py-1'>$dateLogged</th>
                </tr>
            ";
        }
    }

    if(isset($_POST['adminAllUsersRequest'])) {
        $function = getAllUsers($pdo);
        foreach($function as $user) {
            $userId = $user['user_id'];
            $fullname = $user['fullname'];
            $dateRegistered = $user['date_registered'];

            $userRole = $user['user_role'];
            $userRole_selectRegular = $userRole == 'REGULAR' ? 'selected' : '';
            $userRole_selectAdmin = $userRole == 'ADMIN' ? 'selected' : '';

            $userStatus = $user['is_suspended'];
            $userStatus_bg = $userStatus == 1 ? 'bg-red-600' : '';
            $userStatus_selectPostive = $userStatus == 0 ? 'selected' : '';
            $userStatus_selectNegative = $userStatus == 1 ? 'selected' : '';

            if($userId != $_SESSION['user_id']) {
                $roleHTML = "
                <select class='outline-none border border-white rounded-xl focus:bg-gray-700 px-2 py-1 hover:cursor-pointer userRoleSelect' data-user-id='$userId'>
                    <option value='regular' $userRole_selectRegular>REGULAR</option>
                    <option value='admin' $userRole_selectAdmin>ADMIN</option>
                </select>
                ";
                $statusHTML = "
                <select class='outline-none border $userStatus_bg border-white rounded-xl focus:bg-gray-700 px-2 py-1 hover:cursor-pointer userStatusSelect' data-user-id='$userId'>
                    <option value='active' $userStatus_selectPostive>ACTIVE</option>
                    <option value='suspended' $userStatus_selectNegative>SUSPENDED</option>
                </select>
                ";
            } else {
                $roleHTML = $userRole == 0 ? "REGULAR" : "ADMIN";
                $statusHTML = $userStatus == 0 ? "ACTIVE" : "SUSPENDED";
            }

            echo "
                <tr class='border group relative'>
                    <th class='border border-white group-hover:border-blue-500 px-2 py-1'>$fullname</th>
                    <th class='border border-white group-hover:border-blue-500 px-2 py-1'>$roleHTML</th>
                    <th class='border border-white group-hover:border-blue-500 px-2 py-1'>$statusHTML</th>
                    <th class='border border-white group-hover:border-blue-500 px-2 py-1'>$dateRegistered</th>
                </tr>
            ";
        }
    }
    
    if(isset($_POST['newBlankDocumentRequest'])) {
        $userOwner = $_POST['owner'];

        $function = createBlankDocument($pdo, $userOwner);
        echo json_encode($function);
    }

    if(isset($_POST['sharedUsersRequest'])) {
        $document_id = $_POST['document_id'];

        $function = getUsersWithDocAccess($pdo, $document_id);
        foreach($function as $user) {
            $userId = $user['user_id'];
            $fullname = $user['fullname'];
            $dateShared = $user['date_shared'];
            $userAccessLevel = getUserDocAccessLevel($pdo, $userId, $document_id)['can_edit'];
            echo "
                <div class='flex flex-row bg-white hover:bg-gray-200 min-h-[70px] px-3 py-1 items-center justify-between text-black'>
                    <h5 class='font-semibold text-base md:text-lg'>$fullname</h5>
                    <div class='w-[30%] md:w-fit items-center md:items-start space-x-0 md:space-x-2 space-y-2 md:space-y-0 py-2 md:py-0'>
                        <p class='hidden md:block text-sm text-gray-500'>Shared on: $dateShared</p>
                        <select class='outline-none border border-black rounded-xl bg-white focus:bg-cyan-300 px-2 py-1 hover:cursor-pointer sharedUserControlLevel' data-user-id='$userId'>
                            <option value='viewer' " . ($userAccessLevel == 0 ? 'selected' : '') . ">Viewer</option>
                            <option value='editor' " . ($userAccessLevel == 1 ? 'selected' : '') . ">Editor</option>
                        </select>
                        <button onclick='revokeDocumentToUser($userId)' class='border border-black rounded-2xl bg-red-300 px-4 py-1 text-base md:text-lg hover:cursor-pointer hover:scale-105 hover:bg-red-500 duration-200'>Remove</button>
                    </div>
                </div>
            ";
        }
    }

    if(isset($_POST['searchUserRequest'])) {
        $keyword = $_POST['keyword'];

        $function = searchUserToShareByName($pdo, $keyword);
        foreach($function as $user) {
            $userId = $user['user_id'];
            $fullname = $user['fullname'];
            echo "
                <div class='flex flex-row min-h-[45px] bg-white hover:bg-gray-200 px-3 py-1 items-center justify-between text-black'>
                    <h5 class='font-semibold text-lg'>$fullname</h5>
                    <button onclick='shareDocumentToUser($userId)' class='border border-black rounded-2xl bg-white hover:bg-cyan-300 my-0 md:my-1 px-3 py-1 md:py-0 text-base md:text-lg hover:cursor-pointer hover:scale-105 duration-200'>Share</button>
                </div>
            ";
        }
    }

    if(isset($_POST['saveDocumentContentRequest'])) {
        $documentId = $_POST['document_id'];
        $content = $_POST['content'];

        saveDocumentContents($pdo, $documentId, $content);
    }

    if(isset($_POST['saveDocumentTitleRequest'])) {
        $documentId = $_POST['document_id'];
        $title = $_POST['content'];

        saveDocumentTitle($pdo, $documentId, $title);
    }

    if(isset($_POST['shareDocumentToUserRequest'])) {
        $userId = $_POST['userId'];
        $documentId = $_POST['documentId'];

        shareDocumentToUser($pdo, $userId, $documentId);
    }

    if(isset($_POST['revokeDocumentToUserRequest'])) {
        $userId = $_POST['userId'];
        $documentId = $_POST['documentId'];

        revokeDocumentToUser($pdo, $userId, $documentId);
    }

    if(isset($_POST['updateUserAccessLevelRequest'])) {
        $userId = $_POST['user_id'];
        $documentId = $_POST['document_id'];
        $accessLevel = $_POST['access_level'] == 'editor' ? 1 : 0;

        updateUserAccessLevel($pdo, $userId, $documentId, $accessLevel);
    }

    if(isset($_POST['sendMessageRequest'])) {
        $message = $_POST['message'];
        $userId = $_POST['user_id'];
        $documentId = $_POST['document_id'];

        $function = sendMessage($pdo, $userId, $documentId, $message);
    }

    if(isset($_POST['chatboxMessagesRequest'])) {
        $documentId = $_POST['document_id'];
        $messages = getDocumentMessages($pdo, $documentId);

        foreach ($messages as $message) {
            $isSender = ($_SESSION['user_id'] == $message['sender_id']);

            $name = $isSender ? "You" : $message['fullname'];
            $content = $message['content'];
            $dateSent = $message['date_sent'];

            $alignment = $isSender ? "items-end" : "items-start";
            $bubbleColor = $isSender ? "bg-cyan-300 rounded-br-none" : "bg-white rounded-bl-none";
            $timestampAlign = $isSender ? "ml-auto" : "mr-auto";
            
            echo "
                <div class='flex flex-col my-4 $alignment messageBox'>
                    <p class='text-sm w-fit max-w-[70%] mx-1 break-words'>$name</p>
                    <div class='border-2 border-black w-fit max-w-[75%] $bubbleColor rounded-xl px-2 py-1 break-words'>$content</div>
                    <p class='w-fit text-xs text-gray-600 $timestampAlign'>$dateSent</p>
                </div>
            ";
        }
    }
?>