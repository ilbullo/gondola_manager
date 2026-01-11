<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Ripartizione Lavori @date($date)</title>
    <style>
        @media print {
            @page {
                margin: 0;
                size: A4 landscape;
            }
            body {
                margin: 1cm; /* Margine fisico per la stampante */
                -webkit-print-color-adjust: exact;
            }
            .no-print { display: none; }
        }
        @page { margin: 5mm 5mm; size: A4 landscape; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.2pt;
            line-height: 1.1;
            margin: 0;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 0.4pt solid #000;
        }

        th, td {
            border: 0.4pt solid #000;
            text-align: center;
            vertical-align: middle;
            padding: 2px 1px;
            font-size: 8.2pt;
        }

        th {
            font-weight: bold;
            background-color: #f3f4f6;
            padding: 4px 1px;
        }

        .slot {
            width: 28px !important;
            height: 24px;
            font-size: 8pt;
        }

        .prev-lic-text {
            display: block;
            font-size: 6.5pt;
            color: #555;
            line-height: 1;
            margin-top: -1px;
            font-style: italic;
        }

        .row-even { background-color: #ffffff; }
        .row-odd { background-color: #f9fafb; }

        .excluded { text-decoration: underline; text-decoration-thickness: 1.5pt; }
        .shared   { font-weight: bold; }

        .lic  { width: 50px; font-weight: bold; background-color: #f3f4f6 !important; }
        .cash { width: 75px; font-weight: bold; }
        .np   { width: 28px; font-weight: bold; color: #333; }

        tfoot td {
            font-weight: bold;
            font-size: 8.5pt;
            border-top: 1.2pt solid #000;
            padding: 5px 1px;
            background-color: #eee;
        }

        .header-box {
            border-bottom: 1.5pt solid #000;
            padding-bottom: 4px;
            margin-bottom: 6px;
            width: 100%;
        }

        .legenda {
            margin-top: 6px;
            font-size: 7.5pt;
            width: 100%;
        }
    </style>
</head>
<body>

<div class="header-box">
    <span style="font-size: 14pt; font-weight: bold; text-transform: uppercase; letter-spacing: -0.5px;">Ripartizione Odierna</span>
    <span style="font-size: 9pt; font-weight: bold; float: right; margin-top: 5px;">
        Data: @date($date) — Bancale: @money($bancaleCost) — Operatore: {{ $generatedBy }}
    </span>
    <div style="clear: both;"></div>
</div>

<table>
    <thead>
        <tr>
            <th class="lic">Lic.</th>
            <th class="cash">Netto</th>
            <th class="np">N</th>
            <th class="np">X</th>
            <th class="np">U</th>
            <th class="np">P</th>
            @for($i = 1; $i <= config('app_settings.matrix.total_slots'); $i++)
                <th class="slot">{{ $i }}</th>
            @endfor
        </tr>
    </thead>
<tbody>
        @foreach($matrix as $row)
            <tr class="{{ $loop->even ? 'row-even' : 'row-odd' }}">
                <td class="lic">{{ $row['license_number'] }}</td>

                {{-- Usiamo netto_raw che viene passato dal metodo toPrintParams del DTO --}}
                <td class="cash">@money($row['netto_raw'] ?? 0, true, false)</td>

                {{-- Se usi toPrintParams, le chiavi dei conteggi sono piatti (n_count, x_count, etc) --}}
                <td class="np">@number($row['n_count'] ?? 0)</td>
                <td class="np">@number($row['x_count'] ?? 0)</td>
                <td class="np" style="color: #666;">@number($row['shared_ff'] ?? 0)</td>
                <td class="np">@number($row['p_count'] ?? 0)</td>

                @for($slot = 1; $slot <= config('app_settings.matrix.total_slots'); $slot++)
                    @php
                        $work = $row['worksMap'][$slot] ?? null;
                    @endphp
                    <td class="slot">
                        @if($work)
                            @php
                                $isAgency   = ($work['value'] ?? '') === 'A';
                                $isExcluded = $work['excluded'] ?? false;
                                $isShared   = $work['shared_from_first'] ?? false;
                                $prevLic    = $work['prev_license_number'] ?? null;
                            @endphp
                            <span class="{{ $isExcluded ? 'excluded' : '' }} {{ $isShared ? 'shared' : '' }}">
                                @if($isAgency)
                                    @trim($work['agency_code'] ?? 'AG', 4)
                                @elseif($isShared)
                                    @trim(!empty($work['voucher']) ? $work['voucher'] : $work['value'], 4)
                                @else
                                    {{ strtoupper($work['value']) }}
                                @endif
                            </span>
                            @if($prevLic)
                                <span class="prev-lic-text">(da:{{ $prevLic }})</span>
                            @endif
                        @endif
                    </td>
                @endfor
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td class="lic">TOT</td>
            <td class="cash">@money($totals['netto'] ?? 0, true, false)</td>
            <td class="np">@number($totals['n'] ?? 0)</td>
            <td class="np">@number($totals['x'] ?? 0)</td>
            <td class="np">@number($totals['shared'] ?? 0)</td>
            <td class="np">@number($totals['p'] ?? 0)</td>
            <td colspan="{{ config('app_settings.matrix.total_slots') }}"></td>
        </tr>
    </tfoot>
</table>

<div class="legenda">
    <div style="float: left; width: 75%;">
        <strong>LEGENDA:</strong>
        N: Noli • X: Contanti • U: {{ config('app_settings.labels.shared_from_first') }} • P: Perdi Volta •
        <u>Sottolineato</u>: Fisso Licenza •
        <strong>Grassetto</strong>: {{ config('app_settings.labels.shared_from_first') }}
    </div>
    <div style="float: right; width: 25%; text-align: right; color: #666; font-style: italic;">
        Generato il: @dateTime($generatedAt)
    </div>
    <div style="clear: both;"></div>
</div>

</body>
</html>
