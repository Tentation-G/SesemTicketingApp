
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>SesemIT - <?= htmlspecialchars($title ?? '') ?></title>

</head>
<body>
    
    <header>
        <form action="index.php?p=logout" method="post">
            <button type="submit">Se déconnecter</button>
        </form>
    </header>

    <main> 
        <?= $content ?? '' ?>
    </main>

    <footer></footer>
</body>

</html>