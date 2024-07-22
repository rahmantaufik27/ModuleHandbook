<?php
require 'vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;

// Path ke file JSON kredensial
$pathToJson = 'storied-precept-243308-e34a60c0b719.json';

// ID spreadsheet dan range data
$spreadsheetId = '1HOkLicWIffmT7UxTqd2XNWKz5CmCLL5Ue9gO9rq5iBA';
$range = 'QUESTIONS BANK'; // Ganti dengan range yang sesuai

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

    $dataById = [];

    // Memproses data dan mengelompokkan berdasarkan ID
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
$midTerm = [];
$examTerm = [];
$contents = [];

if (!empty($selectedData)) {
    $totalQuestions = count($selectedData);
    $midTermCount = (int)floor($totalQuestions / 2);

    // Mengumpulkan soal dan konten
    foreach ($selectedData as $question) {
        $soal = $question[3];
        $konten = $question[2];
        $code = $question[0];
        $course = $question[1];

        if (!isset($contents[$konten])) {
            $contents[$konten] = "c-" . (count($contents) + 1);
        }

        if (count($midTerm) < $midTermCount) {
            $midTerm[] = ['soal' => $soal, 'konten' => $konten];
        } else {
            $examTerm[] = ['soal' => $soal, 'konten' => $konten];
        }
    }

    // Mengumpulkan konten unik
    foreach ($contents as $konten => $kode) {
        $uniqueContents[$kode] = $konten;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question Page</title>
    <link href="style.css" rel="stylesheet">
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
    <h2>Question Examples</h2>
    
    <?php if (!empty($selectedData)): ?>
    
    <h3>Course: <?= htmlspecialchars($course, ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>)</h3>
    <hr>
    <h3>Exam Instructions</h3>
    <ul>
        <li>Please do not communicate with your classmates during the exam. All work must be completed independently.</li>
        <li>If you are caught cheating, your exam will be immediately invalidated and you will receive a zero.</li>
        <li>You are not allowed to use any electronic devices or outside materials during the exam.</li>
        <li>If you have any questions about the exam, please raise your hand and ask the proctor for clarification.</li>
        <li>Any form of cheating, including plagiarism, will not be tolerated and will result in disciplinary action.</li>
        <li>Any attempt to cheat will be reported and may result in a failing grade for this course.</li>
        <li>As believers in our religion, it is our duty to be truthful and honest in all our actions, including during this exam.</li>
    </ul>
    <hr>
    
        <h3>Midterm Exam</h3>
        <ol>
            <?php foreach ($midTerm as $item): ?>
                <li>[<?= htmlspecialchars($contents[$item['konten']], ENT_QUOTES, 'UTF-8') ?>] <?= htmlspecialchars($item['soal'], ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ol>
        <h3>Final Exam</h3>
        <ol>
            <?php foreach ($examTerm as $item): ?>
                <li>[<?= htmlspecialchars($contents[$item['konten']], ENT_QUOTES, 'UTF-8') ?>] <?= htmlspecialchars($item['soal'], ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ol>
        <hr>
        <h3>Contents of Course</h3>
        <ol>
            <?php foreach ($uniqueContents as $kode => $konten): ?>
                <li class="content-item">[<?= htmlspecialchars($kode, ENT_QUOTES, 'UTF-8') ?>] <?= htmlspecialchars($konten, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ol>
    <?php else: ?>
        <div class="warning">Data is not found!</div>
    <?php endif; ?>
</body>
</html>
