<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Arus Kas</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .income { color: green; }
        .expense { color: red; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN ARUS KAS BULANAN</h2>
        <p>Periode: {{ $filters['start_date'] }} s/d {{ $filters['end_date'] }}</p>
    </div>

    <h3>Tren Bulanan</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Bulan</th>
                <th>Pemasukan</th>
                <th>Pengeluaran</th>
                <th>Net</th>
            </tr>
        </thead>
        <tbody>
            @foreach($monthlyData as $data)
            <tr>
                <td>{{ $data['month'] }}</td>
                <td class="text-right income">Rp {{ number_format($data['income'], 0, ',', '.') }}</td>
                <td class="text-right expense">Rp {{ number_format($data['expense'], 0, ',', '.') }}</td>
                <td class="text-right {{ $data['net'] >= 0 ? 'income' : 'expense' }}">
                    Rp {{ number_format($data['net'], 0, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 50px; text-align: right;">
        <p>Dicetak pada: {{ date('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
