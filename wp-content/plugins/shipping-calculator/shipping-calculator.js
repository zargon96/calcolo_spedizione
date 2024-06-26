var provinceMap = {
    "AO": "Aosta",
    "AL": "Alessandria",
    "AT": "Asti",
    "BI": "Biella",
    "CN": "Cuneo",
    "GD Torino": "Torino",
    "GD Novara": "Novara",
    "NO": "Novara",
    "TO": "Torino",
    "VB": "Verbano-Cusio-Ossola",
    "VC": "Vercelli",
    "BG": "Bergamo",
    "BS": "Brescia",
    "CO": "Como",
    "CO (Lago)": "Como (Lago)",
    "Campione": "Campione d'Italia",
    "CR": "Cremona",
    "GD Milano": "Milano",
    "LC": "Lecco",
    "LC (Lago)": "Lecco (Lago)",
    "LO": "Lodi",
    "MB": "Monza e della Brianza",
    "MI": "Milano",
    "MI (ZTL)": "Milano (ZTL)",
    "MN": "Mantova",
    "PV": "Pavia",
    "SO": "Sondrio",
    "VA": "Varese",
    "BL": "Belluno",
    "PD": "Padova",
    "RO": "Rovigo",
    "TV": "Treviso",
    "VE": "Venezia",
    "VE (Laguna)": "Venezia (Laguna)",
    "VI": "Vicenza",
    "VR": "Verona",
    "BZ": "Bolzano",
    "TN": "Trento",
    "GO": "Gorizia",
    "PN": "Pordenone",
    "TS": "Trieste",
    "UD": "Udine",
    "GE": "Genova",
    "IM": "Imperia",
    "SP": "La Spezia",
    "SV": "Savona",
    "BO": "Bologna",
    "FC": "Forlì-Cesena",
    "FE": "Ferrara",
    "MO": "Modena",
    "MO (Montagna)": "Modena (Montagna)",
    "PC": "Piacenza",
    "PR": "Parma",
    "RA": "Ravenna",
    "RE": "Reggio Emilia",
    "RN": "Rimini",
    "SM": "San Marino",
    "AR": "Arezzo",
    "FI": "Firenze",
    "GR": "Grosseto",
    "GR (Isole)": "Grosseto (Isole)",
    "LI": "Livorno",
    "LI (Elba)": "Livorno (Elba)",
    "LI (Capraia)": "Livorno (Capraia)",
    "LU": "Lucca",
    "MS": "Massa e Carrara",
    "PI": "Pisa",
    "PI (Volterra)": "Pisa (Volterra)",
    "PO": "Prato",
    "PT": "Pistoia",
    "SI": "Siena",
    "AN": "Ancona",
    "AP": "Ascoli Piceno",
    "FM": "Fermo",
    "MC": "Macerata",
    "PU": "Pesaro e Urbino",
    "PG": "Perugia",
    "TR": "Terni",
    "FR": "Frosinone",
    "LT": "Latina",
    "LT (Isole)": "Latina (Isole)",
    "RI": "Rieti",
    "RM (Fuori GRA)": "Roma (Fuori GRA)",
    "RM (GRA)": "Roma (GRA)",
    "VT": "Viterbo",
    "AQ": "L'Aquila",
    "CH": "Chieti",
    "PE": "Pescara",
    "TE": "Teramo",
    "CB": "Campobasso",
    "IS": "Isernia",
    "AV": "Avellino",
    "BN": "Benevento",
    "CE": "Caserta",
    "NA": "Napoli",
    "NA (Isole)": "Napoli (Isole)",
    "NA (Capri)": "Napoli (Capri)",
    "NA (Amalfitana)": "Napoli (Amalfitana)",
    "SA": "Salerno",
    "SA (Costiera)": "Salerno (Costiera)",
    "BA": "Bari",
    "BR": "Brindisi",
    "BT": "Barletta-Andria-Trani",
    "FG": "Foggia",
    "FG (Isole)": "Foggia (Isole)",
    "LE": "Lecce",
    "TA": "Taranto",
    "MT": "Matera",
    "PZ": "Potenza",
    "CS": "Cosenza",
    "CZ": "Catanzaro",
    "KR": "Crotone",
    "RC": "Reggio Calabria",
    "VV": "Vibo Valentia",
    "AG": "Agrigento",
    "AG (Isole)": "Agrigento (Isole)",
    "CL": "Caltanissetta",
    "CT": "Catania",
    "EN": "Enna",
    "ME": "Messina",
    "ME (Isole)": "Messina (Isole)",
    "PA": "Palermo",
    "PA (Isole)": "Palermo (Isole)",
    "RG": "Ragusa",
    "SR": "Siracusa",
    "TP": "Trapani",
    "TP (Isole)": "Trapani (Isole)",
    "CA": "Cagliari",
    "NU": "Nuoro",
    "OR": "Oristano",
    "SS": "Sassari",
    "SS (Isole)": "Sassari (Isole)",
    "SU": "Sud Sardegna",
    "SU (Isole)": "Sud Sardegna (Isole)"
};

