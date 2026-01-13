<style>
    /* Tutto isolato per la stampa */
    @media print {
        @page { 
            margin: 5mm 5mm; 
            size: A4 portrait; 
        }

        .agency-report-container {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.32;
            color: #000;
            background: white;
            width: 100%;
        }

        .agency-report-container h1 {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 0 0 4mm 0;
            padding-bottom: 3mm;
            border-bottom: 1pt solid #000;
            text-transform: uppercase;
        }

        .agency-report-container .header {
            text-align: center;
            font-size: 10pt;
            margin: 4mm 0 6mm 0;
            line-height: 1.4;
        }

        .agency-report-container table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3mm;
            border: 0.7pt solid #444;
        }

        .agency-report-container th {
            border-bottom: 1.4pt solid #000;
            padding: 6px;
            text-align: left;
            font-weight: bold;
            background: #f9f9f9 !important;
            -webkit-print-color-adjust: exact;
            text-transform: uppercase;
        }

        .agency-report-container td {
            padding: 6px;
            vertical-align: middle;
            border-bottom: 0.5pt solid #ccc;
            border-right: 0.5pt solid #ddd;
        }

        .agency-report-container .time { text-align: center; font-weight: bold; width: 60px; }
        .agency-report-container .agency { font-weight: bold; color: #333; }
        .agency-report-container .licenses { font-weight: bold; font-size: 11pt; }

        .agency-report-container .total-box {
            margin-top: 8mm;
            border-top: 1.5pt solid #000;
            padding-top: 4mm;
        }

        .agency-report-container .footer {
            margin-top: 10mm;
            text-align: center;
            font-size: 8pt;
            color: #777;
            border-top: 0.5pt solid #ccc;
            padding-top: 3mm;
        }
    }
</style>

<div class="agency-report-container">
    <h1>Report Servizi Agenzia</h1>

    <div class="header">
        <div><strong>Data Servizio:</strong> {{ $date }}</div>
        <div><strong>Operatore:</strong> {{ $generatedBy }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="12%">Orario</th>
                <th width="30%">Agenzia</th>
                <th width="23%">Voucher</th>
                <th width="35%">Licenze impegnate</th>
            </tr>
        </thead>
        <tbody>
            @forelse($agencyReport as $item)
                <tr>
                    <td class="time">{{ $item['time'] }}</td>
                    <td class="agency">{{ $item['agency_name'] }}</td>
                    <td class="voucher">{{ $item['voucher'] === '–' ? '—' : $item['voucher'] }}</td>
                    <td class="licenses">
                        {{ implode(' • ', $item['licenses']) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center; padding:20px; font-style:italic; color:#666;">
                        Nessun servizio agenzia registrato per la data odierna.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(!empty($agencyReport))
        <div class="total-box">
            <div style="text-align: right; font-size: 11pt; margin-bottom: 2mm;">
                Numero totale servizi: <strong>{{ count($agencyReport) }}</strong>
            </div>
            <div style="text-align: right; font-size: 13pt; font-weight: bold; text-transform: uppercase;">
                Totale barche impiegate: {{ collect($agencyReport)->sum('count') }}
            </div>
        </div>
    @endif

    <div class="footer">
        Generato dal sistema gestionale il {{ $generatedAt }}<br>
        Documento ad uso interno - {{ $generatedBy }}
    </div>
</div>