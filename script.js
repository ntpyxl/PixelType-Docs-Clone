const handleFormDirectory = "core/handleForms.php";
const secondsFromLastInputBeforeSave = 2000; // 1 second = 1000 millisecond
const documentId = new URLSearchParams(window.location.search).get("document_id");

$('#accountRegistrationForm').on('submit', function(event) {
    event.preventDefault();
    const formData = {
        username: $('#usernameField').val(),
        password: $('#passwordField').val(),
        verifyPassword: $('#verifyPasswordField').val(),
        firstname: $('#firstnameField').val(),
        lastname: $('#lastnameField').val(),
        accountRegistrationRequest: 1
    };

    $.ajax({
        type: "POST",
        url: handleFormDirectory,
        data: formData,
        success: function(data) {
            if(data.trim() == "accountRegistered") {
                window.location.href = "login.php?registerAccountSuccess=1";
            } else if(data.trim() == "passwordNotVerified") {
                changeMessage(
                    "Failed to Register Account!",
                    "Password is not verified! Make sure your passwords match!",
                    1);
            } else if(data.trim() == "usernameExists") {
                changeMessage(
                    "Failed to Register Account!",
                    "Username already exists!",
                    1);
            } else {
                changeMessage(
                    "Failed to Register Account!",
                    data.trim(),
                    1);
            }
        }
    })
})

$('#userLoginForm').on('submit', function(event) {
    event.preventDefault();
    const formData = {
        username: $('#usernameField').val(),
        password: $('#passwordField').val(),
        userLoginRequest: 1
    };

    $.ajax({
        type: "POST",
        url: handleFormDirectory,
        data: formData,
        success: function(data) {
            if(data.trim() == "loginSuccess") {
                window.location.href = "index.php?userLoginSuccess=1"
            } else if(data.trim() == "usernameNotExisting"){
                changeMessage(
                    "Failed to Log In!",
                    "User does not exist!",
                    1);
            } else if(data.trim() == "incorrectPassword"){
                changeMessage(
                    "Failed to Log In!",
                    "Incorrect password!",
                    1);
            } else {
                changeMessage(
                    "Failed to Log In!",
                    data.trim(),
                    1);
            }
        }
    })
})

function createNewDocument(user_id) {
    const data = {
        owner: user_id,
        newBlankDocumentRequest: 1
    };

    $.ajax({
        type: "POST",
        url: handleFormDirectory,
        data: data,
        success: function(data) {
            const parsedData = JSON.parse(data);
            if(parsedData[0].trim() == "blankDocumentCreated") {
                link = "document.php?document_id=" + parsedData[1];
                window.location.href = link;
            } else {
                changeMessage(
                    "Failed to Create New Document!",
                    parsedData[0].trim(),
                    1);
            }
        }
    })
}

let saveDocTitleTimeout;
$('.documentTitle').on('input', function(event) {
    event.preventDefault();

    clearTimeout(saveDocTitleTimeout);
    saveDocTitleTimeout = setTimeout(function() {
        const data = {
            document_id: documentId,
            content: $('.documentTitle').val(),
            saveDocumentTitleRequest: 1
        };

        $.ajax({
            type: "POST",
            url: handleFormDirectory,
            data: data,
            success: function() {
                console.log("Document title saved");
            },
            error: function(xhr) {
                console.error("Document title save failed:", xhr.responseText);
            }
        });
    }, secondsFromLastInputBeforeSave);
})

// document content autosave is in quillScript.js

$('#searchUserField').on('input', function() {
    const data = {
        keyword: $('#searchUserField').val(),
        searchUserRequest: 1
    }

    if(data.keyword != "") {
        $.ajax({
            type: "POST",
            url: handleFormDirectory,
            data: data,
            success: function(data) {
                $('#searchResults').html(data);
            }
        })
    }
})

////////////////////////////////////////////////
////////////////////////////////////////////////

function changeMessage(title, message, type) {
    const messageBox = $('#messageBox');
    messageBox.find('#title').text(title);
    messageBox.find('#message').text(message);

    if(type == 0) { // POSITIVE
        messageBox.removeClass('bg-red-800');
        messageBox.addClass('bg-green-800');
    } else if(type == 1) { // NEGATIVE
        messageBox.removeClass('bg-green-800');
        messageBox.addClass('bg-red-800');
    }

    if(message == "") {
        messageBox.find('#message').addClass('hidden');
    } else {
        messageBox.find('#message').removeClass('hidden');
    }

    messageBox.removeClass('hidden')
    if(type == 0) {
        setTimeout(function() {
            messageBox.fadeOut();
        }, 5000);
    }
}

function removeURLParameter(parameter) {
    const url = new URL(window.location);
    url.searchParams.delete(parameter);
    window.history.replaceState({}, document.title, url.pathname);
}

function openDocumentAccessManagementModal() {
    $('#manageDocumentAccess').removeClass('hidden');
    $('body').addClass('overflow-hidden');
}

function closeDocumentAccessManagementModal() {
    $('#manageDocumentAccess').addClass('hidden');
    $('body').removeClass('overflow-hidden');
}