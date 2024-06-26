<?php

$pallet_info = [
    'FP' => [
        'description' => 'Descrizione per FP',
        'image' => plugin_dir_url(__FILE__) . 'img/pallet1.png'
    ],
    'LP' => [
        'description' => 'Descrizione per LP',
        'image' => plugin_dir_url(__FILE__) . 'img/pallet2.png'
    ],
    'ULP' => [
        'description' => 'Descrizione per ULP',
        'image' => plugin_dir_url(__FILE__) . 'img/pallet3.png'
    ],
    'HP' => [
        'description' => 'Descrizione per HP',
        'image' => plugin_dir_url(__FILE__) . 'img/pallet4.png'
    ],
    'ELP' => [
        'description' => 'Descrizione per ELP',
        'image' => plugin_dir_url(__FILE__) . 'img/pallet5.png'
    ],
    'QP' => [
        'description' => 'Descrizione per QP',
        'image' => plugin_dir_url(__FILE__) . 'img/pallet6.png'
    ],
    'MQP' => [
        'description' => 'Descrizione per MQP',
        'image' => plugin_dir_url(__FILE__) . 'img/pallet7.png'
    ],
];

$csv_file_path = plugin_dir_path( __FILE__ ) . 'tariffe_consegna.csv';

$csv_file = fopen( $csv_file_path, 'r' );
$rates = [];
$provinces = [];
$pallet_types = [];
if ( $csv_file !== false ) {
    $headers = fgetcsv( $csv_file, 0, ';' );
    while ( ( $data = fgetcsv( $csv_file, 0, ';' ) ) !== false ) {
        $provincia = $data[0];
        $provinces[$provincia] = $provincia;
        $data = array_map( function( $value ) {
            return str_replace( ',', '.', $value );
        }, $data );

        $express_rates = array_combine( array_slice( $headers, 2, 7 ), array_slice( $data, 2, 7 ) );
        $standard_rates = array_combine( array_slice( $headers, 9, 7 ), array_slice( $data, 9, 7 ) );

        $rates[$provincia] = [
            'express' => $express_rates,
            'standard' => $standard_rates
        ];

        if ( empty( $pallet_types ) ) {
            $pallet_types = array_keys( $express_rates );
        }
    }
    fclose( $csv_file );
} else {
    echo '<p>Impossibile aprire il file CSV.</p>';
}

$json_data = json_encode( $rates );
echo "<script>var shippingData = $json_data;</script>";
?>

<form id="spedizioneForm" class="container">
    <div class="row">
        <div class="col-md-12 mb-3">
            <h4>Seleziona la Partenza e Destinazione:</h4>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="partenza">Partenza:</label>
                        <select name="partenza" class="form-control js-example-tags" id="partenza" data-calc="true" required>
                            <?php foreach ($provinces as $province) {
                                echo "<option value='$province'>$province</option>";
                            } ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="destinazione">Destinazione:</label>
                        <select name="destinazione" class="form-control js-example-tags" id="destinazione" data-calc="true" required>
                            <?php foreach ($provinces as $province) {
                                echo "<option value='$province'>$province</option>";
                            } ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="form-group pallet-container">
                <h4>Seleziona la tipologia di Pallet:</h4>
                <div id="tipo_pallet_container" class="row">
                    <?php
                    foreach ($pallet_types as $palletType) {
                        $description = isset($pallet_info[$palletType]['description']) ? $pallet_info[$palletType]['description'] : 'Descrizione non disponibile';
                        $image = isset($pallet_info[$palletType]['image']) ? $pallet_info[$palletType]['image'] : plugin_dir_url(__FILE__) . 'img/default.png';
                        echo "
                        <div class='pallet-option col-md-3 mt-2 mb-2 pt-2' data-pallet='$palletType'>
                            <img src='$image' alt='$palletType'>
                            <div class='pallet-info'>
                                <h5>$palletType</h5>
                                <p class='mb-2 text-wrap overflow-hidden'>$description</p>
                                <div class='quantity-container row'>
                                    <button type='button' class='btn btn-outline-secondary decrementQuantity col-md-3'>-</button>
                                    <input type='number' class='form-control pallet-quantity col-md-5 text-center' name='quantita[$palletType]' value='1' min='1' readonly>
                                    <button type='button' class='btn btn-outline-secondary incrementQuantity col-md-3'>+</button>
                                </div>
                            </div>
                        </div>";
                    }
                    ?>
                </div>
                <input type="hidden" name="tipo_pallet" id="tipo_pallet" required>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <h4>Tipo di Spedizione:</h4>
                <div id="tipo_spedizione_container">
                    <div class="row riga-mobile">
                        <div class="col-md-6">
                            <label>
                                <input type="radio" name="tipo_spedizione" value="express" checked required> Express
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label>
                                <input type="radio" name="tipo_spedizione" value="standard" required> Standard
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <h4>Opzioni aggiuntive:</h4>
                <div id="opzioni_aggiuntive">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="opzioni_aggiuntive[]" id="sponda_idraulica" value="sponda_idraulica" disabled>
        <label class="form-check-label" for="sponda_idraulica">Consegna con sponda idraulica</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="opzioni_aggiuntive[]" id="assicurazione" value="assicurazione" disabled>
        <label class="form-check-label" for="assicurazione">Assicurazione</label>
    </div>
    <div id="assicurazione_valori_container" class="mt-2 mb-2" style="display: none;">
        <label for="assicurazione_valori">Inserisci il valore dell'assicurazione:</label>
        <p class="help-block">(Minimo 500 euro)</p>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text">€</span>
            </div>
            <input type="number" class="form-control" id="assicurazione_valori" name="assicurazione_valori" min="500" required>
            <div class="invalid-feedback" id="assicurazione_valore_invalid_feedback"></div>
        </div>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="opzioni_aggiuntive[]" id="contrassegno" value="contrassegno" disabled>
        <label class="form-check-label" for="contrassegno">Contrassegno</label>
    </div>
    <div id="contrassegno_valori_container" class="mt-2" style="display: none;">
        <label for="contrassegno_valori">Inserisci il valore del contrassegno:</label>
        <p class="help-block">(Minimo 50 euro)</p>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text">€</span>
            </div>
            <input type="number" class="form-control" id="contrassegno_valori" name="contrassegno_valori" min="50" required>
            <div class="invalid-feedback" id="contrassegno_valore_invalid_feedback"></div>
        </div>
    </div>
