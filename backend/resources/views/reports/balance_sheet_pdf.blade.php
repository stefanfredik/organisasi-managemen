<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Neraca</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .section-title { background-color: #f2f2f2; padding: 5px; font-weight: bold; margin-top: 15px; border: 1px solid #ddd; }
        .table { width: 100%; border-collapse: collapse; }
        .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; background-color: #fafafa; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN NERACA KEUANGAN</h2>
        <p>Per Tanggal: {{ $filters['as_of_date'] }}</p>
    </div>

    <div class="section-title">AKTIVA (ASET)</div>
    <table class="table">
        <tr>
            <td>Kas dan Setara Kas (Dompet)</td>
            <td class="text-right">Rp {{ number_format($assets['cash'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Piutang Iuran (Pending)</td>
            <td class="text-right">Rp {{ number_format($assets['receivables'], 0, ',', '.') }}</td>
        </tr>
        <tr class="total-row">
            <td>TOTAL AKTIVA</td>
            <td class="text-right">Rp {{ number_format($assets['total'], 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="section-title">PASIVA (KEWAJIBAN & EKUITAS)</div>
    <table class="table">
        <tr>
            <td colspan="2" style="font-style: italic;">Kewajiban</td>
        </tr>
        <tr>
            <td style="padding-left: 20px;">Komitmen Donasi Aktif</td>
            <td class="text-right">Rp {{ number_format($liabilities['donationCommitments'], 0, ',', '.') }}</td>
        </tr>
        <tr class="total-row">
            <td>Total Kewajiban</td>
            <td class="text-right">Rp {{ number_format($liabilities['total'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="2" style="font-style: italic;">Ekuitas</td>
        </tr>
        <tr>
            <td style="padding-left: 20px;">Saldo Laba Ditahan</td>
            <td class="text-right">Rp {{ number_format($equity['retainedEarnings'], 0, ',', '.') }}</td>
        </tr>
        <tr class="total-row">
            <td>TOTAL PASIVA</td>
            <td class="text-right">Rp {{ number_format($liabilities['total'] + $equity['total'], 0, ',', '.') }}</td>
        </tr>
    </table>

    <div style="margin-top: 50px; text-align: right;">
        <p>Dicetak pada: {{ date('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
