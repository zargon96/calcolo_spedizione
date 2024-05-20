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
    include plugin_dir_path(__FILE__) . 'shipping-calculator-form.php';

    return ob_get_clean(); // Restituisce e pulisce il buffer di output
}

add_shortcode('shipping_calculator', 'shipping_calculator_shortcode');

// AJAX per calcolare il costo della spedizione
add_action('wp_ajax_calculate_shipping', 'calculate_shipping');
add_action('wp_ajax_nopriv_calculate_shipping', 'calculate_shipping');

function calculate_shipping() {
    // Recupera i dati inviati via AJAX
    $partenza = sanitize_text_field($_POST['partenza']);
    $destinazione = sanitize_text_field($_POST['destinazione']);
    $tipoSpedizione = sanitize_text_field($_POST['tipoSpedizione']);
    $tipoPallet = sanitize_text_field($_POST['tipoPallet']);
    $opzioniAggiuntive = sanitize_text_field($_POST['opzioniAggiuntive']);

    // Carica i dati dal file CSV
    $csv_file_path = plugin_dir_path(__FILE__) . 'tariffe_consegna.csv';
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

// AJAX per inviare la richiesta
add_action('wp_ajax_submit_request', 'submit_request');
add_action('wp_ajax_nopriv_submit_request', 'submit_request');

function submit_request() {
    $nome_mittente = sanitize_text_field($_POST['nome_mittente']);
    $indirizzo_mittente = sanitize_text_field($_POST['indirizzo_mittente']);
    $citta_mittente = sanitize_text_field($_POST['citta_mittente']);
    $cap_mittente = sanitize_text_field($_POST['cap_mittente']);
    $telefono_mittente = sanitize_text_field($_POST['telefono_mittente']);
    $email_mittente = sanitize_email($_POST['email_mittente']);
    
    $nome_destinatario = sanitize_text_field($_POST['nome_destinatario']);
    $indirizzo_destinatario = sanitize_text_field($_POST['indirizzo_destinatario']);
    $citta_destinatario = sanitize_text_field($_POST['citta_destinatario']);
    $cap_destinatario = sanitize_text_field($_POST['cap_destinatario']);
    $telefono_destinatario = sanitize_text_field($_POST['telefono_destinatario']);
    $email_destinatario = sanitize_email($_POST['email_destinatario']);
    
    $partenza = sanitize_text_field($_POST['partenza']);
    $destinazione = sanitize_text_field($_POST['destinazione']);
    $tipoSpedizione = sanitize_text_field($_POST['tipoSpedizione']);
    $tipoPallet = sanitize_text_field($_POST['tipoPallet']);
    $opzioniAggiuntive = sanitize_text_field($_POST['opzioniAggiuntive']);
    $costoSpedizione = sanitize_text_field($_POST['costoSpedizione']);

    // Invia l'email
    $to = $email_mittente; // Puoi cambiare questo indirizzo email con quello del destinatario se preferisci
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
    $headers = ['Content-Type: text/plain; charset=UTF-8'];
    wp_mail($to, $subject, $body, $headers);
    
    wp_die();
}

function shipping_calculator_enqueue_scripts() {
    wp_enqueue_style('shipping-calculator-css', plugin_dir_url(__FILE__) . 'shipping-calculator.css');
    wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css');
    wp_enqueue_script('shipping-calculator-js', plugin_dir_url(__FILE__) . 'shipping-calculator.js', ['jquery'], null, true);
    wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js', ['jquery'], null, true);
}
add_action('wp_enqueue_scripts', 'shipping_calculator_enqueue_scripts');




