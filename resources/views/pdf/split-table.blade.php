<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ripartizione Lavori {{ $timestamp }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; margin: 10mm; color: #000; }
        h1 { font-size: 16pt; text-align: center; margin-bottom: 10px; }
        .header { margin-bottom: 15px; text-align: center; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; text-align: center; }
        th { background-color: #e0e0e0; font-weight: bold; }
        .license { width: 140px; text-align: left; font-weight: bold; }
        .summary { background-color: #d0d0d0; font-weight: bold; }
        .slot { font-size: 8pt; }
        .A { background-color: #f0f0f0; }
        .X, .N, .P { background-color: #d0d0d0; }
        .excluded { background-color: #555; color: white; }
        .sff { border: 2px solid #000; }
        tfoot td { background-color: #bbb; font-weight: bold; font-size: 10pt; }
    </style>
</head>
<body>
    <h1>Ripartizione Lavori - {{ $timestamp }}</h1>
    <div class="header">Costo Bancale: € {{ number_format($bancaleCost, 2) }} - Bancale in servizio: {{ auth()->check() ? auth()->user()->name : 'N/A' }}</div>

    <table>
        <thead>
            <tr>
                <th class="license">Licenza</th>
                <th class="summary">Contanti (€)</th>
                <th class="summary">N</th>
                <th class="summary">P</th>
                @for($i=1; $i<=25; $i++)<th class="slot">{{ $i }}</th>@endfor
            </tr>
        </thead>
        <tbody>
            @foreach($splitTable as $row)
                <tr>
                    <td class="license">{{ $row['license'] }} ({{ $row['user_name'] }})</td>
                    <td class="summary">€ {{ number_format($row['cash_due'], 2) }}</td>
                    <td class="summary">{{ $row['n_count'] }}</td>
                    <td class="summary">{{ $row['p_count'] }}</td>
                    @for($s=1; $s<=25; $s++)
                        @php
                            $work = $row['assignments'][$s] ?? null;
                            $isMain = $work && ($work->slot ?? null) === $s;
                        @endphp
                        @if($isMain)
                            <td colspan="{{ $work->slots_occupied }}"
                                class="slot {{ $work->value }} {{ $work->excluded ?? false ? 'excluded' : '' }} {{ $work->shared_from_first ?? false ? 'sff' : '' }}">
                                {{ $work->value === 'A' ? ($work->agency->code ?? 'AG') : $work->value }}
                                {{ $work->shared_from_first ?? false ? '*' : '' }}
                            </td>
                        @elseif(!$work)
                            <td class="slot"></td>
                        @endif
                    @endfor
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td style="text-align:right; font-weight:bold;">TOTALE GENERALE</td>
                <td>€ {{ number_format(collect($splitTable)->sum('cash_due'), 2) }}</td>
                <td>{{ collect($splitTable)->sum('n_count') }}</td>
                <td>{{ collect($splitTable)->sum('p_count') }}</td>
                <td colspan="25"></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>