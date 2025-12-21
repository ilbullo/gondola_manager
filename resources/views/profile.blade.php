{{-- resources/views/profile/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-xl text-slate-800 uppercase italic tracking-tighter">
            {{ __('Profilo Utente') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            {{-- HEADER DI SEZIONE --}}
            <div class="mb-8">
                <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter leading-none">Impostazioni Account</h1>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1">Gestisci la tua identità e la sicurezza</p>
            </div>

            {{-- CARD: INFORMAZIONI PROFILO --}}
            <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-200 overflow-hidden transition-all">
                <div class="px-8 py-6 bg-slate-900 text-white flex justify-between items-center">
                    <h3 class="text-lg font-black uppercase italic tracking-tighter">Dati Personali</h3>
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-width="2.5"/></svg>
                </div>
                <div class="p-8 sm:p-10">
                    <div class="max-w-xl">
                        <livewire:profile.update-profile-information-form />
                    </div>
                </div>
            </div>

            {{-- CARD: CAMBIO PASSWORD --}}
            <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-200 overflow-hidden transition-all">
                <div class="px-8 py-6 bg-slate-900 text-white flex justify-between items-center">
                    <h3 class="text-lg font-black uppercase italic tracking-tighter">Sicurezza Account</h3>
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 00-2 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke-width="2.5"/></svg>
                </div>
                <div class="p-8 sm:p-10">
                    <div class="max-w-xl">
                        <livewire:profile.update-password-form />
                    </div>
                </div>
            </div>

            {{-- CARD: ELIMINAZIONE ACCOUNT (STILE ALERT) --}}
            <div class="bg-white rounded-[2.5rem] shadow-xl border border-rose-200 overflow-hidden transition-all">
                <div class="px-8 py-6 bg-rose-600 text-white flex justify-between items-center">
                    <h3 class="text-lg font-black uppercase italic tracking-tighter">Cancellazione</h3>
                    <svg class="w-5 h-5 text-rose-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2.5"/></svg>
                </div>
                <div class="p-8 sm:p-10 bg-rose-50/10">
                    <div class="max-w-xl text-rose-600 font-bold uppercase text-[10px] mb-4 tracking-widest">
                        Attenzione: questa operazione è irreversibile.
                    </div>
                    <div class="max-w-xl">
                        <livewire:profile.delete-user-form />
                    </div>
                </div>
            </div>

        </div>
    </div>

    <style>
        /* Unifichiamo i bottoni di salvataggio al tuo stile Indigo */
        button[type="submit"], .inline-flex.items-center.px-4.py-2.bg-gray-800 {
            background-color: #4f46e5 !important;
            border: none !important;
            font-weight: 900 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.1em !important;
            font-size: 10px !important;
            border-radius: 0.75rem !important;
            padding: 0.75rem 1.5rem !important;
            transition: all 0.2s !important;
        }
        button[type="submit"]:hover {
            background-color: #4338ca !important;
            transform: translateY(-1px);
        }
        /* Unifichiamo gli input allo stile delle altre pagine */
        input {
            border-radius: 1rem !important;
            border: none !important;
            background-color: #f8fafc !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            padding: 0.75rem 1rem !important;
        }
        input:focus {
            ring: 4px !important;
            ring-color: #e0e7ff !important; /* indigo-100 */
        }
    </style>
</x-app-layout>