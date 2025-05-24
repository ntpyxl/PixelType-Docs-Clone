<?php
require_once "core/functions.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PixelType - Home</title>

        <link href="styles.css" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    </head>
    <body class="bg-gray-700">
        <div class="grid grid-cols-12 gap-2 text-white">
            <div class="col-span-12 gap-2 flex flex-row bg-gray-900 px-6 py-2">
                <div class="col-span-12 md:col-span-4 flex space-x-5 px-3 py-2 items-center">
                    <h4 class="text-xl font-semibold">
                        Hello, <span class="font-bold">
                            <?php echo getUserFullNameById($pdo, $_SESSION['user_id'])['fullname'] ?>
                        </span>
                    </h4>

                    <button onclick="window.location='core/logout.php'" class="border border-white rounded-2xl px-6 py-1 text-lg hover:cursor-pointer hover:scale-110 hover:bg-gray-800 duration-200">Logout</button>
                </div>
                
                <div class="col-span-12 md:col-span-4 px-3 py-2">
                    <div id="messageBox" class="hidden max-w-[80vw] md:max-w-[26vw] h-fit rounded-2xl my-auto px-5 py-2 text-white text-center">
                        <h4 id="title" class="text-xl font-semibold"></h4>
                        <p id="message" class="mt-3"></p>
                    </div>
                </div>
            </div>

            <div class="col-span-12 px-8 py-3 bg-gray-800">
                <h3 class="text-2xl font-semibold my-2">Your documents</h3>
                
                <button onclick="createNewDocument(<?php echo $_SESSION['user_id'] ?>)" class="border border-white rounded-2xl mt-1 mb-3 px-6 py-2 text-lg hover:cursor-pointer hover:scale-105 hover:bg-gray-700 duration-200">+ Create a new blank document</button>

                <div class="w-full overflow-x-auto">
                    <table class="border border-separate border-spacing-0 border-white min-w-[500px] table-auto">
                        <tr>
                            <th class="border border-white px-2 py-1">DOCUMENT TITLE</th>
                            <th class="border border-white w-[20%] md:w-[15%] px-2 py-1">LAST UPDATED</th>
                            <th class="border border-white w-[20%] md:w-[15%] px-2 py-1">DATE CREATED</th>
                        </tr>
                        <?php
                        foreach(getCreatedDocuments($pdo, $_SESSION['user_id']) as $ownedDoc) {
                        ?>
                            <tr onclick="window.location='document.php?document_id=<?php echo $ownedDoc['document_id'] ?>'" class="border group relative z-50 select-none cursor-pointer">
                                <th class="border border-white group-hover:border-blue-500 px-2 py-1"><?php echo $ownedDoc['title'] ?></th>
                                <th class="border border-white group-hover:border-blue-500 px-2 py-1"><?php echo $ownedDoc['last_updated'] ?></th>
                                <th class="border border-white group-hover:border-blue-500 px-2 py-1"><?php echo $ownedDoc['date_created'] ?></th>
                            </tr>
                        <?php
                        }
                        ?>
                    </table>
                </div>
            </div>

            <div class="col-span-12 px-8 py-3 bg-gray-800">
                <h3 class="text-2xl font-semibold my-2">Shared to you</h3>
                <div class="w-full overflow-x-auto">
                    <table class="border border-separate border-spacing-0 border-white min-w-[500px] table-auto">
                        <tr>
                            <th class="border border-white px-2 py-1">DOCUMENT TITLE</th>
                            <th class="border border-white px-2 py-1">OWNER</th>
                            <th class="border border-white w-[20%] md:w-[15%] px-2 py-1">LAST UPDATED</th>
                            <th class="border border-white w-[20%] md:w-[15%] px-2 py-1">DATE CREATED</th>
                        </tr>
                        <?php
                        foreach(getSharedDocuments($pdo, $_SESSION['user_id']) as $sharedDoc) {
                        ?>
                            <tr onclick="window.location='document.php?document_id=<?php echo $sharedDoc['document_id'] ?>'" class="border group relative z-50 select-none cursor-pointer">
                                <th class="border border-white group-hover:border-blue-500 px-2 py-1"><?php echo $sharedDoc['title'] ?></th>
                                <th class="border border-white group-hover:border-blue-500 px-2 py-1"><?php echo $sharedDoc['owner_name'] ?></th>
                                <th class="border border-white group-hover:border-blue-500 px-2 py-1"><?php echo $sharedDoc['last_updated'] ?></th>
                                <th class="border border-white group-hover:border-blue-500 px-2 py-1"><?php echo $sharedDoc['date_created'] ?></th>
                            </tr>
                        <?php
                        }
                        ?>
                    </table>
                </div>
            </div>
            
        </div>

        <script src="script.js"></script>

        <?php if(isset($_GET['userLoginSuccess'])): ?>
            <script>
                changeMessage("Successfully Logged In!", "", 0);
                removeURLParameter("userLoginSuccess")
            </script>
        <?php endif; ?>
    </body>
</html>