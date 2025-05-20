const handleFormDirectory = "core/handleForms.php";

$('#accountRegistrationForm').on('submit', function(event){
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
            if(data.trim() == 0) {
                window.location.href = "login.php";
            } else {
                
            }
        }
    })
})