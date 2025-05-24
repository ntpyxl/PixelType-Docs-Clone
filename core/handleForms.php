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

        $function = getDocumentMessages($pdo, $documentId);
        foreach($function as $message) {
            $senderId = $message['sender_id'];
            $fullname = $message['fullname'];
            $content = $message['content'];
            $dateSent = $message['date_sent'];

            if($_SESSION['user_id'] == $senderId) {
                echo "
                    <div class='flex flex-col my-4 items-end messageBox'>
                        <p class='text-sm w-fit max-w-[70%] mx-1 break-words'>You</p>
                        <div class='border-2 border-black w-fit max-w-[75%] bg-cyan-300 rounded-xl rounded-br-none px-2 py-1 break-words'>$content</div>
                        <p class='w-fit text-xs text-gray-600'>$dateSent</p>
                    </div>
                ";
            } else {
                echo "
                    <div class='flex flex-col my-4 items-start messageBox'>
                        <p class='text-sm w-fit max-w-[70%] mx-1 break-words'>$fullname</p>
                        <div class='border-2 border-black w-fit max-w-[75%] bg-white rounded-xl rounded-bl-none mr-auto px-2 py-1 break-words'>$content</div>
                        <p class='w-fit text-xs text-gray-600 mr-auto'>$dateSent</p>
                    </div>
                ";
            }
        }
    }
?>