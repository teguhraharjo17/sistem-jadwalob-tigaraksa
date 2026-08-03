<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak (403)</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
            background: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            overflow: hidden;
        }

        .container {
            max-width: 500px;
            width: 90%;
            padding: 40px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            backdrop-filter: blur(16px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            animation: scaleIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        h1 {
            font-size: 2.2rem;
            font-weight: 800;
            margin-top: 15px;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 99%, #fecfef 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p {
            font-size: 1rem;
            line-height: 1.6;
            color: #94a3b8;
            margin-top: 0;
            margin-bottom: 30px;
            max-width: 400px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 28px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            font-weight: 600;
            text-decoration: none;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.3);
            transition: all 0.25s ease;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
        }

        .btn:active {
            transform: translateY(0);
        }

        .footer {
            margin-top: 35px;
            font-size: 0.75rem;
            color: #475569;
            letter-spacing: 0.2px;
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.92);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
    <script src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.9.4/dist/dotlottie-wc.js" type="module"></script>
</head>
<body>
    <div class="container">
        <!-- Lottie Animation -->
        <dotlottie-wc src="https://lottie.host/a01a5fcf-7285-464a-9068-3ce7b524b082/oZ5L4vOeOQ.lottie" style="width: 250px; height: 250px" autoplay loop></dotlottie-wc>
        
        <h1>Akses Ditolak</h1>
        <p>Maaf, Anda tidak memiliki izin untuk membuka halaman ini. Silakan hubungi Super Admin untuk mengajukan permohonan akses.</p>
        
        <a href="/dashboard" class="btn">Kembali ke Dashboard</a>
        
        <div class="footer">© {{ date('Y') }} PT. Milenia Mega Mandiri. All rights reserved.</div>
    </div>
</body>
</html>
