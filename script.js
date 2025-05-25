const handleFormDirectory = "core/handleForms.php";
const secondsFromLastInputBeforeSave = 2000; // 1 second = 1000 millisecond
const secondsBeforeNotificationFadeOut = 5000;
const documentId = parseInt(new URLSearchParams(window.location.search).get("document_id"));

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
                window.location.href = "index.php?userLoginSuccess=1";
            } else if(data.trim() == "userSuspended") {
                changeMessage(
                    "User is suspended!",
                    "Please contact moderators.",
                    1);
            } else if(data.trim() == "usernameNotExisting") {
                changeMessage(
                    "Failed to Log In!",
                    "User does not exist!",
                    1);
            } else if(data.trim() == "incorrectPassword") {
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

function updateLogsTable() {
    $.ajax({
        type: "POST",
        url: handleFormDirectory,
        data: {adminLogsTableRequest: 1},
        success: function(data) {
            $('#activityLogsRows').html(data);
        }
    })
}

function updateUserManagementTable() {
    $.ajax({
        type: "POST",
        url: handleFormDirectory,
        data: {adminAllUsersRequest: 1},
        success: function(data) {
            updateLogsTable();
            $('#allUsersRows').html(data);
        }
    })
}

$('#userManagementTable').on('change', '.userRoleSelect', function() {
    const userId = $(this).data('user-id');
    const newUserRole = $(this).val();

    $.ajax({
        url: handleFormDirectory,
        type: 'POST',
        data: {
            user_id: userId,
            user_role: newUserRole,
            updateUserRoleRequest: 1
        },
        success: function(response) {
            updateUserManagementTable();
        }
    });
})

$('#userManagementTable').on('change', '.userStatusSelect', function() {
    const userId = $(this).data('user-id');
    const newUserStatus = $(this).val();

    $.ajax({
        url: handleFormDirectory,
        type: 'POST',
        data: {
            user_id: userId,
            user_status: newUserStatus,
            updateUserStatusRequest: 1
        },
        success: function(response) {
            updateUserManagementTable();
        }
    });
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

                window.location.href = "document.php?document_id=" + parsedData[1];
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
                const documentSavedAlert = $('#documentSavedAlert');
                documentSavedAlert.removeClass('hidden');
                fadeOutNotification(documentSavedAlert);      
            },
            error: function(xhr) {
                console.error("Document title save failed:", xhr.responseText);
            }
        });
    }, secondsFromLastInputBeforeSave);
})

// document content autosave is in quillScript.js

$('#searchUserField').on('input', function() {
    updateSearchedUsers();
})

function updateSearchedUsers() {
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
}

function shareDocumentToUser(userId) {
    const data = {
        userId: userId,
        documentId: documentId,
        shareDocumentToUserRequest: 1
    }

    $.ajax({
        type: "POST",
        url: handleFormDirectory,
        data: data,
        success: function(data) {
            updateSharedUsers();
            updateSearchedUsers();
        }
    })
}

function updateSharedUsers() {
    const data = {
        document_id: documentId,
        sharedUsersRequest: 1
    }
    
    $.ajax({
        type: "POST",
        url: handleFormDirectory,
        data: data,
        success: function(data) {
            $('#usersWithDocumentAccess').html(data);
        }
    })
}

function revokeDocumentToUser(userId) {
    const data = {
        userId: userId,
        documentId: documentId,
        revokeDocumentToUserRequest: 1
    }

    $.ajax({
        type: "POST",
        url: handleFormDirectory,
        data: data,
        success: function(data) {
            updateSharedUsers();
            updateSearchedUsers();
        }
    })
}

$('#usersWithDocumentAccess').on('change', '.sharedUserControlLevel', function() {
    const userId = $(this).data('user-id');
    const newAccessLevel = $(this).val();

    $.ajax({
        url: handleFormDirectory,
        type: 'POST',
        data: {
            user_id: userId,
            document_id: documentId,
            access_level: newAccessLevel,
            updateUserAccessLevelRequest: 1
        },
        success: function(response) {
            console.log('Access level updated');
        }
    });
})

$('#chatboxMessageBox').on('submit', function(event) {
    event.preventDefault();
    const formData = {
        message: $('#messageField').val(),
        user_id: $('#data_userId').val(),
        document_id: documentId,
        sendMessageRequest: 1
    }

    $.ajax({
        type: "POST",
        url: handleFormDirectory,
        data: formData,
        success: function(data) {
            $('#messageField').val('');
            updateChatboxMessages();
        }
    })
})

function updateChatboxMessages() {
    const data = {
        document_id: documentId,
        chatboxMessagesRequest: 1
    }

    $.ajax({
        type: "POST",
        url: handleFormDirectory,
        data: data,
        success: function(data) {
            $('#chatboxMessages').html(data);
            scrollChatboxToBottom();
        }
    })
}

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
        fadeOutNotification(messageBox);
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

function fadeOutNotification(item) {
    setTimeout(function() {
        item.fadeOut(function() {
            $(this).css("display", "");
            $(this).addClass('hidden');
        });
    }, secondsBeforeNotificationFadeOut);
}

function scrollChatboxToBottom() {
    const chatbox = $('#chatbox').find('#chatboxMessages');
    chatbox.scrollTop(chatbox[0].scrollHeight);
}