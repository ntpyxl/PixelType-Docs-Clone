var font = Quill.import('formats/font');
font.whitelist = ['arial', 'times-new-roman', 'serif', 'sans-serif', 'monospace'];
Quill.register(font, true);

const quill = new Quill('#editor-container', {
    modules: {
        toolbar: '#toolbar'
    },
    theme: 'snow'
});

let isInitializing = true;

const rawContent = document.getElementById('loadedDocumentContentData').innerText;
quill.root.innerHTML = rawContent;

let saveDocContentTimeout;
quill.on('text-change', function() {
    if(isInitializing) {
        return;
    }

    clearTimeout(saveDocContentTimeout);
    saveDocContentTimeout = setTimeout(function() {
        const formData = {
            document_id: documentId,
            content: quill.root.innerHTML,
            saveDocumentContentRequest: 1
        };

        $.ajax({
            type: "POST",
            url: handleFormDirectory,
            data: formData,
            success: function() {
                console.log("Document content saved");
            }
        });
    }, secondsFromLastInputBeforeSave);
});

setTimeout(function() {
    isInitializing = false;
}, 100);