<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Scontrino Licenza {{ request('license') }}</title>
    <style>
        @page { margin: 0; }
        body { 
            font-family: 'Courier New', Courier, monospace; 
            width: 72mm; padding: 4mm; margin: 0;
            font-size: 12px; line-height: 1.3; color: #000;
        }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .italic { font-style: italic; }
        .border-b { border-bottom: 1px dashed #000; margin: 6px 0; }
        .flex { display: flex; justify-content: space-between; align-items: flex-start; }
        .total-box { font-size: 15px; margin-top: 10px; border-top: 2px solid #000; padding-top: 6px; }
        .small { font-size: 10px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print();" onafterprint="window.close();">

    <div class="text-center">
        <span class="bold" style="font-size: 15px;">LIQUIDAZIONE TURNO</span><br>
        <span class="bold">LICENZA N. {{ request('license') }}</span>
    </div>

    <div class="border-b"></div>
    <div class="flex"><span>DATA:</span> <span class="bold">{{ request('date') }}</span></div>
    <div class="flex"><span>OP:</span> <span class="bold">{{ request('op') }}</span></div>
    <div class="border-b"></div>

    {{-- VOLUMI --}}
    <div class="flex"><span>NOLI EFFETTIVI (N):</span> <span>{{ request('n_count') }}</span></div>
    <div class="flex"><span>CONTANTI (X) OGGI:</span> <span>{{ request('x_count') }}</span></div>

    {{-- CREDITI FUTURI (Shared FF) --}}
    @if(request('shared_ff') > 0)
        <div class="flex italic small"><span>di cui {{ config('app_settings.labels.shared_from_first') }}:</span> <span>{{ request('shared_ff') }}</span></div>
        <div class="small italic" style="padding-left: 5px;">
            Voucher {{ config('app_settings.labels.shared_from_first') }}: {{ implode(', ', (array)request('shared_vouchers')) }}
        </div>
    @endif

    <div class="border-b"></div>

    {{-- AGENZIE (Crediti Futuri) --}}
    @if(request('agencies') && is_array(request('agencies')))
        <div class="text-center bold small">AGENZIE (Crediti Futuri)</div>
        @foreach(request('agencies') as $name => $voucher)
            <div class="flex small">
                <span>{{ strtoupper(substr($name, 0, 18)) }}</span>
                <span class="bold">{{ $voucher ?: '---' }}</span>
            </div>
        @endforeach
        <div class="border-b"></div>
    @endif

    {{-- CASSA --}}
    <div class="flex"><span>CONGUAGLIO WALLET:</span> <span class="bold">{{ request('wallet_diff') }} €</span></div>

    @if(request('bancale') && request('bancale') !== '0,00')
        <div class="flex"><span>BANCALE:</span> <span class="bold">-{{ request('bancale') }} €</span></div>
    @endif

    <div class="flex total-box bold">
        <span>NETTO DA CONSEGNARE:</span>
        <span>{{ request('final') }} €</span>
    </div>

    <div class="border-b" style="margin-top: 15px;"></div>
    <div class="text-center small italic">
        I lavori 'N', 'A' e '{{ config('app_settings.labels.shared_from_first') }}' sono crediti/attività esclusi dal contante attuale.<br>
        *** DOCUMENTO GESTIONALE ***
    </div>

</body>
</html>