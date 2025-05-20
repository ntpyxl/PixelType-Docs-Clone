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
        <title>PixelType - Login</title>

        <link href="styles.css" rel="stylesheet">
    </head>
    <body class="bg-gray-700">
        <div class="min-h-screen flex items-center justify-center">
            <div class="rounded-4xl bg-gray-900 px-6 md:px-12 py-5 text-white">
                <h3 class="text-3xl font-bold text-center">PixelType</h3>
                <h4 class="mt-3 text-xl font-semibold text-center">Login Page</h4>
                <div class="my-6 space-y-1">
                    <div class="flex flex-col">
                        <label for="usernameField" class="text-lg focus:text-blue-500">Username</label>
                        <input type="text" id="usernameField" class="outline-none border border-white rounded-xl focus:border-blue-500 px-3 py-1" required>
                    </div>
                    <div class="flex flex-col">
                        <label for="passwordField" class="text-lg">Password</label>
                        <input type="password" id="passwordField" class="outline-none border border-white rounded-xl focus:border-blue-500 px-3 py-1" required>
                    </div>

                    <button class="border border-white rounded-2xl mt-6 px-6 py-1 text-lg hover:cursor-pointer hover:scale-110 hover:bg-gray-800 duration-200">Log In</button>
                </div>

                <p class="my-3 mx-3">
                    Don't have an account? 
                    <a href="register.php" class="text-blue-400 hover:underline underline-offset-2">
                        Register here
                    </a>
                </p>
            </div>
        </div>
    </body>
</html>