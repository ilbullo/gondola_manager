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
            text-transform: uppercase;
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
            padding: 6px;
            text-align: left;
            font-weight: bold;
            font-size: 10pt;
            background: #f9f9f9;
            text-transform: uppercase;
        }
        td {
            padding: 6px;
            vertical-align: middle;
            border-bottom: 0.5pt solid #ccc;
            border-right: 0.5pt solid #ddd;
        }
        td:last-child { border-right: none; }
        tr:last-child td { border-bottom: none; }

        .time     { text-align: center; font-weight: bold; width: 60px; }
        .agency   { font-weight: bold; color: #333; }
        .voucher  { font-style: italic; color: #555; }
        .licenses { 
            font-weight: bold;
            font-size: 11pt;
            letter-spacing: 0.5px;
        }

        .total-box {
            margin-top: 8mm;
            border-top: 1.5pt solid #000;
            padding-top: 4mm;
        }
        .total-row {
            text-align: right;
            font-size: 11pt;
            margin-bottom: 2mm;
        }
        .total-final {
            text-align: right;
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #777;
            border-top: 0.5pt solid #ccc;
            padding-top: 3mm;
        }
    </style>
</head>
<body>

    <h1>Report Servizi Agenzia</h1>

    <div class="header">
        <div><strong>Data Servizio:</strong> {{ $date }}</div>
        <div><strong>Operatore:</strong> {{ $generatedBy }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="12%">Orario</th>
                <th width="30%">Agenzia</th>
                <th width="23%">Voucher</th>
                <th width="35%">Licenze impegnate</th>
            </tr>
        </thead>
        <tbody>
            @forelse($agencyReport as $item)
                <tr>
                    <td class="time">{{ $item['time'] }}</td>
                    <td class="agency">{{ $item['agency_name'] }}</td>
                    <td class="voucher">{{ $item['voucher'] === '–' ? '—' : $item['voucher'] }}</td>
                    <td class="licenses">
                        {{-- Uniamo l'array delle licenze con un separatore chiaro --}}
                        {{ implode(' • ', $item['licenses']) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center; padding:20px; font-style:italic; color:#666;">
                        Nessun servizio agenzia registrato per la data odierna.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(!empty($agencyReport))
        <div class="total-box">
            <div class="total-row">
                Numero totale servizi: <strong>{{ count($agencyReport) }}</strong>
            </div>
            <div class="total-final">
                Totale barche impiegate: {{ collect($agencyReport)->sum('count') }}
            </div>
        </div>
    @endif

    <div class="footer">
        Generato dal sistema gestionale il {{ $generatedAt }}<br>
        Documento ad uso interno - {{ $generatedBy }}
    </div>

</body>
</html>