<?php

if (function_exists('curl_version')) {
    echo '';
} else {
    echo 'ERROR: cURL not found';
}

// Cesta k souboru, který chcete načíst
$apikey_path = 'apikey.txt';

// Kontrola, zda soubor existuje a lze ho načíst
if (file_exists($apikey_path) && is_readable($apikey_path)) {
    // Načte obsah souboru
    $apikey = file_get_contents($apikey_path);
} else {
    // Nastaví chybovou zprávu, pokud soubor neexistuje nebo nelze načíst
    $apikey = "apikeyerror";
}
?>

<!DOCTYPE html>
<html lang='cs' data-bs-theme="dark">

<head>
    <title>Smart panel: PID</title>
    <link rel="icon" type="image/x-icon" href="./favicon.ico">
    <meta charset='utf-8'>
    <meta http-equiv="refresh" content="20;url=pid-balkan.php">
</head>

<body>

    <link rel="stylesheet" type="text/css" href="/main.css">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
        </script>

    <script src="hodiny.js"></script>

    <div class="container">

        <?php if ($apikey == "apikeyerror"): ?>
            <p class="center">ERROR loading API KEY</p>
        <?php endif; ?>

        <h1>Odjezdy z Šestajovice, Balkán</h1>
        <!--<h1>Odjezdy z Šestajovice, Za Stodolami</h1>-->

        <script>
            var xhr = new XMLHttpRequest();

            // https://api.golemio.cz/pid/docs/openapi/#/%F0%9F%95%92%20Public%20Departures%20(v2)/get_v2_public_departureboards

            // Šestajovice Balkán
            xhr.open('GET', 'https://api.golemio.cz/v2/public/departureboards?stopIds=%7B%220%22%3A%20%5B%22U1613Z1%22%2C%20%22U1613Z2%22%5D%7D&limit=5&minutesAfter=360', true);

            // Nastavení hlavičky pro API klíč
            xhr.setRequestHeader('X-Access-Token', '<?php echo htmlspecialchars($apikey, ENT_QUOTES, 'UTF-8'); ?>');

            // Nastavení typu přijatých dat (volitelné, pokud očekáváš JSON)
            xhr.setRequestHeader('accept', 'application/json');

            // Šestajovice Za Stodolami
            //xhr.open('GET', 'https://api.golemio.cz/v2/public/departureboards?stopIds=%7B%220%22%3A%20%5B%22U1500Z1%22%2C%20%22U1500Z2%22%5D%7D&limit=5&minutesAfter=360', true);

            // Bazar
            //xhr.open('GET', 'https://api.golemio.cz/v2/public/departureboards?stopIds=%7B%220%22%3A%20%5B%22U18Z1P%22%2C%20%22U18Z1%22%2C%20%22U18Z2P%22%2C%20%22U18Z2%22%5D%7D&limit=5&minutesAfter=360', true);

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

        <script>
            // URL pro API dotaz
            const apiUrl = "https://api.open-meteo.com/v1/forecast?latitude=50.1069497&longitude=14.6770131&current_weather=true";

            // Funkce pro získání dat
            async function ziskejTeplotu() {
                try {
                    const response = await fetch(apiUrl); // Načti data z API
                    if (!response.ok) {
                        throw new Error(`Chyba: ${response.status}`);
                    }
                    const data = await response.json(); // Převeď odpověď na JSON
                    const teplota = data.current_weather.temperature; // Získej teplotu

                    // Vypiš teplotu uživateli
                    document.getElementById("teplota").textContent = `${teplota} °C`;
                } catch (error) {
                    // Ošetři chyby
                    document.getElementById("teplota").textContent = `Nepodařilo se načíst teplotu: ${error.message}`;
                }
            }

            // Zavolej funkci pro získání dat
            ziskejTeplotu();
        </script>

        <h1 style="display: flex; justify-content: space-between;">
            <span id="teplota">Načítám teplotu...</span>
            <div id="hodiny"></div>
            <a href="pid-balkan-mapa.php" id="odkaz">Mapa</a>
        </h1>

        <pre id="testOutput"></pre>

        <script>
            // Funkce pro přesměrování na jinou stránku
            document.addEventListener("click", function () {
                window.location.href = "http://localhost/pid-balkan.php";  // Změň na URL, kam chceš přesměrovat
            });
        </script>

    </div>

</body>

</html>