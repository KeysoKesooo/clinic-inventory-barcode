<?php
  require_once('includes/load.php');
  page_require_level(3);
?>
<?php include_once('layouts/header.php'); ?>

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
        max-width: 700px;
        margin: auto;
        padding: 25px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.12);
        position: relative;
    }

    #sale-form-container img {
        max-width: 30%;
        max-height: 20%;
        object-fit: contain;
        display: block;
        margin: 10px auto;
    }

    #reader {
        width: 100%;
        max-width: 100%;
        margin: 20px auto;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        position: relative;
    }

    /* Scanner overlay strip */
    .scanner-overlay {
        position: absolute;
        top: 30%;
        /* shifted upward */
        left: 50%;
        width: 80%;
        height: 100px;
        transform: translate(-50%, -50%);
        border: 3px solid #00c853;
        background: rgba(0, 200, 83, 0.1);
        border-radius: 8px;
        pointer-events: none;
        z-index: 2;
    }
    </style>
</head>

<body>

    <div class="scanner-container text-center">
        <h3 class="mb-3">📷 Scan Medicine Barcode</h3>
        <p class="text-muted">Point your camera at the barcode to record a medicine.</p>

        <div id="reader">
            <!-- Overlay strip -->
            <div class="scanner-overlay"></div>
        </div>

        <!-- Form will appear here after scan -->
        <div id="sale-form-container" class="mt-3"></div>
    </div>

    <!-- Success sound -->
    <audio id="scanSound" src="ding.mp3" preload="auto"></audio>

    <script>
    let html5QrCode;
    let isScanning = false;

    function startScanner() {
        html5QrCode = new Html5Qrcode("reader");

        // 🟢 OVERRIDE scan region calculation
        html5QrCode._qrRegion = function(viewfinderWidth, viewfinderHeight) {
            let boxWidth = 200; // width of scan box
            let boxHeight = 100; // height of scan box

            return {
                x: (viewfinderWidth - boxWidth) / 2,
                y: (viewfinderHeight * 0.1) - (boxHeight / 2), // 30% from top
                width: boxWidth,
                height: boxHeight
            };
        };

        html5QrCode.start({
                facingMode: "environment"
            }, {
                fps: 10,
                qrbox: {
                    width: 500,
                    height: 100
                }, // still required for size
                experimentalFeatures: {
                    useBarCodeDetectorIfSupported: true
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

    // Auto start scanner on page load
    startScanner();
    </script>
</body>
<?php include_once('layouts/footer.php'); ?>

</html>