<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema CEAL - Check-in</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 24px;
            background-color: #f0f0f0;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        h1 {
            font-size: 36px;
            color: #2c5282;
            text-align: center;
        }
        .btn-checkin {
            display: block;
            width: 100%;
            padding: 30px;
            font-size: 32px;
            background-color: #48bb78;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            margin: 40px 0;
            transition: background-color 0.3s;
        }
        .btn-checkin:hover {
            background-color: #38a169;
        }
        .info-box {
            background-color: #ebf8ff;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 5px solid #4299e1;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Check-in CEAL</h1>

        <div class="info-box">
            <strong>Bem-vindo, {{ $patient->user->name }}!</strong><br>
            Hoje é {{ now()->format('d/m/Y') }}
        </div>

        <form action="{{ route('checkin.store') }}" method="POST">
            @csrf
            <button type="submit" class="btn-checkin">
                FAZER CHECK-IN
            </button>
        </form>

        <div style="text-align: center;">
            <a href="#" style="font-size: 20px; color: #4299e1;">Ver meus agendamentos</a>
        </div>
    </div>
</body>
</html>
