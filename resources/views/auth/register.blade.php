<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
   .<div class="flex flex-col items-center bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6">Create an Account</h2>
       <form>
            <input type = "text"  placeholder="School Name" class="form-control" >
            <input type = "text"  placeholder="First Name" >
            <input type = "text"  placeholder="Last Name" >
            <input type = "email"  placeholder="Email Address" >
            <input type = "password"  placeholder="Password" >
       </form>
   </div>
</body>
</html>
