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
    var fields = [
        '#nome_mittente', 
        '#indirizzo_mittente', 
        '#citta_mittente', 
        '#cap_mittente', 
        '#telefono_mittente', 
        '#email_mittente',
        '#nome_destinatario', 
        '#indirizzo_destinatario', 
        '#citta_destinatario', 
        '#cap_destinatario', 
        '#telefono_destinatario', 
        '#email_destinatario'
    ];

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

    function updateProvince() {
        var partenza = partenzaSelect.val();
        var destinazione = destinazioneSelect.val();
        $('#provincia_mittente').val(partenza);
        $('#provincia_destinatario').val(destinazione);
    }

    tipoSpedizioneSelect.change(updatePalletTypes);
    destinazioneSelect.change(function() {
        updatePalletTypes();
        updateProvince();
    });
    partenzaSelect.change(function() {
        updatePalletTypes();
        updateProvince();
    });

    // Inizializza i tipi di pallet e le province alla prima esecuzione
    updatePalletTypes();
    updateProvince();

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
            if (response.success) {
                // Mostra l'avviso di successo
                $('#alertContainer').html('<div class="alert alert-success alert-dismissible fade show" role="alert">Richiesta inviata con successo.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
                setTimeout(function() {
                    window.location.href = window.location.href.split('?')[0] + '?success=1';
                }, 2000);
            } else {
                $('.alert').remove(); // Rimuovi eventuali avvisi esistenti
                $('#alertContainer').prepend('<div class="alert alert-danger alert-dismissible fade show" role="alert">Errore: ' + response.data.errors.join('') + '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');    
                // Scrolla alla posizione dell'alert di errore
                $('html, body').animate({
                    scrollTop: $('#alertContainer').offset().top
                }, 'slow');            
            }
        })
    });
    // Funzione per permettere solo numeri nei campi CAP e massimo 5 cifre
    $('#cap_mittente, #cap_destinatario').on('input', function() {
        this.value = this.value.replace(/\D/g, '').substring(0, 5);
    });
    // Funzione per permettere solo numeri nel cellulare con massimo 10 cifre
    $('#telefono_mittente, #telefono_destinatario').on('input', function() {
        this.value = this.value.replace(/\D/g, '').substring(0, 10);
    });
    // Funzioni di validazione con classi bootstrap
    function validateField(field) {
        if (field.checkValidity()) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
        }
    }

    // Associa l'evento 'input' a tutti i campi nell'array
    $(fields.join(',')).on('input', function() {
        validateField(this);
    });

    // Associa l'evento 'click' al pulsante di submit
    submitButton.click(function() {
        $(fields.join(',')).each(function() {
            validateField(this);
        });
    });
});