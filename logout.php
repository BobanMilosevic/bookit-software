<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - BookIT</title>
</head>
<body>
    <script>
        // Logout: Entferne User aus localStorage und leite weiter
        localStorage.removeItem('bookit_current_user');
        window.location.href = 'login.php';
    </script>
</body>
</html>