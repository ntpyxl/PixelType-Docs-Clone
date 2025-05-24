<?php
require_once "core/functions.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
}

$isDocumentOwner = false;
$isDocumentShared = false;
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

if(!$isDocumentOwner && !$isDocumentShared) {
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

        <!-- indescribable pile of garbage TODO: FIX LATER -->
        <div class="bg-gray-900 text-white flex justify-between items-center px-4 py-3">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-2 md:space-y-0 w-full">
                <div class="flex flex-wrap items-center space-x-2">
                    <button onclick="window.location='index.php'" class="border border-white rounded-2xl px-4 py-1 text-lg hover:cursor-pointer hover:scale-110 hover:bg-gray-800 duration-200">Homepage</button>

                    <!-- title, and history and access button for desktop -->
                    <input type="text" value="<?php echo getDocumentTitle($pdo, $_GET['document_id'])['title'] ?>" class="hidden md:block outline-none focus:border-2 focus:border-blue-500 w-[40vw] rounded-xl bg-white ml-3 px-2 py-1 text-black documentTitle" readonly>

                    <h4 id="documentSavedAlert" class="rounded-3xl bg-gray-700 px-5 py-1 hidden">Saved</h4>
                </div>

                <div class="md:flex space-x-2 justify-end docManagementButtons invisible hidden">
                    <button onclick="window.location=''" class="border border-white rounded-2xl px-4 py-1 text-lg hover:cursor-pointer hover:scale-110 hover:bg-gray-800 duration-200">View History</button>
                    <button onclick="openDocumentAccessManagementModal()" class="border border-white rounded-2xl px-4 py-1 text-lg hover:cursor-pointer hover:scale-110 hover:bg-gray-800 duration-200">Manage Access</button>
                </div>

                <!-- title, and history and access button for mobile -->
                <input type="text" value="<?php echo getDocumentTitle($pdo, $_GET['document_id'])['title'] ?>" class="md:hidden block outline-none focus:border-2 focus:border-blue-500 w-full rounded-xl bg-white mt-3 p-2 text-black documentTitle" readonly>

                <div class="flex space-x-2 mt-3 docManagementButtons invisible md:hidden">
                    <button onclick="window.location=''" class="border border-white rounded-2xl px-4 py-1 text-lg hover:cursor-pointer hover:scale-110 hover:bg-gray-800 duration-200">History</button>
                    <button onclick="openDocumentAccessManagementModal()" class="border border-white rounded-2xl px-4 py-1 text-lg hover:cursor-pointer hover:scale-110 hover:bg-gray-800 duration-200">Access</span></button>
                </div>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row">
            <!-- QUILL EDITOR -->
            <div class="bg-white lg:w-[816px] mx-auto">
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
                <div class="width-full h-[calc(89vh_-_5px)]"> <!-- TODO: FIX HEIGHT. NOT RESPONSIVE. -->
                    <div id="editor-container"></div>
                </div>
            </div>

            <!-- CHATBOX -->
            <div id="chatbox" class="bg-gray-900 w-full lg:w-[25%] h-[calc(94vh_-_7px)] mx-auto lg:mx-0 p-3 space-y-2 hidden"> <!-- TODO: FIX HEIGHT. NOT RESPONSIVE. -->
                <h3 class="text-2xl font-semibold text-center text-white">CHATBOX</h3>
                <div id="chatboxMessages" class="flex flex-col w-[98%] h-[78%] bg-gray-200 mx-auto p-2 overflow-y-auto">
                </div>

                <form id="chatboxMessageBox">
                    <div class="w-[98%] h-[10%] mx-auto">
                        <textarea id="messageField" placeholder="Type your message here..." class="outline-none border-2 border-transparent w-full h-full bg-white p-2 resize-none focus:border-blue-500"></textarea>
                    </div>

                    <div class="flex">
                        <input type="hidden" id="data_userId" value="<?php echo $_SESSION['user_id'] ?>">
                        <input type="submit" value="Send" class="w-[94%] border border-white rounded-2xl mx-auto mt-2 px-4 py-1 text-lg text-white hover:cursor-pointer hover:scale-105 hover:bg-gray-800 duration-200">
                    </div>
                </form>
            </div>
        </div>

        <div id="manageDocumentAccess" class="fixed top-0 left-0 z-10 w-full h-full bg-black/55 hidden">
            <div id="content" class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-11/12 md:w-3/5 h-11/12 z-20 px-4 md:px-10 py-5 rounded-2xl bg-gray-900 text-white">
                <h3 class="text-2xl font-semibold text-center">MANAGE DOCUMENT ACCESS</h3>

                <h3 class="mt-5 text-2xl font-semibold">USERS WITH ACCESS</h3>
                <div id="usersWithDocumentAccess" class="h-[280px] mt-2 mb-3 overflow-auto">
                    <!-- Shared user cards are in core/handleForms.php line 48 -->
                </div>
                
                <input type="text" id="searchUserField" placeholder="Add a user" class="outline-none border-2 border-transparent focus:border-blue-500 w-full h-[38px] rounded-xl bg-white px-3 py-1 text-black">
                <div id="searchResults" class="h-[240px] md:h-[320px] m-2 overflow-auto">
                    <!-- Searched user cards are in core/handleForms.php line 71 -->
                </div>

                <button onclick="closeDocumentAccessManagementModal()" class="border border-white w-full rounded-2xl px-4 py-1 text-lg hover:cursor-pointer hover:scale-105 hover:bg-gray-800 duration-200">Close</button>
            </div>
        </div>

        <script src="script.js"></script>
        <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
        <script src="quillScript.js"></script>

        <?php
        if($isDocumentOwner || $canEdit) {
        ?>
            <script>
                $('.docManagementButtons').removeClass('invisible');
                $('#chatbox').removeClass('hidden');
                $('.documentTitle').removeAttr('readonly');
            </script>
        <?php
        } else {
        ?>
            <script>
                quill.enable(false);
                const toolbar = document.getElementById('toolbar');
                toolbar.classList.add('pointer-events-none', 'opacity-60');
            </script>
        <?php
        }
        ?>
    </body>
</html>