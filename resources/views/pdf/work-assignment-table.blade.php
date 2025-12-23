{{-- resources/views/pdf/work-assignment-table.blade.php --}}
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Tabella Assegnazione - {{ $date }}</title>
    <style>
        /* 1. Margini e setup pagina identici per coerenza */
        @page { margin: 5mm 5mm; size: A4 landscape; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.2pt;
            line-height: 1.1;
            margin: 0;
            color: #000;
        }

        /* Header compatto su una sola riga */
        .header-container {
            border-bottom: 1.5pt solid #000; 
            padding-bottom: 3px; 
            margin-bottom: 5px; 
            width: 100%;
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
            font-size: 8.2pt;
        }

        th {
            font-weight: bold;
            border-bottom: 1.5pt solid #000;
            padding: 3px 1px;
            background-color: #f3f4f6;
        }

        /* Alternanza colori righe (Zebra) */
        .row-even { background-color: #ffffff; }
        .row-odd { background-color: #fcfcfc; }

        .license-col {
            width: 80px; /* Ridotto leggermente per dare spazio agli slot */
            text-align: left !important;
            padding-left: 6px !important;
            font-weight: bold;
            background-color: #f9fafb !important;
        }

        /* Altezza slot ricalibrata a 25px come nell'altro documento */
        .slot {
            width: 29px !important;
            height: 25px;
            font-size: 8.4pt;
        }

        .voucher {
            font-size: 6.5pt;
            color: #444;
            display: block;
            line-height: 0.9;
            margin-top: -1px;
        }

        /* Badge compatti */
        .badge {
            display: inline-block;
            font-size: 6pt;
            font-weight: bold;
            padding: 0px 2px;
            border-radius: 3px;
            margin-left: 1px;
            border: 0.3pt solid #000;
            line-height: 1;
        }
        .F { background-color: #fee2e2; color: #991b1b; } /* Rosso leggero */
        .R { background-color: #fef9c3; color: #854d0e; } /* Giallo leggero */

        .empty { color: #ccc; }

        /* Footer unificato */
        .footer-container {
            margin-top: 5px; 
            padding-top: 3px; 
            font-size: 7.5pt; 
            width: 100%;
            border-top: 0.2pt solid #eee;
        }
    </style>
</head>
<body>

<div class="header-container">
    <span style="font-size: 13pt; font-weight: bold; text-transform: uppercase;">Tabella Assegnazione Lavori</span>
    <span style="font-size: 8.5pt; font-weight: bold; float: right; margin-top: 4px;">
        Data: <strong>{{ $date }}</strong> — Generato da: <strong>{{ $generatedBy }}</strong>
    </span>
    <div style="clear: both;"></div>
</div>

<table>
    <thead>
        <tr>
            <th class="license-col">Lic.</th>
            @for($i = 1; $i <= config('constants.matrix.total_slots'); $i++)
                <th style="width: 29px;">{{ $i }}</th>
            @endfor
        </tr>
    </thead>
    <tbody>
        @foreach($matrix as $row)
            @php $rowClass = $loop->index % 2 == 0 ? 'row-even' : 'row-odd'; @endphp
            <tr class="{{ $rowClass }}">
                <td class="license-col">{{ $row['license_number'] }}</td>
                @for($slot = 0; $slot <= 24; $slot++)
                    @php $work = $row['worksMap'][$slot] ?? null @endphp
                    <td class="slot">
                        @if($work)
                            <div style="line-height: 1;">
                                <span>
                                    @if($work['value'] === 'A')
                                        {{ $work['agency_code'] ?? 'A' }}
                                    @elseif($work['value'] === 'X')
                                        X
                                    @else
                                        {{ $work['value'] }}
                                    @endif
                                </span>

                                @if($work['excluded'] ?? false)
                                    <span class="badge F">F</span>
                                @endif
                                @if($work['shared_from_first'] ?? false)
                                    <span class="badge R">R</span>
                                @endif
                                
                                @if($work['voucher'] ?? false)
                                    <span class="voucher">({{ Str::limit($work['voucher'], 4, '') }})</span>
                                @endif
                            </div>
                        @else
                            <span class="empty">-</span>
                        @endif
                    </td>
                @endfor
            </tr>
        @endforeach
    </tbody>
</table>

<div class="footer-container">
    <div style="float: left; width: 60%;">
        <strong>Legenda:</strong> F = Fisso alla licenza • R = Ripartito dal 1° colonna • (Vouch) = Codice Voucher
    </div>
    <div style="float: right; width: 40%; text-align: right; color: #555;">
        Generato alle {{ $generatedAt }} — {{ $generatedBy }}
    </div>
    <div style="clear: both;"></div>
</div>

</body>
</html>