</div>

            </div>
            <div class="col-md-12">
                <button type="button" class="btn btn-primary" id="calculateButton" disabled>Calcola tariffa</button>
            </div>
            <div class="col-md-12">
                <div id="result"></div>
            </div>
            <div class="col-md-12 mt-3">
                <button type="button" class="btn btn-primary" id="nextbutton" style="display: none;">Avanti</button>
            </div>
        </div>
    </div>
</form>

<form class="container">
    <div id="datiPersonali" class="hidden">
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Dati Personali - Mittente</h5>
                        <form>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="mittente[nome]">Nominativo:</label>
                                        <input type="text" class="form-control" name="mittente[nome]" id="mittente[nome]" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="mittente[indirizzo]">Indirizzo:</label>
                                        <input type="text" class="form-control" name="mittente[indirizzo]" id="mittente[indirizzo]" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="mittente[citta]">Città:</label>
                                        <input type="text" class="form-control" name="mittente[citta]" id="mittente[citta]" pattern="[A-Za-z\s]+" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="mittente[cap]">CAP:</label>
                                        <input type="text" class="form-control" name="mittente[cap]" id="mittente[cap]" maxlength="5" pattern="\d{5}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="provincia_mittente">Provincia:</label>
                                        <input type="text" class="form-control" name="provincia_mittente" id="provincia_mittente" disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mittente[telefono]">Cellulare:</label>
                                        <input type="text" class="form-control" name="mittente[telefono]" id="mittente[telefono]" maxlength="10" pattern="\d{9,10}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mittente[email]">Email:</label>
                                        <input type="email" class="form-control" name="mittente[email]" id="mittente[email]" required>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Dati Personali - Destinatario</h5>
                        <form>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="destinatario[nome]">Nominativo:</label>
                                        <input type="text" class="form-control" name="destinatario[nome]" id="destinatario[nome]" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="destinatario[indirizzo]">Indirizzo:</label>
                                        <input type="text" class="form-control" name="destinatario[indirizzo]" id="destinatario[indirizzo]" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="destinatario[citta]">Città:</label>
                                        <input type="text" class="form-control" name="destinatario[citta]" id="destinatario[citta]" pattern="[A-Za-z\s]+" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="destinatario[cap]">CAP:</label>
                                        <input type="text" class="form-control" name="destinatario[cap]" id="destinatario[cap]" maxlength="5" pattern="\d{5}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="provincia_destinatario">Provincia:</label>
                                        <input type="text" class="form-control" name="provincia_destinatario" id="provincia_destinatario" disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="destinatario[telefono]">Cellulare:</label>
                                        <input type="text" class="form-control" name="destinatario[telefono]" id="destinatario[telefono]" maxlength="10" pattern="\d{9,10}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="destinatario[email]">Email:</label>
                                        <input type="email" class="form-control" name="destinatario[email]" id="destinatario[email]" required>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row riga-mobile">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-secondary backButton">Indietro</button>
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="button" class="btn btn-success" id="submitButton">Invia richiesta</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center sticky-top">
                    <div class="card-body">
                        <h5 class="card-title">Riepilogo Spedizione</h5>
                        <div class="row" id="summary">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <strong>Partenza:</strong>
                                    <p class="card-text" id="summaryPartenza"></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <strong>Destinazione:</strong>
                                    <p class="card-text" id="summaryDestinazione"></p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <strong>Tipo di Spedizione:</strong>
                                    <p class="card-text" id="summaryTipoSpedizione"></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <strong>Tipo di Pallet:</strong>
                                    <p class="card-text" id="summaryTipoPallet"></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <strong>Quantità:</strong>
                                    <p class="card-text" id="summaryQuantita"></p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <strong>Opzioni Aggiuntive:</strong>
                                    <p class="card-text" id="summaryOpzioni"></p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <strong>Totale spedizione:</strong>
                                    <p class="card-text" id="summaryCosto"></p>
                                </div>
                            </div>
                            <div class="col-md-12 text-center">
                                <button type="button" class="btn btn-secondary backButton">modifica spedizione</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="requestResult"></div>
</form>


