<?php
// Načtení obsahu souboru song.json
$json = file_get_contents('config.json');

// Parsování JSON do PHP pole
$config = json_decode($json, true);

// Získání dat z JSON
$refreshTime = isset($config['refreshTime']) ? $config['refreshTime'] : 0;
$pidUrl = isset($config['pidUrl']) ? $config['pidUrl'] : 0;
$pidApiKey = isset($config['pidApiKey']) ? $config['pidApiKey'] : 0;
$weatherUrl = isset($config['weatherUrl']) ? $config['weatherUrl'] : 0;
$enableMap = isset($config['enableMap']) ? $config['enableMap'] : 0;
?>

<?php

// Funkce pro získání dat z API
function ziskejTeplotu($url)
{
    try {
        // Inicializace cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true); // Chyby HTTP budou také zachyceny

        // Načtení dat
        $response = curl_exec($ch);

        // Ověření chyby při načítání
        if ($response === false) {
            throw new Exception(curl_error($ch));
        }

        // Zavření cURL
        curl_close($ch);

        // Dekódování JSON odpovědi
        $data = json_decode($response, true);

        // Ověření struktury dat
        if (isset($data['current_weather']['temperature'])) {
            return $data['current_weather']['temperature'];
        } else {
            throw new Exception("Nesprávná struktura odpovědi API");
        }
    } catch (Exception $e) {
        return "Nepodařilo se načíst teplotu: " . $e->getMessage();
    }
}

// Získání teploty
$teplota = ziskejTeplotu($weatherUrl);
?>

<!DOCTYPE html>
<html lang='cs' data-bs-theme="dark">

<head>
    <title>Smart panel: PID Tabule</title>
    <link rel="icon" type="image/x-icon" href="./favicon.ico">
    <meta charset='utf-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="<?php echo htmlspecialchars($refreshTime); ?>;url=pid-tabule.php">
</head>

<body>

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

    <script src="hodiny.js"></script>

    <div class="stranka">

        <h1><span class="vetsiText">Odjezdy z Šestajovice, Balkán</span></h1>

        <?php

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
        $response = curl_exec($ch);

        // Kontrola chyb
        if (curl_errno($ch)) {
            echo "cURL error: " . curl_error($ch);
            exit;
        }

        // Zavření cURL
        curl_close($ch);

        // Zpracování dat
        $data = json_decode($response, true);

        // Generování tabulky
        if (!empty($data)) {
            echo '<table class="output table table-striped" id="busSchedule">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Číslo</th>';
            echo '<th>Směr</th>';
            echo '<th>Čas</th>';
            echo '<th>Zpoždění</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            foreach ($data[0] as $entry) {
                $bus = $entry['route']['short_name'];
                $time = date('H:i', strtotime($entry['departure']['timestamp_scheduled']));
                $delaySeconds = $entry['departure']['delay_seconds'];
                $delayMinutes = floor($delaySeconds / 60);
                $headsign = $entry['trip']['headsign'];

                echo '<tr>';
                echo "<td>$bus</td>";
                echo "<td>$headsign</td>";
                echo "<td>$time</td>";
                echo "<td>$delayMinutes</td>";
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo "Žádná data nebyla nalezena.";
        }
        ?>

        <h1 style="display: flex; justify-content: space-between;">
            <span id="teplota" class="vetsiText"><?php echo htmlspecialchars($teplota) ?> °C</span>
            <div id="hodiny" class="vetsiText"></div>
            <?php if ($enableMap == "true"): ?>
                <a href="pid-mapa.php" id="odkaz" class="vetsiText">Mapa</a>
            <?php endif; ?>
        </h1>

        <pre id="testOutput"></pre>

        <script>
            // Funkce pro přesměrování na jinou stránku
            document.addEventListener("click", function () {
                window.location.href = "./pid-tabule.php";  // Změň na URL, kam chceš přesměrovat
            });
        </script>

    </div>

</body>

</html>