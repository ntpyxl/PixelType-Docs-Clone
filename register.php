<?php
session_start();

if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PixelType - Register</title>

        <link href="styles.css" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    </head>
    <body class="bg-gray-700">
        <div class="min-h-screen flex flex-col items-center justify-center">
            <div class="rounded-4xl bg-gray-900 my-3 px-6 md:px-12 py-5 text-white">
                <h3 class="text-3xl font-bold text-center">PixelType</h3>
                <h4 class="mt-3 text-xl font-semibold text-center">Register Page</h4>
                <form id="accountRegistrationForm" class="my-6 space-y-1">
                    <div>
                        <div class="flex flex-col">
                            <label for="usernameField">Username</label>
                            <input type="text" id="usernameField" class="peer outline-none border border-white rounded-xl focus:border-blue-500 px-3 py-1" required>
                        </div>
                        <div class="flex flex-col"> 
                            <label for="passwordField">Password</label>
                            <input type="password" id="passwordField" class="outline-none border border-white rounded-xl focus:border-blue-500 px-3 py-1" required>
                        </div>
                        <div class="flex flex-col">
                            <label for="verifyPasswordField">Verify Password</label>
                            <input type="password" id="verifyPasswordField" class="outline-none border border-white rounded-xl focus:border-blue-500 px-3 py-1" required>
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="flex flex-col">
                            <label for="firstnameField">Firstname</label>
                            <input type="text" id="firstnameField" class="outline-none border border-white rounded-xl focus:border-blue-500 px-3 py-1" required>
                        </div>
                        <div class="flex flex-col">
                            <label for="lastnameField">Lastname</label>
                            <input type="text" id="lastnameField" class="outline-none border border-white rounded-xl focus:border-blue-500 px-3 py-1" required>
                        </div>
                    </div>

                    <input type="submit" value="Register" class="border border-white rounded-2xl mt-6 px-6 py-1 text-lg hover:cursor-pointer hover:scale-110 hover:bg-gray-800 duration-200">
                </form>

                <p class="my-3 mx-3">
                    Already have an account? 
                    <a href="login.php" class="text-blue-400 hover:underline underline-offset-2">
                        Login here
                    </a>
                </p>
            </div>

            <div id="messageBox" class="hidden max-w-[80vw] md:max-w-[26vw] rounded-2xl my-3 px-5 py-2 text-white text-center">
                <h4 id="title" class="text-xl font-semibold"></h4>
                <p id="message" class="mt-3"></p>
            </div>
        </div>

    <script src="script.js"></script>
    </body>
</html>