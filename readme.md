# pid-panel

## Kiosk mode
`chromium --kiosk "https://www.example.com"`

## Pokud nefunguje získání dat přes php (projevuje se bílou obrazovkou, protože php neví, co má dělat, protože nezná curl)

nainstalovat php-curl:

`sudo apt install php-curl`

přidat do /etc/php/[verzephp]/apache2/php.ini text:

`extension=curl`

po každé takové změně restartovat apache server:

`sudo systemctl restart apache2`

## Odkaz na dokumentaci api, co je používáno

https://api.golemio.cz/pid/docs/openapi/#/%F0%9F%95%92%20Public%20Departures%20(v2)/get_v2_public_departureboards

## Web settings
https://github.com/yudai/gotty

<!-- https://github.com/tsl0922/ttyd -->

./gotty --permit-write nano /var/www/html/config.json