jQuery(document).ready(function($) {
    var opzioniAggiuntiveLabels = {
        'sponda_idraulica': 'Consegna con sponda idraulica',
        'assicurazione': 'Assicurazione',
        'contrassegno': 'Contrassegno'
    };

    var previousSelectedPallet = null;

    $('#tipo_pallet_container').on('click', '.pallet-option', function() {
        if (previousSelectedPallet && previousSelectedPallet !== this) {
            $(previousSelectedPallet).find('.pallet-quantity').val(1);
            $(previousSelectedPallet).find('.quantity-container').hide();
        }
        
        $('.pallet-option').removeClass('selected');
        $(this).addClass('selected');
        var selectedPallet = $(this).data('pallet');
        $('#tipo_pallet').val(selectedPallet);
        disableNextButton(); 
        checkCalculateButton(); 

        $(this).find('.quantity-container').show();

        previousSelectedPallet = this;
    });

    // Funzione per abilitare/disabilitare le checkbox delle opzioni aggiuntive
    function toggleCheckboxes(enable) {
        $('#opzioni_aggiuntive input[type="checkbox"]').prop('disabled', !enable);
    }

    // Inizialmente disabilita tutte le checkbox
    toggleCheckboxes(false);
    $('.quantity-container').hide();

    $('#tipo_pallet_container').on('click', '.decrementQuantity', function() {
        var quantityInput = $(this).siblings('.pallet-quantity');
        var currentValue = parseInt(quantityInput.val());
        if (currentValue > 1) {
            quantityInput.val(currentValue - 1);
        }
    });

    $('#tipo_pallet_container').on('click', '.incrementQuantity', function() {
        var quantityInput = $(this).siblings('.pallet-quantity');
        var currentValue = parseInt(quantityInput.val());
        quantityInput.val(currentValue + 1);
    });

    $('#tipo_pallet_container').on('click', '.pallet-option', function() {
        if (previousSelectedPallet && previousSelectedPallet !== this) {
            $(previousSelectedPallet).find('.pallet-quantity').val(1);
            $(previousSelectedPallet).find('.quantity-container').hide();
        }
        
        $('.pallet-option').removeClass('selected');
        $(this).addClass('selected');
        var selectedPallet = $(this).data('pallet');
        $('#tipo_pallet').val(selectedPallet);
        disableNextButton(); 
        checkCalculateButton(); 
    
        $(this).find('.quantity-container').show();
        previousSelectedPallet = this;
    
        // Abilita le checkbox delle opzioni aggiuntive
        toggleCheckboxes(true);
    });

    var calculateButton = $('#calculateButton');
    var nextbutton = $('#nextbutton');

    function checkCalculateButton() {
        var selectedPallet = $('#tipo_pallet').val();
        var selectedSpedizione = $('input[name="tipo_spedizione"]:checked').val();
        if (selectedPallet && selectedSpedizione) {
            calculateButton.prop('disabled', false);
        } else {
            calculateButton.prop('disabled', true);
        }
    }

    function updateProvinceDisplay() {
        $('#partenza option, #destinazione option').each(function() {
            var value = $(this).val();
            if (provinceMap[value]) {
                $(this).text(provinceMap[value]);
            }
        });
    }

    updateProvinceDisplay();

    var partenzaSelect = $('#partenza');
    var destinazioneSelect = $('#destinazione');
    var tipoPalletSelect = $('#tipo_pallet');
    var opzioniAggiuntiveSelect = $('#opzioni_aggiuntive');
    var submitButton = $('#submitButton');
    var datiPersonaliDiv = $('#datiPersonali');
    var backButton = $('.backButton');
    var fields = [
        'mittente[nome]', 
        'mittente[indirizzo]', 
        'mittente[citta]', 
        'mittente[cap]', 
        'mittente[telefono]', 
        'mittente[email]',
        'destinatario[nome]', 
        'destinatario[indirizzo]', 
        'destinatario[citta]', 
        'destinatario[cap]', 
        'destinatario[telefono]', 
        'destinatario[email]'
    ];

    nextbutton.prop('disabled', true);

    $('.js-example-tags').select2({
        tags: true,
    });

    function updatePalletTypes() {
        var tipoSpedizione = $('input[name="tipo_spedizione"]:checked').val();
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

    $('#assicurazione').change(function() {
        if ($(this).is(':checked')) {
            $('#assicurazione_valori_container').show();
            triggerCalculate(); // Trigger the calculation when assicurazione is checked
        } else {
            $('#assicurazione_valori_container').hide();
        }
    });

    $('#contrassegno').change(function() {
        if ($(this).is(':checked')) {
            $('#contrassegno_valori_container').show();
        } else {
            $('#contrassegno_valori_container').hide();
        }
    });

    function disableNextButton() {
        nextbutton.prop('disabled', true);
    }

    $('input[name="tipo_spedizione"]').change(function() {
        updatePalletTypes();
        disableNextButton();
        checkCalculateButton();
    });
    
    destinazioneSelect.change(function() {
        updatePalletTypes();
        updateProvince();
        disableNextButton();
        checkCalculateButton();
    });
    
    partenzaSelect.change(function() {
        updatePalletTypes();
        updateProvince();
        disableNextButton();
        checkCalculateButton();
    });

    tipoPalletSelect.change(disableNextButton);
    opzioniAggiuntiveSelect.change(disableNextButton);

    updatePalletTypes();
    updateProvince();
    checkCalculateButton();

    $('#assicurazione').change(function() {
        if ($(this).is(':checked')) {
            $('#assicurazione_valori_container').show();
        } else {
            $('#assicurazione_valori_container').hide();
            $('#assicurazione_valori').val(''); 
            $('#assicurazione_valori').removeClass('is-invalid');
        }
    });

    $('#contrassegno').change(function() {
        if ($(this).is(':checked')) {
            $('#contrassegno_valori_container').show();
        } else {
            $('#contrassegno_valori_container').hide();
            $('#contrassegno_valori').val(''); 
            $('#contrassegno_valori').removeClass('is-invalid');
        }
    });

    function toggleCheckboxes(enable) {
        $('#opzioni_aggiuntive input[type="checkbox"]').prop('disabled', !enable);
    }

    // Inizialmente disabilita tutte le checkbox
    toggleCheckboxes(false);

    calculateButton.click(function() {
        var partenza = partenzaSelect.val();
        var destinazione = destinazioneSelect.val();
        var tipoSpedizione = $('input[name="tipo_spedizione"]:checked').val();
        var tipoPallet = tipoPalletSelect.val();
        var quantita = $(`.pallet-option[data-pallet='${tipoPallet}'] .pallet-quantity`).val();
    
        var assicurazioneValore = $('#assicurazione_valori').val();
        var contrassegnoValore = $('#contrassegno_valori').val();
        var opzioniAggiuntive = [];
        $('#opzioni_aggiuntive input:checked').each(function() {
            opzioniAggiuntive.push($(this).val());
        });
    
        $.post('/wp-admin/admin-ajax.php', {
            action: 'calculate_shipping',
            partenza: partenza,
            destinazione: destinazione,
            tipoSpedizione: tipoSpedizione,
            tipoPallet: tipoPallet,
            quantita: quantita,
            opzioniAggiuntive: opzioniAggiuntive,
            assicurazioneValore: assicurazioneValore,
            contrassegnoValore: contrassegnoValore
        }, function(response) {
            var result = JSON.parse(response);
            var costoSpedizione = parseFloat(result.costoSpedizione);
            var baseCostoSpedizione = parseFloat(result.baseCostoSpedizione);
    
            // Update the minimum value and message of assicurazione based on the shipping cost
            var newMinAssicurazione = baseCostoSpedizione * 0.10;
            $('#assicurazione_valori').attr('min', newMinAssicurazione);
            $('#assicurazione_valore_invalid_feedback').text('ATTENZIONE: Il valore dell\'assicurazione non può essere inferiore a €' + newMinAssicurazione.toFixed(2));
            $('#assicurazione_valori_container .help-block').text('(Minimo ' + newMinAssicurazione.toFixed(2) + ' euro)');
    
            // Validate assicurazione value dynamically
            if (opzioniAggiuntive.includes('assicurazione')) {
                if (!assicurazioneValore) {
                    $('#assicurazione_valori').addClass('is-invalid');
                    $('#assicurazione_valore_invalid_feedback').text('ATTENZIONE: Il valore dell\'assicurazione non può essere vuoto.');
                    return;
                } else if (assicurazioneValore < newMinAssicurazione) {
                    $('#assicurazione_valori').addClass('is-invalid');
                    $('#assicurazione_valore_invalid_feedback').text('ATTENZIONE: Il valore dell\'assicurazione non può essere inferiore a €' + newMinAssicurazione.toFixed(2));
                    return;
                } else {
                    $('#assicurazione_valori').removeClass('is-invalid');
                }
            }
    
            // Validate contrassegno value
            if (opzioniAggiuntive.includes('contrassegno')) {
                if (!contrassegnoValore) {
                    $('#contrassegno_valori').addClass('is-invalid');
                    $('#contrassegno_valore_invalid_feedback').text('ATTENZIONE: Il valore del contrassegno non può essere vuoto.');
                    return;
                } else if (contrassegnoValore < 50) {
                    $('#contrassegno_valori').addClass('is-invalid');
                    $('#contrassegno_valore_invalid_feedback').text('ATTENZIONE: Il valore del contrassegno non può essere inferiore a 50 euro.');
                    return;
                } else {
                    $('#contrassegno_valori').removeClass('is-invalid');
                }
            }
    
            $('#result').text('Il costo di spedizione è: €' + result.costoSpedizione);
            $('#summaryPartenza').text(provinceMap[partenza] || partenza);
            $('#summaryDestinazione').text(provinceMap[destinazione] || destinazione);
            $('#summaryTipoSpedizione').text(tipoSpedizione);
            $('#summaryTipoPallet').text(tipoPallet);
            $('#summaryQuantita').text(quantita);
    
            var opzioniAggiuntiveReadable = opzioniAggiuntive.map(function(opzione) {
                return opzioniAggiuntiveLabels[opzione] || opzione;
            });
    
            if (opzioniAggiuntiveReadable.length > 0) {
                $('#summaryOpzioni').html(opzioniAggiuntiveReadable.join(', '));
            } else {
                $('#summaryOpzioni').text('Nessuna opzione aggiuntiva');
            }
    
            var dettagliOpzioni = result.dettagliOpzioni;
    
            if (dettagliOpzioni.assicurazione) {
                $('#summaryOpzioni').append('<br>Valore Assicurazione: € ' + dettagliOpzioni.assicurazione.valore);
            }
    
            if (dettagliOpzioni.contrassegno) {
                $('#summaryOpzioni').append('<br>Valore Contrassegno: € ' + dettagliOpzioni.contrassegno.valore);
            }
            $('#summaryCosto').html('€ ' + result.costoSpedizione);
            $('#summary').removeClass('hidden'); 
            nextbutton.show();
            nextbutton.prop('disabled', false);
        }).fail(function() {
            alert('Errore nel calcolo del costo di spedizione!');
        });
    });
    

    function resetFormValidation() {
        $(fields.map(field => `#${field.replace(/\[/g, '\\[').replace(/\]/g, '\\]')}`).join(',')).each(function() {
            $(this).removeClass('is-invalid');
            $(this).removeClass('is-valid');
        });
    }

    nextbutton.click(function() {
        $('#spedizioneForm').hide();
        datiPersonaliDiv.show(); 
    });

    backButton.click(function() {
        resetFormValidation();
        datiPersonaliDiv.hide();
        $('#spedizioneForm').show(); 
        disableNextButton(); 
    });

    submitButton.click(function() {
        resetFormValidation();
        var invalidFields = [];

        $(fields.map(field => `#${field.replace(/\[/g, '\\[').replace(/\]/g, '\\]')}`).join(',')).each(function() {
            if (!validateField(this)) {
                invalidFields.push(this);
            }
        });

        var nomeMittente = $('#mittente\\[nome\\]').val();
        var indirizzoMittente = $('#mittente\\[indirizzo\\]').val();
        var cittaMittente = $('#mittente\\[citta\\]').val();
        var capMittente = $('#mittente\\[cap\\]').val();
        var telefonoMittente = $('#mittente\\[telefono\\]').val();
        var emailMittente = $('#mittente\\[email\\]').val();

        var nomeDestinatario = $('#destinatario\\[nome\\]').val();
        var indirizzoDestinatario = $('#destinatario\\[indirizzo\\]').val();
        var cittaDestinatario = $('#destinatario\\[citta\\]').val();
        var capDestinatario = $('#destinatario\\[cap\\]').val();
        var telefonoDestinatario = $('#destinatario\\[telefono\\]').val();
        var emailDestinatario = $('#destinatario\\[email\\]').val();

        var partenza = $('#partenza').val();
        var destinazione = $('#destinazione').val();
        var tipoSpedizione = $('input[name="tipo_spedizione"]:checked').val();
        var tipoPallet = $('#tipo_pallet').val();
        var quantita = $(`.pallet-option[data-pallet='${tipoPallet}'] .pallet-quantity`).val();
        var opzioniAggiuntive = [];
        $('#opzioni_aggiuntive input:checked').each(function() {
            opzioniAggiuntive.push($(this).val());
        });
        var costoSpedizione = $('#result').text().split('€')[1].trim();
        var assicurazioneValore = $('#assicurazione_valori').val();
        var contrassegnoValore = $('#contrassegno_valori').val();

        $.post('/wp-admin/admin-ajax.php', {
            action: 'submit_request',
            'mittente[nome]': nomeMittente,
            'mittente[indirizzo]': indirizzoMittente,
            'mittente[citta]': cittaMittente,
            'mittente[cap]': capMittente,
            'mittente[telefono]': telefonoMittente,
            'mittente[email]': emailMittente,
            'destinatario[nome]': nomeDestinatario,
            'destinatario[indirizzo]': indirizzoDestinatario,
            'destinatario[citta]': cittaDestinatario,
            'destinatario[cap]': capDestinatario,
            'destinatario[telefono]': telefonoDestinatario,
            'destinatario[email]': emailDestinatario,
            partenza: partenza,
            destinazione: destinazione,
            tipoSpedizione: tipoSpedizione,
            tipoPallet: tipoPallet,
            quantita: quantita,
            opzioniAggiuntive: opzioniAggiuntive,
            costoSpedizione: costoSpedizione,
            assicurazioneValore: assicurazioneValore,
            contrassegnoValore: contrassegnoValore
        }, function(response) {
            if (response.success) {
                $('#alertContainer').html('<div class="alert alert-success alert-dismissible fade show" role="alert">Richiesta inviata con successo.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
            } else {
                $('.alert').remove(); 
                $('#alertContainer').prepend('<div class="alert alert-danger alert-dismissible fade show" role="alert">Errore: ' + response.data.errors.join('') + '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');    
                $('html, body').animate({
                    scrollTop: $('#alertContainer').offset().top
                }, 'slow');            
            }
        }).fail(function() {
            $('.alert').remove(); 
            $('#alertContainer').prepend('<div class="alert alert-danger alert-dismissible fade show" role="alert">Errore di rete. Per favore riprova più tardi.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');    
            $('html, body').animate({
                scrollTop: $('#alertContainer').offset().top
            }, 'slow');
        });
    });

    $('#mittente\\[cap\\], #destinatario\\[cap\\]').on('input', function() {
        this.value = this.value.replace(/\D/g, '').substring(0, 5);
    });

    $('#mittente\\[telefono\\], #destinatario\\[telefono\\]').on('input', function() {
        this.value = this.value.replace(/\D/g, '').substring(0, 10);
    });

    function allowOnlyLetters(input) {
        var value = input.value;
        var regex = /[^a-zA-Z\s]/g;
        input.value = value.replace(regex, '');
    }

    $('#mittente\\[citta\\],#destinatario\\[citta\\]').on('input', function() {
        allowOnlyLetters(this);
    });

    function validateField(field) {
        if (field.checkValidity()) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            return true;
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            return false;
        }
    }

    $(fields.map(field => `#${field.replace(/\[/g, '\\[').replace(/\]/g, '\\]')}`).join(',')).on('input', function() {
        validateField(this);
    });

    submitButton.click(function() {
        resetFormValidation();
        var invalidFields = [];

        $(fields.map(field => `#${field.replace(/\[/g, '\\[').replace(/\]/g, '\\]')}`).join(',')).each(function() {
            if (!validateField(this)) {
                invalidFields.push(this);
            }
        });
    });
});


