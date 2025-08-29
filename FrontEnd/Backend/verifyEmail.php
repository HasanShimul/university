<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ABC Digital Wallet - Verify Email</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="min-h-screen bg-gradient-to-r from-teal-400 via-teal-500 to-teal-700 flex flex-col items-center">

    <header class="w-full bg-gray-800 p-4 text-white text-center shadow-lg">
        <h1 class="text-xl font-bold">ABC Digital Wallet</h1>
    </header>

    <div class="w-full py-2 px-4 bg-gradient-to-r from-green-300 via-green-400 to-green-500 text-center text-white text-lg font-semibold shadow-md">
        Email Verification
    </div>

    <main class="flex-grow flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-black bg-opacity-20 backdrop-filter backdrop-blur-sm p-8 rounded-lg shadow-xl border border-white border-opacity-30 text-white text-center">
            <h2 class="text-2xl font-semibold mb-6">Verify Your Email Address</h2>
            <p class="mb-4">A 4-digit verification code has been sent to your registered email address. Please enter it below.</p>

            <form action="Backend/verify_email.php" method="POST" class="space-y-4">
                <div>
                    <label for="verificationCode" class="block text-md mb-1">Verification Code</label>
                    <input type="text" id="verificationCode" name="verificationCode" maxlength="4"
                           class="w-full p-2 rounded-md bg-gradient-to-r from-gray-200 via-gray-300 to-gray-400 text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-500 text-center text-lg tracking-widest"
                           placeholder=" _ _ _ _ " required>
                </div>
                
                <div class="flex justify-center space-x-4 pt-4">
                    <button type="submit" name="action" value="verify"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-md shadow-lg transition duration-300 ease-in-out">
                        Verify
                    </button>
                    <button type="submit" name="action" value="skip"
                            class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded-md shadow-lg transition duration-300 ease-in-out">
                        Skip
                    </button>
                </div>
                <div class="mt-4">
                    <a href="Backend/resend_code.php" class="text-blue-300 hover:text-blue-100 text-sm">Resend Code</a>
                </div>
                <?php if (isset($_GET['error'])): ?>
                    <p class="text-red-300 text-sm mt-2"><?php echo htmlspecialchars($_GET['error']); ?></p>
                <?php endif; ?>
            </form>
        </div>
    </main>
</body>
</html>
