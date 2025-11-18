{{-- resources/views/pdf/agency-report.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>Report Agenzie {{ $timestamp }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #000; margin: 10mm; }
        h1 { font-size: 18px; margin-bottom: 10px; }
        h2 { font-size: 14px; margin-top: 15px; margin-bottom: 5px; border-bottom: 1px solid #000; padding-bottom: 2px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; vertical-align: top; }
        th { background-color: #ddd; font-weight: bold; }
        .agency-table td:nth-child(2) { width: 10%; } /* Timestamp */
        .agency-table td:nth-child(3) { width: 25%; } /* Voucher */
        .agency-table td:nth-child(4) { width: 35%; } /* License Numbers */
    </style>
</head>
<body>
    <h1>Report Dettagliato Lavori per Agenzia</h1>
    <p>Data di Generazione: {{ $timestamp }}</p>

    {{-- Stampa i dati raggruppati --}}
    @foreach ($agencyReport as $agencyName => $works)
        <h2>Agenzia: {{ $agencyName }}</h2>
        <table class="agency-table">
            <thead>
                <tr>
                    <th>Tipo Lavoro</th>
                    <th>Ora Servizio</th>
                    <th>Note / Voucher</th>
                    <th>Licenze Assegnate</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($works as $work)
                    <tr>
                        <td>{{ $work['work_type'] }} ({{ $work['slots_assigned'] }} slot)</td>
                        <td>{{ $work['timestamp'] ? $work['timestamp']->format('H:i') : 'N/A' }}</td>
                        <td>{{ $work['voucher'] ?: '-' }}</td>
                        <td>{{ $work['license_numbers'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</body>
</html>
