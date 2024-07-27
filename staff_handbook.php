<?php
require 'vendor/autoload.php';

function getStaffProfile($nidn) {
    $url = 'https://script.google.com/macros/s/AKfycbwxmS-Zz49ey-Ui2zzSx_KFOi3tZnlaxm6Ei02efXW6oapnLVKzjBuQpadYf_EOsfWs/exec?nidn=' . $nidn;
    $response = file_get_contents($url);

    if ($response === FALSE) {
        return null; // Handle the error as needed
    }

    $jsonData = json_decode($response, true);
    return $jsonData;
}

// Determine protocol (http or https)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';

// Header HTML dan link ke Bootstrap CSS
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <title>Staff Profile</title>
</head>
<body>
<div class="container mt-5">';

if (isset($_GET['nidn'])) {
    $nidn = $_GET['nidn'];
    $staffProfile = getStaffProfile($nidn);

    if ($staffProfile) {
        // Tabel Profil Staf
        echo '<h2>Profil Staf</h2>';
        echo '<table class="table table-bordered">';
        echo '<tr><th>Nama</th><td>' . $staffProfile['NAME'] . '</td></tr>';
        echo '<tr><th>NIDN</th><td>' . $staffProfile['NIDN'] . '</td></tr>';
        echo '<tr><th>Posisi</th><td>' . $staffProfile['POSITION'] . '</td></tr>';
        echo '<tr><th>Karir Akademik</th><td>' . $staffProfile['ACADEMIC CAREER'] . '</td></tr>';
        echo '<tr><th>Pendidikan</th><td>';
        echo '<strong>S1:</strong> ' . $staffProfile['Pendidikan S1'] . ' (Tahun: ' . $staffProfile['Tahun Lulus S1'] . ')<br>';
        echo '<strong>S2:</strong> ' . $staffProfile['Pendidikan S2'] . ' (Tahun: ' . $staffProfile['Tahun Lulus S2'] . ')<br>';
        echo '<strong>S3:</strong> ' . ($staffProfile['Pendidikan S3'] ? $staffProfile['Pendidikan S3'] : '-') . ' (Tahun: ' . ($staffProfile['Tahun Lulus S3'] ? $staffProfile['Tahun Lulus S3'] : '-') . ')';
        echo '</td></tr>';
        echo '</table>';

        // Tempatkan div untuk data SINTA dan Google Scholar
        echo '<div id="sinta-info"><h3>Informasi SINTA (ID: ' . $staffProfile['SINTA ID'] . ')</h3></div>';
        echo '<div id="gs-info"><h3>Informasi Google Scholar (ID: ' . $staffProfile['GS ID'] . ')</h3></div>';
    } else {
        echo '<div class="alert alert-danger">Profil tidak ditemukan untuk NIDN: ' . $nidn . '</div>';
    }
} else {
    echo '<div class="alert alert-danger">Parameter NIDN tidak ditemukan.</div>';
}

