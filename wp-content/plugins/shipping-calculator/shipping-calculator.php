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
        add_shortcode('shipping_calculator', [$this, 'render_shortcode']);
        add_action('wp_ajax_calculate_shipping', [$this, 'calculate_shipping']);
        add_action('wp_ajax_nopriv_calculate_shipping', [$this, 'calculate_shipping']);
        add_action('wp_ajax_submit_request', [$this, 'submit_request']);
        add_action('wp_ajax_nopriv_submit_request', [$this, 'submit_request']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function render_shortcode() {
        ob_start();
        echo '<div id="alertContainer">';
        if ('success' == 1) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    Richiesta inviata con successo.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                  </div>';
        }
        include plugin_dir_path(__FILE__) . 'shipping-calculator-form.php';
        return ob_get_clean();
    }

    public function calculate_shipping() {
        $partenza = sanitize_text_field($_POST['partenza']);
        $destinazione = sanitize_text_field($_POST['destinazione']);
        $tipoSpedizione = sanitize_text_field($_POST['tipoSpedizione']);
        $tipoPallet = sanitize_text_field($_POST['tipoPallet']);
        $quantita = intval($_POST['quantita']);
        $opzioniAggiuntive = $_POST['opzioniAggiuntive'] ?? [];
        $assicurazioneValore = intval($_POST['assicurazioneValore']);
        $contrassegnoValore = intval($_POST['contrassegnoValore']);
    
        $csv_file_path = plugin_dir_path(__FILE__) . 'tariffe_consegna.csv';
        $csv_file = fopen($csv_file_path, 'r');
        $rates = [];
        if ($csv_file !== false) {
            $headers = fgetcsv($csv_file, 0, ';');
            while (($data = fgetcsv($csv_file, 0, ';')) !== false) {
                $provincia = $data[0];
                $data = array_map(function ($value) {
                    return str_replace(',', '.', $value);
                }, $data);
    
                $express_rates = array_combine(array_slice($headers, 2, 7), array_slice($data, 2, 7));
                $standard_rates = array_combine(array_slice($headers, 9, 7), array_slice($data, 9, 7));
    
                $rates[$provincia] = [
                    'express' => $express_rates,
                    'standard' => $standard_rates
                ];
            }
            fclose($csv_file);
        }
    
        $tariffaBase = floatval($rates[$destinazione][$tipoSpedizione][$tipoPallet]) ?? 0;
        $costoSpedizione = $tariffaBase * $quantita;
    
        $opzioni = [
            'sponda_idraulica' => ['costo' => 50, 'moltiplicatore' => 1.03, 'moltiplicatore_valore' => false],
            'assicurazione' => ['moltiplicatore' => 1.02, 'moltiplicatore_valore' => true],
            'contrassegno' => ['moltiplicatore' => 1.02, 'moltiplicatore_valore' => true],
        ];
    
        $dettagliOpzioni = [];
    
        foreach ($opzioniAggiuntive as $opzione) {
            if (isset($opzioni[$opzione])) {
                if ($opzioni[$opzione]['moltiplicatore_valore']) {
                    if ($opzione === 'assicurazione' && $assicurazioneValore > 0) {
                        $costoAggiuntivo = $assicurazioneValore * ($opzioni[$opzione]['moltiplicatore'] - 1);
                        $dettagliOpzioni['assicurazione'] = [
                            'valore' => $assicurazioneValore,
                            'moltiplicatore' => $opzioni[$opzione]['moltiplicatore'],
                            'costo' => $costoAggiuntivo
                        ];
                    } 
                    if ($opzione === 'contrassegno' && $contrassegnoValore > 0) {
                        $costoAggiuntivo = $contrassegnoValore * ($opzioni[$opzione]['moltiplicatore'] - 1);
                        $dettagliOpzioni['contrassegno'] = [
                            'valore' => $contrassegnoValore,
                            'moltiplicatore' => $opzioni[$opzione]['moltiplicatore'],
                            'costo' => $costoAggiuntivo
                        ];
                    }
                } else {
                    $costoAggiuntivo = $opzioni[$opzione]['costo'];
                    $costoSpedizione += $costoAggiuntivo;
                    $costoSpedizione *= $opzioni[$opzione]['moltiplicatore'];
                    $dettagliOpzioni[$opzione] = [
                        'costo' => $costoAggiuntivo,
                        'moltiplicatore' => $opzioni[$opzione]['moltiplicatore']
                    ];
                }
            }
        }
    
        if ($partenza !== 'FI' && $partenza !== 'PO') {
            $costoSpedizione *= 1.10;
        }
    
        echo json_encode([
            'costoSpedizione' => number_format($costoSpedizione, 2),
            'dettagliOpzioni' => $dettagliOpzioni,
            'baseCostoSpedizione' => $tariffaBase
        ]);
        wp_die();
        wp_die();
    }
    
    public function submit_request() {
        $errors = [];
    
        $mittente = $_POST['mittente'];
        $destinatario = $_POST['destinatario'];
        $partenza = sanitize_text_field($_POST['partenza']);
        $destinazione = sanitize_text_field($_POST['destinazione']);
        $tipoSpedizione = sanitize_text_field($_POST['tipoSpedizione']);
        $tipoPallet = sanitize_text_field($_POST['tipoPallet']);
        $quantita = intval($_POST['quantita']);
        $opzioniAggiuntive = $_POST['opzioniAggiuntive'] ?? [];
        $costoSpedizione = sanitize_text_field($_POST['costoSpedizione']);
        $assicurazioneValore = intval($_POST['assicurazioneValore']);
        $contrassegnoValore = intval($_POST['contrassegnoValore']);
    
        $required_fields = [
            'mittente' => [
                'nome' => ['name' => 'Nome Mittente', 'required' => true],
                'indirizzo' => ['name' => 'Indirizzo Mittente', 'required' => true],
                'citta' => ['name' => 'Città Mittente', 'required' => true],
                'cap' => ['name' => 'CAP Mittente', 'required' => true],
                'telefono' => ['name' => 'Telefono Mittente', 'required' => true],
                'email' => ['name' => 'Email Mittente', 'required' => true],
            ],
            'destinatario' => [
                'nome' => ['name' => 'Nome Destinatario', 'required' => true],
                'indirizzo' => ['name' => 'Indirizzo Destinatario', 'required' => true],
                'citta' => ['name' => 'Città Destinatario', 'required' => true],
                'cap' => ['name' => 'CAP Destinatario', 'required' => true],
                'telefono' => ['name' => 'Telefono Destinatario', 'required' => true],
                'email' => ['name' => 'Email Destinatario', 'required' => true],
            ],
        ];
    
        foreach ($required_fields as $type => $fields) {
            foreach ($fields as $field_key => $field) {
                if ($field['required'] && empty($$type[$field_key])) {
                    $errors[] = "<br/> &bull; Il campo {$field['name']} è obbligatorio.";
                }
            }
        }
    
        foreach (['mittente', 'destinatario'] as $type) {
            if (!empty($$type['email']) && !filter_var($$type['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "L'email del {$type} non è valida.";
            }
        }
    
        if (!empty($errors)) {
            wp_send_json_error(['errors' => $errors]);
        } else {
            $data = [
                'mittente' => array_map('sanitize_text_field', $mittente),
                'destinatario' => array_map('sanitize_text_field', $destinatario),
                'partenza' => sanitize_text_field($partenza),
                'destinazione' => sanitize_text_field($destinazione),
                'tipoSpedizione' => sanitize_text_field($tipoSpedizione),
                'tipoPallet' => sanitize_text_field($tipoPallet),
                'quantita' => intval($quantita),
                'opzioniAggiuntive' => $opzioniAggiuntive,
                'assicurazioneValore' => $assicurazioneValore,
                'contrassegnoValore' => $contrassegnoValore,
                'costoSpedizione' => sanitize_text_field($costoSpedizione),
            ];
    
            $opzioniAggiuntiveLabels = [
                'sponda_idraulica' => 'Consegna con sponda idraulica',
                'assicurazione' => 'Assicurazione',
                'contrassegno' => 'Contrassegno'
            ];
    
            $opzioniAggiuntiveReadable = array_map(function ($opzione) use ($opzioniAggiuntiveLabels) {
                return $opzioniAggiuntiveLabels[$opzione] ?? $opzione;
            }, $data['opzioniAggiuntive']);
    
            $to = [$data['mittente']['email'], $data['destinatario']['email']];
            $subject = 'Dettagli della Richiesta di Spedizione';
    
            $fields_to_include = [
                'Nominativo' => 'nome',
                'Indirizzo' => 'indirizzo',
                'Città' => 'citta',
                'CAP' => 'cap',
                'Cellulare' => 'telefono',
                'Email' => 'email'
            ];
    
            $body = "Mittente:\n";
            foreach ($fields_to_include as $label => $field) {
                $body .= "$label Mittente: {$data['mittente'][$field]}\n";
            }
    
            $body .= "\nDestinatario:\n";
            foreach ($fields_to_include as $label => $field) {
                $body .= "$label Destinatario: {$data['destinatario'][$field]}\n";
            }
    
            $body .= "\nRiepilogo:\n";
            $body .= "Partenza: {$data['partenza']}\n";
            $body .= "Destinazione: {$data['destinazione']}\n";
            $body .= "Tipo di Spedizione: {$data['tipoSpedizione']}\n";
            $body .= "Tipo di Pallet: {$data['tipoPallet']}\n";
            $body .= "Quantità: {$data['quantita']}\n";
    
            if (!empty($opzioniAggiuntiveReadable)) {
                $body .= "Opzioni aggiuntive: " . implode(', ', $opzioniAggiuntiveReadable) . "\n";
            } else {
                $body .= "Opzioni aggiuntive: Nessuna opzione aggiuntiva\n";
            }
    
            if (in_array('assicurazione', $data['opzioniAggiuntive']) && $data['assicurazioneValore'] > 0) {                
                $body .= "Valore Assicurazione: €{$data['assicurazioneValore']} + 2% \n";
            }
    
            if (in_array('contrassegno', $data['opzioniAggiuntive']) && $data['contrassegnoValore'] > 0) {
                $body .= "Valore Contrassegno: €{$data['contrassegnoValore']} + 2% \n";
            }
    
            $body .= "Totale costo spedizione: €{$data['costoSpedizione']}\n";
    
            $headers = ['Content-Type: text/plain; charset=UTF-8'];
            wp_mail($to, $subject, $body, $headers);
    
            wp_send_json_success();
            wp_die();
        }
    }
    
    
    public function enqueue_scripts() {
        wp_enqueue_style('shipping-calculator-css', plugin_dir_url(__FILE__) . 'shipping-calculator.css');
        wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css');
        wp_enqueue_script('shipping-calculator-js', plugin_dir_url(__FILE__) . 'shipping-calculator.js', ['jquery'], null, true);
        wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js', ['jquery'], null, true);
    }
}

new Shipping_Calculator_Plugin();

