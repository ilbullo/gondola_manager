<!DOCTYPE html>
<html lang="it" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Gondola System PRO - Integrale</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .service-grid { display: grid; grid-template-columns: repeat(25, 52px); gap: 4px; width: max-content; }
        .sticky-column { position: sticky; left: 0; z-index: 20; background: white; box-shadow: 4px 0 10px -2px rgba(0,0,0,0.1); width: 190px; flex-shrink: 0; }
        .config-item { height: 50px; display: flex; align-items: center; }
        .job-pill { cursor: pointer; transition: transform 0.1s; display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1; }
        .job-pill:active { transform: scale(0.95); }
    </style>
</head>
<body class="h-full bg-slate-200 font-sans antialiased text-gray-900 overflow-hidden" 
      x-data="{ 
        view: 'setup', 
        allLicenze: [101, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111, 112, 113, 114, 115],
        selectedLicenze: [101, 105, 110, 112], 
        selectedType: 'A', importo: 90, voucher: '', selectedAgenzia: '', mode: 'none', 
        showAgenzie: false,
        showConfirmRipartizione: false,
        showDetail: false,
        activeJob: { type: '', time: '', agency: '', price: 0, voucher: '', code: '' },
        
        agenziaCodes: {
            'Bauer': 'BAUE', 'Danieli': 'DANI', 'Gritti': 'GRIT', 'Monaco': 'MONA',
            'Metropole': 'METR', 'Londra': 'LOND', 'Europa': 'EURO', 'S. Clemente': 'CLEM'
        },
        agenzie: ['Bauer', 'Danieli', 'Gritti', 'Monaco', 'Metropole', 'Londra', 'Europa', 'S. Clemente'],

        getTypeColor(t) {
            const colors = { 'A': 'bg-indigo-600', 'X': 'bg-emerald-500', 'P': 'bg-rose-600', 'N': 'bg-yellow-400 text-slate-900' };
            return colors[t] || 'bg-slate-500';
        },

        getElapsedTime(startTime) {
            const now = new Date();
            const [hours, minutes] = startTime.split(':').map(Number);
            const start = new Date(); start.setHours(hours, minutes, 0);
            const diffMins = Math.floor((now - start) / 60000);
            if (diffMins < 0) return 'In attesa';
            if (diffMins < 60) return diffMins + ' min fa';
            return Math.floor(diffMins / 60) + 'h ' + (diffMins % 60) + 'm fa';
        },

        toggleLicenza(n) {
            if(this.selectedLicenze.includes(n)) {
                this.selectedLicenze = this.selectedLicenze.filter(i => i !== n);
            } else { this.selectedLicenze.push(n); }
        },
        moveUp(index) {
            if(index > 0) {
                let el = this.selectedLicenze.splice(index, 1)[0];
                this.selectedLicenze.splice(index - 1, 0, el);
            }
        },
        moveDown(index) {
            if(index < this.selectedLicenze.length - 1) {
                let el = this.selectedLicenze.splice(index, 1)[0];
                this.selectedLicenze.splice(index + 1, 0, el);
            }
        },
        selectType(t) {
            this.selectedType = t;
            if(t === 'A') { this.showAgenzie = true; } 
            else { this.selectedAgenzia = ''; this.showAgenzie = false; }
        },
        openDetail(type, agency, time, price, v) {
            const code = type === 'A' ? (this.agenziaCodes[agency] || 'AGEN') : type;
            this.activeJob = { type, agency, time, price, voucher: v, code };
            this.showDetail = true;
        }
      }">

    <template x-if="view === 'setup'">
        <div class="h-full flex flex-col p-8 overflow-y-auto animate-in fade-in">
            <div class="max-w-4xl mx-auto w-full">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-3xl font-black text-slate-800 uppercase italic">Configurazione Turno</h1>
                    <button @click="selectedLicenze = []" class="px-6 py-2 bg-rose-500 text-white rounded-xl font-black uppercase text-xs shadow-lg">Svuota</button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-white rounded-[2.5rem] p-6 shadow-xl border border-slate-300">
                        <h2 class="text-[10px] font-black text-slate-400 uppercase mb-4 tracking-widest">Licenze Disponibili</h2>
                        <div class="grid grid-cols-4 gap-2">
                            <template x-for="lic in allLicenze">
                                <button @click="toggleLicenza(lic)"
                                        :class="selectedLicenze.includes(lic) ? 'bg-indigo-600 text-white border-indigo-700' : 'bg-slate-100 text-slate-500 border-slate-200'"
                                        class="h-12 rounded-xl border-2 font-black transition-all" x-text="lic"></button>
                            </template>
                        </div>
                    </div>
                    <div class="bg-slate-800 rounded-[2.5rem] p-6 shadow-xl text-white">
                        <h2 class="text-[10px] font-black text-slate-500 uppercase mb-4 tracking-widest">Ordine di Servizio</h2>
                        <div class="space-y-2">
                            <template x-for="(lic, index) in selectedLicenze" :key="lic">
                                <div class="flex items-center bg-white/10 p-3 rounded-xl border border-white/10">
                                    <span class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center font-black mr-3 shadow-lg" x-text="lic"></span>
                                    <span class="flex-1 font-black text-xs uppercase text-slate-400">Pos. <span class="text-white ml-1" x-text="index + 1"></span></span>
                                    <div class="flex gap-1">
                                        <button @click="moveUp(index)" class="p-2 hover:bg-white/20 rounded-lg">↑</button>
                                        <button @click="moveDown(index)" class="p-2 hover:bg-white/20 rounded-lg">↓</button>
                                        <button @click="toggleLicenza(lic)" class="p-2 text-rose-400 hover:bg-rose-500 hover:text-white rounded-lg">✕</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <button x-show="selectedLicenze.length > 0" @click="view = 'matrice'" class="w-full mt-6 py-4 bg-emerald-500 text-slate-900 rounded-2xl font-black text-lg uppercase shadow-lg">Inizia Lavoro →</button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <template x-if="view === 'matrice'">
        <div class="h-full flex flex-col overflow-hidden animate-in fade-in">
            <header class="bg-slate-900 text-white shadow-2xl z-50 flex flex-wrap items-center px-4 py-3 gap-3 shrink-0">
                <div class="flex gap-2 border-r border-white/10 pr-4">
                    <button @click="view = 'setup'" class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center hover:bg-white/20"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-width="3"/></svg></button>
                </div>

                <div class="flex gap-1 bg-white/10 p-1 rounded-xl h-[50px]">
                    <template x-for="t in ['A', 'X', 'P', 'N']">
                        <button @click="selectType(t)" :class="selectedType == t ? getTypeColor(t) + ' shadow-lg scale-105' : 'text-slate-500'" class="w-10 rounded-lg font-black transition-all" x-text="t"></button>
                    </template>
                </div>

                <div class="config-item relative shrink-0">
                    <div class="absolute left-3 top-1 text-[7px] font-black text-slate-500 uppercase">Prezzo</div>
                    <input type="number" x-model="importo" class="w-20 h-full bg-white/10 border border-white/10 rounded-xl pl-3 pt-2 text-xl font-black text-emerald-400 outline-none">
                </div>

                <div x-show="selectedType === 'A' && selectedAgenzia" @click="showAgenzie = true" class="config-item px-3 bg-indigo-500 rounded-xl gap-3 shadow-lg cursor-pointer hover:bg-indigo-400 transition-colors">
                    <div class="flex flex-col"><span class="text-[7px] font-black text-indigo-200 uppercase">Agenzia</span><span class="text-[10px] font-black text-white uppercase" x-text="selectedAgenzia"></span></div>
                </div>

                <div class="config-item relative shrink-0">
                    <div class="absolute left-3 top-1 text-[7px] font-black text-slate-500 uppercase">Voucher</div>
                    <input type="text" x-model="voucher" maxlength="15" class="w-40 h-full bg-white/5 border border-white/10 rounded-xl pl-3 pt-2 text-xs font-black text-white uppercase outline-none">
                </div>

                <div class="flex gap-1.5 bg-white/5 p-1 rounded-xl border border-white/10">
                    <button @click="mode = (mode === 'fisso' ? 'none' : 'fisso')" :class="mode === 'fisso' ? 'bg-indigo-600' : 'text-slate-500'" class="h-10 px-3 rounded-lg text-[8px] font-black uppercase transition-all">Lavoro Fisso</button>
                    <button @click="mode = (mode === 'condiviso' ? 'none' : 'condiviso')" :class="mode === 'condiviso' ? 'bg-cyan-600' : 'text-slate-500'" class="h-10 px-3 rounded-lg text-[8px] font-black uppercase transition-all">Condiviso 1°</button>
                </div>

                <button @click="showConfirmRipartizione = true" class="config-item px-4 ml-auto bg-amber-500 hover:bg-amber-400 border border-amber-300 rounded-xl gap-2 shadow-lg shadow-amber-500/20">
                    <span class="text-[10px] font-black text-slate-900 uppercase">Ripartizione</span>
                </button>
            </header>

            <main class="flex-1 overflow-auto bg-slate-200">
                <div class="inline-block min-w-full">
                    <div class="flex items-center bg-slate-50 border-b border-slate-300 sticky top-0 z-40 h-10 uppercase text-[9px] font-black text-slate-400">
                        <div class="sticky-column bg-slate-50 px-4">Licenza / Solo X</div>
                        <div class="service-grid px-4"><template x-for="s in 25"><div class="w-12 text-center" x-text="'S.'+s"></div></template></div>
                    </div>

                    <div class="bg-white divide-y divide-slate-100">
                        <template x-for="(lic, i) in selectedLicenze" :key="lic">
                            <div class="flex items-center hover:bg-indigo-50/20" x-data="{ soloX: false }">
                                <div class="sticky-column flex items-center justify-between px-3 py-2 border-r border-slate-200">
                                    <div class="flex items-center gap-2">
                                        <span class="w-8 h-8 flex-shrink-0 flex items-center justify-center bg-slate-800 text-white rounded-lg font-black text-xs" x-text="lic"></span>
                                        <p class="font-black text-slate-700 text-[10px] uppercase">Socio <span x-text="i+1"></span></p>
                                    </div>
                                    <button @click="soloX = !soloX" :class="soloX ? 'bg-emerald-500 border-emerald-600' : 'bg-slate-200 border-slate-300'" class="w-8 h-4 rounded-full border relative flex items-center transition-all duration-200"><span :class="soloX ? 'translate-x-4' : 'translate-x-0.5'" class="w-3 h-3 bg-white rounded-full shadow-sm transition-transform"></span></button>
                                </div>
                                <div class="service-grid p-2 px-4">
                                    <template x-for="s in 25">
                                        <div class="w-13 h-13 flex justify-center items-center">
                                            <template x-if="lic === 101 && s === 2">
                                                <div @click="openDetail('A', 'Bauer', '14:20', 90, 'V-8892')" class="job-pill w-11 h-11 bg-indigo-600 rounded-xl text-white shadow-md">
                                                    <span class="text-[10px] font-black">BAUE</span>
                                                    <span class="text-[7px] font-bold opacity-80 mt-1 uppercase">V-8892</span>
                                                </div>
                                            </template>
                                            <template x-if="lic === 105 && s === 4">
                                                <div @click="openDetail('X', 'Nessuna', '15:45', 45, 'CORSA-5')" class="job-pill w-11 h-11 bg-emerald-500 rounded-xl text-white shadow-md">
                                                    <span class="text-[12px] font-black">X</span>
                                                    <span class="text-[7px] font-bold opacity-90 mt-1 uppercase">CORSA-5</span>
                                                </div>
                                            </template>
                                            <template x-if="!( (lic === 101 && s === 2) || (lic === 105 && s === 4) )">
                                                <button class="w-11 h-11 border border-slate-200 bg-slate-50 rounded-xl flex items-center justify-center text-slate-300 text-xl font-light hover:border-indigo-400 hover:bg-indigo-50 transition-all">+</button>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </main>
        </div>
    </template>

    <div x-show="showAgenzie" x-cloak class="fixed inset-0 z-[150] flex items-center justify-center bg-slate-900/80 backdrop-blur-md p-4">
        <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-2xl overflow-hidden">
            <div class="bg-indigo-600 p-6 text-center text-white"><h3 class="text-2xl font-black uppercase italic">Seleziona Agenzia</h3></div>
            <div class="p-6 grid grid-cols-2 sm:grid-cols-4 gap-3">
                <template x-for="agenzia in agenzie">
                    <button @click="selectedAgenzia = agenzia; showAgenzie = false" class="h-20 bg-slate-50 border-2 border-slate-200 rounded-2xl flex flex-col items-center justify-center hover:border-indigo-500 transition-all">
                        <span class="text-[8px] font-black text-slate-400 uppercase" x-text="agenziaCodes[agenzia]"></span>
                        <span class="text-xs font-black text-slate-700 uppercase mt-1" x-text="agenzia"></span>
                    </button>
                </template>
            </div>
            <div class="p-4 bg-slate-100"><button @click="showAgenzie = false" class="w-full py-4 bg-slate-400 text-white rounded-2xl font-black uppercase">Annulla</button></div>
        </div>
    </div>

    <div x-show="showDetail" x-cloak class="fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/90 backdrop-blur-md p-4">
        <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-sm overflow-hidden animate-in zoom-in">
            <div :class="getTypeColor(activeJob.type)" class="p-8 text-center text-white">
                <div class="w-20 h-20 bg-white/20 rounded-3xl mx-auto flex items-center justify-center text-3xl font-black mb-4 shadow-xl border border-white/20" x-text="activeJob.code"></div>
                <h3 class="text-lg font-black uppercase tracking-widest" x-text="activeJob.agency !== 'Nessuna' ? activeJob.agency : 'Lavoro Diretto'"></h3>
            </div>
            <div class="p-8 space-y-6">
                <div class="flex justify-between items-start border-b border-slate-100 pb-4">
                    <div><span class="text-[10px] font-black text-slate-400 uppercase block">Partenza</span><span class="text-3xl font-black text-slate-800" x-text="activeJob.time"></span></div>
                    <div class="text-right"><span class="text-[10px] font-black text-rose-500 uppercase block">Stato</span><span class="text-sm font-black text-slate-600 bg-slate-100 px-3 py-1 rounded-full mt-1 inline-block" x-text="getElapsedTime(activeJob.time)"></span></div>
                </div>
                <div x-show="activeJob.voucher" class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                    <span class="text-[9px] font-black text-slate-400 uppercase block mb-1">Codice Voucher</span>
                    <span class="text-xl font-black text-indigo-600 uppercase" x-text="activeJob.voucher"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs font-black text-slate-400 uppercase">Importo</span>
                    <span class="text-2xl font-black text-emerald-600" x-text="'€' + activeJob.price"></span>
                </div>
                <button @click="showDetail = false" class="w-full py-5 bg-slate-900 text-white rounded-[1.5rem] font-black uppercase text-xs tracking-widest">Chiudi</button>
            </div>
        </div>
    </div>

</body>
</html>