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



/*******************************CODICE CON IL PELLET SCRITTO NEL CODICE A "MANO"********************************************/
// function shipping_calculator_shortcode() {
//     ob_start(); // Start output buffering

//     // Define the path to the CSV file
//     $csv_file_path = get_template_directory() . '/tariffe_consegna.csv';

//     // Attempt to open the CSV file
//     $csv_file = fopen($csv_file_path, 'r');
//     $csv = array();
//     if ($csv_file !== false) {
//         // Read the CSV file line by line and parse it
//         while (($data = fgetcsv($csv_file, 0, ';')) !== false) {
//             $csv[] = $data;
//         }
//         fclose($csv_file);

//         // Use the first row to define array keys and remove it
//         $headers = array_shift($csv);
//         $csv = array_map(function($row) use ($headers) {
//             return array_combine($headers, $row);
//         }, $csv);
//     } else {
//         echo '<p>Unable to open the CSV file.</p>';
//     }

//     // Convert CSV data to JSON for JavaScript
//     $json_data = json_encode($csv);

//     // HTML form part with dynamically populated dropdowns
//     echo '<form id="spedizioneForm">
//             <label for="tipo_pallet">Tipo di Pallet:</label>
//             <select name="tipo_pallet" id="tipo_pallet">
//                 <option value="FP">FP</option>
//                 <option value="LP">LP</option>
//             </select>
//             <label for="destinazione">Destinazione:</label>
//             <select name="destinazione" id="destinazione">';

//     // Populate destinations from CSV data
//     foreach ($csv as $row) {
//         echo '<option value="' . htmlspecialchars($row['Provincia']) . '">' . htmlspecialchars($row['Provincia']) . '</option>';
//     }

//     echo '</select>
//             <input type="checkbox" name="sponda_idraulica" id="sponda_idraulica">
//             <label for="sponda_idraulica">Sponda Idraulica</label>
//             <button type="submit">Calcola</button>
//           </form>
//           <div id="result"></div>';

//     // Inline JavaScript
//     echo "<script>
//             document.addEventListener('DOMContentLoaded', function() {
//                 const form = document.getElementById('spedizioneForm');
//                 const data = $json_data;

//                 form.addEventListener('submit', function(e) {
//                     e.preventDefault();

//                     let tipoPallet = document.getElementById('tipo_pallet').value;
//                     let destinazione = document.getElementById('destinazione').value;
//                     let spondaIdraulica = document.getElementById('sponda_idraulica').checked;

//                     let tariffaBase = 0;
//                     data.forEach(function(row) {
//                         if (row['Provincia'] === destinazione && row[tipoPallet]) {
//                             tariffaBase = parseFloat(row[tipoPallet]);
//                         }
//                     });

//                     let costoSpedizione = tariffaBase;
//                     if (spondaIdraulica) {
//                         costoSpedizione += 50; // Fixed cost
//                         costoSpedizione += costoSpedizione * 0.03; // 3% extra
//                     }

//                     document.getElementById('result').innerText = 'Il costo di spedizione è: € ' + costoSpedizione.toFixed(2);
//                 });
//             });
//           </script>";

//     return ob_get_clean(); // Return and clean the output buffer
// }

// add_shortcode('shipping_calculator', 'shipping_calculator_shortcode');


/*******************************CODICE CON ANCHE IL PELLET LETTO DAL FILE CSV********************************************/
// function shipping_calculator_shortcode() {
//     ob_start(); // Start output buffering

//     // Define the path to the CSV file
//     $csv_file_path = get_template_directory() . '/tariffe_consegna.csv';

//     // Attempt to open the CSV file
//     $csv_file = fopen($csv_file_path, 'r');
//     $csv = array();
//     $headers = array();
//     if ($csv_file !== false) {
//         // Read the CSV file line by line and parse it
//         while (($data = fgetcsv($csv_file, 0, ';')) !== false) {
//             $csv[] = $data;
//         }
//         fclose($csv_file);

//         // Use the first row to define array keys and remove it
//         $headers = array_shift($csv);
//         $csv = array_map(function($row) use ($headers) {
//             return array_combine($headers, $row);
//         }, $csv);
//     } else {
//         echo '<p>Nessun file CSV.</p>';
//     }

//     // Convert CSV data to JSON for JavaScript
//     $json_data = json_encode($csv);
//     echo "<script>console.log('JSON Data:', $json_data);</script>";

//     // HTML form part with dynamically populated dropdowns
//     echo '<form id="spedizioneForm">
//             <label for="tipo_pallet">Tipo di Pallet:</label>
//             <select name="tipo_pallet" id="tipo_pallet">';

//     // Assume pallet types follow a specific pattern and exclude non-pallet columns
//     $pallet_types = array_filter($headers, function($header) {
//         return in_array($header, ['FP', 'LP', 'ULP', 'HP', 'ELP', 'QP', 'MQP']); // List of all possible pallet types
//     });

//     foreach ($pallet_types as $pallet_type) {
//         echo '<option value="' . htmlspecialchars($pallet_type) . '">' . htmlspecialchars($pallet_type) . '</option>';
//     }

