<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Scontrino Licenza {{ request('license') }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 72mm;
            padding: 5mm 4mm;
            margin: 0;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background-color: #fff;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .italic { font-style: italic; }
        .uppercase { text-transform: uppercase; }

        /* Divider tratteggiato tipico delle stampanti termiche */
        .divider {
            border-bottom: 1px dashed #000;
            margin: 8px 0;
            width: 100%;
        }

        .flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 4px;
        }

        .header-title {
            font-size: 16px;
            line-height: 1.2;
            margin-bottom: 4px;
            display: block;
        }

        .section-title {
            font-size: 13px;
            text-decoration: underline;
            margin-top: 5px;
            margin-bottom: 3px;
        }

        .total-box {
            font-size: 16px;
            margin-top: 12px;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 8px 0;
        }

        .small { font-size: 10px; line-height: 1.2; }

        /* Gestione nomi agenzia lunghi */
        .item-name {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print();" onafterprint="window.close();">

    <div class="text-center">
        <span class="bold header-title">LIQUIDAZIONE TURNO</span>
        <span class="bold">LICENZA N. {{ request('license') }}</span>
    </div>

    <div class="divider"></div>

    <div class="flex"><span>DATA:</span> <span class="bold">{{ request('date') }}</span></div>
    <div class="flex"><span>OPERATORE:</span> <span class="bold">{{ request('op') }}</span></div>

    <div class="divider"></div>

    {{-- VOLUMI --}}
    <div class="flex"><span>NOLI EFFETTIVI (N):</span> <span class="bold">{{ request('n_count') }}</span></div>
    <div class="flex"><span>CONTANTI (X):</span> <span class="bold">{{ request('x_count') }}</span></div>
    {{-- PERDI VOLTA SE PRESENTI --}}
    @if(request('p_count')>0)
        <div class="flex"><span>PERDI VOLTA (P):</span> <span class="bold">{{ request('p_count') }}</span></div>
    @endif
    {{-- CREDITI FUTURI (Shared FF) --}}
    @if(request('shared_ff') > 0)
        <div class="divider"></div>
        <div class="flex bold uppercase">
            <span>{{ config('app_settings.labels.shared_from_first') }}:</span>
            <span>{{ request('shared_ff') }}</span>
        </div>
        <div class="small italic" style="margin-top: 2px;">
            Voucher: {{ implode(', ', (array)request('shared_vouchers')) }}
        </div>
    @endif

    <div class="divider"></div>

    {{-- AGENZIE --}}
    @if(request('agencies') && is_array(request('agencies')))
        <div class="bold section-title">RIEPILOGO AGENZIE</div>
        @foreach(request('agencies') as $name => $voucher)
            <div class="flex">
                <span class="item-name">{{ strtoupper($name) }}</span>
                <span class="bold">{{ $voucher ?: '---' }}</span>
            </div>
        @endforeach
        <div class="divider"></div>
    @endif

    {{-- DETTAGLIO CASSA --}}
    <div class="flex">
        <span>TOTALE LAVORI CASH:</span>
        <span class="bold">{{ \App\Helpers\Format::currency(request('x_amount'),true,true)}}</span>
    </div>
    <div class="flex">
        <span>CONGUAGLIO PORTAFOGLIO:</span>
        <span class="bold">{{ \App\Helpers\Format::currency(request('wallet_diff'),true,true) }}</span>
    </div>

    @if(request('bancale') && request('bancale') != '0' && request('bancale') != '0,00')
        <div class="flex">
            <span>BANCALE:</span>
            <span class="bold">-{{\App\Helpers\Format::currency(request('bancale'),true,true)}}</span>
        </div>
    @endif

    <div class="flex total-box bold">
        <span>NETTO PAGATO:</span>
        <span>{{ \App\Helpers\Format::currency(request('final'),true,true) }}</span>
    </div>

    <div style="margin-top: 20px;"></div>
    <div class="text-center small italic">
        I lavori 'N', 'A' e '{{ config('app_settings.labels.shared_from_first') }}' sono crediti<br>
        o attività esclusi dal contante attuale.<br>
        <div class="bold" style="margin-top: 5px; font-style: normal;">*** DOCUMENTO GESTIONALE ***</div>
    </div>

</body>
</html>
