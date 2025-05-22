<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>

        <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
        <link href="styles.css" rel="stylesheet">
        <link href="customStyles.css" rel="stylesheet">
        <link href="textStyles.css" rel="stylesheet">
    </head>
    <body class="bg-gray-700">
        <div class="bg-gray-900 text-white flex justify-between items-center px-4 py-3">
            <button onclick="window.location='index.php'" class="border border-white rounded-2xl px-4 py-1 text-lg hover:cursor-pointer hover:scale-110 hover:bg-gray-800 duration-200">
                Homepage
            </button>

            <div class="flex space-x-4">
                <button onclick="window.location=''" class="border border-white rounded-2xl px-4 py-1 text-lg hover:cursor-pointer hover:scale-110 hover:bg-gray-800 duration-200">
                    <span class="inline sm:hidden">History</span>
                    <span class="hidden sm:inline">View History</span>
                </button>
                <button onclick="window.location=''" class="border border-white rounded-2xl px-4 py-1 text-lg hover:cursor-pointer hover:scale-110 hover:bg-gray-800 duration-200">
                    <span class="inline sm:hidden">Access</span>
                    <span class="hidden sm:inline">Manage Access</span>
                </button>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row space-y-4">
            <div class="border-2 border-black bg-white lg:w-[816px] mx-auto">
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

                    <span class="ql-formats">
                        <button class="ql-save">💾</button>
                    </span>
                </div>

                <div class="width-full h-[87vh]">
                    <div id="editor-container"></div>
                </div>
            </div>

            <div class="bg-gray-900 w-full lg:w-[30%] h-[92vh] mx-auto lg:mx-0 p-3">
                <h3 class="text-2xl font-semibold text-center text-white">CHATBOX</h3>
                <div class="border-2 border-black w-[98%] h-[80%] bg-gray-200 mx-auto p-2">
                    MESSAGES
                </div>

                <div class="border-2 border-black w-[98%] h-[10%] bg-white mx-auto p-2">
                    REPLY
                </div>

                <div class="flex">
                    <button onclick="" class="w-[94%] border border-white rounded-2xl mx-auto mt-2 px-4 py-1 text-lg text-white hover:cursor-pointer hover:scale-105 hover:bg-gray-800 duration-200">
                        SEND
                    </button>
                </div>
            </div>
        </div>

        <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
        <script src="quillScript.js"></script>
    </body>
</html>