<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Pagamento {{ $date }}</title>
    <style>
        @page { margin: 7mm 5mm; size: A4 landscape; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.4pt;
            line-height: 1.25;
            margin: 0;
            color: #000;
        }
        h1 {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 0 0 4px 0;
            border-bottom: 1.5pt solid #000;
        }
        .info {
            text-align: center;
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 0.5pt solid #000;           /* bordino sottilissimo esterno */
        }
        th, td {
            border: 0.5pt solid #000;           /* bordi interni leggerissimi */
            text-align: center;
            vertical-align: middle;
            padding: 3px 1px;
            font-size: 8.6pt;
        }
        th {
            font-weight: bold;
            border-bottom: 2pt solid #000;
            padding: 4px 1px;
        }
        /* larghezze */
        .lic  { width: 62px; font-weight: bold;background-color: #f9fafb !important; }
        .cash { width: 78px; font-weight: bold; font-size: 8.75pt;background-color: #f9fafb !important; }
        .np   { width: 36px; font-weight: bold;background-color: #f9fafb !important; }
        .slot {
            width: 29px !important;
            height: 32px;
            font-size: 8.75pt;
            font-weight: normal;
        }
        .excluded { text-decoration: underline; text-decoration-thickness: 1.8pt; }
        .shared   { font-weight: bold; }

        tfoot td {
            font-weight: bold;
            font-size: 8.75pt;
            border-top: 2pt solid #000;
        }
        .note {
            margin-top: 8px;
            font-size: 8.6pt;
            line-height: 1.3;
        }
        footer {
            text-align:center;
        }
    </style>
</head>
<body>

    <h1>PAGAMENTO LAVORI</h1>
    <div class="info">
        {{ $date }} — Costo Bancale € {{ number_format($bancaleCost, 2) }} - Bancale in servizio <strong>{{ $generatedBy }}</strong>
    </div>

    <table>
        <thead>
            <tr>
                <th class="lic">Lic.</th>
                <th class="cash">Cash</th>
                <th class="np">N</th>
                <th class="np">P</th>
                @for($i = 1; $i <= config('constants.matrix.total_slots'); $i++)
                    <th class="slot">{{ $i }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach($matrix as $row)
                @php $netCash = $row['cash_total'] - $bancaleCost; @endphp
                <tr>
                    <td class="lic">{{ $row['license_number'] }}</td>
                    <td class="cash">€ {{ number_format($netCash, 0) }}</td>
                    <td class="np">{{ $row['n_count'] }}</td>
                    <td class="np">{{ $row['p_count'] }}</td>

                    @for($slot = 1; $slot <= config('constants.matrix.total_slots'); $slot++)
                        @php
                            $work = $row['worksMap'][$slot] ?? null;
                            $isAgency   = $work && $work['value'] === 'A';
                            $isExcluded = $work && ($work['excluded'] ?? false);
                            $isShared   = $work && ($work['shared_from_first'] ?? false);
                            $prevLicenseNumber = $work['prev_license_number'] ?? null;
                        @endphp
                        <td class="slot">
                            @if($work)
                                <span class="{{ $isExcluded ? 'excluded' : '' }} {{ $isShared ? 'shared' : '' }}">
                                    {{ $isAgency ? ($work['agency_code'] ?? 'AG') : strtoupper($work['value']) }}
                                </span>
                                @if($prevLicenseNumber)
                                    <br>
                                    <span style="font-size: 7.5pt; color: #555;">
                                        (da: {{ $prevLicenseNumber }})
                                    </span>
                                @endif
                            @endif
                        </td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Tot.</td>
                <td class="cash">€ {{ number_format($totalCash, 0) }}</td>
                <td class="np">{{ $totalN }}</td>
                <td class="np">{{ $totalP }}</td>
                <td colspan="25"></td>
            </tr>
        </tfoot>
    </table>

    <div class="note">
        <strong>Legenda:</strong>
        Normale = lavoro normale •
        <strong>Grassetto</strong> = ufficio •
        <u>Sottolineato</u> = lavoro fisso alla licenza
    </div>
    <footer>
       Generato alle {{ $generatedAt }} da {{ $generatedBy }}
    </footer>
</body>
</html>
