<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Tabella Assegnazione - {{ $date }}</title>

<style>
    /* --- STILI DI BASE --- */
    .assignment-table-container {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 8.2pt;
        line-height: 1.1;
        color: #000;
        background-color: white;
        width: 100%;
    }

    .assignment-table-container table {
        width: 100%;
        border-collapse: collapse !important; /* Forza il collasso dei bordi */
        table-layout: fixed;
        border: 1px solid #000 !important;
    }

    .assignment-table-container th,
    .assignment-table-container td {
        border: 1px solid #000 !important; /* Bordo forzato per ogni cella */
        text-align: center;
        vertical-align: middle;
        padding: 1.5px 1px;
        font-size: 8.4pt;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .assignment-table-container th {
        font-weight: bold;
        border-bottom: 2px solid #000 !important; /* Header più marcato */
        background-color: #f3f4f6 !important;
    }

    /* ... resto dei tuoi stili (.slot, .voucher-text, ecc.) ... */
    .assignment-table-container .slot { width: 29px !important; height: 25px; }
    .assignment-table-container .voucher-text { display: block; font-size: 6.5pt; color: #444; line-height: 1; margin-top: -1px; }
    .assignment-table-container .row-even { background-color: #ffffff !important; }
    .assignment-table-container .row-odd { background-color: #fcfcfc !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .assignment-table-container .excluded { text-decoration: underline; text-decoration-thickness: 1.8pt; }
    .assignment-table-container .shared { font-weight: bold; color: #444; }
    .assignment-table-container .lic { width: 55px; font-weight: bold; background-color: #f9fafb !important; }
    .assignment-table-container .header-box { border-bottom: 1.5pt solid #000; padding-bottom: 3px; margin-bottom: 5px; width: 100%; }
    .assignment-table-container .empty { color: #ccc; font-size: 7pt; }

    @media print {
        html, body {
            visibility: hidden;
            height: auto !important;
            overflow: visible !important;
            margin: 0 !important;
            padding: 0 !important;
            background: white !important;
        }

        .assignment-table-container,
        .assignment-table-container * {
            visibility: visible !important;
        }

        .assignment-table-container {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            margin: 0 !important;
        }

        @page {
            size: A4 landscape;
            margin: 5mm;
        }
    }
</style>

</head>
<body>

{{-- NOTA: Rimosso onload e onafterprint perché gestiti dall'iframe genitore --}}
<div class="assignment-table-container">
    <div class="header-box">
        <span style="font-size: 13pt; font-weight: bold; text-transform: uppercase;">TABELLA ASSEGNAZIONE LAVORI</span>
        <span style="font-size: 8.5pt; font-weight: bold; float: right; margin-top: 4px;">
            Data: {{ $date }} — Operatore: {{ $generatedBy }}
        </span>
        <div style="clear: both;"></div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="lic">Lic.</th>
                @for($i = 1; $i <= config('app_settings.matrix.total_slots'); $i++)
                    <th class="slot">{{ $i }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach($matrix as $row)
                <tr class="{{ $loop->index % 2 == 0 ? 'row-even' : 'row-odd' }}">
                    <td class="lic">{{ $row['license_number'] }}</td>
                    @for($slot = 0; $slot < config('app_settings.matrix.total_slots'); $slot++)
                        @php
                            $work = $row['worksMap'][$slot] ?? null;
                            $isAgency   = $work && $work['value'] === 'A';
                            $isExcluded = $work && ($work['excluded'] ?? false);
                            $isShared   = $work && ($work['shared_from_first'] ?? false);
                            $voucher    = $work['voucher'] ?? null;
                        @endphp
                        <td class="slot">
                            @if($work)
                                <span class="{{ $isExcluded ? 'excluded' : '' }} {{ $isShared ? 'shared' : '' }}">
                                    @if($isAgency)
                                        {{ $work['agency_code'] ?? 'AG' }}
                                    @elseif($isShared)
                                        {{ strtoupper(!empty($voucher) ? \Illuminate\Support\Str::limit($voucher, 4, '') : $work['value']) }}
                                    @else
                                        {{ strtoupper($work['value']) }}
                                    @endif
                                </span>
                                @if($voucher)
                                    <span class="voucher-text">({{ \Illuminate\Support\Str::limit($voucher, 4, '') }})</span>
                                @endif
                            @else
                                <span class="empty">-</span>
                            @endif
                        </td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 5px; font-size: 7.5pt; width: 100%;">
        <div style="float: left; width: 70%;">
            <strong>Legenda:</strong>
            <u>Sottolineato</u> = Fisso alla licenza •
            <strong>Grassetto</strong> = {{ config('app_settings.labels.shared_from_first') }} •
            (Cod) = Voucher / Provenienza
        </div>
        <div style="float: right; width: 30%; text-align: right; color: #555;">
            Generato: {{ $generatedAt }}
        </div>
        <div style="clear: both;"></div>
    </div>
</div>

</body>
</html>
