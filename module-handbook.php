<?php
require 'vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;

// Path ke file JSON kredensial
$pathToJson = 'storied-precept-243308-adf6b0bb18cb.json';

// ID spreadsheet dan range data
$spreadsheetId = '1HOkLicWIffmT7UxTqd2XNWKz5CmCLL5Ue9gO9rq5iBA';
$range = 'MODULES-HANDBOOK'; // Ganti dengan range yang sesuai

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
    <title>MODULE HANDBOOK</title>
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
    <h2>MODULE HANDBOOK</h2>
    
    <?php if ($data): ?>
    
    <h3>Bachelor of Computer Science</h3>
        <table>
            <tr>
                <th>Module Name</th>
                <td><b><?= htmlspecialchars($data[1], ENT_QUOTES, 'UTF-8') ?></b></td>
            </tr>
            <tr>
                <th>Module Level</th>
                <td>Undergraduate</td>
            </tr>
            <tr>
                <th>Code</th>
                <td><?= htmlspecialchars($data[0], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th>Course</th>
                <td><?= htmlspecialchars($data[1], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th>Description</th>
                <td><?= htmlspecialchars($data[17], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th>Semester</th>
                <td><?= htmlspecialchars($data[2], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th>Lecturer</th>
                <td><?= htmlspecialchars($data[4], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th>Contact Person</th>
                <td><?= htmlspecialchars($data[3], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th>Language</th>
                <td><?= htmlspecialchars($data[5], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th>Relation to Curriculum</th>
                <td><?= htmlspecialchars($data[6], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th>Type of Teaching, Contact Hours</th>
                <td><?= htmlspecialchars($data[7], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th>Workload</th>
                <td>
                    <?php
                        $woList = explode("\n", htmlspecialchars($data[8], ENT_QUOTES, 'UTF-8'));
                    ?>
                    <ul>
                        <?php foreach ($woList as $item): 
                            if ($item != "") {
                                echo "<li>$item</li>";
                            }
                            endforeach;
                        ?>
                    </ul>
                </td>
            </tr>
            <tr>
                <th>Credit Points</th>
                <td><?= htmlspecialchars($data[9], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th>Requirements according to the Examination Regulations</th>
                <td><?= htmlspecialchars($data[10], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th>Learning Outcomes (Course Outcomes) and Their Corresponding PLOs</th>
                <td>
                    <?php
                        $loList = explode("\n", htmlspecialchars($data[11], ENT_QUOTES, 'UTF-8'));
                    ?>
                    <ol>
                        <?php foreach ($loList as $item): 
                            if ($item != "") {
                                echo "<li>$item</li>";
                            }
                            endforeach;
                        ?>
                    </ol>
                </td>
            </tr>
            <tr>
                <th>Competencies/Course Learning Outcomes</th>
                <td>
                    <?php
                        $cloList = explode("\n", htmlspecialchars($data[18], ENT_QUOTES, 'UTF-8'));
                    ?>
                    <ol>
                        <?php foreach ($cloList as $item): 
                            if ($item != "") {
                                echo "<li>$item</li>";
                            }
                            endforeach;
                        ?>
                    </ol>
                </td>
            </tr>
            <tr>
                <th>Contents</th>
                <td>
                    <?php
                        $contentList = explode("\n", htmlspecialchars($data[12], ENT_QUOTES, 'UTF-8'));
                    ?>
                    <ol>
                        <?php foreach ($contentList as $item): 
                            if ($item != "") {
                                echo "<li>$item</li>";
                            }
                            endforeach;
                        ?>
                    </ol>
                </td>
            </tr>
            <tr>
                <th>Study and Examination Requirements and Forms of Examination</th>
                <td>
                    <?php
                        $examList = explode("\n", htmlspecialchars($data[13], ENT_QUOTES, 'UTF-8'));
                    ?>
                    <ul>
                        <?php foreach ($examList as $item): 
                            if ($item != "") {
                                echo "<li>$item</li>";
                            }
                            endforeach;
                        ?>
                    </ul>
                </td>
            </tr>
            <tr>
                <th>Media Employed</th>
                <td><?= htmlspecialchars($data[14], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th>
                    Assessments and Evaluation
                    <h5>
                    Click below for:
                    <ul>
                        <li><a href="question.php?code=<?= htmlspecialchars($data[0], ENT_QUOTES, 'UTF-8') ?>">example questions</a></li>
                        <li><a href="practice.php?code=<?= htmlspecialchars($data[0], ENT_QUOTES, 'UTF-8') ?>">practice instructions</a></li>
                        <li><a href="project.php?code=<?= htmlspecialchars($data[0], ENT_QUOTES, 'UTF-8') ?>">project base instructions</a></li>
                    </ul>
                    </h5>
                </th>
                <td>
                    <?php
                        $evalList = explode("\n", htmlspecialchars($data[15], ENT_QUOTES, 'UTF-8'));
                    ?>
                    <ul>
                        <?php foreach ($evalList as $item): 
                            if ($item != "") {
                                echo "<li>$item</li>";
                            }
                            endforeach;
                        ?>
                    </ul>
                </td>
            </tr>
            <tr>
                <th>Reading List</th>
                <td>
                    <?php
                        $readingList = explode("\n", htmlspecialchars($data[16], ENT_QUOTES, 'UTF-8'));
                    ?>
                    <ol>
                        <?php foreach ($readingList as $item): 
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
        <div class="warning">Data is not found!</div>
    <?php endif; ?>
</body>
</html>