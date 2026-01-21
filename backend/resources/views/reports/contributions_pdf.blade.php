<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Iuran</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .summary { margin-bottom: 20px; width: 100%; border-collapse: collapse; }
        .summary td { padding: 5px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .status-paid { color: green; }
        .status-pending { color: orange; }
        .status-rejected { color: red; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN IURAN ANGGOTA</h2>
        <p>Periode: {{ $filters['start_date'] }} s/d {{ $filters['end_date'] }}</p>
    </div>

    <table class="summary">
        <tr>
            <td><strong>Total Dibayar:</strong></td>
            <td class="text-right status-paid">Rp {{ number_format($summary['totalPaid'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Total Pending:</strong></td>
            <td class="text-right status-pending">Rp {{ number_format($summary['totalPending'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Total Ditolak:</strong></td>
            <td class="text-right status-rejected">Rp {{ number_format($summary['totalRejected'], 0, ',', '.') }}</td>
        </tr>
        <tr style="border-top: 1px solid #000;">
            <td><strong>Total Seluruhnya:</strong></td>
            <td class="text-right"><strong>Rp {{ number_format($summary['total'], 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>Anggota</th>
                <th>Jenis Iuran</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($contributions as $c)
            <tr>
                <td>{{ $c->member->full_name }}</td>
                <td>{{ $c->type->name }}</td>
                <td>{{ $c->payment_date }}</td>
                <td class="status-{{ $c->status }}">
                    {{ ucfirst($c->status) }}
                </td>
                <td class="text-right">
                    Rp {{ number_format($c->amount, 0, ',', '.') }}
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
