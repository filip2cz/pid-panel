<?php
// Načtení obsahu souboru song.json
$json = file_get_contents('config.json');

// Parsování JSON do PHP pole
$config = json_decode($json, true);

// Načtení velikosti okna
if (isset($_COOKIE['window_height']) && isset($_COOKIE['window_width'])) {
    $windowHeight = $_COOKIE['window_height'];
    $pidLimit = floor(($windowHeight - 214) / 65);
} else {
    $pidLimit = 5;
}

// Získání dat z JSON
$refreshTime = isset($config['refreshTime']) ? $config['refreshTime'] : 10;
$pidUrl = isset($config['pidUrl']) ? $config['pidUrl'] . "&limit=$pidLimit" : 0;
$pidApiKey = isset($config['pidApiKey']) ? $config['pidApiKey'] : 0;
$zastavka = isset($config['zastavka']) ? $config['zastavka'] : 0;
$weatherUrl = isset($config['weatherUrl']) ? $config['weatherUrl'] : 0;
$weatherUrl2 = isset($config['weatherUrl2']) ? $config['weatherUrl2'] : 0;
$enableMap = isset($config['enableMap']) ? $config['enableMap'] : 0;
?>

<?php

// Funkce pro získání dat z API
function ziskejTeplotu($url)
{
    if (preg_match("/api.open-meteo.com/", $url)) {
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
    } else if (preg_match("/meteo-pocasi.cz/", $url)) {
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

            // Načtení HTML do DOMDocument
            $dom = new DOMDocument();
            libxml_use_internal_errors(true); // Potlačení chybového výstupu kvůli nevalidnímu HTML
            $dom->loadHTML($response);
            libxml_clear_errors();

            // Použití DOMXPath pro jednodušší hledání prvků
            $xpath = new DOMXPath($dom);

            // Scrapování stavu komunikace
            $status = $xpath->query("//div[contains(@class, 'status_meteo_text')]");
            $stavKomunikace = $status->length > 0 ? trim($status[0]->nodeValue) : 'Neznámý';

            // Scrapování teploty
            $temperature = $xpath->query("//div[contains(@class, 'svalue')]");
            $teplota = $temperature->length > 0 ? trim($temperature[0]->nodeValue) : 'Neznámá';

            if ($stavKomunikace == "on-line") {
                return $teplota;
            } else {
                return ziskejTeplotu($GLOBALS['weatherUrl2']);
            }
        } catch (Exception $e) {
            return "Nepodařilo se načíst teplotu: " . $e->getMessage();
        }
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

    <script>
        function setWindowSizeCookie() {
            document.cookie = "window_height=" + window.innerHeight + "; path=/";
            document.cookie = "window_width=" + window.innerWidth + "; path=/";
        }

        // Zavoláme při načtení a změně velikosti okna
        window.onload = setWindowSizeCookie;
        window.onresize = setWindowSizeCookie;
    </script>

    <script>
        function refreshPage() {
            fetch(window.location.href) // Stáhne aktuální stránku
                .then(response => response.text()) // Převede ji na text
                .then(html => {
                    let newDoc = new DOMParser().parseFromString(html, "text/html"); // Vytvoří nový DOM
                    document.body.innerHTML = newDoc.body.innerHTML; // Přepíše obsah stránky
                })
                .catch(err => console.error("Chyba při načítání stránky:", err));
        }

        // Automatická aktualizace každých X sekund
        setInterval(refreshPage, <?php echo htmlspecialchars($refreshTime * 1000); ?>);
    </script>

    <link rel="stylesheet" type="text/css" href="/main.css">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
        </script>

    <script src="hodiny.js"></script>

    <div class="stranka">

        <h1><span class="vetsiText">Odjezdy z <?php echo htmlspecialchars($zastavka); ?></span></h1>

        <?php

        function shortenText($text, $maxLength)
        {
            // Pokud je text kratší než maximální délka, vrátí se nezměněný
            if (mb_strlen($text) <= $maxLength) {
                return $text;
            }

            // Zkrátí text a přidá tři tečky na konec
            return mb_substr($text, 0, $maxLength) . '...';
        }

        ?>

        <?php

        date_default_timezone_set('Europe/Prague');

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

        if (isset($_COOKIE['maxLetters'])) {
            $maxLength = $_COOKIE['maxLetters'];
        } else {
            $maxLength = 100000000;
        }

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
                //$headsign = $entry['trip']['headsign'];
                $headsign = shortenText($entry['trip']['headsign'], $maxLength);

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

        <footer class="stranka">
            <h1 style="display: flex; justify-content: space-between;">
                <span id="teplota" class="vetsiText"><?php echo htmlspecialchars($teplota) ?> °C</span>
                <div id="hodiny" class="vetsiText">
                    <?php
                    // Nastav časovou zónu (volitelné, pokud není nastavena v konfiguraci serveru)
                    date_default_timezone_set('Europe/Prague');

                    // Získání aktuálního času ve formátu H:i:s (hodiny:minuty:vteřiny)
                    $cas = date('H:i:s');

                    // Zobrazení času na stránce
                    echo "$cas";
                    ?>
                </div>
                <?php if ($enableMap == "true"): ?>
                    <a href="pid-mapa.php" id="odkaz" class="vetsiText">Mapa</a>
                <?php endif; ?>
            </h1>
        </footer>

        <pre id="testOutput"></pre>

        <script>
            // Funkce pro přesměrování na jinou stránku
            document.addEventListener("click", function () {
                window.location.href = "./pid-tabule.php";  // Změň na URL, kam chceš přesměrovat
            });
        </script>

<script>
            // Tento Javascript se stará o to, aby bylo zjištěno číslo, od kdy se zalamuje název konečné stanice na stráce, aby tomu později mohlo PHP předcházet

            // Funkce pro zmenšení hodnoty cookie maxLetters o 1
            function incrementMaxLettersCookie() {
                console.log("Zvětšení cookie 'maxLetters'...");

                // Získání hodnoty cookie maxLetters (pokud existuje)
                let maxLetters = parseInt(getCookie('maxLetters') || '50');
                console.log("Aktuální hodnota cookie 'maxLetters':", maxLetters);

                // Zmenšení hodnoty o 1
                maxLetters--;
                console.log("Nová hodnota cookie 'maxLetters':", maxLetters);

                // Uložení nové hodnoty do cookie bez expirace (session cookie)
                setCookie('maxLetters', maxLetters);
                console.log("Cookie 'maxLetters' byla uložena.");

                // Po úpravě cookie refreshujeme stránku (za 5 vteřin, ať neděláme DDOS)
                setTimeout(() => {
                    console.log("Obnovuji stránku...");
                    location.reload();
                }, 5000);
            }

            // Funkce pro získání hodnoty cookie podle názvu
            function getCookie(name) {
                let match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
                if (match) {
                    return match[2];
                }
                return null;
            }

            // Funkce pro nastavení hodnoty cookie (session cookie)
            function setCookie(name, value) {
                // Nastavení cookie bez expirace, což znamená, že cookie je session cookie
                document.cookie = name + "=" + value + "; path=/";
                console.log(`Cookie '${name}' nastavena na hodnotu: ${value}`);
            }

            // Funkce pro kontrolu, zda se některý řádek v tabulce rozšířil
            function checkRowHeightInTable() {
                console.log("Začínám kontrolu výšky řádků v tabulce...");

                const rows = document.querySelectorAll('#busSchedule tbody tr');  // Získáme všechny řádky tabulky
                let maxHeight = 0;
                let minHeight = 0;

                let changeNeeded = 0;

                // Nejprve zjistíme maximální výšku řádku
                rows.forEach(row => {
                    const rowHeight = row.offsetHeight;  // Získáme výšku řádku
                    console.log(`Výška řádku: ${rowHeight}px`);
                    if (rowHeight > maxHeight) {
                        maxHeight = rowHeight;  // Uložíme maximální výšku
                    }
                    if (minHeight == 0) {
                        minHeight = rowHeight;  // Uložíme minimální výšku
                    }
                    else if (minHeight > rowHeight) {
                        minHeight = rowHeight;
                    }
                });

                console.log("Minimální výška řádků je: ", minHeight, "px");
                console.log("Maximální výška řádků je: ", maxHeight, "px");

                if ((minHeight != maxHeight)) {
                    console.log("Některé řádky jsou větší, je třeba změna.");
                    incrementMaxLettersCookie();
                }
            }

            // Zavoláme funkci pro kontrolu výšky řádků v tabulce
            checkRowHeightInTable();

        </script>

    </div>

</body>

</html>