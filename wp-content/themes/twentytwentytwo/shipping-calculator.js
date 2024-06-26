jQuery(document).ready(function($) {
    var tipoSpedizioneSelect = $('#tipo_spedizione');
    var destinazioneSelect = $('#destinazione');
    var partenzaSelect = $('#partenza');
    var tipoPalletSelect = $('#tipo_pallet');
    var opzioniAggiuntiveSelect = $('#opzioni_aggiuntive');
    var calculateButton = $('#calculateButton');
    var submitButton = $('#submitButton');
    var datiPersonaliDiv = $('#datiPersonali');
    var nextbutton = $('#nextbutton');
    

    // Initialize Select2 with tagging
    $('.js-example-tags').select2({
        tags: true,
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
            $('#summaryPartenza').text(partenza);
            $('#summaryDestinazione').text(destinazione);
            $('#summaryTipoSpedizione').text(tipoSpedizione);
            $('#summaryTipoPallet').text(tipoPallet);
            $('#summaryOpzioni').text(opzioniAggiuntive);
            $('#summaryCosto').text('€' + response);
            $('#summary').removeClass('hidden'); // Mostra il riepilogo
            $('#nextbutton').show();
        }).fail(function() {
            alert('Errore nel calcolo del costo di spedizione!');
        });
    });

    nextbutton.click(function() {
        $('#spedizioneForm').hide();
        datiPersonaliDiv.show(); // Mostra i campi dei dati anagrafici
    });

    submitButton.click(function() {
        var form = $('#spedizioneForm')[0];
        if (!form.checkValidity()) {
            alert('Per favore, compila tutti i campi obbligatori.');
            return;
        }

        var nomeMittente = $('#nome_mittente').val();
        var indirizzoMittente = $('#indirizzo_mittente').val();
        var cittaMittente = $('#citta_mittente').val();
        var capMittente = $('#cap_mittente').val();
        var telefonoMittente = $('#telefono_mittente').val();
        var emailMittente = $('#email_mittente').val();

        var nomeDestinatario = $('#nome_destinatario').val();
        var indirizzoDestinatario = $('#indirizzo_destinatario').val();
        var cittaDestinatario = $('#citta_destinatario').val();
        var capDestinatario = $('#cap_destinatario').val();
        var telefonoDestinatario = $('#telefono_destinatario').val();
        var emailDestinatario = $('#email_destinatario').val();

        if (!validateEmail(emailMittente) || !validateEmail(emailDestinatario)) {
            alert('Per favore, inserisci un indirizzo email valido.');
            return;
        }

        var partenza = $('#partenza').val();
        var destinazione = $('#destinazione').val();
        var tipoSpedizione = $('#tipo_spedizione').val();
        var tipoPallet = $('#tipo_pallet').val();
        var opzioniAggiuntive = $('#opzioni_aggiuntive').val();
        var costoSpedizione = $('#result').text().split('€')[1].trim();

        // Chiamata AJAX per inviare la richiesta
        $.post('/wp-admin/admin-ajax.php', {
            action: 'submit_request',
            nome_mittente: nomeMittente,
            indirizzo_mittente: indirizzoMittente,
            citta_mittente: cittaMittente,
            cap_mittente: capMittente,
            telefono_mittente: telefonoMittente,
            email_mittente: emailMittente,
            nome_destinatario: nomeDestinatario,
            indirizzo_destinatario: indirizzoDestinatario,
            citta_destinatario: cittaDestinatario,
            cap_destinatario: capDestinatario,
            telefono_destinatario: telefonoDestinatario,
            email_destinatario: emailDestinatario,
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
            alert('Errore nell\'invio della richiesta!');
        });
    });

    function validateEmail(email) {
        var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
});
