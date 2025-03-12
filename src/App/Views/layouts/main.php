<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Blog</title>
    <style>
        .flash-message { padding: 10px; margin-bottom: 10px; border: 1px solid; }
        .flash-message.error { border-color: red; color: red; }
        .flash-message.success { border-color: green; color: green; }
    </style>
</head>
<body>
    <header>
        <h1>My Blog</h1>
    </header>
    <main>
        <?= $content ?>
    </main>
    <footer>
        <p>&copy; 2025 My Blog</p>
    </footer>
</body>
</html>