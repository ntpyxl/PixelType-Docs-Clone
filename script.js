function format(commands) {
  const editor = document.getElementById("editor");
  editor.focus();

  setTimeout(() => {
    for (const [command, value] of commands) {
      document.execCommand(command, false, value || null);
    }
  }, 0);
}

function saveDoc() {
  const content = document.getElementById("editor").innerHTML;
  localStorage.setItem("docContent", content);
  alert("Document saved!");
}

window.onload = function () {
  const saved = localStorage.getItem("docContent");
  if (saved) {
    document.getElementById("editor").innerHTML = saved;
  }
};