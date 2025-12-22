<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Pagamento {{ $date }}</title>
<style>
    /* 1. Riduzione margini pagina: guadagniamo circa 4mm verticali */
    @page { margin: 5mm 5mm; size: A4 landscape; }
    
    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 8.2pt; /* Leggerissimo downgrade per compattezza */
        line-height: 1.1; /* Ridotto per evitare sprechi tra le righe */
        margin: 0;
        color: #000;
    }

    h1 {
        text-align: center;
        font-size: 13pt; /* Da 14pt a 13pt */
        font-weight: bold;
        margin: 0; /* Rimosso margine per recuperare spazio in alto */
        padding-bottom: 2px;
        border-bottom: 1.2pt solid #000;
    }

    .info {
        text-align: center;
        font-size: 8.5pt;
        font-weight: bold;
        margin-top: 2px;
        margin-bottom: 4px; /* Ridotto da 6px */
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
        padding: 1.5px 1px; /* Padding ridotto per abbassare l'altezza riga */
        font-size: 8.4pt;
    }

    th {
        font-weight: bold;
        border-bottom: 1.5pt solid #000;
        padding: 3px 1px;
        background-color: #f3f4f6;
    }

    /* 2. ALTEZZA SLOT: Ridotta a 26px (dai 32px originali) */
    /* 6px x 22 righe = 132px recuperati (ovvero circa 5-6 righe extra) */
    .slot {
        width: 29px !important;
        height: 25px; 
        font-size: 8.4pt;
    }

    /* Ottimizzazione testo "da:" per non spingere i bordi */
    .prev-lic-text {
        display: block;
        font-size: 6.8pt;
        color: #444;
        line-height: 1;
        margin-top: -1px;
    }

    /* Stile per l'alternanza colori righe */
    .row-even { background-color: #ffffff; }
    .row-odd { background-color: #fcfcfc; } /* Grigio quasi impercettibile per non pesare */

    .lic  { width: 62px; font-weight: bold; background-color: #f9fafb !important; }
    .cash { width: 75px; font-weight: bold; background-color: #f9fafb !important; }
    .np   { width: 32px; font-weight: bold; background-color: #f9fafb !important; }

    tfoot td {
        font-weight: bold;
        font-size: 8.5pt;
        border-top: 1.5pt solid #000;
        padding: 2px 1px;
    }

    .note {
        margin-top: 5px; /* Da 8px a 5px */
        font-size: 8pt;
    }

    footer {
        text-align: center;
        font-size: 7.5pt;
        margin-top: 2px;
    }
</style>
</head>
<body>

<div style="border-bottom: 1.5pt solid #000; padding-bottom: 3px; margin-bottom: 5px; width: 100%;">
    <span style="font-size: 13pt; font-weight: bold; text-transform: uppercase; margin-right: 15px;">PAGAMENTO LAVORI</span>
    <span style="font-size: 8.5pt; font-weight: bold; float: right; margin-top: 4px;">
        {{ $date }} — Costo Bancale € {{ number_format($bancaleCost, 2) }} — Servizio: <strong>{{ $generatedBy }}</strong>
    </span>
    <div style="clear: both;"></div>
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
                @php 
                    $netCash = $row['cash_total'] - $bancaleCost; 
                    // Alternanza classi
                    $rowClass = $loop->index % 2 == 0 ? 'row-even' : 'row-odd';
                @endphp
                <tr class="{{ $rowClass }}">
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
                                    <span class="prev-lic-text">
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

    <div style="margin-top: 5px; padding-top: 3px; font-size: 7.5pt; width: 100%;">
    <div style="float: left; width: 70%;">
        <strong>Legenda:</strong> Normale = lavoro • <strong>Grassetto</strong> = ufficio • <u>Sottolineato</u> = fisso licenza
    </div>
    <div style="float: right; width: 30%; text-align: right; color: #555;">
        Generato alle {{ $generatedAt }} da {{ $generatedBy }}
    </div>
    <div style="clear: both;"></div>
</div>
</body>
</html>
