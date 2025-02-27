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
        $maxLength = $_COOKIE['maxLetters'];
        $text2 = shortenText("Toto je kalibrační text.t e s t e s t e s t e s t", $maxLength);
        $text = shortenText("Toto je kalibrační text.MMMMMMMMMMMMMMMMMMMMMMMMMM", $maxLength);
        $text3 = shortenText("A B C D E F G H I J K L M N O P Q R S T U V W X Y Z", $maxLength);
        ?>

        <h1>Probíhá kalibrace displeje</h1>

        <table class="output table table-striped" id="busSchedule">
            <thead>
                <tr>
                    <th>Číslo</th>
                    <th>Směr</th>
                    <th>Čas</th>
                    <th>Zpoždění</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>000</td>
                    <td>kalibrace</td>
                    <td>00:00</td>
                    <td>000</td>
                </tr>
                <tr>
                    <td>000</td>
                    <td>
                        <?php
                        echo $text;
                        ?>
                    </td>
                    <td>00:00</td>
                    <td>000</td>
                </tr>
                <tr>
                    <td>000</td>
                    <td>
                        <?php
                        echo $text2;
                        ?>
                    </td>
                    <td>00:00</td>
                    <td>000</td>
                </tr>
                <tr>
                    <td>000</td>
                    <td>
                        <?php
                        echo $text3;
                        ?>
                    </td>
                    <td>00:00</td>
                    <td>000</td>
                </tr>
            </tbody>
        </table>

        <?php echo $text2; ?> <br>
        <?php echo $text; ?> <br>
        <?php echo $text3; ?> <br>

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

                // Po úpravě cookie refreshujeme stránku
                console.log("Obnovuji stránku...");
                location.reload();
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
                    else if (minHeight < rowHeight) {
                        changeNeeded = 1;
                    }
                });

                console.log("Maximální výška řádků je: ", maxHeight, "px");

                if (changeNeeded == 1) {
                    console.log("Některé řádky jsou větší, je třeba změna.");
                    incrementMaxLettersCookie();
                }
                else {
                    window.location.href = "./pid-tabule.php";
                }
            }

            // Zavoláme funkci pro kontrolu výšky řádků v tabulce
            checkRowHeightInTable();

        </script>

    </div>

</body>

</html>