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
    <body class="bg-gray-100">
        <div class="border-2 border-black mx-auto w-[816px]">
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

            <div class="width-full h-[728px]">
                <div id="editor-container"></div>
            </div>
        </div>

        <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
        <script src="quillScript.js"></script>
    </body>
</html>