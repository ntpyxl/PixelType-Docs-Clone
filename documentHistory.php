<?php
require_once "core/functions.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
}

$isDocumentOwner = false;
$isAdmin = false;
$canEdit = false;
if($_SESSION['user_id'] == getDocumentOwner($pdo, $_GET['document_id'])['user_owner']) {
    $isDocumentOwner = true;
} else {
    if(getUserDocAccessLevel($pdo, $_SESSION['user_id'], $_GET['document_id'])['can_edit'] == 1) {
        $canEdit = true;
    }
}
if($_SESSION['user_role'] == "ADMIN") {
    $isAdmin = true;
}

if(!$isDocumentOwner && !$isAdmin && !$canEdit) {
    header("Location: document.php?document_id=" . $_GET['document_id']);
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PixelType Document</title>

        <link href="styles.css" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    </head>
    <body class="bg-gray-700">
        <div class="bg-gray-900 text-white flex justify-between items-center px-4 py-3">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-2 md:space-y-0 w-full">
                <div class="flex flex-col md:flex-row md:flex-wrap md:items-center md:space-x-2 space-y-2 md:space-y-0">
                    <button onclick="window.location='document.php?document_id=<?php echo $_GET['document_id'] ?>'" class="border border-white rounded-2xl px-4 py-1 text-lg hover:cursor-pointer hover:scale-110 hover:bg-gray-800 duration-200">Return</button>

                    <h3 class="w-full md:w-[40vw] text-xl font-semibold text-white ml-0 md:ml-3 mt-1 md:mt-0 px-2 py-1"><?php echo getDocumentTitle($pdo, $_GET['document_id'])['title'] ?> History</h3>
                </div>
            </div>
        </div>

        <div class="mt-2 space-y-3">
            <?php
            foreach(getDocLogData($pdo, $_GET['document_id']) as $change) {
            ?>
                <div class="flex flex-col md:flex-row bg-gray-800 px-4 py-3 space-x-0 md:space-x-4 space-y-3 md:space-y-0">
                    <div class="text-white px-3 py-1">
                        <h4 class="font-semibold text-2xl">Change by:</h4>
                        <p class="font-bold text-xl"><?php echo $change['suspect_name']?></p>
                        <p class="text-gray-400 text-xs"><?php echo $change['date_logged']?></p>
                    </div>
                    <div class="flex flex-col lg:w-[816px] space-y-3">
                        <div class="bg-gray-700 px-3 pt-3 pb-5">
                            <h3 class="text-xl font-semibold text-white"><?php echo $change['change_type'] == "CONTENT" ? "INSERTED" : "NEW TITLE" ?></h3>
                            <div class="bg-green-300 px-3 py-1 break-words">
                                <?php echo $change['inserted_content'] ?>
                            </div>
                        </div>
                        <div class="bg-gray-700 px-3 pt-3 pb-5">
                            <h3 class="text-xl font-semibold text-white"><?php echo $change['change_type'] == "CONTENT" ? "DELETED" : "OLD TITLE" ?></h3>
                            <div class="bg-red-300 px-3 py-1 break-words">
                                <?php echo $change['deleted_content'] ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>

        <script src="script.js"></script>
    </body>
</html>