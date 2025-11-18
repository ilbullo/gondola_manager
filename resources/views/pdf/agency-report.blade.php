<!-- resources/views/pdf/agency-report.blade.php -->
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Report Agenzie {{ today()->format('d/m/Y') }}</title>
    <style>
        body {
            font-family: Calibri, Arial, sans-serif;
            font-size: 10.5pt;               /* Font più piccolo */
            margin: 14mm 18mm 20mm 18mm;      /* Parte dall’alto, margine minimo sopra */
            color: #000;
            line-height: 1.35;
        }

        .title {
            font-size: 16pt;
            font-weight: bold;
            margin: 0 0 4px 0;
            text-align: center;
        }
        .subtitle {
            font-size: 11pt;
            margin: 0 0 12px 0;
            text-align: center;
            color: #444;
        }
        .meta {
            font-size: 10pt;
            margin-bottom: 14px;
            color: #444;
        }
        .meta strong { color: #000; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 10.5pt;
            border: 1pt solid #000;         /* Bordo esterno */
        }
        th {
            font-weight: bold;
            background-color: #f5f5f5;
            padding: 7px 9px;                 /* Altezza riga ridotta */
            border: 1px solid #000;
            font-size: 11pt;
        }
        td {
            padding: 6px 9px;                 /* Riga molto compatta */
            border: 1px solid #000;
            vertical-align: top;
        }

        /* Alternanza righe */
        tbody tr:nth-child(odd) td:not(.agency-cell) {
            background-color: #f9f9f9;
        }

        /* Agenzia con bordo sinistro più visibile */
        .agency-cell {
            font-weight: bold;
            font-size: 11pt;
            border-left: 4px solid #000;
            padding-left: 11px !important;
        }

        /* Larghezze colonne */
        col:nth-child(1) { width: 31%; }
        col:nth-child(2) { width: 13%; }
        col:nth-child(3) { width: 24%; }
        col:nth-child(4) { width: 32%; }

        .no-data {
            text-align: center;
            padding: 40px;
            font-size: 12pt;
            color: #666;
            font-style: italic;
            border: 1px dashed #aaa;
            margin: 30px 0;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9pt;
            color: #777;
            border-top: 1px solid #ccc;
            padding-top: 8px;
        }
    </style>
</head>
<body>

    <div class="title">Report Lavori Agenzie</div>
    <div class="subtitle">Data – {{ today()->format('d F Y') }}</div>

    <div class="meta">
        @if($bancaleUser ?? null)
            Bancale in servizio: <strong>{{ $bancaleUser }}</strong>
        @endif
    </div>

    @if(empty($agencyReport) || collect($agencyReport)->flatten(1)->isEmpty())
        <div class="no-data">Nessun servizio di tipo “A” assegnato nella giornata.</div>
    @else
        <table>
            <colgroup>
                <col><col><col><col>
            </colgroup>
            <thead>
                <tr>
                    <th>Agenzia</th>
                    <th>Orario</th>
                    <th>Note / Voucher</th>
                    <th>Licenze Assegnate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($agencyReport as $agencyName => $works)
                    @php
                        $works = collect($works)->sortBy('time');
                        $rowCount = $works->count();
                    @endphp

                    @foreach($works as $index => $work)
                        <tr>
                            @if($index === 0)
                                <td rowspan="{{ $rowCount }}" class="agency-cell">
                                    {{ $agencyName }}
                                </td>
                            @endif
                            <td>{{ $work['time'] }}</td>
                            <td>{{ $work['voucher'] === '-' ? '–' : $work['voucher'] }}</td>
                            <td>{{ $work['license_numbers'] }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Documento generato automaticamente — {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>