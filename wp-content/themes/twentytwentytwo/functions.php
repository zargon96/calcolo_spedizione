<?php
/**
 * Twenty Twenty-Two functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_Two
 * @since Twenty Twenty-Two 1.0
 */


if ( ! function_exists( 'twentytwentytwo_support' ) ) :

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * @since Twenty Twenty-Two 1.0
	 *
	 * @return void
	 */
	function twentytwentytwo_support() {

		// Add support for block styles.
		add_theme_support( 'wp-block-styles' );

		// Enqueue editor styles.
		add_editor_style( 'style.css' );
	}

endif;

add_action( 'after_setup_theme', 'twentytwentytwo_support' );

if ( ! function_exists( 'twentytwentytwo_styles' ) ) :

	/**
	 * Enqueue styles.
	 *
	 * @since Twenty Twenty-Two 1.0
	 *
	 * @return void
	 */
	function twentytwentytwo_styles() {
		// Register theme stylesheet.
		$theme_version = wp_get_theme()->get( 'Version' );

		$version_string = is_string( $theme_version ) ? $theme_version : false;
		wp_register_style(
			'twentytwentytwo-style',
			get_template_directory_uri() . '/style.css',
			array(),
			$version_string
		);

		// Enqueue theme stylesheet.
		wp_enqueue_style( 'twentytwentytwo-style' );
	}

endif;

add_action( 'wp_enqueue_scripts', 'twentytwentytwo_styles' );

// Add block patterns
require get_template_directory() . '/inc/block-patterns.php';

/**
 * Enqueue Bootstrap CSS and JS.
 */
function add_bootstrap_to_wp() {
    // Bootstrap CSS
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css', array(), '4.5.2');

    // Custom CSS (if any)
    wp_enqueue_style('custom-style', get_stylesheet_uri());

    // jQuery
    wp_enqueue_script('jquery');

    // Bootstrap JS
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array('jquery'), '4.5.2', true);
}
add_action('wp_enqueue_scripts', 'add_bootstrap_to_wp');

/***************************solo lettura del file csv per vedere se lo leggeva*********************************/
// // Funzione per leggere e visualizzare il CSV come tabella HTML
// function shipping_calculator_shortcode($atts) {
//     ob_start(); // Avvia la memorizzazione dell'output

//     // Default CSV file path (puoi personalizzarlo con attributi dello shortcode)
//     $csv_file_path = '/tariffe_consegna.csv';

//     // Verifica se sono stati forniti attributi personalizzati nello shortcode
//     $atts = shortcode_atts(array(
//         'file' => $csv_file_path // Imposta il percorso del file CSV, se specificato
//     ), $atts);

//     $csv_file = fopen(get_template_directory() . $atts['file'], 'r'); // Apre il file CSV

//     if ($csv_file !== false) {
//         echo '<table border="1">';

//         while (($read_data = fgetcsv($csv_file, 1000, ',')) !== false) {
//             echo '<tr>';
//             foreach ($read_data as $column) {
//                 echo '<td>' . esc_html($column) . '</td>'; // Mostra ogni cella come dato HTML sicuro
//             }
//             echo '</tr>';
//         }

//         echo '</table>';
//         fclose($csv_file); // Chiude il file CSV
//     } else {
//         echo 'Impossibile aprire il file CSV.'; // Messaggio di errore se il file non può essere aperto
//     }

//     return ob_get_clean(); // Restituisci e pulisci l'output memorizzato
// }
// add_shortcode('shipping_calculator', 'shipping_calculator_shortcode');


function shipping_calculator_shortcode() {
    ob_start(); // Inizia a memorizzare l'output

    // Mostra l'alert di successo se il parametro di query `success` è presente
    if (isset($_GET['success']) && $_GET['success'] == 1) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Richiesta inviata con successo.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
              </div>';
    }

    // Definisce il percorso al file CSV
    $csv_file_path = get_template_directory() . '/tariffe_consegna.csv';

    // Apre il file CSV e legge i dati
    $csv_file = fopen($csv_file_path, 'r');
    $rates = [];
    $provinces = [];
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
        }
        fclose($csv_file);
    } else {
        echo '<p>Impossibile aprire il file CSV.</p>';
    }

    $json_data = json_encode($rates);
    echo "<script>var shippingData = $json_data;</script>";

    // Form HTML con menu a tendina popolati dinamicamente
    ?>
    <style>
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group select, .form-group input[type="text"], .form-group input[type="email"], .form-group input[type="date"] {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        #result {
            margin-top: 20px;
            font-weight: bold;
        }
        #requestResult {
            margin-top: 20px;
        }
        .hidden {
            display: none;
        }
    </style>
    <form id="spedizioneForm" class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="partenza">Partenza:</label>
                    <select name="partenza" class="form-control" id="partenza" data-calc="true" required>
                        <?php foreach ($provinces as $province) {
                            echo "<option value='$province'>$province</option>";
                        } ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="destinazione">Destinazione:</label>
                    <select name="destinazione" class="form-control" id="destinazione" data-calc="true" required>
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
                        foreach ($rates as $province => $methods) {
                            foreach ($methods as $pallets) {
                                foreach ($pallets as $palletType) {
                                    echo "<option value='$palletType'>$palletType</option>";
                                }
                                // Una volta popolate le opzioni per un metodo, non è necessario ripeterle
                                break 2;
                            }
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

    <script>
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
    </script>
    <?php
    return ob_get_clean(); // Restituisce e pulisce il buffer di output
}

