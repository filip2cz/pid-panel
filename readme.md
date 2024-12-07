# pid-panel

## Kiosk mode
`chromium --kiosk "https://www.example.com"`

## Pokud nefunguje získání dat přes php

přidat do /etc/php/8.1/apache2/php.ini text:

`extension=curl`