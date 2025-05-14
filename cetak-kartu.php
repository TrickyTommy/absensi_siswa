<?php
require 'vendor/autoload.php';
include 'koneksi.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM siswas WHERE id=$id");
$data = $result->fetch_assoc();

// Generate QR Code
$qr = new QrCode($data['nis']);
$writer = new PngWriter();
$result = $writer->write($qr);
$qrcode = base64_encode($result->getString());
?>

<!DOCTYPE html>
<html>
<head>
    <title>Print Kartu Siswa</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background:rgb(245, 245, 245);
            margin: 0;
            padding: 20px;
        }

        .page {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            flex-wrap: wrap;
            justify-content: space-around;
            page-break-after: always;
        }

        .card {
            width: 5.5cm;
            height: 8.5cm;
            padding: 5px;
            border-radius: 15px;
            margin: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
            flex: 0 0 calc(33.333% - 20px);
            background-image:url('assets/image/SMK.png');
            background-size: cover;
            background-position: center;
        }

        .student-info {
            width: 100%;
            display: grid;
            grid-template-columns: 60% 40%;
            grid-template-columns: 2;
            background: #05a5f82e;
        }

        .info-row {
            margin: 8px 0;
            align-items: center;
        }

        .info-row strong {
            width: 80px;
            color: #475569;
            font-size: 12px;
        }

        .info-row span {
            color: #0f172a;
            font-size: 10px;
            font-weight: 500;
            display: block;
        }

        .box {
            border: solid 2px black;
            width: 2cm;
            height: 3cm;
        }

        .qr {
            left: 40px;
            top: 147px;
            width: 150px;
        }

        .qr-code {
                        margin-top: -10px; /* Add negative margin to move text up */

            text-align: center;
            border-radius: 10px;
        }

        .qr-code img {
            max-width: 100px;
            height: auto;
        }

        .validity {
            position: relative;
            text-align: center;
            font-size: 7px;
            color: rgb(6 102 240);
            bottom: -1px;
        }

        .tittle {
            text-align: center;
        }

        .tittle h2 {
            margin: 5px 0;
            font-size: 16px;
        }

        .tittle h3 {
            margin: 5px 0;
            font-size: 12px;
        }

        .scan {
                        margin-top: -10px; /* Add negative margin to move text up */

            margin-bottom: 0px;
            text-align: center;
            font-size: 10px;
            font-weight: 500;
            color: rgb(6, 6, 6);
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .card {
                box-shadow: none;
                page-break-inside: avoid;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="card">
            <div class="tittle">
                <h2>KARTU PELAJAR</h2>
                <h3>SMK BUDI MULIA KARAWANG</h3>
            </div>
              
            <div class="student-info">
                <div class="photo">
                    <div class="box">
                    </div>
                </div>

                <div class="student">
                    <div class="info-row">
                        <span><?= htmlspecialchars($data['nama']) ?></span>
                        <span><?= htmlspecialchars($data['kelas']) ?></span>
                        <span><?= htmlspecialchars($data['jurusan']) ?></span>
                            <span>NISN/NIS : <?= htmlspecialchars($siswa['nisn']) ?></span>
                    </div>
                </div>
            </div>

            <div class="scan">
                <p>SCAN MEE !</p>
            </div>
                
            <div class="qr-code">
                <img src="data:image/png;base64,<?= $qrcode ?>" alt="QR Code">
            </div>

            <div class="validity">
                Kartu ini berlaku selama yang bersangkutan menjadi siswa<br>
                SMK BUDI MULIA KARAWANG
            </div>
        </div>
    </div>

    <div class="no-print" style="text-align: center;">
        <button onclick="window.print()">Print Kartu</button>
    </div>
</body>
</html>