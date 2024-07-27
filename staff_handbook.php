<?php
require 'vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;

function getProfile($nidn = null) {
    $pathToCredentials = 'storied-precept-243308-adf6b0bb18cb.json';
    $client = new Client();
    $client->setApplicationName('Google Sheets API PHP');
    $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
    $client->setAuthConfig($pathToCredentials);

    $service = new Sheets($client);
    $spreadsheetId = '1HOkLicWIffmT7UxTqd2XNWKz5CmCLL5Ue9gO9rq5iBA';
    $range = 'STAFF-HANDBOOK!A2:N';

    try {
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();
    } catch (Exception $e) {
        return json_encode(['error' => 'Error fetching data: ' . $e->getMessage()]);
    }

    $headers = ['NIDN', 'NAME', 'POSITION', 'ACADEMIC CAREER', 'EMPLOYMENT', 'INDUSTRI COLLABORATION', 'SINTA ID', 'GS ID', 'Pendidikan S1', 'Pendidikan S2', 'Pendidikan S3', 'Tahun Lulus S1', 'Tahun Lulus S2', 'Tahun Lulus S3'];
    $profiles = [];

    foreach ($values as $row) {
        if (count($row) == count($headers)) {
            $profile = array_combine($headers, $row);
        } else {
            $row = array_pad($row, count($headers), null);
            $profile = array_combine($headers, $row);
        }
        $profiles[] = $profile;
    }

    if ($nidn !== null) {
        foreach ($profiles as $profile) {
            if ($profile['NIDN'] === $nidn) {
                return json_encode($profile);
            }
        }
        return json_encode(null);
    }

    return json_encode($profiles);
}
 header('Content-Type: application/json');
 echo getProfile(isset($_GET['nidn']) ? $_GET['nidn'] : null);
?>

