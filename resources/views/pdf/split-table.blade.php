{{-- resources/views/pdf/split-table.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>Ripartizione {{ $timestamp }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #000; margin: 10mm; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; }
        th { background-color: #eee; font-weight: bold; }
        .license-col { width: 15%; text-align: left; }
        .summary-col { width: 8%; font-weight: bold; }
        .slot-cell { width: 2%; font-size: 8px; }
        /* Stili in BIANCO/NERO per i tipi di lavoro */
        .A { background-color: #ddd; }
        .X, .N, .P { background-color: #bbb; }
        .excluded { background-color: #666; color: #fff; font-weight: bold; }
        .sff { border: 2px solid #000; } /* Shared From First */
    </style>
</head>
<body>
    <h1>Tabella Ripartizione Lavori</h1>
    <p>Data: {{ $timestamp }} | Costo Bancale: € {{ number_format($bancaleCost, 2) }}</p>

    <table>
        <thead>
            <tr>
                <th class="license-col">Licenza / Operatore</th>
                <th class="summary-col">Cash Dovuto (€)</th>
                <th class="summary-col">Tot. N</th>
                <th class="summary-col">Tot. P</th>
                @for ($i = 1; $i <= 25; $i++)
                    <th class="slot-cell">{{ $i }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            {{-- Stampa i dati da $splitTable --}}
            @foreach ($splitTable as $row)
                <tr>
                    <td class="license-col">{{ $row['license'] }} ({{ $row['user_name'] }})</td>
                    <td class="summary-col">{{ number_format($row['cash_due'], 2) }}</td>
                    <td class="summary-col">{{ $row['n_count'] }}</td>
                    <td class="summary-col">{{ $row['p_count'] }}</td>

                    @for ($slot = 1; $slot <= 25; $slot++)
                        @php
                            $work = $row['assignments'][$slot] ?? null;
                            $isMainWork = $work && $work->slot === $slot;
                            $colSpan = $isMainWork ? $work->slots_occupied : 1;
                            $workValue = $work->value ?? '';
                            $classes = $workValue ? $workValue : '';
                            if ($work->excluded ?? false) $classes .= ' excluded';
                            if ($work->shared_from_first ?? false) $classes .= ' sff';
                        @endphp

                        @if ($isMainWork)
                            <td colspan="{{ $colSpan }}" class="slot-cell {{ $classes }}">
                                {{ $workValue }}@if($work->shared_from_first ?? false)*@endif
                            </td>
                        @elseif (!$work)
                            <td class="slot-cell"></td>
                        @endif
                    @endfor
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            @php
                $collection = collect($splitTable);
                $totalCashDue = $collection->sum('cash_due');
                $totalN = $collection->sum('n_count');
                $totalP = $collection->sum('p_count');
            @endphp
            <tr>
                <td colspan="4" style="text-align: right; font-size: 12px; font-weight: bold;">TOTALE GENERALE</td>
                <td class="summary-col" style="background-color: #999;">€ {{ number_format($totalCashDue, 2) }}</td>
                <td class="summary-col" style="background-color: #999;">{{ $totalN }}</td>
                <td class="summary-col" style="background-color: #999;">{{ $totalP }}</td>
                <td colspan="25" style="background-color: #999;"></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
