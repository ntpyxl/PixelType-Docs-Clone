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

    if(isset($_POST['searchUserRequest'])) {
        $keyword = $_POST['keyword'];

        $function = searchUserByName($pdo, $keyword);

        foreach($function as $user) {
            echo "
                <div class='flex flex-row bg-white hover:bg-gray-200 px-3 py-1 justify-between text-black'>
                    <h5 class='font-semibold text-lg'>" . $user['fullname'] . "</h5>
                    <button onclick='' class='border border-black rounded-2xl px-3 py-1 text-lg hover:cursor-pointer hover:scale-105 hover:bg-cyan-300 duration-200'>Share</button>
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
?>