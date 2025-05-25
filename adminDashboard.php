<?php
require_once "core/functions.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
}
if($_SESSION['user_role'] == "REGULAR") {
    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PixelType - Admin Dashboard</title>

        <link href="styles.css" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    </head>
    <body class="bg-gray-700">
        <div class="grid grid-cols-12 gap-2 text-white">
            <div class="col-span-12 gap-2 flex flex-col md:flex-row bg-gray-900 px-6 py-2">
                <div class="flex flex-col md:flex-row md:h-[60px] space-x-0 md:space-x-5 space-y-4 md:space-y-0 px-3 py-2 items-center">
                    <h4 class="text-xl font-semibold">
                        Hello, <span class="font-bold">
                            <?php echo getUserFullNameById($pdo, $_SESSION['user_id'])['fullname'] ?>
                        </span>
                    </h4>
                    <div class="space-x-3">
                        <button onclick="window.location='core/logout.php'" class="border border-white rounded-2xl px-6 py-1 text-lg hover:cursor-pointer hover:scale-110 hover:bg-gray-800 duration-200">Logout</button>
                        
                        <button onclick="window.location='index.php'" class="border border-white rounded-2xl px-6 py-1 text-lg hover:cursor-pointer hover:scale-110 hover:bg-gray-800 duration-200">Homepage</button>
                    </div>
                </div>
                
                <div class="px-3 py-2">
                    <div id="messageBox" class="hidden max-w-[80vw] md:max-w-[26vw] h-fit rounded-2xl my-auto px-5 py-2 text-white text-center">
                        <h4 id="title" class="text-xl font-semibold"></h4>
                        <p id="message" class="mt-3"></p>
                    </div>
                </div>
            </div>

            <div class="col-span-12 px-8 py-3 bg-gray-800">
                <h3 class="text-2xl font-semibold my-2">All documents</h3>
                <div class="w-full max-h-[300px] overflow-x-auto overflow-y-auto">
                    <table class="border border-separate border-spacing-0 border-white min-w-[500px] w-full table-auto">
                        <tr>
                            <th class="border border-white px-2 py-1">DOCUMENT TITLE</th>
                            <th class="border border-white px-2 py-1">OWNER</th>
                            <th class="border border-white px-2 py-1">SHARED WITH</th>
                            <th class="border border-white w-[20%] md:w-[15%] px-2 py-1">LAST UPDATED</th>
                            <th class="border border-white w-[20%] md:w-[15%] px-2 py-1">DATE CREATED</th>
                        </tr>
                        <?php
                        foreach(getAllDocuments($pdo) as $docs) {
                        ?>
                            <tr onclick="window.location='document.php?document_id=<?php echo $docs['document_id'] ?>'" class="border group relative select-none cursor-pointer">
                                <th class="border border-white group-hover:border-blue-500 px-2 py-1"><?php echo $docs['title'] ?></th>
                                <th class="border border-white group-hover:border-blue-500 px-2 py-1"><?php echo $docs['owner_name'] ?></th>
                                <th class="border border-white group-hover:border-blue-500 px-2 py-1">
                                    <?php
                                    foreach(getUsersNameSharedDoc($pdo, $docs['document_id']) as $sharedUser) {
                                        echo $sharedUser['fullname'] . ($sharedUser['can_edit'] == 1 ? " [Editor]" : " [Viewer]") . "<br>";
                                    }
                                    ?>
                                </th>
                                <th class="border border-white group-hover:border-blue-500 px-2 py-1"><?php echo $docs['last_updated'] ?></th>
                                <th class="border border-white group-hover:border-blue-500 px-2 py-1"><?php echo $docs['date_created'] ?></th>
                            </tr>
                        <?php
                        }
                        ?>
                    </table>
                </div>
            </div>

            <div class="col-span-12 px-8 py-3 bg-gray-800">
                <h3 class="text-2xl font-semibold my-2">All users</h3>
                <div class="w-full max-h-[300px] overflow-x-auto overflow-y-auto">
                    <table id="userManagementTable" class="border border-separate border-spacing-0 border-white min-w-[500px] w-full table-auto">
                        <tr>
                            <th class="border border-white px-2 py-1">NAME</th>
                            <th class="border border-white w-[20%] md:w-[15%] px-2 py-1">ROLE</th>
                            <th class="border border-white w-[20%] md:w-[15%] px-2 py-1">STATUS</th>
                            <th class="border border-white w-[20%] md:w-[15%] px-2 py-1">DATE REGISTERED</th>
                        </tr>
                        <tbody id="allUsersRows">
                            <!-- HTML rows are located in handleForms.php -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-span-12 px-8 py-3 bg-gray-800">
                <h3 class="text-2xl font-semibold my-2">Activity Logs</h3>
                <div class="w-full max-h-[500px] overflow-x-auto overflow-y-auto">
                    <table class="border border-separate border-spacing-0 border-white min-w-[500px] w-full table-auto">
                        <tr>
                            <th class="border border-white w-[20%] md:w-[8%] px-2 py-1">ACTION</th>
                            <th class="border border-white px-2 py-1">DONE BY</th>
                            <th class="border border-white w-[20%] md:w-[12%] px-2 py-1">WHERE</th>
                            <th class="border border-white w-[20%] md:w-[8%] px-2 py-1">TYPE</th>
                            <th class="border border-white px-2 py-1">AFFECTED USER</th>
                            <th class="border border-white px-2 py-1">REMARKS</th>
                            <th class="border border-white w-[20%] md:w-[10%] px-2 py-1">DATE LOGGED</th>
                        </tr>
                        <tbody id="activityLogsRows">
                            <!-- HTML rows are located in handleForms.php -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script src="script.js"></script>

        <script>
            $(document).ready(function() {
                updateUserManagementTable();
                updateLogsTable();
            });
        </script>

        <?php if(isset($_GET['userLoginSuccess'])): ?>
            <script>
                changeMessage("Successfully Logged In!", "", 0);
                removeURLParameter("userLoginSuccess")
            </script>
        <?php endif; ?>
    </body>
</html>