<?php
// verzendmail.php - Verstuurt bevestigingsmail met aanvraaggegevens

// Zet error reporting aan voor debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include de mail functie
require_once('mail.php');

// Alleen POST requests toestaan
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Alleen POST requests toegestaan";
    exit;
}

try {
    // Lees JSON input
    $rawInput = file_get_contents("php://input");
    
    if (empty($rawInput)) {
        http_response_code(400);
        echo "Geen input data ontvangen";
        exit;
    }
    
    $data = json_decode($rawInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo "JSON decode fout: " . json_last_error_msg();
        exit;
    }
    
    // Valideer verplichte velden
    $voornaam = trim($data['voornaam'] ?? '');
    $achternaam = trim($data['achternaam'] ?? '');
    $email = trim($data['email'] ?? '');
    $telefoon = trim($data['telefoon'] ?? '');
    $tefinancieren = trim($data['tefinancieren'] ?? '');
    $looptijd = trim($data['looptijd'] ?? '');
    $slottermijn = trim($data['slottermijn'] ?? '');
    
    // Optionele velden
    $maandlast = trim($data['maandlast'] ?? '');
    $kvk = trim($data['kvk'] ?? '');
    $bedrijfsnaam = trim($data['bedrijfsnaam'] ?? '');
    $voertuigLink = trim($data['voertuigLink'] ?? '');
    
    if (empty($voornaam) || empty($email)) {
        http_response_code(400);
        echo "Voornaam en email zijn verplicht";
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo "Ongeldig email adres";
        exit;
    }
    
    // Bereken maandlast als deze niet is meegegeven
    if (empty($maandlast) && !empty($tefinancieren) && !empty($looptijd) && !empty($slottermijn)) {
        $rente = 0.06;
        $marge = 0.05;
        $maandRente = $rente / 12;
        $gefinancierd = floatval($tefinancieren) * (1 + $marge);
        $aflosBedrag = ($gefinancierd - floatval($slottermijn)) * $maandRente / (1 - pow(1 + $maandRente, -intval($looptijd)));
        $maandlast = number_format($aflosBedrag + (floatval($slottermijn) / intval($looptijd)), 2, '.', '');
    }
    
    // Maak email content
    $emailSubject = "Bevestiging financieringsaanvraag - J&N Finance";
    
    $emailBody = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; color: #333; }
            .header { background-color: #f2d7d7; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .details { background-color: #f9f9f9; padding: 15px; margin: 15px 0; border-radius: 5px; }
            .footer { background-color: #e8bcbc; padding: 15px; text-align: center; color: white; }
            table { width: 100%; border-collapse: collapse; }
            td { padding: 8px; border-bottom: 1px solid #ddd; }
            .label { font-weight: bold; width: 200px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1 style='color: #c58a8a; margin: 0;'>J&N Finance</h1>
            <h2 style='color: #a37474; margin: 10px 0 0 0;'>Bevestiging Aanvraag</h2>
        </div>
        
        <div class='content'>
            <p>Beste " . htmlspecialchars($voornaam) . " " . htmlspecialchars($achternaam) . ",</p>
            
            <p>Bedankt voor je aanvraag bij J&N Finance. We hebben je gegevens goed ontvangen en zullen zo spoedig mogelijk contact met je opnemen.</p>
            
            <div class='details'>
                <h3 style='color: #c58a8a; margin-top: 0;'>Jouw aanvraaggegevens:</h3>
                <table>
                    <tr><td class='label'>Naam:</td><td>" . htmlspecialchars($voornaam . ' ' . $achternaam) . "</td></tr>
                    <tr><td class='label'>Email:</td><td>" . htmlspecialchars($email) . "</td></tr>
                    <tr><td class='label'>Telefoon:</td><td>" . htmlspecialchars($telefoon) . "</td></tr>
                    <tr><td class='label'>Te financieren bedrag:</td><td>€ " . number_format(floatval($tefinancieren), 2, ',', '.') . "</td></tr>
                    <tr><td class='label'>Looptijd:</td><td>" . htmlspecialchars($looptijd) . " maanden</td></tr>
                    <tr><td class='label'>Slottermijn:</td><td>€ " . number_format(floatval($slottermijn), 2, ',', '.') . "</td></tr>";
    
    if (!empty($maandlast)) {
        $emailBody .= "<tr><td class='label'>Maandlast:</td><td>€ " . number_format(floatval($maandlast), 2, ',', '.') . "</td></tr>";
    }
    
    if (!empty($kvk)) {
        $emailBody .= "<tr><td class='label'>KvK nummer:</td><td>" . htmlspecialchars($kvk) . "</td></tr>";
    }
    
    if (!empty($bedrijfsnaam)) {
        $emailBody .= "<tr><td class='label'>Bedrijfsnaam:</td><td>" . htmlspecialchars($bedrijfsnaam) . "</td></tr>";
    }
    
    if (!empty($voertuigLink)) {
        $emailBody .= "<tr><td class='label'>Voertuig link:</td><td><a href='" . htmlspecialchars($voertuigLink) . "' target='_blank'>" . htmlspecialchars($voertuigLink) . "</a></td></tr>";
    }
    
    $emailBody .= "
                </table>
            </div>
            
            <p><strong>Wat gebeurt er nu?</strong></p>
            <ul>
                <li>We beoordelen je aanvraag binnen 24 uur</li>
                <li>Je ontvangt bericht van ons over de uitkomst</li>
                <li>Bij goedkeuring nemen we contact op voor de verdere afhandeling</li>
            </ul>
            
            <p>Heb je vragen over je aanvraag? Neem dan contact met ons op:</p>
            <p>
                <strong>Telefoon:</strong> 0228 506923<br>
                <strong>Email:</strong> info@jennfinance.nl
            </p>
            
            <p>Met vriendelijke groet,<br>
            Het team van J&N Finance</p>
        </div>
        
        <div class='footer'>
            <p style='margin: 0;'>J&N Finance - Uw partner in financiering</p>
        </div>
    </body>
    </html>";
    
    // Verstuur de email
    mymail($email, $emailSubject, $emailBody);
    
    // Verstuur ook een notificatie naar het bedrijf
    $notificatieSubject = "Nieuwe financieringsaanvraag ontvangen";
    $notificatieBody = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <h2>Nieuwe aanvraag ontvangen</h2>
        <p><strong>Van:</strong> " . htmlspecialchars($voornaam . ' ' . $achternaam) . "</p>
        <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
        <p><strong>Telefoon:</strong> " . htmlspecialchars($telefoon) . "</p>
        <p><strong>Bedrag:</strong> € " . number_format(floatval($tefinancieren), 2, ',', '.') . "</p>
        <p><strong>Looptijd:</strong> " . htmlspecialchars($looptijd) . " maanden</p>
        <p><strong>Datum:</strong> " . date('d-m-Y H:i:s') . "</p>
        
        " . (!empty($bedrijfsnaam) ? "<p><strong>Bedrijf:</strong> " . htmlspecialchars($bedrijfsnaam) . "</p>" : "") . "
        " . (!empty($kvk) ? "<p><strong>KvK:</strong> " . htmlspecialchars($kvk) . "</p>" : "") . "
        " . (!empty($voertuigLink) ? "<p><strong>Voertuig:</strong> <a href='" . htmlspecialchars($voertuigLink) . "'>" . htmlspecialchars($voertuigLink) . "</a></p>" : "") . "
    </body>
    </html>";
    
    // Verstuur notificatie naar het bedrijf
    mymail("info@jennfinance.nl", $notificatieSubject, $notificatieBody);
    
    // Succes response
    http_response_code(200);
    echo "Bevestigingsmails succesvol verzonden";
    
} catch (Exception $e) {
    error_log("Email error: " . $e->getMessage());
    http_response_code(500);
    echo "Fout bij het verzenden van email: " . $e->getMessage();
}
?>