jQuery(document).ready(function($) {
    var tipoSpedizioneSelect = $('#tipo_spedizione');
    var destinazioneSelect = $('#destinazione');
    var partenzaSelect = $('#partenza');
    var tipoPalletSelect = $('#tipo_pallet');
    var opzioniAggiuntiveSelect = $('#opzioni_aggiuntive');
    var calculateButton = $('#calculateButton');
    var submitButton = $('#submitButton');
    var datiPersonaliDiv = $('#datiPersonali');

    // Initialize Select2 with tagging
    $('.js-example-tags').select2({
        tags: true
    });

    function updatePalletTypes() {
        var tipoSpedizione = tipoSpedizioneSelect.val();
        var destinazione = destinazioneSelect.val();
        tipoPalletSelect.empty();
        var options = shippingData[destinazione][tipoSpedizione];
        $.each(options, function(palletType) {
            tipoPalletSelect.append($('<option>', {
                value: palletType,
                text: palletType
            }));
        });
    }

    tipoSpedizioneSelect.change(updatePalletTypes);
    destinazioneSelect.change(updatePalletTypes);
    partenzaSelect.change(updatePalletTypes);

    // Inizializza i tipi di pallet alla prima esecuzione
    updatePalletTypes();

    calculateButton.click(function() {
        var partenza = partenzaSelect.val();
        var destinazione = destinazioneSelect.val();
        var tipoSpedizione = tipoSpedizioneSelect.val();
        var tipoPallet = tipoPalletSelect.val();
        var opzioniAggiuntive = opzioniAggiuntiveSelect.val();

        // Chiamata AJAX per calcolare il costo della spedizione
        $.post('/wp-admin/admin-ajax.php', {
            action: 'calculate_shipping',
            partenza: partenza,
            destinazione: destinazione,
            tipoSpedizione: tipoSpedizione,
            tipoPallet: tipoPallet,
            opzioniAggiuntive: opzioniAggiuntive
        }, function(response) {
            $('#result').text('Il costo di spedizione è: €' + response);
            datiPersonaliDiv.removeClass('hidden');
        }).fail(function() {
            alert('Errore nel calcolo del costo di spedizione!');
        });
    });

    submitButton.click(function() {
        var form = $('#spedizioneForm')[0];
        if (!form.checkValidity()) {
            alert('Per favore, compila tutti i campi obbligatori.');
            return;
        }

        var nome = $('#nome').val();
        var cognome = $('#cognome').val();
        var indirizzo = $('#indirizzo').val();
        var telefono = $('#telefono').val();
        var email = $('#email').val();
        var dataNascita = $('#data_nascita').val();

        if (!validateEmail(email)) {
            alert('Per favore, inserisci un indirizzo email valido.');
            return;
        }

        var partenza = partenzaSelect.val();
        var destinazione = destinazioneSelect.val();
        var tipoSpedizione = tipoSpedizioneSelect.val();
        var tipoPallet = tipoPalletSelect.val();
        var opzioniAggiuntive = opzioniAggiuntiveSelect.val();
        var costoSpedizione = $('#result').text().split('€')[1].trim();

        // Chiamata AJAX per inviare la richiesta
        $.post('/wp-admin/admin-ajax.php', {
            action: 'submit_request',
            nome: nome,
            cognome: cognome,
            indirizzo: indirizzo,
            telefono: telefono,
            email: email,
            dataNascita: dataNascita,
            partenza: partenza,
            destinazione: destinazione,
            tipoSpedizione: tipoSpedizione,
            tipoPallet: tipoPallet,
            opzioniAggiuntive: opzioniAggiuntive,
            costoSpedizione: costoSpedizione
        }, function(response) {
            // Reindirizza alla stessa pagina con parametro di query success=1
            window.location.href = window.location.href.split('?')[0] + '?success=1';
        }).fail(function() {
            alert('Errore nell" invio della richiesta!');
        });
    });

    function validateEmail(email) {
        var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
});