//     echo '</select>
//             <label for="destinazione">Provincia:</label>
//             <select name="destinazione" id="destinazione">';

//     // Populate destinations from CSV data
//     foreach ($csv as $row) {
//         echo '<option value="' . htmlspecialchars($row['Provincia']) . '">' . htmlspecialchars($row['Provincia']) . '</option>';
//     }

//     echo '</select>
//             <input type="checkbox" name="sponda_idraulica" id="sponda_idraulica">
//             <label for="sponda_idraulica">Sponda Idraulica</label>
//             <button type="submit">Calcola</button>
//           </form>
//           <div id="result"></div>';

//     // Inline JavaScript
//     echo "<script>
//     document.addEventListener('DOMContentLoaded', function() {
//         const form = document.getElementById('spedizioneForm');
//         const data = $json_data;
    
//         form.addEventListener('submit', function(e) {
//             e.preventDefault();
    
//             let tipoPallet = document.getElementById('tipo_pallet').value;
//             let destinazione = document.getElementById('destinazione').value;
//             let spondaIdraulica = document.getElementById('sponda_idraulica').checked;
    
//             let tariffaBase = 0;
//             let partenzaDaFirenzePrato = (destinazione === 'FI' || destinazione === 'PO');
    
//             // Calcola la tariffa base in base al tipo di pallet e alla destinazione
//             data.forEach(function(row) {
//                 if (row['Provincia'] === destinazione && row[tipoPallet]) {
//                     tariffaBase = parseFloat(row[tipoPallet]);
//                 }
//             });
    
//             console.log('Tariffa base:', tariffaBase);  // Debug della tariffa base calcolata
    
//             let costoSpedizione = tariffaBase;
    
//             // Aggiunge il costo per la sponda idraulica e il 3% extra se necessario
//             if (spondaIdraulica) {
//                 costoSpedizione += 50; // Costo fisso per la sponda idraulica
//                 costoSpedizione *= 1.03; // Aggiunge il 3% al costo totale
//                 console.log('Costo spedizione dopo sponda idraulica:', costoSpedizione); // Debug dopo aggiunta sponda idraulica
//             }
    
//             // Applica un aumento del 10% se la spedizione non parte da Firenze o Prato
//             if (!partenzaDaFirenzePrato) {
//                 costoSpedizione *= 1.10;
//             }
    
//             console.log('Costo spedizione finale:', costoSpedizione); // Debug del costo finale di spedizione
    
//             // Mostra il costo finale di spedizione
//             document.getElementById('result').innerText = 'Il costo di spedizione è: € ' + costoSpedizione.toFixed(2);
//             document.getElementById('paypalAmount').value = costoSpedizione.toFixed(2);
//             document.querySelector('#paypalForm input[type=\"image\"]').disabled = false;
//         });
//     });
    
//     </script>";
    

//       echo "<style>
//        #paypalForm input[type='image'][disabled] {
//         opacity: 0.5; 
//         cursor: not-allowed;
//       }
//       </style>";

//         echo "<form id='paypalForm' action='https://www.paypal.com/cgi-bin/webscr' method='post'>
//             <input type='hidden' name='cmd' value='_xclick'>
//             <input type='hidden' name='business' value='TUO_EMAIL_PAYPAL'>
//             <input type='hidden' name='item_name' value='Costo di Spedizione'>
//             <input type='hidden' name='amount' id='paypalAmount'>
//             <input type='hidden' name='currency_code' value='EUR'>
//             <input type='hidden' name='notify_url' value='URL_PER_NOTIFICHE_IPN'>
//             <input type='hidden' name='return' value='URL_DI_RITORNO_DOPO_PAGAMENTO'>
//             <input type='hidden' name='cancel_return' value='URL_DI_ANNULLAMENTO'>
//             <input type='image' src='https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-large.png' border='0' name='submit' alt='PayPal - The safer, easier way to pay online!'disabled='disabled'>
//             <img alt='' border='0' src='https://www.paypalobjects.com/en_US/i/scr/pixel.gif' width='1' height='1'>
//         </form>";


//     return ob_get_clean(); // Return and clean the output buffer
// }

// add_shortcode('shipping_calculator', 'shipping_calculator_shortcode');

