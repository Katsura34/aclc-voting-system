<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-center mb-6">
            Create an Account
        </h2>

        <form class="space-y-4">
            <input
                type="text"
                placeholder="School Name"
                class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-300"
            >

            <input
                type="text"
                placeholder="First Name"
                class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-300"
            >

            <input
                type="text"
                placeholder="Last Name"
                class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-300"
            >

            <input
                type="email"
                placeholder="Email Address"
                class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-300"
            >

            <input
                type="password"
                placeholder="Password"
                class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-300"
            >

            <button
                type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition"
            >
                Register
            </button>
        </form>
    </div>
</body>
</html>