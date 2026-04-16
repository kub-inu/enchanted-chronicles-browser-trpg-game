<?php
$app = config('app');

$pageTitle = isset($title) && $title !== ''
    ? $app['title'] . $app['title_separator'] . $title
    : $app['title'];
?>
<!DOCTYPE html>
<html lang="sk" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
</head>
<body>
    <header>
    </header>

    <main>
        <?= $content ?>
    </main>

    <footer>
    </footer>
</body>
</html>