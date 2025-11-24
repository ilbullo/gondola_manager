@props(["colour" => "emerald","message"])
<div
x-data="{ show: true }"
    x-show="show"
    x-init="setTimeout(() => { show = false }, 2000)"
    x-transition:leave.duration.500ms
    role="alert"
    class="mb-6 p-4 bg-{{$colour}}-50 border border-{{$colour}}-200 text-{{$colour}}-700 rounded-xl flex items-center gap-3 shadow-sm animate-fade-in">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <span class="font-medium">{{ $message }}</span>
</div>
