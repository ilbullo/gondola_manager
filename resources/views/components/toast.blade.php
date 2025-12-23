{{-- resources/views/components/toast.blade.php --}}
<div x-data="{ 
        show: false, 
        message: '', 
        title: '' 
     }"
     @notify.window="
        {{-- Log di controllo: apparirà nella console F12 per vedere cosa arriva --}}
        console.log($event.detail); 
        
        message = $event.detail.message;
        title = $event.detail.title;
        show = true;
        setTimeout(() => show = false, 3000);
     "
     class="fixed bottom-10 right-10 z-[300] pointer-events-none"
     x-cloak>
    
    <div x-show="show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-10 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         class="bg-slate-900 text-white px-8 py-5 rounded-[2rem] shadow-2xl border border-white/10 flex items-center gap-4 pointer-events-auto">
        
        <div class="w-8 h-8 rounded-full flex items-center justify-center bg-emerald-500">
            <x-icon name="check" class="w-4 h-4 text-white" />
        </div>

        <div class="flex flex-col">
            {{-- RIMOSSO il testo fisso 'Sistema', ora è SOLO x-text --}}
            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 leading-none mb-1" 
                  x-text="title"></span>
            
            <span class="text-xs font-black uppercase italic tracking-tight" 
                  x-text="message"></span>
        </div>
    </div>
</div>