<?php
require 'vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;

// Path ke file JSON kredensial
$pathToJson = 'storied-precept-243308-adf6b0bb18cb.json';

// ID spreadsheet dan range data
$spreadsheetId = '1HOkLicWIffmT7UxTqd2XNWKz5CmCLL5Ue9gO9rq5iBA';
$range = 'PROJECT-BASE INSTRUCTIONS'; // Ganti dengan range yang sesuai

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

    // Mengambil ID dari parameter URL
    $id = isset($_GET['code']) ? $_GET['code'] : '';

    $data = null;
    if (!empty($id) && !empty($values)) {
        foreach ($values as $row) {
            if ($row[0] == $id) {
                $data = $row;
                break;
            }
        }
    }
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="style.css" rel="stylesheet">
    <title>PROJECT</title>
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
    
    <h2>PROJECT INSTRUCTION</h2>
    
    <?php if ($data): ?>

    <h3>Course: <?= htmlspecialchars($data[1], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($data[0], ENT_QUOTES, 'UTF-8') ?>)</h3>
        <table>
            <tr>
                <th>Objective</th>
                <td><?= htmlspecialchars($data[2], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th>Case Study</th>
                <td><?= htmlspecialchars($data[3], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th>Questions</th>
                <td>
                    <?php
                        $questionList = explode("\n", htmlspecialchars($data[4], ENT_QUOTES, 'UTF-8'));
                    ?>
                    <ol>
                        <?php foreach ($questionList as $item): 
                            if ($item != "") {
                                echo "<li>$item</li>";
                            }
                            endforeach;
                        ?>
                    </ol>
                </td>
            </tr>
            <tr>
                <th>Grading</th>
                <td>
                    <?php
                        $gradingList = explode("\n", htmlspecialchars($data[5], ENT_QUOTES, 'UTF-8'));
                    ?>
                    <ol>
                        <?php foreach ($gradingList as $item): 
                            if ($item != "") {
                                echo "<li>$item</li>";
                            }
                            endforeach;
                        ?>
                    </ol>
                </td>
            </tr>
        </table>
    <?php else: ?>
        <div class="warning">Data is not found or this course does not have a project!</div>
    <?php endif; ?>
</body>
</html>