function shipping_calculator_shortcode() {
    ob_start(); // Start output buffering

    // Define the path to the CSV file
    $csv_file_path = get_template_directory() . '/tariffe_consegna.csv';

    // Attempt to open the CSV file
    $csv_file = fopen($csv_file_path, 'r');
    $csv = array();
    $headers = array();
    if ($csv_file !== false) {
        // Read the CSV file line by line and parse it
        while (($data = fgetcsv($csv_file, 0, ';')) !== false) {
            // Convert numeric values by replacing comma with dot  
            $data = array_map(function($value) {
                return is_numeric($value) ? str_replace(',', '.', $value) : $value; 
            }, $data);
            $csv[] = $data;
        }
        fclose($csv_file);

        // Use the first row to define array keys and remove it
        $headers = array_shift($csv);
        $csv = array_map(function($row) use ($headers) {
            return array_combine($headers, $row);
        }, $csv);
    } else {
        echo '<p>Nessun file CSV.</p>';
    }

    // Convert CSV data to JSON for JavaScript
    $json_data = json_encode($csv);
    echo "<script>console.log('JSON Data:', $json_data);</script>";

    // HTML form part with dynamically populated dropdowns
    echo '<form id="spedizioneForm">
            <label for="tipo_pallet">Tipo di Pallet:</label>
            <select name="tipo_pallet" id="tipo_pallet">';
    
    // Assume pallet types follow a specific pattern and exclude non-pallet columns
    $pallet_types = array_filter($headers, function($header) {
        return in_array($header, ['FP', 'LP', 'ULP', 'HP', 'ELP', 'QP', 'MQP']); // List of all possible pallet types
    });

    foreach ($pallet_types as $pallet_type) {
        echo '<option value="' . htmlspecialchars($pallet_type) . '">' . htmlspecialchars($pallet_type) . '</option>';
    }

    echo '</select>
            <label for="destinazione">Provincia:</label>
            <select name="destinazione" id="destinazione">';
    
    // Populate destinations from CSV data
    foreach ($csv as $row) {
        echo '<option value="' . htmlspecialchars($row['Provincia']) . '">' . htmlspecialchars($row['Provincia']) . '</option>';
    }

    echo '</select>
            <input type="checkbox" name="sponda_idraulica" id="sponda_idraulica">
            <label for="sponda_idraulica">Sponda Idraulica</label>
            <button type="submit">Calcola</button>
          </form>
          <div id="result"></div>';

    // Inline JavaScript for processing the form and calculating the shipping cost
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('spedizioneForm');
        let data = JSON.parse('$json_data');
    
        // Converti i numeri con virgole in numeri con punti decimali
        data = data.map(row => {
            Object.keys(row).forEach(key => {
                if (!isNaN(parseFloat(row[key].replace(',', '.')))) {
                    row[key] = parseFloat(row[key].replace(',', '.'));
                }
            });
            return row;
        });
    
        form.addEventListener('submit', function(e) {
            e.preventDefault();
    
            let tipoPallet = document.getElementById('tipo_pallet').value;
            let destinazione = document.getElementById('destinazione').value;
            let spondaIdraulica = document.getElementById('sponda_idraulica').checked;
    
            let tariffaBase = 0;
            let partenzaDaFirenzePrato = (destinazione === 'FI' || destinazione === 'PO');
    
            // Calcola la tariffa base in base al tipo di pallet e alla destinazione
            data.forEach(function(row) {
                if (row['Provincia'] === destinazione && typeof row[tipoPallet] === 'number') {
                    tariffaBase = row[tipoPallet];
                }
            });
    
            console.log('Tariffa base:', tariffaBase); // Debug della tariffa base calcolata
    
            let costoSpedizione = tariffaBase;
    
            // Aggiunge il costo per la sponda idraulica e il 3% extra se necessario
            if (spondaIdraulica) {
                costoSpedizione += 50; // Costo fisso per la sponda idraulica
                costoSpedizione *= 1.03; // Aggiunge il 3% al costo totale
                console.log('Costo spedizione dopo sponda idraulica:', costoSpedizione); // Debug dopo aggiunta sponda idraulica
            }
    
            // Applica un aumento del 10% se la spedizione non parte da Firenze o Prato
            if (!partenzaDaFirenzePrato) {
                costoSpedizione *= 1.10;
            }
    
            console.log('Costo spedizione finale:', costoSpedizione); // Debug del costo finale di spedizione
    
            // Mostra il costo finale di spedizione
            document.getElementById('result').innerText = 'Il costo di spedizione è: € ' + costoSpedizione.toFixed(2);
            document.getElementById('paypalAmount').value = costoSpedizione.toFixed(2);
            document.querySelector('#paypalForm input[type=\"image\"]').disabled = false;
        });
    });
    
    </script>";

    echo "<style>
       #paypalForm input[type='image'][disabled] {
        opacity: 0.5; 
        cursor: not-allowed;
      }
      </style>";

    echo "<form id='paypalForm' action='https://www.paypal.com/cgi-bin/webscr' method='post'>
            <input type='hidden' name='cmd' value='_xclick'>
            <input type='hidden' name='business' value='YOUR_PAYPAL_EMAIL'>
            <input type='hidden' name='item_name' value='Shipping Cost'>
            <input type='hidden' name='amount' id='paypalAmount'>
            <input type='hidden' name='currency_code' value='EUR'>
            <input type='hidden' name='notify_url' value='IPN_NOTIFICATION_URL'>
            <input type='hidden' name='return' value='RETURN_URL_AFTER_PAYMENT'>
            <input type='hidden' name='cancel_return' value='CANCEL_URL'>
            <input type='image' src='https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-large.png' border='0' name='submit' alt='PayPal - The safer, easier way to pay online!'disabled='disabled'>
            <img alt='' border='0' src='https://www.paypalobjects.com/en_US/i/scr/pixel.gif' width='1' height='1'>
        </form>";

    return ob_get_clean(); // Return and clean the output buffer
}

add_shortcode('shipping_calculator', 'shipping_calculator_shortcode');




