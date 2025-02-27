<?php
// Načtení obsahu souboru song.json
$json = file_get_contents('config.json');

// Parsování JSON do PHP pole
$config = json_decode($json, true);

// Získání dat z JSON
$pidUrl = isset($config['pidUrl']) ? $config['pidUrl'] . "&limit=30" : 0;
$pidApiKey = isset($config['pidApiKey']) ? $config['pidApiKey'] : 0;
?>
<!DOCTYPE html>
<html lang='cs' data-bs-theme="dark">

<head>
    <title>Smart panel: PID Tabule</title>
    <link rel="icon" type="image/x-icon" href="./favicon.ico">
    <meta charset='utf-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>

    <noscript>
        <meta http-equiv="refresh" content="<?php echo htmlspecialchars($refreshTime); ?>;url=pid-tabule.php">
    </noscript>

    <style>
        @media screen and (min-width: 1900px) {
            body {
                zoom: 2;
            }
        }
    </style>

    <link rel="stylesheet" type="text/css" href="/main.css">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
        </script>

    <div class="stranka">

        <?php
        session_start();

        // Inicializace cURL
        $ch = curl_init();

        // Nastavení cURL
        curl_setopt($ch, CURLOPT_URL, $pidUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "X-Access-Token: $pidApiKey",
            "Accept: application/json"
        ]);

        // Vykonání požadavku
        $_SESSION['response'] = curl_exec($ch);

        // Kontrola chyb
        if (curl_errno($ch)) {
            echo "cURL error: " . curl_error($ch);
            exit;
        }

        // Zavření cURL
        curl_close($ch);
        ?>

    </div>

    <meta http-equiv="refresh" content="1;url=kalibrace-displej.php">

</body>

</html>