<?php
// opslaan.php

// Alleen POST‐requests toegestaan
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Alleen POST requests toegestaan";
    exit;
}

// Lees (geparste) JSON of fallback naar $_POST
$rawInput = file_get_contents("php://input");
$jsonData = json_decode($rawInput, true);
$data = is_array($jsonData) ? $jsonData : $_POST;

// Controle op verplichte velden
$verplicht = ['voornaam', 'achternaam', 'telefoon', 'email', 'tefinancieren', 'slottermijn', 'looptijd'];
foreach ($verplicht as $veld) {
    if (empty($data[$veld])) {
        http_response_code(400);
        echo "Fout: verplicht veld ontbreekt: $veld";
        exit;
    }
}

// Map de verzending naar een rijen‐array, trim alle invoer
$voornaam      = trim($data['voornaam']);
$achternaam    = trim($data['achternaam']);
$telefoon      = trim($data['telefoon']);
$email         = trim($data['email']);
$tefinancieren = trim($data['tefinancieren']);
$looptijd      = trim($data['looptijd']);
$maandlast     = trim($data['maandlast'] ?? '');    // optioneel (is readonly), kan komen uit localStorage
$slottermijn   = trim($data['slottermijn']);
$kvk           = trim($data['kvk'] ?? '');
$bedrijfsnaam  = trim($data['bedrijfsnaam'] ?? '');
$voertuigLink  = trim($data['voertuigLink'] ?? '');
// Server‐timestamp (Europe/Amsterdam)
date_default_timezone_set('Europe/Amsterdam');
$datum = date('Y-m-d H:i:s');

// Zet bestandspad
$bestand = 'aanvragen.csv';
$bestaat = file_exists($bestand);

// Probeer het CSV‐bestand in append‐modus te openen
$file = fopen($bestand, 'a');
if ($file === false) {
    http_response_code(500);
    echo "Fout: Kan bestand niet openen";
    exit;
}

// Voeg exclusieve lock toe om race conditions te voorkomen
if (!flock($file, LOCK_EX)) {
    fclose($file);
    http_response_code(500);
    echo "Fout: Kan bestand niet locken";
    exit;
}

// Indien bestand nog niet bestaat, schrijf header‐rij
if (!$bestaat) {
    $header = [
        'Datum',
        'Voornaam',
        'Achternaam',
        'Telefoon',
        'E-mail',
        'Te financieren bedrag',
        'Looptijd (maanden)',
        'Maandbedrag',
        'Slottermijn',
        'KvK',
        'Bedrijfsnaam',
        'Voertuig link'
    ];
    fputcsv($file, $header);
}

// Voorbereiding data‐rij
$rij = [
    $datum,
    $voornaam,
    $achternaam,
    $telefoon,
    $email,
    $tefinancieren,
    $looptijd,
    $maandlast,
    $slottermijn,
    $kvk,
    $bedrijfsnaam,
    $voertuigLink
];

// Schrijf de data‐rij naar CSV
$result = fputcsv($file, $rij);

// Ontgrendel en sluit bestand
flock($file, LOCK_UN);
fclose($file);

// Antwoord voor de client
if ($result !== false) {
    http_response_code(200);
    echo "OK";
} else {
    http_response_code(500);
    echo "Fout bij het opslaan van gegevens";
}
