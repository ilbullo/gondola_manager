{{-- Overlay Caricamento Globale --}}
<div class="fixed inset-0 bg-slate-900/40 backdrop-blur-[2px] z-[1000] flex items-center justify-center">
    <div class="bg-white p-6 rounded-3xl shadow-2xl flex items-center gap-4">
        <div class="w-6 h-6 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
        <span class="font-black uppercase text-xs tracking-widest text-slate-700">{{$text}}...</span>
    </div>
</div>
