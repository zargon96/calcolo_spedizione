<?php
/*
Plugin Name: Shipping Calculator
Description: A plugin to calculate shipping rates.
Version: 1.0.0
Author: Revool
*/

if (!defined('ABSPATH')) {
    exit;
}

class Shipping_Calculator_Plugin {

    public function __construct() {
        // shortcode
        add_shortcode( 'shipping_calculator', [ $this, 'render_shortcode' ] );

        // AJAX
        add_action( 'wp_ajax_calculate_shipping', [ $this, 'calculate_shipping' ] );
        add_action( 'wp_ajax_nopriv_calculate_shipping', [ $this, 'calculate_shipping' ] );
        add_action( 'wp_ajax_submit_request', [ $this, 'submit_request' ] );
        add_action( 'wp_ajax_nopriv_submit_request', [ $this, 'submit_request' ] );

        // scripts e stili
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    public function render_shortcode() {
        ob_start();
        echo '<div id="alertContainer">';
        if ( isset( $_GET['success'] ) && $_GET['success'] == 1 ) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    Richiesta inviata con successo.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                  </div>';
        }

        include plugin_dir_path( __FILE__ ) . 'shipping-calculator-form.php';

        return ob_get_clean();
    }

    public function calculate_shipping() {
        $partenza = sanitize_text_field( $_POST['partenza'] );
        $destinazione = sanitize_text_field( $_POST['destinazione'] );
        $tipoSpedizione = sanitize_text_field( $_POST['tipoSpedizione'] );
        $tipoPallet = sanitize_text_field( $_POST['tipoPallet'] );
        $opzioniAggiuntive = sanitize_text_field( $_POST['opzioniAggiuntive'] );

        $csv_file_path = plugin_dir_path( __FILE__ ) . 'tariffe_consegna.csv';
        $csv_file = fopen( $csv_file_path, 'r' );
        $rates = [];
        if ( $csv_file !== false ) {
            $headers = fgetcsv( $csv_file, 0, ';' );
            while ( ( $data = fgetcsv( $csv_file, 0, ';' ) ) !== false ) {
                $provincia = $data[0];
                $data = array_map( function( $value ) {
                    return str_replace( ',', '.', $value );
                }, $data );

                $express_rates = array_combine( array_slice( $headers, 2, 7 ), array_slice( $data, 2, 7 ) );
                $standard_rates = array_combine( array_slice( $headers, 9, 7 ), array_slice( $data, 9, 7 ) );

                $rates[$provincia] = [
                    'express' => $express_rates,
                    'standard' => $standard_rates
                ];
            }
            fclose( $csv_file );
        }

        $tariffaBase = floatval( $rates[$destinazione][$tipoSpedizione][$tipoPallet] ) ?? 0;
        $costoSpedizione = $tariffaBase;

        if ( $opzioniAggiuntive === 'sponda_idraulica' ) {
            $costoSpedizione += 50;
            $costoSpedizione *= 1.03;
        }
        if ( $opzioniAggiuntive === 'assicurazione' ) {
            $costoSpedizione += 20;
            $costoSpedizione *= 1.02;
        }
        if ( $opzioniAggiuntive === 'consegna_rapida' ) {
            $costoSpedizione += 30;
            $costoSpedizione *= 1.05;
        }
        if ( $partenza !== 'FI' && $partenza !== 'PO' ) {
            $costoSpedizione *= 1.10;
        }

        echo number_format( $costoSpedizione, 2 );
        wp_die();
    }

