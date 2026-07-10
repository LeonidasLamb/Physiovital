<?php
/**
 * PhysioVital Recruiting-Funnel: Mail-Versand
 *
 * Diese Datei per FTP oder über den Hosting-Dateimanager in das
 * Hauptverzeichnis von physiovital-frankfurt.de hochladen (dorthin,
 * wo auch wp-config.php liegt).
 *
 * Danach in der index.html des Funnels eintragen:
 *   const WEBHOOK_URL = "https://physiovital-frankfurt.de/send-bewerbung.php";
 *
 * Kein Anbieter, keine Registrierung, keine Verifizierung nötig.
 */

// ===== Einstellungen =====
$empfaenger = 'bewerbung@physiovital-frankfurt.de';
$absender   = 'noreply@physiovital-frankfurt.de'; // sollte zur Domain gehören (Spam-Schutz)

// ===== CORS: erlaubt Anfragen von der GitHub-Pages-Seite =====
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST')   { http_response_code(405); echo '{"ok":false}'; exit; }

// ===== Daten einlesen =====
$daten = json_decode(file_get_contents('php://input'), true);
if (!is_array($daten)) { http_response_code(400); echo '{"ok":false}'; exit; }

// Einfacher Spam-Schutz: nur Anfragen aus dem Funnel annehmen
if (($daten['quelle'] ?? '') !== 'physio-funnel') { http_response_code(403); echo '{"ok":false}'; exit; }

$feld = function($key) use ($daten) {
    $wert = trim((string)($daten[$key] ?? ''));
    // Header-Injection verhindern
    return str_replace(["\r", "\n"], ' ', strip_tags($wert));
};

$name      = $feld('name');
$email     = $feld('email');
$telefon   = $feld('tel');
$gehalt    = $feld('gehalt');
$erfahrung = $feld('erfahrung');
$uebernahme= $feld('uebernahme');

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($telefon) < 6) {
    http_response_code(422); echo '{"ok":false}'; exit;
}

// ===== Mail zusammenbauen =====
$betreff = 'Neue Bewerbung: ' . $name;
$text = "Neue Bewerbung über den Recruiting-Funnel\n"
      . "==========================================\n\n"
      . "Name:               $name\n"
      . "E-Mail:             $email\n"
      . "Telefon:            $telefon\n\n"
      . "Gehaltsvorstellung: $gehalt\n"
      . "Erfahrung:          $erfahrung\n"
      . "Praxisübernahme:    $uebernahme\n\n"
      . "Eingegangen am: " . date('d.m.Y \u\m H:i \U\h\r') . "\n"
      . "Tipp: Innerhalb von 24 Stunden zurückrufen, das wurde auf der Seite versprochen.\n";

$header = "From: PhysioVital Funnel <$absender>\r\n"
        . "Reply-To: $name <$email>\r\n"
        . "Content-Type: text/plain; charset=utf-8\r\n";

$gesendet = mail($empfaenger, '=?UTF-8?B?' . base64_encode($betreff) . '?=', $text, $header);

echo json_encode(['ok' => (bool)$gesendet]);
