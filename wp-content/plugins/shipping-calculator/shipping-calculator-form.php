<?php
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
        <div class="col-md-6">
            <div class="form-group">
                <label for="partenza">Partenza:</label>
                <select name="partenza" class="form-control js-example-tags" id="partenza" data-calc="true" required>
                    <?php foreach ( $provinces as $province ) {
                        echo "<option value='$province'>$province</option>";
                    } ?>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="destinazione">Destinazione:</label>
                <select name="destinazione" class="form-control js-example-tags" id="destinazione" data-calc="true" required>
                    <?php foreach ( $provinces as $province ) {
                        echo "<option value='$province'>$province</option>";
                    } ?>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="tipo_spedizione">Tipo di Spedizione:</label>
                <select name="tipo_spedizione" class="form-control" id="tipo_spedizione" data-calc="true" required>
                    <option value="express">Express</option>
                    <option value="standard">Standard</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="tipo_pallet">Tipo di Pallet:</label>
                <select name="tipo_pallet" class="form-control" id="tipo_pallet" data-calc="true" required>
                    <?php
                    foreach ( $pallet_types as $palletType ) {
                        echo "<option value='$palletType'>$palletType</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="opzioni_aggiuntive">Opzioni aggiuntive:</label>
                <select name="opzioni_aggiuntive" class="form-control" id="opzioni_aggiuntive">
                    <option value="none">Nessuna</option>
                    <option value="sponda_idraulica">Consegna con sponda idraulica</option>
                    <option value="assicurazione">Assicurazione</option>
                    <option value="consegna_rapida">Consegna rapida</option>
                </select>
            </div>
        </div>
        <div class="col-md-12 text-right">
            <button type="button" class="btn btn-primary" id="calculateButton">Calcola tariffa</button>
        </div>

        <div class="col-md-12">
            <div id="result"></div>
        </div>
        <div class="col-md-12 mt-3 text-right">
            <button type="button" class="btn btn-primary" id="nextbutton" style="display: none;">avanti</button>
        </div>
    </div>
</form>

<form class="container">
    <div id="datiPersonali" class="hidden">
        <div class="row">
            <div class="col-md-8">
                <!-- Dati Personali Mittente -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Dati Personali - Mittente</h5>
                        <form>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="nome_mittente">Nominativo:</label>
                                        <input type="text" class="form-control" name="nome_mittente" id="nome_mittente" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="indirizzo_mittente">Indirizzo:</label>
                                        <input type="text" class="form-control" name="indirizzo_mittente" id="indirizzo_mittente" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="citta_mittente">Città:</label>
                                        <input type="text" class="form-control" name="citta_mittente" id="citta_mittente" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="cap_mittente">CAP:</label>
                                        <input type="text" class="form-control" name="cap_mittente" id="cap_mittente" maxlength="5" pattern="\d{5}" required>
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
                                        <label for="telefono_mittente">Cellulare:</label>
                                        <input type="text" class="form-control" name="telefono_mittente" id="telefono_mittente" maxlength="10" pattern="\d{10}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email_mittente">Email:</label>
                                        <input type="email" class="form-control" name="email_mittente" id="email_mittente" required>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Dati Personali Destinatario -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Dati Personali - Destinatario</h5>
                        <form>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="nome_destinatario">Nominativo:</label>
                                        <input type="text" class="form-control" name="nome_destinatario" id="nome_destinatario" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="indirizzo_destinatario">Indirizzo:</label>
                                        <input type="text" class="form-control" name="indirizzo_destinatario" id="indirizzo_destinatario" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="citta_destinatario">Città:</label>
                                        <input type="text" class="form-control" name="citta_destinatario" id="citta_destinatario" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="cap_destinatario">CAP:</label>
                                        <input type="text" class="form-control" name="cap_destinatario" id="cap_destinatario" maxlength="5" pattern="\d{5}" required>
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
                                        <label for="telefono_destinatario">Cellulare:</label>
                                        <input type="text" class="form-control" name="telefono_destinatario" id="telefono_destinatario" maxlength="10" pattern="\d{10}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email_destinatario">Email:</label>
                                        <input type="email" class="form-control" name="email_destinatario" id="email_destinatario" required>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="mt-3 text-right">
                    <button type="button" class="btn btn-success" id="submitButton">Invia richiesta</button>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Riepilogo Spedizione -->
                <div class="card text-center sticky-top">
                    <div class="card-body">
                        <h5 class="card-title">Riepilogo Spedizione</h5>
                        <div class="row">
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
                            <div class="col-md-12">
                                <div class="form-group">
                                    <strong>Tipo di Pallet:</strong>
                                    <p class="card-text" id="summaryTipoPallet"></p>
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
                                    <strong>Costo Totale:</strong>
                                    <p class="card-text" id="summaryCosto"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="requestResult"></div>
</form>