add_shortcode('shipping_calculator', 'shipping_calculator_shortcode');

// Aggiungi azioni AJAX per calcolare il costo della spedizione
add_action('wp_ajax_calculate_shipping', 'calculate_shipping');
add_action('wp_ajax_nopriv_calculate_shipping', 'calculate_shipping');

function calculate_shipping() {
    // Recupera i dati inviati via AJAX
    $partenza = $_POST['partenza'];
    $destinazione = $_POST['destinazione'];
    $tipoSpedizione = $_POST['tipoSpedizione'];
    $tipoPallet = $_POST['tipoPallet'];
    $opzioniAggiuntive = $_POST['opzioniAggiuntive'];

    // Carica i dati dal file CSV
    $csv_file_path = get_template_directory() . '/tariffe_consegna.csv';
    $csv_file = fopen($csv_file_path, 'r');
    $rates = [];
    if ($csv_file !== false) {
        $headers = fgetcsv($csv_file, 0, ';');
        while (($data = fgetcsv($csv_file, 0, ';')) !== false) {
            $provincia = $data[0];
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
        }
        fclose($csv_file);
    }

    // Calcola il costo della spedizione
    $tariffaBase = floatval($rates[$destinazione][$tipoSpedizione][$tipoPallet]) ?? 0;
    $costoSpedizione = $tariffaBase;
    
    if ($opzioniAggiuntive === 'sponda_idraulica') {
        $costoSpedizione += 50; // Aggiungi costo fisso
        $costoSpedizione *= 1.03; // Aggiungi il 3% del costo totale
    }
    if ($opzioniAggiuntive === 'assicurazione') {
        $costoSpedizione += 20; // Aggiungi costo fisso
        $costoSpedizione *= 1.02; // Aggiungi il 2% del costo totale
    }
    if ($opzioniAggiuntive === 'consegna_rapida') {
        $costoSpedizione += 30; // Aggiungi costo fisso
        $costoSpedizione *= 1.05; // Aggiungi il 5% del costo totale
    }
    if ($partenza !== 'FI' && $partenza !== 'PO') {
        $costoSpedizione *= 1.10; // Aggiungi il 10% per partenze non da FI o PO
    }

    echo number_format($costoSpedizione, 2);
    wp_die();
}

// Aggiungi azioni AJAX per inviare la richiesta
add_action('wp_ajax_submit_request', 'submit_request');
add_action('wp_ajax_nopriv_submit_request', 'submit_request');

function submit_request() {
    $nome = sanitize_text_field($_POST['nome']);
    $cognome = sanitize_text_field($_POST['cognome']);
    $indirizzo = sanitize_text_field($_POST['indirizzo']);
    $telefono = sanitize_text_field($_POST['telefono']);
    $email = sanitize_email($_POST['email']);
    $dataNascita = sanitize_text_field($_POST['dataNascita']);
    $partenza = sanitize_text_field($_POST['partenza']);
    $destinazione = sanitize_text_field($_POST['destinazione']);
    $tipoSpedizione = sanitize_text_field($_POST['tipoSpedizione']);
    $tipoPallet = sanitize_text_field($_POST['tipoPallet']);
    $opzioniAggiuntive = sanitize_text_field($_POST['opzioniAggiuntive']);
    $costoSpedizione = sanitize_text_field($_POST['costoSpedizione']);

    // Invia la email (puoi usare una libreria come PHPMailer per questo)
    $to = $email;
    $subject = 'Dettagli della Richiesta di Spedizione';
    $body = "
        Nome: $nome\n
        Cognome: $cognome\n
        Indirizzo: $indirizzo\n
        Cellulare: $telefono\n
        Email: $email\n
        Data di Nascita: $dataNascita\n
        Partenza: $partenza\n
        Destinazione: $destinazione\n
        Tipo di Spedizione: $tipoSpedizione\n
        Tipo di Pallet: $tipoPallet\n
        Opzioni aggiuntive: $opzioniAggiuntive\n
        Costo di Spedizione: €$costoSpedizione\n
    ";
    $headers = ['Content-Type: text/plain; charset=UTF-8'];
    wp_mail($to, $subject, $body, $headers);

    echo 'Richiesta inviata con successo';
    wp_die();
}











