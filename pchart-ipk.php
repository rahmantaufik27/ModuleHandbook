<?php
require 'vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;

function getClient($pathToCredentials) {
    $client = new Client();
    $client->setApplicationName('Google Sheets API PHP');
    $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
    $client->setAuthConfig($pathToCredentials);
    return $client;
}

function getSpreadsheetData($service, $spreadsheetId, $range) {
    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    return $response->getValues();
}

function processData($values, $npmFilter = null) {
    $data = [];
    $ipk_distribution = [
        ['range' => '(2, 2.2)', 'min' => 2.0, 'max' => 2.2, 'count' => 0],
        ['range' => '(2.2, 2.4)', 'min' => 2.2, 'max' => 2.4, 'count' => 0],
        ['range' => '(2.4, 2.6)', 'min' => 2.4, 'max' => 2.6, 'count' => 0],
        ['range' => '(2.6, 2.8)', 'min' => 2.6, 'max' => 2.8, 'count' => 0],
        ['range' => '(2.8, 3)', 'min' => 2.8, 'max' => 3.0, 'count' => 0],
        ['range' => '(3, 3.2)', 'min' => 3.0, 'max' => 3.2, 'count' => 0],
        ['range' => '(3.2, 3.4)', 'min' => 3.2, 'max' => 3.4, 'count' => 0],
        ['range' => '(3.4, 3.6)', 'min' => 3.4, 'max' => 3.6, 'count' => 0],
        ['range' => '(3.6, 3.8)', 'min' => 3.6, 'max' => 3.8, 'count' => 0],
        ['range' => '(3.8, 4)', 'min' => 3.8, 'max' => 4.0, 'count' => 0],
    ];

    $ipk_npm = null;

    if (!empty($values)) {
        foreach ($values as $row) {
            $ipk = (float)$row[9]; // Assumes IPK is in the 10th column (index 9)
            $data[] = [
                'npm' => $row[1],
                'nama' => $row[2],
                'ipk' => $ipk,
                'sks' => $row[18],
                'toefl' => $row[19],
                'lama_studi' => $row[17]
            ];

            // Update IPK distribution count
            foreach ($ipk_distribution as &$range) {
                if ($ipk > $range['min'] && $ipk <= $range['max']) {
                    $range['count']++;
                    break;
                }
            }

            // Get IPK for the specified NPM
            if ($npmFilter !== null && $npmFilter === $row[1]) {
                $ipk_npm = $ipk;
            }
        }
    }

    return [$data, $ipk_distribution, $ipk_npm];
}

function generateChart($ipk_distribution, $ipk_npm = null) {
    include("pChart2.1.4/class/pData.class.php");
    include("pChart2.1.4/class/pDraw.class.php");
    include("pChart2.1.4/class/pImage.class.php");

    // Create data object
    $MyData = new pData();

    // Extract ranges and counts
    $labels = array_column($ipk_distribution, 'range');
    $values = array_column($ipk_distribution, 'count');
    
    // Add points to the dataset
    $MyData->addPoints($values, "Frequency");
    $MyData->setSerieDescription("Frequency", "IPK Distribution");
    
    // Add labels to the x-axis
    $MyData->addPoints($labels, "Labels");
    $MyData->setAbscissa("Labels");

    // Create pChart object
    $myPicture = new pImage(1200, 800, $MyData);

    // Draw background and border
    $Settings = ["R"=>255, "G"=>255, "B"=>255, "Dash"=>1, "DashR"=>190, "DashG"=>203, "DashB"=>107];
    $myPicture->drawFilledRectangle(0, 0, 1200, 800, $Settings);

    $myPicture->drawRectangle(0, 0, 1199, 799, ["R"=>0, "G"=>0, "B"=>0]);

    // Draw the title
    $myPicture->setFontProperties(["FontName"=>"pChart2.1.4/fonts/Forgotte.ttf","FontSize"=>20]);
    $myPicture->drawText(600, 35, "Distribusi IPK / GPA Distribution", ["FontSize"=>20,"Align"=>TEXT_ALIGN_MIDDLEMIDDLE]);

    // Draw the scale and the chart
    $myPicture->setGraphArea(60, 60, 1150, 700);
    $myPicture->setFontProperties(["FontName"=>"pChart2.1.4/fonts/Forgotte.ttf","FontSize"=>12]);
    $myPicture->drawScale(["CycleBackground"=>TRUE]);
    $myPicture->drawBarChart(["DisplayValues"=>TRUE, "DisplayPos"=>LABEL_POS_INSIDE, "Surrounding"=>-30]);

    // Draw the IPK line if provided
    if ($ipk_npm !== null) {
        $index = null;
        foreach ($ipk_distribution as $i => $range) {
            if ($ipk_npm > $range['min'] && $ipk_npm <= $range['max']) {
                $index = $i;
                break;
            }
        }

        if ($index !== null) {
            $xPosition = 60 + ($index * (1090 / count($labels))); // Calculate x position of the line
            $myPicture->drawLine($xPosition, 60, $xPosition, 700, ["R"=>255, "G"=>0, "B"=>0, "Alpha"=>100, "Ticks"=>4]);
        }
    }

    // Render the picture to browser
    header('Content-Type: image/png');
    $myPicture->render(null); // Null parameter to output directly to the browser
}

$pathToCredentials = 'storied-precept-243308-adf6b0bb18cb.json';
$spreadsheetId = '119wjg7Z_jjZi0v7tmHpxOLZWaPhbzeV3k9I4daclJdI';
$range = 'Data Wisuda!A2:AF';  // Adjust range based on your sheet structure

$client = getClient($pathToCredentials);
$service = new Sheets($client);
$values = getSpreadsheetData($service, $spreadsheetId, $range);

$npmFilter = isset($_GET['npm']) ? $_GET['npm'] : null;
list($data, $ipk_distribution, $ipk_npm) = processData($values, $npmFilter);

if (isset($_GET['chart']) && $_GET['chart'] === 'true') {
    generateChart($ipk_distribution, $ipk_npm);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="style.css" rel="stylesheet">
    <title>Data Mahasiswa</title>
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

    <h2>Distribusi IPK / GPA Distribution</h2>

    <?php
        $src = (!empty($_GET['npm'])) ? ("?chart=true&npm=" . htmlspecialchars($_GET['npm'])) : ("?chart=true");
    ?>
    <img src="<?php echo $src; ?>" alt="Grafik Distribusi IPK">

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Rentang IPK / GPA Range</th>
                <th>Frekuensi / Frequency</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ipk_distribution as $index => $range): ?>
                <tr>
                    <td><?php echo htmlspecialchars($index + 1); ?></td>
                    <td><?php echo htmlspecialchars($range['range']); ?></td>
                    <td><?php echo htmlspecialchars($range['count']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Detail</h2>
    <table>
        <thead>
            <tr>
                <th>NPM</th>
                <th>Nama / Name</th>
                <th>IPK / GPA</th>
                <th>SKS / Credit</th>
                <th>TOEFL</th>
                <th>Lama Studi (Tahun) / Length of Study (Year)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['npm']); ?></td>
                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                    <td><?php echo htmlspecialchars($row['ipk']); ?></td>
                    <td><?php echo htmlspecialchars($row['sks']); ?></td>
                    <td><?php echo htmlspecialchars($row['toefl']); ?></td>
                    <td><?php echo htmlspecialchars($row['lama_studi']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
