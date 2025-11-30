<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Report Agenzie - {{ $date }}</title>
    <style>
        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.32;
            margin: 13mm 12mm;
            color: #000;
        }
        h1 {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 0 0 4mm 0;
            padding-bottom: 3mm;
            border-bottom: 1pt solid #000;
        }
        .header {
            text-align: center;
            font-size: 10pt;
            margin: 4mm 0 6mm 0;
            line-height: 1.4;
        }
        .header strong { font-weight: bold; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3mm;
            border: 0.7pt solid #444;
            font-size: 10pt;
        }
        th {
            border-bottom: 1.4pt solid #000;
            padding: 5px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 10.8pt;
            background: transparent;
        }
        td {
            padding: 4.5px 6px;
            vertical-align: top;
            border-bottom: 0.5pt solid #ccc;
            border-right: 0.5pt solid #ddd;
        }
        td:last-child { border-right: none; }
        tr:last-child td { border-bottom: none; }

        .agency   { font-weight: normal; font-size: 10.5pt; }
        .voucher  { font-style: italic; }
        .time     { text-align: center; font-weight: normal; white-space: nowrap; font-size: 10.5pt; }
        .licenses { 
            font-weight: normal;
            font-size: 10.3pt;
            line-height: 1.38;
        }

        .total {
            margin-top: 9mm;
            padding-top: 6mm;
            border-top: 1.6pt solid #000;
            text-align: right;
            font-size: 11.5pt;
            font-weight: bold;
        }
        .footer {
            margin-top: 10mm;
            padding-top: 5mm;
            border-top: 0.6pt solid #aaa;
            text-align: center;
            font-size: 8.8pt;
            color: #555;
        }
    </style>
</head>
<body>

    <h1>REPORT SERVIZI AGENZIA</h1>

    <div class="header">
        <div><strong>Data servizio:</strong> {{ $date }}</div>
        <div><strong>Bancale:</strong> {{ $generatedBy }}</div>
        <div><strong>Generato il:</strong> {{ $generatedAt }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="11%">Orario</th>
                <th width="29%">Agenzia</th>
                <th width="19%">Voucher</th>
                <th width="41%">Licenze</th>
            </tr>
        </thead>
        <tbody>
            @forelse($agencyReport as $item)
                <tr>
                    <td class="time">{{ $item['time'] }}</td>
                    <td class="agency">{{ $item['agency_name'] }}</td>
                    <td class="voucher">{{ $item['voucher'] === '–' ? '—' : $item['voucher'] }}</td>
                    <td class="licenses">{{ $item['licenses'] ?: '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center; padding:12px; font-style:italic; color:#666;">
                        Nessun servizio agenzia assegnato
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(!empty($agencyReport))
        <div class="total">
            Totale barche impegnate: {{ collect($agencyReport)->sum('count') }}
        </div>
    @endif

    <div class="footer">
        Generato automaticamente dal sistema di ripartizione lavori<br>
        {{ $generatedBy }} — {{ $generatedAt }}
    </div>

</body>
</html>