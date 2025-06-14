<?php
require_once "core/functions.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
}

$isDocumentOwner = false;
$isDocumentShared = false;
$isAdmin = false;
$canEdit = false;
if($_SESSION['user_id'] == getDocumentOwner($pdo, $_GET['document_id'])['user_owner']) {
    $isDocumentOwner = true;
}
if(in_array($_SESSION['user_id'], getUserIdsWithDocAccess($pdo, $_GET['document_id']))) {
    $isDocumentShared = true;
    if(getUserDocAccessLevel($pdo, $_SESSION['user_id'], $_GET['document_id'])['can_edit'] == 1) {
        $canEdit = true;
    }
}
if($_SESSION['user_role'] == "ADMIN") {
    $isAdmin = true;
}

if(!$isDocumentOwner && !$isAdmin && !$isDocumentShared) {
    header("Location: index.php?documentAccessRestricted=1");
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PixelType Document</title>

        <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
        <link href="styles.css" rel="stylesheet">
        <link href="customStyles.css" rel="stylesheet">
        <link href="textStyles.css" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    </head>
    <body class="bg-gray-700">
        <div id="header" class="bg-gray-900 text-white justify-between items-center px-4 py-3">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-2 md:space-y-0 w-full">
                <div class="flex flex-col md:flex-row md:items-center md:space-x-2 space-y-2 md:space-y-0">
                    <button onclick="window.location='index.php'" class="border border-white rounded-2xl px-4 py-1 text-lg hover:cursor-pointer hover:scale-110 hover:bg-gray-800 duration-200">Homepage</button>

                    <input type="text" value="<?php echo getDocumentTitle($pdo, $_GET['document_id'])['title'] ?>" class="outline-none focus:border-2 focus:border-blue-500 w-full md:w-[40vw] rounded-xl bg-white ml-0 md:ml-3 mt-1 md:mt-0 px-2 py-1 text-black documentTitle" <?php if(!($isDocumentOwner || $canEdit)) {echo "readonly";} ?>>

                    <h4 id="documentSavedAlert" class="rounded-3xl bg-gray-700 px-5 py-1 text-center hidden">Saved</h4>
                </div>

                <div class="flex flex-row space-x-2 md:justify-end">
                    <?php
                    if($isDocumentOwner || $isAdmin || $canEdit) {
                    ?>
                        <button onclick="window.location='documentHistory.php?document_id=<?php echo $_GET['document_id'] ?>'" class="flex-1 md:flex-none border border-white rounded-2xl px-4 py-1 text-lg text-center hover:cursor-pointer hover:scale-110 hover:bg-gray-800 duration-200">View History</button>
                    <?php
                    }
                    if($isDocumentOwner) {
                    ?>
                        <button onclick="openDocumentAccessManagementModal()" class="flex-1 md:flex-none border border-white rounded-2xl px-4 py-1 text-lg text-center hover:cursor-pointer hover:scale-110 hover:bg-gray-800 duration-200">Manage Access</button>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>

        <div id="mainBody" class="flex flex-col lg:flex-row space-y-4 md:space-y-0">
            <!-- QUILL EDITOR -->
            <div class="bg-white lg:w-[816px] h-fit mx-auto">
                <div id="toolbar">
                    <span class="ql-formats">
                        <select class="ql-font">
                            <option value="arial" selected>Arial</option>
                            <option value="times-new-roman">Times New Roman</option>
                            <option value="sans-serif">Sans Serif</option>
                            <option value="serif">Serif</option>
                            <option value="monospace">Monospace</option>
                        </select>
                        <select class="ql-size"></select>
                        <select class="ql-header">
                            <option value="1">Heading 1</option>
                            <option value="2">Heading 2</option>
                            <option value="3">Heading 3</option>
                            <option selected></option>
                        </select>
                    </span>
                    
                    <span class="ql-formats">
                        <button class="ql-bold"></button>
                        <button class="ql-italic"></button>
                        <button class="ql-underline"></button>
                        <button class="ql-strike"></button>
                    </span>

                    <span class="ql-formats">
                        <button class="ql-list" value="ordered"></button>
                        <button class="ql-list" value="bullet"></button>
                        <button class="ql-list" value="check"></button>
                    </span>

                    <span class="ql-formats">
                        <button class="ql-image"></button>
                    </span>

                    <span class="ql-formats">
                        <select class="ql-align">
                            <option selected></option>
                            <option value="center"></option>
                            <option value="right"></option>
                            <option value="justify"></option>
                        </select>
                    </span>
                </div>

                <div id="loadedDocumentContentData" class="hidden"><?php echo htmlspecialchars(loadDocumentContents($pdo, $_GET['document_id'])['content']); ?></div>
                <div>
                    <div id="editor-container"></div>
                </div>
            </div>

            <!-- CHATBOX -->
            <?php
            if($isDocumentOwner || $isAdmin || $canEdit) {
            ?>
                <div id="chatbox" class="flex flex-col bg-gray-900 w-full lg:w-[25%] mx-auto lg:mx-0 p-3 space-y-2">
                    <h3 class="text-2xl font-semibold text-center text-white">CHATBOX</h3>
                    <div id="chatboxMessages" class="flex flex-col flex-grow w-[98%] bg-gray-200 mx-auto p-2 overflow-y-auto">
                        <!-- HTML rows are located in handleForms.php -->
                    </div>
                    <?php
                        if($isDocumentOwner || $canEdit) {
                    ?>
                        <form id="chatboxMessageBox">
                            <div class="w-[98%] mx-auto">
                                <textarea id="messageField" placeholder="Type your message here..." class="outline-none border-2 border-transparent w-full h-full bg-white p-2 resize-none focus:border-blue-500"></textarea>
                            </div>

                            <div class="flex">
                                <input type="hidden" id="data_userId" value="<?php echo $_SESSION['user_id'] ?>">
                                <input type="submit" value="Send" class="w-[94%] border border-white rounded-2xl mx-auto mt-2 px-4 py-1 text-lg text-white hover:cursor-pointer hover:scale-105 hover:bg-gray-800 duration-200">
                            </div>
                        </form>
                    <?php
                    }
                    ?>
                </div>
            <?php
            }
            ?>
        </div>

        <div id="manageDocumentAccess" class="fixed top-0 left-0 z-10 w-full h-full bg-black/55 hidden">
            <div id="content" class="flex flex-col fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-11/12 md:w-3/5 h-11/12 z-20 px-4 md:px-10 py-5 rounded-2xl bg-gray-900 text-white">
                <h3 class="text-2xl font-semibold text-center">MANAGE DOCUMENT ACCESS</h3>
                <h3 class="mt-5 text-2xl font-semibold">USERS WITH ACCESS</h3>
                <div id="usersWithDocumentAccess" class="flex flex-col flex-grow min-h-[70px] max-h-[280px] mt-2 mb-3 overflow-auto">
                    <!-- HTML rows -->
                </div>

                <input type="text" id="searchUserField" placeholder="Add a user" class="outline-none border-2 border-transparent focus:border-blue-500 w-full min-h-[38px] rounded-lg bg-white px-3 py-1 text-black">
                <div id="searchResults" class="flex flex-col flex-grow min-h-[45px] max-h-[270px] md:max-h-[315px] m-2 overflow-auto">
                    <!-- HTML rows -->
                </div>

                <button onclick="closeDocumentAccessManagementModal()" class="border border-white w-full rounded-2xl px-4 py-1 text-lg hover:cursor-pointer hover:scale-105 hover:bg-gray-800 duration-200">Close</button>
            </div>
        </div>

        <script src="script.js"></script>
        <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
        <script src="quillScript.js"></script>

        <script>
            function adjustDocEditorHeight() {
                var viewportHeight = $(window).height();
                var headerHeight = $('#header').outerHeight();
                var toolbarHeight = $('#toolbar').outerHeight();

                var editorHeight = viewportHeight - headerHeight - toolbarHeight - 2;
                var chatboxHeight = viewportHeight - headerHeight - 24;

                $('#editor-container').height(editorHeight);
                $('#chatbox').height(chatboxHeight);
            }

            $(document).ready(function() {
                updateChatboxMessages();
                updateSharedUsers();
                adjustDocEditorHeight();
            });
            $(window).on('resize', function() {
                adjustDocEditorHeight();
            });
        </script>

        <?php
        if(($isAdmin && !$canEdit) || ($isDocumentShared && !$canEdit)) {
        ?>
            <script>
                quill.enable(false);
                document.getElementById('toolbar').classList.add('pointer-events-none', 'opacity-60');
            </script>
        <?php
        }
        ?>
    </body>
</html>