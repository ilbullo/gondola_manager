<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Ripartizione Lavori {{ $date }}</title>
<style>
    @page { margin: 5mm 5mm; size: A4 landscape; }
    
    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 8.2pt;
        line-height: 1.1;
        margin: 0;
        color: #000;
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
        font-size: 8.4pt;
    }

    th {
        font-weight: bold;
        border-bottom: 1.5pt solid #000;
        padding: 3px 1px;
        background-color: #f3f4f6;
    }

    .slot {
        width: 29px !important;
        height: 25px; 
        font-size: 8.4pt;
    }

    .prev-lic-text {
        display: block;
        font-size: 6.8pt;
        color: #444;
        line-height: 1;
        margin-top: -1px;
    }

    .row-even { background-color: #ffffff; }
    .row-odd { background-color: #fcfcfc; }

    .excluded { text-decoration: underline; text-decoration-thickness: 1.8pt; }
    .shared   { font-weight: bold; color: #444; }

    .lic  { width: 55px; font-weight: bold; background-color: #f9fafb !important; }
    .cash { width: 70px; font-weight: bold; background-color: #f9fafb !important; }
    .np   { width: 30px; font-weight: bold; background-color: #f9fafb !important; }

    tfoot td {
        font-weight: bold;
        font-size: 8.5pt;
        border-top: 1.5pt solid #000;
        padding: 4px 1px;
        background-color: #eee;
    }

    .header-box {
        border-bottom: 1.5pt solid #000; 
        padding-bottom: 3px; 
        margin-bottom: 5px; 
        width: 100%;
    }
</style>
</head>
<body>

<div class="header-box">
    <span style="font-size: 13pt; font-weight: bold; text-transform: uppercase;">RIPARTIZIONE E CASSA</span>
    <span style="font-size: 8.5pt; font-weight: bold; float: right; margin-top: 4px;">
        Data: {{ $date }} — Bancale: € {{ number_format($bancaleCost, 2, ',', '.') }} — Operatore: {{ $generatedBy }}
    </span>
    <div style="clear: both;"></div>
</div>

    <table>
        <thead>
            <tr>
                <th class="lic">Lic.</th>
                <th class="cash">Netto</th>
                <th class="np">N</th>
                <th class="np">X</th>
                <th class="np">U</th>
                <th class="np">P</th>
                @for($i = 1; $i <= config('app_settings.matrix.total_slots'); $i++)
                    <th class="slot">{{ $i }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach($matrix as $row)
                @php 
                    $rowClass = $loop->index % 2 == 0 ? 'row-even' : 'row-odd';
                @endphp
                <tr class="{{ $rowClass }}">
                    <td class="lic">{{ $row['license_number'] }}</td>
                    {{-- SOLID: 'final' arriva dal DTO tramite toPrintParams() ed è già il netto calcolato --}}
                    <td class="cash">€ {{ $row['final'] }}</td>
                    <td class="np">{{ $row['n_count'] }}</td>
                    <td class="np">{{ $row['x_count'] }}</td>
                    <td class="np" style="color: #666;">{{ $row['shared_ff'] }}</td>
                    <td class="np">{{ $row['p_count'] }}</td>

                    @for($slot = 1; $slot <= config('app_settings.matrix.total_slots'); $slot++)
                        @php
                            $work = $row['worksMap'][$slot] ?? null;
                            $isAgency   = $work && $work['value'] === 'A';
                            $isExcluded = $work && ($work['excluded'] ?? false);
                            $isShared   = $work && ($work['shared_from_first'] ?? false);
                            $prevLicenseNumber = $work['prev_license_number'] ?? null;
                        @endphp
                        <td class="slot">
                            @if($work)
                                <span class="{{ $isExcluded ? 'excluded' : '' }} {{ $isShared ? 'shared' : '' }}">
                                    @if($isAgency)
                                        {{ ($work['agency_code'] ?? 'AG') }}
                                    @elseif($isShared)
                                        {{-- Visualizza i primi 4 caratteri del voucher o il valore se assente --}}
                                        {{ strtoupper(!empty($work['voucher']) ? \Illuminate\Support\Str::limit($work['voucher'], 4, '') : $work['value']) }}
                                    @else
                                        {{ strtoupper($work['value']) }}
                                    @endif
                                </span>
                                @if($prevLicenseNumber)
                                    <span class="prev-lic-text">(da: {{ $prevLicenseNumber }})</span>
                                @endif
                            @endif
                        </td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="lic">TOT</td>
                <td class="cash">€ {{ number_format($totalCash, 0, ',', '.') }}</td>
                <td class="np">{{ $totalN }}</td>
                <td class="np">{{ $totalX }}</td>
                <td class="np">{{ collect($matrix)->sum('shared_ff') }}</td>
                <td class="np">{{ collect($matrix)->sum('p_count') }}</td>
                <td colspan="{{ config('app_settings.matrix.total_slots') }}"></td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 5px; font-size: 7.5pt; width: 100%;">
        <div style="float: left; width: 70%;">
            <strong>Legenda:</strong> 
            N = Noli • X = Contanti • U = {{ config('app_settings.labels.shared_from_first') }} • 
            <u>Sottolineato</u> = Fisso Licenza • 
            <strong>Grassetto</strong> = {{ config('app_settings.labels.shared_from_first') }}
        </div>
        <div style="float: right; width: 30%; text-align: right; color: #555;">
            Generato: {{ $generatedAt }}
        </div>
        <div style="clear: both;"></div>
    </div>
</body>
</html>