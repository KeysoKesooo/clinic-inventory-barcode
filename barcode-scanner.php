<?php
  require_once('includes/load.php');
  page_require_level(3);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode Sales Scanner</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- QR Scanner -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
    body {
        font-family: Arial, sans-serif;
        background: #f0f2f5;
    }

    .scanner-container {
        max-width: 650px;
        margin: auto;
        margin-top: 60px;
        padding: 25px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.12);
    }

    #reader {
        width: 100%;
        max-width: 450px;
        margin: 20px auto;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .btn-custom {
        width: 48%;
    }

    .back-btn {
        position: absolute;
        top: 20px;
        left: 20px;
    }
    </style>
</head>

<body>
    <!-- Back Button -->
    <a href="product.php" class="btn btn-outline-secondary back-btn">
        ← Back
    </a>

    <div class="scanner-container text-center">
        <h3 class="mb-3">📷 Scan Product Barcode</h3>
        <p class="text-muted">Point your camera at the barcode to record a sale.</p>

        <div id="reader"></div>

        <!-- Form will appear here after scan -->
        <div id="sale-form-container" class="mt-3"></div>

        <div class="d-flex justify-content-between mt-4">
            <button id="start-scan" class="btn btn-primary btn-custom">Start Scanning</button>
            <button id="stop-scan" class="btn btn-danger btn-custom">Stop Scanning</button>
        </div>
    </div>

    <!-- Success sound -->
    <audio id="scanSound" src="ding.mp3" preload="auto"></audio>

    <script>
    let html5QrCode;
    let isScanning = false;

    function startScanner() {
        html5QrCode = new Html5Qrcode("reader");
        html5QrCode.start({
                facingMode: "environment"
            }, {
                fps: 10,
                qrbox: {
                    width: 300,
                    height: 300
                }
            },
            onScanSuccess
        ).then(() => {
            isScanning = true;
            $("#sale-form-container").html('<div class="text-muted mt-2">✅ Scanner is running...</div>');
        }).catch(err => {
            $("#sale-form-container").html('<div class="text-danger">❌ Error: ' + err + '</div>');
        });
    }

    function stopScanner() {
        if (isScanning && html5QrCode) {
            html5QrCode.stop().then(() => {
                isScanning = false;
                $("#sale-form-container").html('<div class="text-warning mt-2">⏹️ Scanner stopped.</div>');
            });
        }
    }

    function onScanSuccess(decodedText) {
        stopScanner();
        document.getElementById("scanSound").play();

        // Fetch product info and show sale form
        $.post("fetch_product.php", {
            product_id: decodedText
        }, function(response) {
            $('#sale-form-container').html(response);
        }).fail(function(xhr) {
            $('#sale-form-container').html('<div class="text-danger">❌ Error: ' + xhr.responseText + '</div>');
        });
    }

    $("#start-scan").click(startScanner);
    $("#stop-scan").click(stopScanner);

    // Auto start scanner on page load
    startScanner();
    </script>
</body>

</html>