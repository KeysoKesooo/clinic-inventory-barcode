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
        background: #f8f9fa;
    }

    .scanner-container {
        max-width: 600px;
        margin: auto;
        margin-top: 50px;
        padding: 20px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    #reader {
        width: 100%;
        max-width: 450px;
        margin: auto;
    }

    .product-card {
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 15px;
        margin-top: 15px;
        background: #fdfdfd;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        text-align: center;
    }

    .product-card img {
        max-width: 200px;
        margin-bottom: 10px;
        border-radius: 10px;
    }
    </style>
</head>

<body>
    <div class="scanner-container text-center">
        <h3 class="mb-3">📷 Scan Product Barcode</h3>
        <p class="text-muted">Point your camera at the barcode to record.</p>

        <div id="reader"></div>

        <!-- Form will appear here after scan -->
        <div id="sale-form-container"></div>

        <button id="start-scan" class="btn btn-primary mt-3">Start Scanning</button>
        <button id="stop-scan" class="btn btn-danger mt-3">Stop Scanning</button>
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
            $("#sale-form-container").html('<div class="text-muted mt-2">Scanner is running...</div>');
        }).catch(err => {
            $("#sale-form-container").html('<div class="text-danger">Error: ' + err + '</div>');
        });
    }

    function stopScanner() {
        if (isScanning && html5QrCode) {
            html5QrCode.stop().then(() => {
                isScanning = false;
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
            $('#sale-form-container').html('<div class="text-danger">Error: ' + xhr.responseText + '</div>');
        });
    }

    $("#start-scan").click(startScanner);
    $("#stop-scan").click(stopScanner);

    // Auto start
    startScanner();
    </script>
</body>

</html>