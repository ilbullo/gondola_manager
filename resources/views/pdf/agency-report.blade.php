<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Report Servizi Agenzia - {{ $date }}</title>

<style>
    /* --- CONFIGURAZIONE PAGINA --- */
    @page {
        margin: 8mm; /* Leggermente più margine per il formato portrait */
        size: A4 portrait;
    }

    /* --- STILI DI BASE --- */
    .agency-report-container {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 10pt;
        line-height: 1.32;
        color: #000;
        background: white;
        width: 100%;
        margin: 0;
        padding: 0;
    }

    .agency-report-container h1 {
        text-align: center;
        font-size: 14pt;
        font-weight: bold;
        margin: 0 0 4mm 0;
        padding-bottom: 3mm;
        border-bottom: 2px solid #000 !important; /* Bordo più marcato */
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
        border-collapse: collapse !important; /* Fondamentale per i bordi */
        margin-top: 3mm;
        border: 1px solid #000 !important;
    }

    .agency-report-container th {
        border-bottom: 2px solid #000 !important;
        padding: 8px 6px;
        text-align: left;
        font-weight: bold;
        background-color: #f3f3f3 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        text-transform: uppercase;
        font-size: 9pt;
    }

    .agency-report-container td {
        padding: 7px 6px;
        vertical-align: middle;
        border-bottom: 1px solid #ccc !important; /* Bordo riga più visibile */
        border-right: 1px solid #eee !important; /* Bordo colonna più visibile */
        font-size: 10pt;
    }

    .agency-report-container td:last-child {
        border-right: none !important;
    }

    .agency-report-container .time {
        text-align: center;
        font-weight: bold;
        width: 65px;
    }

    .agency-report-container .agency {
        font-weight: bold;
        color: #000;
    }

    /* ... resto degli stili ... */
    .agency-report-container .voucher { font-style: italic; color: #444; }
    .agency-report-container .licenses { font-weight: bold; font-size: 11pt; letter-spacing: 0.3px; }
    .agency-report-container .total-box { margin-top: 10mm; border-top: 2px solid #000 !important; padding-top: 4mm; }
    .agency-report-container .total-row { text-align: right; font-size: 11pt; margin-bottom: 2mm; }
    .agency-report-container .total-final { text-align: right; font-size: 13pt; font-weight: bold; text-transform: uppercase; }
    .agency-report-container .footer { margin-top: 15mm; text-align: center; font-size: 8pt; color: #666; border-top: 1px solid #ccc !important; padding-top: 3mm; }

    /* --- FIX SPECIFICO PER STAMPA BROWSER --- */
    @media print {
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Forza la visibilità della tabella se usata dentro iframe */
        .agency-report-container {
            visibility: visible !important;
        }
    }
</style>

</head>
<body>

<div class="agency-report-container">
    <h1>Report Servizi Agenzia</h1>

    <div class="header">
        <div><strong>Data Servizio:</strong> {{ $date }}</div>
        <div><strong>Operatore:</strong> {{ $generatedBy }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="15%">Orario</th>
                <th width="30%">Agenzia</th>
                <th width="20%">Voucher</th>
                <th width="35%">Licenze impegnate</th>
            </tr>
        </thead>
        <tbody>
            @forelse($agencyReport as $item)
                <tr>
                    <td class="time">{{ $item['time'] }}</td>
                    <td class="agency">{{ $item['agency_name'] }}</td>
                    <td class="voucher">{{ (!isset($item['voucher']) || $item['voucher'] === '–' || empty($item['voucher'])) ? '—' : $item['voucher'] }}</td>
                    <td class="licenses">
                        {{ implode(' • ', $item['licenses']) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center; padding:30px; font-style:italic; color:#666;">
                        Nessun servizio agenzia registrato per la data odierna.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(!empty($agencyReport))
        <div class="total-box">
            <div class="total-row">
                Numero totale servizi: <strong>{{ count($agencyReport) }}</strong>
            </div>
            <div class="total-final">
                Totale barche impiegate: {{ collect($agencyReport)->sum('count') }}
            </div>
        </div>
    @endif

    <div class="footer">
        Generato dal sistema gestionale il {{ $generatedAt }}<br>
        Documento ad uso interno - Operatore: {{ $generatedBy }}
    </div>
</div>

</body>
</html>
