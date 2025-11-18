<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Ripartizione Lavori - {{ $timestamp }}</title>
<style>
    body {
        font-family: "Calibri", Arial, sans-serif;
        font-size: 9.5pt;
        margin: 12mm 10mm;
        color: #000;
        line-height: 1.3;
    }
    h1 {
        font-size: 16pt;
        font-weight: bold;
        text-align: center;
        margin: 0 0 8px 0;
        border-bottom: 1.5pt solid #000;   /* più sottile */
        padding-bottom: 6px;
    }
    .header {
        text-align: center;
        font-size: 10pt;
        margin-bottom: 12px;
        font-weight: bold;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 8px;
        border: 1pt solid #999;            /* bordo esterno leggero */
    }
    th, td {
        border: 0.5pt solid #aaa;          /* bordi interni leggerissimi */
        padding: 6px 4px;
        text-align: center;
        font-size: 9.2pt;
        vertical-align: middle;
        background-color: transparent !important;
    }
    th {
        font-weight: bold;

        border-bottom: 1.5pt solid #000;   /* solo header più marcato */
    }
    .license {
        text-align: left;
        font-weight: bold;
        padding-left: 8px;
        width: 145px;
        border-left: 2pt solid #000;       /* bordo sinistro licenza più evidente */
    }
    .cash {
        font-weight: bold;
        font-size: 10.5pt;
    }
    .summary {
        font-weight: bold;
    }
    .slot {
        font-size: 8.8pt;
    }
    /* Lavoro fisso → grassetto + sottolineato */
    .excluded {
        font-weight: bold !important;
        text-decoration: underline !important;
    }
    /* Lavoro condiviso → asterisco */
    .sff::after {
        content: "*";
        font-weight: bold;
    }
    tfoot td {
        font-weight: bold;
        font-size: 10.5pt;
        background-color: transparent !important;
        border-top: 1.5pt solid #000;      /* totale con linea leggera ma visibile */
    }
    .footer-note {
        margin-top: 15px;
        font-size: 8.5pt;
        color: #000;
    }
</style>
</head>
<body>

    <h1>RIPARTIZIONE LAVORI GIORNALIERA</h1>
    <div class="header">
        {{ $timestamp }} — Costo bancale: € {{ number_format($bancaleCost, 2) }}
        @if(isset($bancaleName))
            — Bancale in servizio: {{ $bancaleName }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th class="license">Licenza / Operatore</th>
                <th class="cash">Contanti Dovuti</th>
                <th class="summary">N</th>
                <th class="summary">P</th>
                @for($i = 1; $i <= 25; $i++)
                    <th style="font-size:8.5pt;">{{ $i }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach($splitTable as $row)
                <tr>
                    <td class="license">
                        {{ $row['license'] }} 
                        <small style="color:#555;font-size:8pt;">{{ $row['user_name'] }}</small>
                    </td>
                    <td class="cash">€ {{ number_format($row['cash_due'], 2) }}</td>
                    <td class="summary">{{ $row['n_count'] }}</td>
                    <td class="summary">{{ $row['p_count'] }}</td>

                    @for($s = 1; $s <= 25; $s++)
                        @php
                            $work = $row['assignments'][$s] ?? null;
                            $isMain = $work && ($work->slot ?? null) === $s;
                        @endphp

                        @if($isMain)
                            <td colspan="{{ $work->slots_occupied }}"
                                class="slot {{ $work->value }}
                                       {{ ($work->excluded ?? false) ? 'excluded' : '' }}
                                       {{ ($work->shared_from_first ?? false) ? 'sff' : '' }}">
                                @if($work->value === 'A')
                                    <strong>{{ $work->agency->code ?? 'AG' }}</strong>
                                @else
                                    {{ $work->value }}
                                @endif
                            </td>
                        @elseif(!$work)
                            <td></td>
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

    <div class="footer-note">
        <strong>Legenda:</strong>
        • Codice in grassetto = Lavoro Agenzia
        • Sottolineato = Lavoro fisso (escluso da ripartizione)
        • * = Lavoro ripartito dal primo turno
        • N = Nolo
        • X = Contanti
        • P = Perdi Volta

    </div>

</body>
</html>