var font = Quill.import('formats/font');
font.whitelist = ['arial', 'times-new-roman', 'serif', 'sans-serif', 'monospace'];
Quill.register(font, true);

const quill = new Quill('#editor-container', {
    modules: {
        toolbar: '#toolbar'
    },
    theme: 'snow'
});

document.querySelector('.ql-save').addEventListener('click', () => {
    const content = quill.getContents();
    localStorage.setItem('quill-doc', JSON.stringify(content));
    alert("Document saved!");
});

const saved = localStorage.getItem('quill-doc');
if (saved) {
    quill.setContents(JSON.parse(saved));
}