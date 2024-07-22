<?php
require 'vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;

// Path ke file JSON kredensial
$pathToJson = 'storied-precept-243308-adf6b0bb18cb.json';

// ID spreadsheet dan range data
$spreadsheetId = '1HOkLicWIffmT7UxTqd2XNWKz5CmCLL5Ue9gO9rq5iBA';
$range = 'PRACTICE-INSTRUCTIONS'; // Ganti dengan range yang sesuai

// Membuat client Google
$client = new Client();
$client->setApplicationName('Google Sheets API PHP');
$client->setScopes(Sheets::SPREADSHEETS_READONLY);
$client->setAuthConfig($pathToJson);

// Membuat service Sheets
$service = new Sheets($client);

try {
    // Mengambil data dari Google Sheets
    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    $values = $response->getValues();

    // Mengelompokkan data berdasarkan ID
    $dataById = [];
    if (!empty($values)) {
        foreach ($values as $row) {
            if (isset($row[0])) {
                $id = $row[0];
                if (!isset($dataById[$id])) {
                    $dataById[$id] = [];
                }
                $dataById[$id][] = $row;
            }
        }
    }
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

$id = isset($_GET['code']) ? $_GET['code'] : null;
$selectedData = $id && isset($dataById[$id]) ? $dataById[$id] : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <title>LIST OF COURSES</title>
</head>
<body>
    <table>
        <tr>
            <td><center><a href="index.php"><img src="Logo_UnivLampung.png" style="width:100px;height:100px;"></a></center></td>
            <td><b>UNIVERSITY OF LAMPUNG <br/>
            FACULTY OF MATHEMATICS AND NATURAL SCIENCE <br/>
            Department of Computer Science</b> <br/>
            Jl. Prof. Dr. Soemantri Brodjonegoro No. 1 Bandar Lampung 35145</td>
        </tr>
    </table>
    <h2>PRACTICE INSTRUCTION</h2>
    
    <?php if (!empty($selectedData)): ?>

        <h3>Course: <?= htmlspecialchars($selectedData[0][1], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($selectedData[0][0], ENT_QUOTES, 'UTF-8') ?>)</h3>
    
        <table>
            <thead>
                <tr>
                    <th>Topics</th>
                    <th>Instructions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($selectedData as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row[3], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?php
                                $instList = explode("\n", htmlspecialchars($row[4], ENT_QUOTES, 'UTF-8'));
                            ?>
                            <ol>
                                <?php foreach ($instList as $item): 
                                    if ($item != "") {
                                        echo "<li>$item</li>";
                                    }
                                    endforeach;
                                ?>
                            </ol>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
    <div class="warning">Data is not found or this course does not have a practice!</div>
    <?php endif; ?>
</body>
</html>