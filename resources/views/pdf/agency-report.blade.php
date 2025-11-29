<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Report Agenzie - {{ $date }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; margin: 15mm; line-height: 1.5; }
        h1 { text-align: center; font-size: 18pt; font-weight: bold; border-bottom: 2pt solid #000; padding-bottom: 8px; margin-bottom: 15px; }
        .header { text-align: center; font-weight: bold; margin-bottom: 25px; font-size: 12pt; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #000; color: white; padding: 12px 10px; text-align: center; font-weight: bold; }
        td { border: 1pt solid #333; padding: 12px 10px; vertical-align: top; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .agency { font-weight: bold; font-size: 13pt; }
        .times  { text-align: center; font-weight: bold; font-size: 13pt; color: #c00; background: #fff8f8; }
        .licenses { font-family: "Courier New", monospace; font-weight: bold; font-size: 14pt; line-height: 1.8; }
        .total { margin-top: 30px; padding: 15px; border-top: 4pt double #000; text-align: center; font-size: 18pt; font-weight: bold; }
        .footer { margin-top: 40px; padding-top: 10px; border-top: 1pt solid #999; text-align: center; font-size: 10pt; color: #555; }
    </style>
</head>
<body>

    <h1>REPORT AGENZIE</h1>
    <div class="header">
        Servizi svolti il <strong>{{ $date }}</strong><br>
        Generato il {{ $generatedAt }} da {{ $generatedBy }}
    </div>

    <table>
        <thead>
            <tr>
                <th width="38%">Agenzia e Voucher</th>
                <th width="14%">Ora servizio</th>
                <th width="48%">Licenze assegnate</th>
            </tr>
        </thead>
        <tbody>
            @forelse($agencyReport as $item)
                <tr>
                    <td class="agency">{{ $item['agency_display'] }}</td>
                    <td class="times">{{ $item['times'] }}</td>
                    <td class="licenses">{{ $item['licenses'] ?: '–' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align:center; padding:50px; font-weight:bold; color:#900; font-size:14pt;">
                        Nessun servizio agenzia trovato per questa data.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(!empty($agencyReport))
        <div class="total">
            TOTALE LICENZE IMPEGNATE IN SERVIZI AGENZIA: {{ collect($agencyReport)->sum('count') }}
        </div>
    @endif

    <div class="footer">
        Generato da: {{ $generatedBy }} il {{ $generatedAt }}
    </div>

</body>
</html>