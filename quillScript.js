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
quill.on('text-change', function(delta) {
    if (isInitializing) { return; }

    const hasTextChange = delta.ops.some(op => {
        return (op.insert && typeof op.insert === 'string') || op.delete;
    });
    if (!hasTextChange) { return; }

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
            success: function(data) {
                const documentSavedAlert = $('#documentSavedAlert');
                documentSavedAlert.removeClass('hidden');
                fadeOutNotification(documentSavedAlert);
            }
        });
    }, secondsFromLastInputBeforeSave);
});

setTimeout(function() {
    isInitializing = false;
}, 100);