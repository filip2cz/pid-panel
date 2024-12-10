<?php
// Načtení obsahu souboru song.json
$json = file_get_contents('config.json');

// Parsování JSON do PHP pole
$config = json_decode($json, true);

// Získání dat z JSON
$refreshTime = isset($config['refreshTime']) ? $config['refreshTime'] : 0;
$pidUrl = isset($config['pidUrl']) ? $config['pidUrl'] : 0;
$pidApiKey = isset($config['pidApiKey']) ? $config['pidApiKey'] : 0;
$zastavka = isset($config['zastavka']) ? $config['zastavka'] : 0;
$weatherUrl = isset($config['weatherUrl']) ? $config['weatherUrl'] : 0;
$enableMap = isset($config['enableMap']) ? $config['enableMap'] : 0;
?>

<?php
function checkUserPassword($username, $password) {
    $script = './check_password.sh';
    $escapedUsername = escapeshellarg($username);
    $escapedPassword = escapeshellarg($password);

    $output = shell_exec("bash $script $escapedUsername $escapedPassword");

    return trim($output) === 'OK';
}

// Použití
if (checkUserPassword('uzivatel', 'heslo')) {
    echo "Ověření bylo úspěšné.";
} else {
    echo "Neplatné uživatelské jméno nebo heslo.";
}
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

    <link rel="stylesheet" type="text/css" href="/main.css">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
        </script>

    <script src="hodiny.js"></script>

    <div class="stranka">

        <h1><span class="vetsiText">Nastavení</span></h1>



    </div>

</body>

</html>