<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Donasi</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .summary { margin-bottom: 20px; width: 100%; border-collapse: collapse; }
        .summary td { padding: 5px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .progress-bar { width: 100%; bg-color: #eee; height: 10px; border-radius: 5px; }
        .progress-fill { background-color: #4f46e5; height: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN CAMPAIGN DONASI</h2>
        <p>Status: {{ $filters['status'] ?: 'Semua' }}</p>
    </div>

    <table class="summary">
        <tr>
            <td><strong>Total Target:</strong></td>
            <td class="text-right">Rp {{ number_format($summary['totalTarget'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Total Terkumpul:</strong></td>
            <td class="text-right" style="color: green;">Rp {{ number_format($summary['totalCollected'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Total Sisa Target:</strong></td>
            <td class="text-right" style="color: red;">Rp {{ number_format($summary['totalRemaining'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Tingkat Ketercapaian:</strong></td>
            <td class="text-right"><strong>{{ number_format($summary['completionRate'], 1) }}%</strong></td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>Nama Campaign</th>
                <th>Status</th>
                <th>Target</th>
                <th>Terkumpul</th>
                <th>Progress</th>
            </tr>
        </thead>
        <tbody>
            @foreach($donations as $d)
            <tr>
                <td>{{ $d->program_name }}</td>
                <td>{{ ucfirst($d->status) }}</td>
                <td class="text-right">Rp {{ number_format($d->target_amount, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($d->collected_amount, 0, ',', '.') }}</td>
                <td>
                    {{ number_format(($d->collected_amount / $d->target_amount) * 100, 1) }}%
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
