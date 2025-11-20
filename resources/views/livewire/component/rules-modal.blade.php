<div class="inline-block">
    
    {{-- LINK DI ATTIVAZIONE --}}
    <a 
        href="#" 
        wire:click.prevent="openModal" 
        class="text-indigo-600 hover:text-indigo-800 text-sm font-medium underline flex items-center gap-1"
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
        </svg>
        Regole di Ripartizione
    </a>

    {{-- MODALE --}}
    <div 
        x-data="{ show: @entangle('isOpen') }" 
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
    >
        {{-- SFONDO SCURO --}}
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 transition-opacity" @click="show = false; @this.call('closeModal')"></div>

        {{-- CONTENITORE CENTRALE --}}
        <div class="flex items-center justify-center min-h-screen p-4">
            <div 
                class="bg-white rounded-lg shadow-xl transform transition-all max-w-3xl w-full overflow-hidden"
                @click.away="show = false; @this.call('closeModal')"
            >
                
                {{-- INTESTAZIONE --}}
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800">
                        Come funziona la ripartizione dei servizi
                    </h3>
                    <button @click="show = false; @this.call('closeModal')" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                {{-- CONTENUTO --}}
                <div class="p-6 space-y-8 text-gray-700">

                    {{-- 1. I VINCOLI (COSA NON SI PUÒ FARE) --}}
                    <section>
                        <h4 class="text-indigo-700 font-bold uppercase text-sm tracking-wider mb-3 border-b pb-1">
                            1. Regole e Limiti Fondamentali
                        </h4>
                        <ul class="space-y-3">
                            <li class="flex items-start bg-gray-50 p-3 rounded-md">
                                <span class="text-xl mr-3">🔒</span>
                                <div>
                                    <strong class="block text-gray-900">Lavori Fissi (Esclusi)</strong>
                                    <span class="text-sm">Se un lavoro è stato segnato come "fisso", non viene spostato. Rimane assegnato alla licenza che lo ha svolto.</span>
                                </div>
                            </li>
                            <li class="flex items-start bg-gray-50 p-3 rounded-md">
                                <span class="text-xl mr-3">✋</span>
                                <div>
                                    <strong class="block text-gray-900">Non si lavora il doppio</strong>
                                    <span class="text-sm">Non possiamo assegnare a una licenza più servizi di quelli che ha fatto veramente. Esempio: se Mario ha fatto 5 servizi reali, in tabella non potrà averne 6.</span>
                                </div>
                            </li>
                            <li class="flex items-start bg-gray-50 p-3 rounded-md">
                                <span class="text-xl mr-3">⏰</span>
                                <div>
                                    <strong class="block text-gray-900">Rispettare il Turno</strong>
                                    <span class="text-sm">Se una licenza fa solo la Mattina, non riceverà lavori del pomeriggio. Lo stesso vale al contrario.</span>
                                </div>
                            </li>
                            <li class="flex items-start bg-gray-50 p-3 rounded-md">
                                <span class="text-xl mr-3">🚫</span>
                                <div>
                                    <strong class="block text-gray-900">Niente Agenzie (Se richiesto)</strong>
                                    <span class="text-sm">Se è stata spuntata l'opzione per escludere le Agenzie, quella licenza non riceverà lavori di tipo 'A'.</span>
                                </div>
                            </li>
                        </ul>
                    </section>

                    {{-- 2. L'ORDINE DI ASSEGNAZIONE --}}
                    <section>
                        <h4 class="text-indigo-700 font-bold uppercase text-sm tracking-wider mb-3 border-b pb-1">
                            2. Chi viene servito prima?
                        </h4>
                        <div class="pl-2 space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="bg-indigo-100 text-indigo-800 font-bold w-8 h-8 flex items-center justify-center rounded-full shrink-0">1</div>
                                <p class="text-sm">
                                    <strong>Prima le Agenzie (A):</strong> Vengono distribuite per prime tra tutte le licenze disponibili.
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="bg-indigo-100 text-indigo-800 font-bold w-8 h-8 flex items-center justify-center rounded-full shrink-0">2</div>
                                <p class="text-sm">
                                    <strong>Poi la "Ripartizione dal Primo":</strong> I lavori speciali che devono seguire l'ordine della lista vengono assegnati partendo dal primo spazio vuoto in alto.
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="bg-indigo-100 text-indigo-800 font-bold w-8 h-8 flex items-center justify-center rounded-full shrink-0">3</div>
                                <p class="text-sm">
                                    <strong>Infine i Contanti e simili:</strong> Tutto il resto (X, N, P) viene distribuito per ultimo.
                                </p>
                            </div>
                        </div>
                    </section>

                    {{-- 3. GESTIONE CONTANTI E RECUPERO --}}
                    <section class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                        <h4 class="text-blue-800 font-bold text-sm mb-2">
                            💡 Come gestiamo Nolo (N) e Perdi Volta (P)?
                        </h4>
                        <p class="text-sm mb-3">
                            Per essere sicuri che i conti tornino e che il carico sia diviso equamente, usiamo questo sistema:
                        </p>
                        <ol class="list-decimal list-inside text-sm space-y-2 pl-2 text-gray-800">
                            <li>
                                All'inizio facciamo finta che <strong>Nolo (N)</strong> e <strong>Perdi Volta (P)</strong> siano semplici <strong>Contanti (X)</strong>. Li mettiamo tutti insieme in un unico gruppo.
                            </li>
                            <li>
                                Distribuiamo questo gruppo in parti uguali a tutte le licenze. Così tutti hanno lo stesso numero di servizi.
                            </li>
                            <li>
                                <strong>Alla fine rimettiamo tutto a posto:</strong> andiamo a sostituire le "X" assegnate con i veri "N" o "P" che quella licenza aveva fatto. Così ognuno si riprende i propri lavori specifici.
                            </li>
                        </ol>
                    </section>

                </div>

                {{-- FOOTER --}}
                <div class="bg-gray-100 px-6 py-4 text-right">
                    <button 
                        @click="show = false; @this.call('closeModal')" 
                        class="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded hover:bg-gray-700 transition"
                    >
                        Ho capito, chiudi
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>