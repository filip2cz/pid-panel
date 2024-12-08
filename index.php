<!DOCTYPE html>
<html lang='cs' data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="10;url=pid-tabule.php">
    <title>Přesměrování...</title>
</head>

<body>

    <link rel="stylesheet" type="text/css" href="/main.css">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
        </script>

    <div class="container">

        <h1>Device info</h1>

        <p>Local IP adress:

            <?php
            // Získání lokální IP adresy serveru
            $local_ip = shell_exec("hostname -I");

            // Vypsání IP adresy na stránku
            echo $local_ip;
            ?>
        </p>

    </div>

</body>

</html>