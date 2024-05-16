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

/***************************solo lettura del file csv per test*********************************/
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

    // Includi il file PHP del modulo
    include get_template_directory() . '/shipping-calculator-form.php';

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
    $citta = sanitize_text_field($_POST['citta']);
    $cap = sanitize_text_field($_POST['cap']);
    $telefono = sanitize_text_field($_POST['telefono']);
    $email = sanitize_email($_POST['email']);
    $dataNascita = sanitize_text_field($_POST['dataNascita']);
    $partenza = sanitize_text_field($_POST['partenza']);
    $destinazione = sanitize_text_field($_POST['destinazione']);
    $tipoSpedizione = sanitize_text_field($_POST['tipoSpedizione']);
    $tipoPallet = sanitize_text_field($_POST['tipoPallet']);
    $opzioniAggiuntive = sanitize_text_field($_POST['opzioniAggiuntive']);
    $costoSpedizione = sanitize_text_field($_POST['costoSpedizione']);

    // Invia l' email
    $to = $email;
    $subject = 'Dettagli della Richiesta di Spedizione';
    $body = "
        Nome: $nome\n
        Cognome: $cognome\n
        Indirizzo: $indirizzo\n
        citta: $citta\n
        cap: $cap\n
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
    
    wp_die();
}

function shipping_calculator_enqueue_scripts() {
    wp_enqueue_style('shipping-calculator-css', get_template_directory_uri() . '/shipping-calculator.css');
    wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css');
    wp_enqueue_script('shipping-calculator-js', get_template_directory_uri() . '/shipping-calculator.js', [], false, true);
    wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js', ['jquery'], false, true);
}
add_action('wp_enqueue_scripts', 'shipping_calculator_enqueue_scripts');












