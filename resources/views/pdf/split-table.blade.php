<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Ripartizione Odierna - {{ $date }}</title>

<style>
    /* --- CONFIGURAZIONE PAGINA --- */
    @page {
        margin: 5mm 5mm;
        size: A4 landscape;
    }

    /* --- STILI DI BASE --- */
    .print-wrapper {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 8.2pt;
        line-height: 1.1;
        color: #000;
        background: white;
        width: 100%;
    }

    .print-wrapper table {
        width: 100%;
        border-collapse: collapse !important; /* Forza l'unione dei bordi */
        table-layout: fixed;
        border: 1px solid #000 !important; /* Bordo esterno marcato */
    }

    .print-wrapper th,
    .print-wrapper td {
        border: 1px solid #000 !important; /* Bordo celle forzato */
        text-align: center;
        vertical-align: middle;
        padding: 2px 1px;
        font-size: 8pt;
        /* Forza la visibilità sui browser moderni */
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .print-wrapper th {
        font-weight: bold;
        background-color: #f3f4f6 !important;
        padding: 4px 1px;
        border-bottom: 2px solid #000 !important;
    }

    .slot { width: 28px !important; height: 24px; }

    .prev-lic-text {
        display: block;
        font-size: 6.5pt;
        color: #555;
        line-height: 1;
        margin-top: -1px;
        font-style: italic;
    }

    .row-even { background-color: #ffffff !important; }
    .row-odd {
        background-color: #f9fafb !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .excluded { text-decoration: underline; text-decoration-thickness: 1.5pt; }
    .shared   { font-weight: bold; }

    .lic  { width: 45px; font-weight: bold; background-color: #f3f4f6 !important; }
    .cash { width: 70px; font-weight: bold; }
    .np   { width: 25px; font-weight: bold; }

    tfoot td {
        font-weight: bold;
        font-size: 8.5pt;
        border-top: 1.5pt solid #000 !important;
        padding: 5px 1px;
        background-color: #eeeeee !important;
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

    /* --- FIX SPECIFICO PER STAMPA BROWSER --- */
    @media print {
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>

</head>
<body>

<div class="print-wrapper">
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
                <th class="np" title="Noli">N</th>
                <th class="np" title="Contanti">X</th>
                <th class="np" title="Shared">U</th>
                <th class="np" title="Perdi Volta">P</th>
                @for($i = 1; $i <= config('app_settings.matrix.total_slots'); $i++)
                    <th class="slot">{{ $i }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach($matrix as $row)
                <tr class="{{ $loop->even ? 'row-even' : 'row-odd' }}">
                    <td class="lic">{{ $row['license_number'] }}</td>
                    <td class="cash">@money($row['netto_raw'] ?? 0, true, false)</td>
                    <td class="np">@number($row['n_count'] ?? 0)</td>
                    <td class="np">@number($row['x_count'] ?? 0)</td>
                    <td class="np" style="color: #666;">@number($row['shared_ff'] ?? 0)</td>
                    <td class="np">@number($row['p_count'] ?? 0)</td>

                    @for($slot = 1; $slot <= config('app_settings.matrix.total_slots'); $slot++)
                        @php $work = $row['worksMap'][$slot] ?? null; @endphp
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
                {{-- Cella vuota per gli slot --}}
                <td colspan="{{ config('app_settings.matrix.total_slots') }}"></td>
            </tr>
        </tfoot>
    </table>

    <div class="legenda">
        <div style="float: left; width: 75%;">
            <strong>LEGENDA:</strong>
            N: Noli • X: Contanti • U: Shared • P: Perdi Volta •
            <u>Sottolineato</u>: Fisso Licenza •
            <strong>Grassetto</strong>: Shared
        </div>
        <div style="float: right; width: 25%; text-align: right; color: #666; font-style: italic;">
            Generato il: @dateTime($generatedAt)
        </div>
        <div style="clear: both;"></div>
    </div>
</div>

</body>
</html>
