{{-- resources/views/pdf/work-assignment-table.blade.php --}}
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Tabella Assegnazione - {{ $date }}</title>
    <style>
        /* Aggiunto il margin e size da split-table.blade.php per coerenza in PDF */
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
        /* La classe slot è stata omessa, ma i valori di font e height sono stati mantenuti qui */
        .slot {
            width: 29px !important;
            height: 32px;
            font-size: 9.4pt; /* font-size da slot in split-table */
            font-weight: normal;
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
        /* Colonna Licenza: mantenuto lo stile di work-assignment-table.blade.php */
        .license-col {
            width: 90px;
            text-align: left !important;
            padding-left: 10px;
            font-weight: bold;
            background-color: #f9fafb !important;
            /* Rimosse position: sticky e z-index: 10, non utili per la stampa PDF */
        }
        /* Tutte le 25 colonne slot: la larghezza si adatterà */
        .slot-col {
            width: 29px !important; /* Larghezza fissata a 29px per tutti gli slot, come in split-table */
            font-size: 8pt;
        }

        .voucher {
            font-size: 7pt;
            color: #6b7280;
            display: block;
            line-height: 1;
            margin-top: 1px;
        }
        .badge {
            display: inline-block;
            font-size: 6.5pt;
            font-weight: bold;
            padding: 1px 5px;
            border-radius: 999px;
            margin-left: 3px;
            border: 1px solid #666;
        }
        .F {
            background-color: #f0f0f0;
            color: #333;
        }
        .R {
            background-color: #e0e0e0;
            color: #000;
        }
        .empty {
            color: #bbb;
        }
        /* Non serve tfoot o note, ma aggiungo footer per coerenza */
        footer {
            text-align:center;
            margin-top: 8px; /* Aggiunto un piccolo margine per separare */
            font-size: 8.4pt; /* Allineato al body font size */
        }
    </style>
</head>
<body>
    <h1>Tabella Assegnazione Lavori</h1>
    <div class="info">
        Data: <strong>{{ $date }}</strong> •
        Generato da: <strong>{{ $generatedBy }}</strong> •
        {{ $generatedAt }}
    </div>

    <table>
        <thead>
            <tr>
                <th class="license-col">Lic.</th>
                @for($i = 1; $i <= config('constants.matrix.total_slots'); $i++)
                    <th class="slot-col">{{ $i }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach($matrix as $row)
                <tr>
                    <td class="license-col">{{ $row['license_number'] }}</td>
                    @for($slot = 0; $slot <= 24; $slot++)
                        @php $work = $row['worksMap'][$slot] ?? null @endphp
                        <td class="slot">
                            @if($work)
                                @if($work['value'] === 'A')
                                    {{ $work['agency_code'] ?? 'A' }}
                                    @if($work['voucher'] ?? false)
                                        <span class="voucher">({{ Str::limit($work['voucher'], 4, '') }})</span>
                                    @endif
                                @elseif($work['value'] === 'X')
                                    X
                                    @if($work['voucher'] ?? false)
                                        <span class="voucher">({{ Str::limit($work['voucher'], 4, '') }})</span>
                                    @endif
                                @else
                                    {{ $work['value'] }}
                                @endif

                                @if($work['excluded'] ?? false)
                                    <span class="badge F">F</span>
                                @endif
                                @if($work['shared_from_first'] ?? false)
                                    <span class="badge R">R</span>
                                @endif
                            @else
                                <span class="empty">&nbsp;</span>
                            @endif
                        </td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>
    <footer>
       Generato alle {{ $generatedAt }} da {{ $generatedBy }}
    </footer>
</body>
</html>
