<?php
/*
Plugin Name: Kontaktformular Plugin mit Ajax - für The Key
Description: Ein einfaches WordPress-Plugin für ein Kontaktformular mit Ajax-Verarbeitung.
Version: 1.0
Author: Frank Illner
*/

// Shortcode für das Kontaktformular
function contact_form_shortcode() {
    ob_start();
    include 'contact-form-template.php'; // Lade das Template für das Kontaktformular
    return ob_get_clean();
}
add_shortcode('contact_form', 'contact_form_shortcode');

// Optionen für das Plugin hinzufügen
function contact_form_options() {
    add_option('contact_form_email', get_option('admin_email'));
    add_option('contact_form_confirmation_message', 'Vielen Dank! Ihre Nachricht wurde erfolgreich gesendet.');
}
register_activation_hook(__FILE__, 'contact_form_options');

// E-Mail-Adresse für Benachrichtigungen
function get_contact_form_email() {
    return get_option('contact_form_email');
}

// Bestätigungsnachricht für Einreichungen
function get_contact_form_confirmation_message() {
    return get_option('contact_form_confirmation_message');
}

// Ajax-Verarbeitung des Kontaktformulars
add_action('wp_ajax_process_contact_form', 'process_contact_form');
add_action('wp_ajax_nopriv_process_contact_form', 'process_contact_form');

// E-Mail senden und Daten speichern
function process_contact_form() {
    $responseData = $_POST['formData'];
    if (isset($responseData['submit'])) {
        // Verarbeite das Formular wie zuvor
        $name = sanitize_text_field($responseData['fullname']);
        $email = sanitize_email($responseData['mail']);
        $subject = sanitize_text_field($responseData['subject']);
        $message = sanitize_textarea_field($responseData['message']);

        // Validierung der Eingaben
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            echo '<div class="error">Bitte füllen Sie alle Felder aus.</div>';
            wp_die(); // Beende die Ausführung
        }

        // Nachricht zusammenstellen
        $admin_email = get_contact_form_email();
        $to = $admin_email;
        $headers = "From: $name <$email>";
        $message_body = "Name: $name\n\nEmail: $email\n\nBetreff: $subject\n\nNachricht:\n$message";

        // Email senden
        wp_mail($to, $subject, $message_body, $headers);

        // Daten in der Datenbank speichern
        global $wpdb;
        $table_name = $wpdb->prefix . 'contact_form_entries';
        $wpdb->insert($table_name, array(
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
            'date' => current_time('mysql')
        ));
    }
    wp_die(); // Beende die Ausführung
}


// Aktiviere das Plugin und erstelle die erforderliche Datenbanktabelle
function create_contact_form_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'contact_form_entries';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(50) NOT NULL,
        email varchar(100) NOT NULL,
        subject varchar(100) NOT NULL,
        message text NOT NULL,
        date datetime NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'create_contact_form_table');

// Admin-Dashboard: Eintrag anzeigen und löschen
function show_contact_form_entries() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'contact_form_entries';
    
    // Überprüfe, ob eine Aktion zum Löschen eines Eintrags ausgeführt wurde
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['entry_id'])) {
        $entry_id = intval($_GET['entry_id']);
        $wpdb->delete($table_name, array('id' => $entry_id));
        echo '<div class="notice notice-success"><p>Eintrag erfolgreich gelöscht.</p></div>';
    }
    
    // Zeige alle Einträge an
    $entries = $wpdb->get_results("SELECT * FROM $table_name ORDER BY date DESC");
    ?>
    <div class="wrap">
        <h2>Eingegangene Nachrichten</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>E-Mail</th>
                    <th>Betreff</th>
                    <th>Nachricht</th>
                    <th>Datum</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entries as $entry): ?>
                    <tr>
                        <td><?php echo $entry->id; ?></td>
                        <td><?php echo $entry->name; ?></td>
                        <td><?php echo $entry->email; ?></td>
                        <td><?php echo $entry->subject; ?></td>
                        <td><?php echo $entry->message; ?></td>
                        <td><?php echo $entry->date; ?></td>
                        <td>
                            <a href="?page=contact-form-entries&action=delete&entry_id=<?php echo $entry->id; ?>" class="delete" onclick="return confirm('Sind Sie sicher, dass Sie diesen Eintrag löschen möchten?')">Löschen</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Admin-Dashboard: Menüeintrag hinzufügen
function contact_form_menu() {
    add_menu_page('Kontaktformular Einträge', 'Kontaktformular', 'manage_options', 'contact-form-entries', 'show_contact_form_entries');
}
add_action('admin_menu', 'contact_form_menu');

// Funktion zum Einbinden des CSS
function enqueue_contact_form_styles() {
    wp_enqueue_style('contact-form-style', plugin_dir_url(__FILE__) . 'contact-form.css');
}
// Hook zum Einbinden des CSS
add_action('wp_enqueue_scripts', 'enqueue_contact_form_styles');

// Enqueue JavaScript-Datei für Ajax-Verarbeitung
function enqueue_contact_form_scripts() {
    wp_enqueue_script('contact-form-script', plugin_dir_url(__FILE__) . 'contact-form.js', array('jquery'), null, true);
    wp_localize_script('contact-form-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'enqueue_contact_form_scripts');

?>
