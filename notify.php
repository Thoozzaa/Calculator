<?php
// Verbeterde notify.php voor testen van email functionaliteit

// Zet error reporting aan
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('mail.php');

echo "<h2>Email Test</h2>";
echo "<p>Bezig met het versturen van een test email...</p>";

try {
    $result = mymail("thoozzaa@gmail.com", "TEST van J&N Finance", "Dit is een test email van het aanvraagformulier systeem.");
    
    if ($result) {
        echo "<p style='color: green; font-weight: bold;'>✅ Email succesvol verzonden!</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Email verzenden mislukt.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>❌ Fout bij het verzenden van email:</p>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    
    // Suggesties voor veelvoorkomende problemen
    echo "<h3>Mogelijke oplossingen:</h3>";
    echo "<ul>";
    echo "<li>Controleer of de SMTP instellingen correct zijn</li>";
    echo "<li>Controleer of de gebruikersnaam en wachtwoord kloppen</li>";
    echo "<li>Controleer of de SMTP server bereikbaar is</li>";
    echo "<li>Probeer een andere poort (bijv. 465 voor SSL)</li>";
    echo "</ul>";
}
?>