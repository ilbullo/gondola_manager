{{-- resources/views/profile/show.blade.php --}}
<x-app-layout>
    <div class="h-full flex flex-col overflow-hidden bg-slate-100">
        
        {{-- HEADER FISSO --}}
        <div class="shrink-0 p-4 sm:p-8">
            <div class="max-w-6xl mx-auto w-full">
                <div class="flex items-center gap-4 mb-2">
                    <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter leading-none">Profilo Utente</h1>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1">Configurazione Account e Sicurezza</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- AREA SCORREVOLE INDIPENDENTE --}}
        <div class="flex-1 overflow-y-auto custom-scrollbar p-4 sm:p-8 pt-0">
            <div class="max-w-6xl mx-auto space-y-8 pb-12">
                
                {{-- CARD: INFORMAZIONI PROFILO --}}
                <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-200 overflow-hidden">
                    <div class="px-8 py-6 bg-slate-900 text-white flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-black uppercase italic tracking-tighter leading-none">Dati Personali</h3>
                            <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-1">Aggiorna le tue informazioni di contatto</p>
                        </div>
                        <div class="p-2 bg-white/5 rounded-xl text-slate-400">
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-width="2.5"/></svg>
                        </div>
                    </div>
                    <div class="p-8 sm:p-10 bg-white">
                        <div class="max-w-xl">
                            @livewire('profile.update-profile-information-form')
                        </div>
                    </div>
                </div>

                {{-- CARD: CAMBIO PASSWORD --}}
                <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-200 overflow-hidden">
                    <div class="px-8 py-6 bg-slate-900 text-white flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-black uppercase italic tracking-tighter leading-none">Sicurezza Account</h3>
                            <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-1">Gestisci la tua password di accesso</p>
                        </div>
                        <div class="p-2 bg-white/5 rounded-xl text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 00-2 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke-width="2.5"/></svg>
                        </div>
                    </div>
                    <div class="p-8 sm:p-10 bg-white">
                        <div class="max-w-xl">
                            @livewire('profile.update-password-form')
                        </div>
                    </div>
                </div>

                {{-- CARD: ELIMINAZIONE ACCOUNT --}}
                <div class="bg-white rounded-[2.5rem] shadow-xl border border-rose-200 overflow-hidden">
                    <div class="px-8 py-6 bg-rose-600 text-white flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-black uppercase italic tracking-tighter leading-none">Cancellazione</h3>
                            <p class="text-[8px] font-bold text-rose-200 uppercase tracking-widest mt-1">Rimuovi definitivamente i tuoi dati</p>
                        </div>
                        <div class="p-2 bg-white/10 rounded-xl text-rose-200">
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2.5"/></svg>
                        </div>
                    </div>
                    <div class="p-8 sm:p-10 bg-rose-50/20">
                        <div class="max-w-xl">
                            @livewire('profile.delete-user-form')
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('custom_css')
        <style>
            /* CSS per uniformare i componenti Jetstream/Breeze al tuo stile Pro */
            
            /* Pulsanti Submit (Indigo Pro) */
            button[type="submit"], 
            .inline-flex.items-center.px-4.py-2.bg-gray-800 {
                background-color: #4f46e5 !important;
                border: none !important;
                font-weight: 900 !important;
                text-transform: uppercase !important;
                letter-spacing: 0.15em !important;
                font-size: 10px !important;
                border-radius: 1rem !important;
                padding: 1rem 1.5rem !important;
                box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2) !important;
                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            }

            button[type="submit"]:hover {
                background-color: #4338ca !important;
                transform: translateY(-2px) !important;
                box-shadow: 0 20px 25px -5px rgba(79, 70, 229, 0.2) !important;
            }

            button[type="submit"]:active {
                transform: translateY(0px) !important;
            }

            /* Input (Slate Clean) */
            input[type="text"], input[type="email"], input[type="password"] {
                border-radius: 1rem !important;
                border: 2px solid #f1f5f9 !important;
                background-color: #f8fafc !important;
                font-weight: 800 !important;
                font-size: 14px !important;
                padding: 1rem !important;
                color: #1e293b !important;
                text-transform: uppercase !important;
                transition: all 0.2s !important;
            }

            input:focus {
                border-color: #e0e7ff !important;
                background-color: #ffffff !important;
                box-shadow: 0 0 0 4px #e0e7ff !important;
                outline: none !important;
            }

            /* Scrollbar minimal */
            .custom-scrollbar::-webkit-scrollbar { width: 4px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.05); border-radius: 10px; }
        </style>
    @endpush
</x-app-layout>