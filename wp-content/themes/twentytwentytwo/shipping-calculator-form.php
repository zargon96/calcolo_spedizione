<?php
// Definisce il percorso al file CSV
$csv_file_path = get_template_directory() . '/tariffe_consegna.csv';

// Apre il file CSV e legge i dati
$csv_file = fopen($csv_file_path, 'r');
$rates = [];
$provinces = [];
$pallet_types = [];
if ($csv_file !== false) {
    $headers = fgetcsv($csv_file, 0, ';');
    while (($data = fgetcsv($csv_file, 0, ';')) !== false) {
        $provincia = $data[0];
        $provinces[$provincia] = $provincia;
        $data = array_map(function($value) {
            return str_replace(',', '.', $value); // Normalizza i valori numerici
        }, $data);
        // Prepara i sottovettori per express e standard
        $express_rates = array_combine(array_slice($headers, 2, 7), array_slice($data, 2, 7));
        $standard_rates = array_combine(array_slice($headers, 9, 7), array_slice($data, 9, 7));

        // Memorizza le tariffe in un array multidimensionale sotto la chiave della provincia
        $rates[$provincia] = [
            'express' => $express_rates,
            'standard' => $standard_rates
        ];

        // Se pallet_types è vuoto, riempilo con i tipi di pallet del primo metodo di spedizione trovato
        if (empty($pallet_types)) {
            $pallet_types = array_keys($express_rates);
        }
    }
    fclose($csv_file);
} else {
    echo '<p>Impossibile aprire il file CSV.</p>';
}

$json_data = json_encode($rates);
echo "<script>var shippingData = $json_data;</script>";
?>

<form id="spedizioneForm" class="container">
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
                    // Popola le opzioni del tipo di pallet in base ai dati caricati
                    foreach ($pallet_types as $palletType) {
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
        <div class="col-md-12">
            <button type="button" class="btn btn-primary" id="calculateButton">Calcola tariffa</button>
        </div>
    </div>
    
    <div class="col-md-12">
        <div id="result"></div>
    </div>
    <div id="datiPersonali" class="hidden">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="nome">Nome:</label>
                    <input type="text" class="form-control" name="nome" id="nome" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="cognome">Cognome:</label>
                    <input type="text" class="form-control" name="cognome" id="cognome" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="indirizzo">Indirizzo:</label>
                    <input type="text" class="form-control" name="indirizzo" id="indirizzo" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="telefono">Cellulare:</label>
                    <input type="text" class="form-control" name="telefono" id="telefono" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" name="email" id="email" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="data_nascita">Data di nascita:</label>
                    <input type="date" class="form-control" name="data_nascita" id="data_nascita" required>
                </div>
            </div>
            <div class="col-md-6">
                <button type="button" class="btn btn-success" id="submitButton">Invia richiesta</button>
            </div>
        </div>
    </div>
    <div id="requestResult"></div>
</form>

