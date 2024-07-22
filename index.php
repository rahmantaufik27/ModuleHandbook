<?php
require 'vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;

// Path ke file JSON kredensial
$pathToJson = 'storied-precept-243308-adf6b0bb18cb.json';

// ID spreadsheet dan range data
$spreadsheetId = '1HOkLicWIffmT7UxTqd2XNWKz5CmCLL5Ue9gO9rq5iBA';
$range = 'MODULES-HANDBOOK!A2:T'; // Ganti dengan range yang sesuai

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

    $dataByClass = [];

    if (!empty($values)) {
        foreach ($values as $row) {
            if (isset($row[19])) {
                $sem = $row[19];
                if (!isset($dataByClass[$sem])) {
                    $dataByClass[$sem] = [];
                }
                $dataByClass[$sem][] = $row;
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
    <h2>LIST OF COURSES</h2>

    <table>
        <tr>
            <th>Information</th>
            <td>
                <i class="material-icons" title="Module Handbook">description</i>Module Handbook <br/>
                <i class="material-icons" title="Practice Instruction">announcement</i>Practice Instruction <br/>
                <i class="material-icons" title="Question Examples">assignment</i>Question Examples <br/>
                <i class="material-icons" title="Project Instruction">pageview</i>Project Instruction <br/>
            </td>
        </tr>
    </table>
    
    <?php if (!empty($dataByClass)): ?>
        <?php foreach ($dataByClass as $sem => $rows): ?>
            <br/><h3>Semester <?= htmlspecialchars($sem, ENT_QUOTES, 'UTF-8') ?></h3>
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Course</th>
                        <th>Credits</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr data-href="module.php?id=<?= htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8') ?>">
                            <td><?= htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row[9][0], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <a href="module-handbook.php?code=<?= htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8') ?>"><i class="material-icons" title="Module Handbook">description</i></a>
                                <a href="practice.php?code=<?= htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8') ?>"><i class="material-icons" title="Practice Instruction">announcement</i></a>
                                <a href="question.php?code=<?= htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8') ?>"><i class="material-icons" title="Question Examples">assignment</i></a>
                                <a href="project.php?code=<?= htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8') ?>"><i class="material-icons" title="Project Instruction">pageview</i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="warning">Data is not found!</div>
    <?php endif; ?>
</body>
</html>