    public function submit_request() {
        $errors = [];

        // Check required fields
        $required_fields = [
            'nome_mittente', 'indirizzo_mittente', 'citta_mittente', 'cap_mittente', 'telefono_mittente', 'email_mittente',
            'nome_destinatario', 'indirizzo_destinatario', 'citta_destinatario', 'cap_destinatario', 'telefono_destinatario', 'email_destinatario',
            'partenza', 'destinazione', 'tipoSpedizione', 'tipoPallet', 'costoSpedizione'
        ];

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "<br/> &bull; Il campo {$field} è obbligatorio.";
            }
        }

        // Validate email fields
        if (!empty($_POST['email_mittente']) && !filter_var($_POST['email_mittente'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'email del mittente non è valida.';
        }

        if (!empty($_POST['email_destinatario']) && !filter_var($_POST['email_destinatario'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'email del destinatario non è valida.';
        }

        // If there are errors, return them
        if (!empty($errors)) {
            wp_send_json_error(['errors' => $errors]);
        } else {
            // Proceed with sending the email
            $nome_mittente = sanitize_text_field( $_POST['nome_mittente'] );
            $indirizzo_mittente = sanitize_text_field( $_POST['indirizzo_mittente'] );
            $citta_mittente = sanitize_text_field( $_POST['citta_mittente'] );
            $cap_mittente = sanitize_text_field( $_POST['cap_mittente'] );
            $telefono_mittente = sanitize_text_field( $_POST['telefono_mittente'] );
            $email_mittente = sanitize_email( $_POST['email_mittente'] );
            
            $nome_destinatario = sanitize_text_field( $_POST['nome_destinatario'] );
            $indirizzo_destinatario = sanitize_text_field( $_POST['indirizzo_destinatario'] );
            $citta_destinatario = sanitize_text_field( $_POST['citta_destinatario'] );
            $cap_destinatario = sanitize_text_field( $_POST['cap_destinatario'] );
            $telefono_destinatario = sanitize_text_field( $_POST['telefono_destinatario'] );
            $email_destinatario = sanitize_email( $_POST['email_destinatario'] );
            
            $partenza = sanitize_text_field( $_POST['partenza'] );
            $destinazione = sanitize_text_field( $_POST['destinazione'] );
            $tipoSpedizione = sanitize_text_field( $_POST['tipoSpedizione'] );
            $tipoPallet = sanitize_text_field( $_POST['tipoPallet'] );
            $opzioniAggiuntive = sanitize_text_field( $_POST['opzioniAggiuntive'] );
            $costoSpedizione = sanitize_text_field( $_POST['costoSpedizione'] );

            $to = $email_mittente;
            $subject = 'Dettagli della Richiesta di Spedizione';
            $body = "
                Mittente:\n
                Nominativo Mittente: $nome_mittente\n
                Indirizzo Mittente: $indirizzo_mittente\n
                Città Mittente: $citta_mittente\n
                CAP Mittente: $cap_mittente\n
                Cellulare Mittente: $telefono_mittente\n
                Email Mittente: $email_mittente\n\n
                Destinatario:\n
                Nominativo Destinatario: $nome_destinatario\n
                Indirizzo Destinatario: $indirizzo_destinatario\n
                Città Destinatario: $citta_destinatario\n
                CAP Destinatario: $cap_destinatario\n
                Cellulare Destinatario: $telefono_destinatario\n
                Email Destinatario: $email_destinatario\n\n
                Riepilogo:\n
                Partenza: $partenza\n
                Destinazione: $destinazione\n
                Tipo di Spedizione: $tipoSpedizione\n
                Tipo di Pallet: $tipoPallet\n
                Opzioni aggiuntive: $opzioniAggiuntive\n
                Costo di Spedizione: €$costoSpedizione\n
            ";
            $headers = [ 'Content-Type: text/plain; charset=UTF-8' ];
            wp_mail( $to, $subject, $body, $headers );

            // Risposta JSON di successo
            wp_send_json_success();
            
            wp_die();
        }
    }
    public function enqueue_scripts() {
        wp_enqueue_style( 'shipping-calculator-css', plugin_dir_url( __FILE__ ) . 'shipping-calculator.css' );
        wp_enqueue_style( 'select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css' );
        wp_enqueue_script( 'shipping-calculator-js', plugin_dir_url( __FILE__ ) . 'shipping-calculator.js', [ 'jquery' ], null, true );
        wp_enqueue_script( 'select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js', [ 'jquery' ], null, true );
    }
}

new Shipping_Calculator_Plugin();