// Footer HTML
echo '</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var sintaId = "' . $staffProfile['SINTA ID'] . '";
        var gsId = "' . $staffProfile['GS ID'] . '";
        var protocol = "' . $protocol . '";
        var host = "' . $_SERVER['HTTP_HOST'] . '";

        if (sintaId) {
            fetchSintaData(sintaId, protocol, host);
        }

        if (gsId) {
            fetchGSData(gsId, protocol, host);
        }
    });

    function fetchSintaData(sid, protocol, host) {
        fetch(protocol + host + "/sinta_api.php?sid=" + sid + "&type=research")
            .then(response => response.json())
            .then(data => {
                var sintaInfo = document.getElementById("sinta-info");
                var researchHtml = "<h4>Penelitian</h4><table class=\'table table-striped\'><thead><tr><th>Judul</th><th>Ketua</th><th>Anggota</th><th>Sumber Dana</th><th>Tahun</th><th>Besar Dana</th></tr></thead><tbody>";
                data.forEach(item => {
                    researchHtml += "<tr><td>" + item.title + "</td><td>" + item.leader + "</td><td>" + item.members.join(", ") + "</td><td>" + item.funding_source + "</td><td>" + item.year + "</td><td>" + item.amount + "</td></tr>";
                });
                researchHtml += "</tbody></table>";
                sintaInfo.innerHTML += researchHtml;

                // Fetch IPR, Community Services, Scopus Articles, and Summary similarly
                fetch(protocol + host + "/sinta_api.php?sid=" + sid + "&type=ipr")
                    .then(response => response.json())
                    .then(data => {
                        var iprHtml = "<h4>IPR</h4><table class=\'table table-striped\'><thead><tr><th>Judul</th><th>Inventor</th><th>Tahun</th><th>Nomor Permohonan</th><th>Jenis IPR</th></tr></thead><tbody>";
                        data.forEach(item => {
                            iprHtml += "<tr><td>" + item.title + "</td><td>" + item.inventors + "</td><td>" + item.year + "</td><td>" + item.application_number + "</td><td>" + item.ipr_type + "</td></tr>";
                        });
                        iprHtml += "</tbody></table>";
                        sintaInfo.innerHTML += iprHtml;
                    });

                fetch(protocol + host + "/sinta_api.php?sid=" + sid + "&type=cs")
                    .then(response => response.json())
                    .then(data => {
                        var csHtml = "<h4>Layanan Masyarakat</h4><table class=\'table table-striped\'><thead><tr><th>Judul</th><th>Ketua</th><th>Anggota</th><th>Sumber Dana</th><th>Tahun</th><th>Besar Dana</th></tr></thead><tbody>";
                        data.forEach(item => {
                            csHtml += "<tr><td>" + item.title + "</td><td>" + item.leader + "</td><td>" + item.members.join(", ") + "</td><td>" + item.funding_source + "</td><td>" + item.year + "</td><td>" + item.amount + "</td></tr>";
                        });
                        csHtml += "</tbody></table>";
                        sintaInfo.innerHTML += csHtml;
                    });

                fetch(protocol + host + "/sinta_api.php?sid=" + sid + "&type=scopus")
                    .then(response => response.json())
                    .then(data => {
                        var scopusHtml = "<h4>Artikel Scopus</h4><table class=\'table table-striped\'><thead><tr><th>Judul</th><th>Urutan Penulis</th><th>Quartile</th><th>Penerbit</th><th>Tahun</th><th>Sitasi</th></tr></thead><tbody>";
                        data.forEach(item => {
                            scopusHtml += "<tr><td>" + item.title + "</td><td>" + item.author_order + "</td><td>" + item.quartile + "</td><td>" + item.publisher + "</td><td>" + item.year + "</td><td>" + item.cited + "</td></tr>";
                        });
                        scopusHtml += "</tbody></table>";
                        sintaInfo.innerHTML += scopusHtml;
                    });

                fetch(protocol + host + "/sinta_api.php?sid=" + sid + "&type=summary")
                    .then(response => response.json())
                    .then(data => {
                        var summaryHtml = "<h4>Ringkasan SINTA</h4><table class=\'table table-striped\'><thead><tr><th>Scopus</th><th>GScholar</th><th>WOS</th></tr></thead><tbody>";
                        summaryHtml += "<tr><td>Article: " + data.summary.Scopus.Article + "<br>Citation: " + data.summary.Scopus.Citation + "<br>Cited Document: " + data.summary.Scopus.Cited_Document + "<br>H-Index: " + data.summary.Scopus.H_Index + "<br>i10-Index: " + data.summary.Scopus.i10_Index + "<br>G-Index: " + data.summary.Scopus.G_Index + "</td>";
                        summaryHtml += "<td>Article: " + data.summary.GScholar.Article + "<br>Citation: " + data.summary.GScholar.Citation + "<br>Cited Document: " + data.summary.GScholar.Cited_Document + "<br>H-Index: " + data.summary.GScholar.H_Index + "<br>i10-Index: " + data.summary.GScholar.i10_Index + "<br>G-Index: " + data.summary.GScholar.G_Index + "</td>";
                        summaryHtml += "<td>Article: " + data.summary.WOS.Article + "<br>Citation: " + data.summary.WOS.Citation + "<br>Cited Document: " + data.summary.WOS.Cited_Document + "<br>H-Index: " + data.summary.WOS.H_Index + "<br>i10-Index: " + data.summary.WOS.i10_Index + "<br>G-Index: " + data.summary.WOS.G_Index + "</td>";
                        summaryHtml += "</tr></tbody></table>";
                        sintaInfo.innerHTML += summaryHtml;
                    });
            });
    }

    function fetchGSData(gsId, protocol, host) {
        fetch(protocol + host + "/gs_api.php?user=" + gsId + "&sortby=pubdate")
            .then(response => response.json())
            .then(data => {
                var gsInfo = document.getElementById("gs-info");
                var last5Html = "<h4>5 Publikasi Terakhir</h4><table class=\'table table-striped\'><thead><tr><th>Judul</th><th>Penulis</th><th>Penerbit</th><th>Tahun</th><th>Sitasi</th></tr></thead><tbody>";
                data.forEach(item => {
                    last5Html += "<tr><td>" + item.title + "</td><td>" + item.authors + "</td><td>" + item.publisher + "</td><td>" + item.year + "</td><td>" + item.cited + "</td></tr>";
                });
                last5Html += "</tbody></table>";
                gsInfo.innerHTML += last5Html;

                // Fetch Top Cited Publications similarly
                fetch(protocol + host + "/gs_api.php?user=" + gsId + "&sortby=citations")
                    .then(response => response.json())
                    .then(data => {
                        var topCitedHtml = "<h4>5 Publikasi dengan Sitasi Terbanyak</h4><table class=\'table table-striped\'><thead><tr><th>Judul</th><th>Penulis</th><th>Penerbit</th><th>Tahun</th><th>Sitasi</th></tr></thead><tbody>";
                        data.forEach(item => {
                            topCitedHtml += "<tr><td>" + item.title + "</td><td>" + item.authors + "</td><td>" + item.publisher + "</td><td>" + item.year + "</td><td>" + item.cited + "</td></tr>";
                        });
                        topCitedHtml += "</tbody></table>";
                        gsInfo.innerHTML += topCitedHtml;
                    });
            });
    }
</script>
</body>
</html>';
