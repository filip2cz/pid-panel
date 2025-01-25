function aktualizujHodiny() {
    const hodinyElement = document.getElementById('hodiny');

    const aktualniCas = new Date().toLocaleString('cs-CZ', {
        timeZone: 'Europe/Prague',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    });

    hodinyElement.textContent = aktualniCas;
}

setInterval(aktualizujHodiny, 1000);