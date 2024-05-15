document.addEventListener('DOMContentLoaded', function() {
    var tipoSpedizioneSelect = document.getElementById('tipo_spedizione');
    var destinazioneSelect = document.getElementById('destinazione');
    var partenzaSelect = document.getElementById('partenza');
    var tipoPalletSelect = document.getElementById('tipo_pallet');
    var opzioniAggiuntiveSelect = document.getElementById('opzioni_aggiuntive');
    var calculateButton = document.getElementById('calculateButton');
    var submitButton = document.getElementById('submitButton');
    var datiPersonaliDiv = document.getElementById('datiPersonali');
    var requestResult = document.getElementById('requestResult');

    function updatePalletTypes() {
        var tipoSpedizione = tipoSpedizioneSelect.value;
        var destinazione = destinazioneSelect.value;
        tipoPalletSelect.innerHTML = '';
        var options = shippingData[destinazione][tipoSpedizione];
        Object.keys(options).forEach(function (palletType) {
            var option = document.createElement('option');
            option.value = palletType;
            option.textContent = palletType;
            tipoPalletSelect.appendChild(option);
        });
    }

    tipoSpedizioneSelect.addEventListener('change', updatePalletTypes);
    destinazioneSelect.addEventListener('change', updatePalletTypes);
    partenzaSelect.addEventListener('change', updatePalletTypes);

    // Inizializza i tipi di pallet alla prima esecuzione
    updatePalletTypes();

    calculateButton.addEventListener('click', function() {
        var partenza = partenzaSelect.value;
        var destinazione = destinazioneSelect.value;
        var tipoSpedizione = tipoSpedizioneSelect.value;
        var tipoPallet = tipoPalletSelect.value;
        var opzioniAggiuntive = opzioniAggiuntiveSelect.value;

        // Chiamata AJAX per calcolare il costo della spedizione
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/wp-admin/admin-ajax.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        xhr.onload = function() {
            if (xhr.status === 200) {
                document.getElementById('result').innerText = 'Il costo di spedizione è: €' + xhr.responseText;
                datiPersonaliDiv.classList.remove('hidden');
            } else {
                alert('Errore nel calcolo del costo di spedizione!');
            }
        };
        xhr.send('action=calculate_shipping&partenza=' + partenza + '&destinazione=' + destinazione + '&tipoSpedizione=' + tipoSpedizione + '&tipoPallet=' + tipoPallet + '&opzioniAggiuntive=' + opzioniAggiuntive);
    });

    submitButton.addEventListener('click', function() {
        var form = document.getElementById('spedizioneForm');
        var valid = form.checkValidity();
        if (!valid) {
            alert('Per favore, compila tutti i campi obbligatori.');
            return;
        }

        var nome = document.getElementById('nome').value;
        var cognome = document.getElementById('cognome').value;
        var indirizzo = document.getElementById('indirizzo').value;
        var telefono = document.getElementById('telefono').value;
        var email = document.getElementById('email').value;
        var dataNascita = document.getElementById('data_nascita').value;

        if (!validateEmail(email)) {
            alert('Per favore, inserisci un indirizzo email valido.');
            return;
        }

        var partenza = partenzaSelect.value;
        var destinazione = destinazioneSelect.value;
        var tipoSpedizione = tipoSpedizioneSelect.value;
        var tipoPallet = tipoPalletSelect.value;
        var opzioniAggiuntive = opzioniAggiuntiveSelect.value;
        var costoSpedizione = document.getElementById('result').innerText.split('€')[1].trim();

        // Chiamata AJAX per inviare la richiesta
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/wp-admin/admin-ajax.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        xhr.onload = function() {
            if (xhr.status === 200) {
                // Reindirizza alla stessa pagina con parametro di query success=1
                window.location.href = window.location.href.split('?')[0] + '?success=1';
            } else {
                alert('Errore nell" invio della richiesta!');
            }
        };
        xhr.send('action=submit_request&nome=' + nome + '&cognome=' + cognome + '&indirizzo=' + indirizzo + '&telefono=' + telefono + '&email=' + email + '&dataNascita=' + dataNascita + '&partenza=' + partenza + '&destinazione=' + destinazione + '&tipoSpedizione=' + tipoSpedizione + '&tipoPallet=' + tipoPallet + '&opzioniAggiuntive=' + opzioniAggiuntive + '&costoSpedizione=' + costoSpedizione);
    });

    function validateEmail(email) {
        var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
});
