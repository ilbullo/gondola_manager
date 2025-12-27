{{-- resources/views/pdf/work-assignment-table.blade.php --}}
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Tabella Assegnazione - {{ $date }}</title>
<style>
    /* 1. Margini e setup pagina identici all'altro documento per coerenza di stampa */
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
        padding: 1.5px 1px;
        font-size: 8.4pt;
    }

    th {
        font-weight: bold;
        border-bottom: 1.5pt solid #000;
        padding: 3px 1px;
        background-color: #f3f4f6;
    }

    /* Dimensioni slot standardizzate 29x25px */
    .slot {
        width: 29px !important;
        height: 25px;
        font-size: 8.4pt;
    }

    /* Stile testo voucher o provenienza */
    .voucher-text {
        display: block;
        font-size: 6.5pt;
        color: #444;
        line-height: 1;
        margin-top: -1px;
    }

    .row-even { background-color: #ffffff; }
    .row-odd { background-color: #fcfcfc; }

    /* Stili logica di business */
    .excluded { text-decoration: underline; text-decoration-thickness: 1.8pt; }
    .shared   { font-weight: bold; color: #444; }

    /* Colonna Licenza coerente con l'altro PDF */
    .lic {
        width: 55px;
        font-weight: bold;
        background-color: #f9fafb !important;
    }

    .header-box {
        border-bottom: 1.5pt solid #000;
        padding-bottom: 3px;
        margin-bottom: 5px;
        width: 100%;
    }

    .empty { color: #ccc; font-size: 7pt; }
</style>
</head>
<body>

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
            @php
                $rowClass = $loop->index % 2 == 0 ? 'row-even' : 'row-odd';
            @endphp
            <tr class="{{ $rowClass }}">
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

</body>
</html>
