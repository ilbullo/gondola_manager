{{-- resources/views/pdf/work-assignment-table.blade.php --}}
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Tabella Assegnazione - {{ $date }}</title>
    <style>
        :root {
        --total-slots: {{ config('constants.matrix.total_slots') }};
        }
        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            margin: 15mm;
            font-size: 9pt;
            color: #1f2937;
        }
        h1 {
            text-align: center;
            margin-bottom: 5mm;
            font-size: 16pt;
            font-weight: bold;
        }
        .info {
            text-align: center;
            margin-bottom: 12mm;
            color: #4b5563;
            font-size: 10pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10mm;
            table-layout: fixed; /* ← FORZA COLONNE DI LARGHEZZA UGUALE */
        }
        th, td {
            border: 1px solid #888;
            padding: 5px 2px;
            text-align: center;
            vertical-align: middle;
            height: 32px;
            font-size: 8.5pt;
        }
        th {
            background-color: #f3f4f6;
            font-weight: bold;
            font-size: 8pt;
            color: #374151;
        }
        /* Colonna Licenza fissa a sinistra */
        .license-col {
            width: 90px;
            text-align: left !important;
            padding-left: 10px;
            font-weight: bold;
            background-color: #f9fafb !important;
            position: sticky;
            left: 0;
            z-index: 10;
        }
        /* Tutte le 25 colonne slot hanno larghezza identica */
        .slot-col {
            width: calc(100% - 90px) / var(--total-slots); /* Distribuisce uniformemente lo spazio restante */
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
                        <td class="slot-col">
                            @if($work)
                                @if($work['value'] === 'A')
                                    <strong>{{ $work['agency_code'] ?? 'A' }}</strong>
                                    @if($work['voucher'] ?? false)
                                        <span class="voucher">({{ Str::limit($work['voucher'], 4, '') }})</span>
                                    @endif
                                @elseif($work['value'] === 'X')
                                    <strong>X</strong>
                                    @if($work['voucher'] ?? false)
                                        <span class="voucher">({{ Str::limit($work['voucher'], 4, '') }})</span>
                                    @endif
                                @else
                                    <strong>{{ $work['value'] }}</strong>
                                @endif

                                @if($work['excluded'] ?? false)
                                    <span class="badge F">F</span>
                                @endif
                                @if($work['shared_from_first'] ?? false)
                                    <span class="badge R">R</span>
                                @endif
                            @else
                                <span class="empty">–</span>
                            @endif
                        </td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>