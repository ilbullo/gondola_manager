<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report Agenzie {{ $timestamp }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; margin: 10mm; }
        h1 { font-size: 18pt; text-align: center; margin-bottom: 10px; }
        h2 { font-size: 14pt; background: #333; color: white; padding: 8px; margin: 25px 0 10px; }
        h3 { font-size: 11pt; background: #666; color: white; padding: 5px; margin: 15px 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #ddd; padding: 8px; text-align: left; font-weight: bold; }
        td { padding: 6px; border-bottom: 1px solid #ccc; vertical-align: top; }
        th, td { border: 1px solid #000; }
        .type { width: 60px; text-align: center; font-weight: bold; }
        .time { width: 70px; }
        .voucher { width: 180px; }
        .licenses { font-family: monospace; }
    </style>
</head>
<body>
    <h1>Report Lavori per Agenzia</h1>
    <p style="text-align:center; font-size:11pt;"><strong>Generato il:</strong> {{ $timestamp }}</p>

    @foreach($agencyReport as $agencyName => $voucherGroups)
        <h2>{{ $agencyName }}</h2>

        @foreach($voucherGroups as $voucherDisplay => $works)
    <h3>Voucher: {{ $voucherDisplay === 'Senza Voucher' ? 'Senza Voucher' : $voucherDisplay }}</h3>
    <table>
        <!-- ... stessa tabella di prima ... -->
        @foreach($works as $work)
            <tr>
                <td class="type">{{ $work['work_type'] }}</td>
                <td>{{ $work['time'] }}</td>
                <td>{{ $work['voucher'] }}</td>
                <td style="text-align:center">{{ $work['slots'] }}</td>
                <td class="licenses">{{ $work['license_numbers'] }}</td>
            </tr>
        @endforeach
    </table>
@endforeach
    @endforeach

    @if(empty($agencyReport))
        <p style="text-align:center; font-style:italic; color:#666; margin-top:50px;">
            Nessun lavoro trovato per oggi.
        </p>
    @endif
</body>
</html>