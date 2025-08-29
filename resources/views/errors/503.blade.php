<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Situs Dalam Pemeliharaan</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2c3e50, #3498db);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
        }

        .container {
            max-width: 600px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            box-shadow: 0 0 30px rgba(0,0,0,0.2);
            animation: fadeIn 1.5s ease;
        }

        h1 {
            font-size: 3em;
            margin-bottom: 0.5em;
            color: #ffffff;
        }

        p {
            font-size: 1.2em;
            margin-bottom: 1.5em;
            color: #e0e0e0;
        }

        .gear {
            font-size: 3em;
            animation: spin 4s linear infinite;
            display: inline-block;
            margin-bottom: 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .footer {
            font-size: 0.9em;
            color: #aaa;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="gear">⚙️</div>
        <h1>Situs Sedang Dalam Pemeliharaan</h1>
        <p>Kami sedang melakukan perbaikan sistem untuk pengalaman yang lebih baik.<br>Silakan kembali beberapa saat lagi.</p>
        <div class="footer">© {{ date('Y') }} PT. Milenia Mega Mandiri. All rights reserved.</div>
    </div>
</body>
</html>
