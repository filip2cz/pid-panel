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