<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Keuangan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .summary { margin-bottom: 20px; width: 100%; border-collapse: collapse; }
        .summary td { padding: 5px; }
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
        <h2>LAPORAN KEUANGAN</h2>
        <p>Periode: {{ $filters['start_date'] }} s/d {{ $filters['end_date'] }}</p>
    </div>

    <table class="summary">
        <tr>
            <td><strong>Total Pemasukan:</strong></td>
            <td class="text-right income">Rp {{ number_format($summary['totalIncome'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Total Pengeluaran:</strong></td>
            <td class="text-right expense">Rp {{ number_format($summary['totalExpense'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Laba/Rugi Bersih:</strong></td>
            <td class="text-right {{ $summary['netIncome'] >= 0 ? 'income' : 'expense' }}">
                Rp {{ number_format($summary['netIncome'], 0, ',', '.') }}
            </td>
        </tr>
        <tr style="border-top: 1px solid #000;">
            <td><strong>Saldo Saat Ini:</strong></td>
            <td class="text-right"><strong>Rp {{ number_format($summary['currentBalance'], 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    <h3>Detail Transaksi</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Tipe</th>
                <th>Kategori</th>
                <th>Keterangan</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $t)
            <tr>
                <td>{{ $t->transaction_date }}</td>
                <td>{{ $t->type == 'in' ? 'Pemasukan' : 'Pengeluaran' }}</td>
                <td>{{ $t->category }}</td>
                <td>{{ $t->description }}</td>
                <td class="text-right {{ $t->type == 'in' ? 'income' : 'expense' }}">
                    Rp {{ number_format($t->amount, 0, ',', '.') }}
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
