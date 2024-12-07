<?php
// Načtení obsahu souboru song.json
$json = file_get_contents('config.json');

// Parsování JSON do PHP pole
$config = json_decode($json, true);

// Získání dat z JSON
$refreshTime = isset($config['refreshTime']) ? $config['refreshTime'] : 0;
$pidUrl = isset($config['pidUrl']) ? $config['pidUrl'] : 0;
$weatherUrl = isset($config['weatherUrl']) ? $config['weatherUrl'] : 0;
$enableMap = isset($config['enableMap']) ? $config['enableMap'] : 0;
?>

<?php

// Funkce pro získání dat z API
function ziskejTeplotu($url) {
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
    <title>Smart panel: PID</title>
    <link rel="icon" type="image/x-icon" href="./favicon.ico">
    <meta charset='utf-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="<?php echo htmlspecialchars($refreshTime); ?>;url=pid-balkan.php">
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
        <!--<h1>Odjezdy z Šestajovice, Za Stodolami</h1>-->

        <script>

            console.log("<?php echo $pidUrl; ?>");

            var xhr = new XMLHttpRequest();

            // https://api.golemio.cz/pid/docs/openapi/#/%F0%9F%95%92%20Public%20Departures%20(v2)/get_v2_public_departureboards

            // Šestajovice Balkán
            xhr.open('GET', '<?php echo $pidUrl; ?>', true);

            // Nastavení hlavičky pro API klíč
            xhr.setRequestHeader('X-Access-Token', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MjgxMCwiaWF0IjoxNzIwMzY0NDE4LCJleHAiOjExNzIwMzY0NDE4LCJpc3MiOiJnb2xlbWlvIiwianRpIjoiMDdhMDlkMGMtNzliNy00MTZmLWFhOTQtMWU2MmFkMWQ0NzkzIn0._oWGvAfru07E29PI2je_G4gWcirD_VCrguMwX70O0ak');

            // Nastavení typu přijatých dat (volitelné, pokud očekáváš JSON)
            xhr.setRequestHeader('accept', 'application/json');

            xhr.onreadystatechange = function () {
                if (this.readyState !== 4) return;
                if (this.status !== 200) return; // or whatever error handling you want

                var rawResponse = this.responseText;

                // console.log(rawResponse); // Zobraz čistý text v konzoli

                // Výpis JSON do <pre>
                // document.getElementById('testOutput').innerHTML = this.responseText;

                // Parsování dat
                const data = JSON.parse(rawResponse);

                // Funkce pro přidání řádku do tabulky
                const tbody = document.querySelector("#busSchedule tbody");

                // Výpis
                data[0].forEach(entry => {
                    const bus = entry.route.short_name;
                    const time = new Date(entry.departure.timestamp_scheduled).toLocaleTimeString('cs-CZ', { hour: '2-digit', minute: '2-digit' });
                    const delay = entry.departure.delay_seconds;
                    const headsign = entry.trip.headsign;

                    // Vytvoření nového řádku
                    const row = document.createElement('tr');

                    // Buňka pro autobus
                    const busCell = document.createElement('td');
                    busCell.textContent = bus;
                    row.appendChild(busCell);

                    // Buňka pro konečnou
                    const headsignCell = document.createElement('td');
                    headsignCell.textContent = headsign;
                    row.appendChild(headsignCell);

                    // Buňka pro čas odjezdu
                    const timeCell = document.createElement('td');
                    timeCell.textContent = time;
                    row.appendChild(timeCell);

                    delayMinutes = Math.floor(Number(delay) / 60);

                    // Buňka pro zpoždění
                    const delayCell = document.createElement('td');
                    delayCell.textContent = delayMinutes;
                    row.appendChild(delayCell);

                    // Přidání řádku do tabulky
                    tbody.appendChild(row);
                });

                /*
                // Výpis do konzole
                const result = data[0].map(entry => {
                    const bus = entry.route.short_name;
                    const time = new Date(entry.departure.timestamp_scheduled).toLocaleTimeString('cs-CZ', { hour: '2-digit', minute: '2-digit' });
                    return `${bus} - ${time}`;
                }).join(", ");
                */

                // Zobrazení uživateli
            };

            xhr.send();
        </script>

        <table class="output table table-striped" id="busSchedule">
            <thead>
                <tr>
                    <td>Číslo</td>
                    <td>Směr</td>
                    <td>Čas</td>
                    <td>Zpoždění</td>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>

        <h1 style="display: flex; justify-content: space-between;">
            <span id="teplota" class="vetsiText"><?php echo htmlspecialchars($teplota) ?> °C</span>
            <div id="hodiny" class="vetsiText"></div>
            <?php if ($enableMap == "true"): ?>
                <a href="pid-balkan-mapa.php" id="odkaz" class="vetsiText">Mapa</a>
            <?php endif; ?>
        </h1>

        <pre id="testOutput"></pre>

        <script>
            // Funkce pro přesměrování na jinou stránku
            document.addEventListener("click", function () {
                window.location.href = "./pid-balkan.php";  // Změň na URL, kam chceš přesměrovat
            });
        </script>

    </div>

</body>

</html>