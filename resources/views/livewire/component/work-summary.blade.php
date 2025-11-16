<div class="mt-2 bg-gray-800 text-white p-2 rounded-md shadow-sm shadow-gray-500/40 space-y-1.5">
    <div class="flex justify-between text-xs font-bold">
        <span>Nolo (N):</span>
        <span id="countN">{{ $counts['N'] }}</span>
    </div>
    <div class="flex justify-between text-xs font-bold">
        <span>Contanti (X):</span>
        <span id="countX">{{ $counts['X'] }}</span>
    </div>
    <div class="flex justify-between text-xs font-bold">
        <span>Agenzie (A):</span>
        <span id="countA">{{ $counts['A'] }}</span>
    </div>
    <div class="flex justify-between text-xs font-bold">
        <span>Perdi Volta (P):</span>
        <span id="countP">{{ $counts['P'] }}</span>
    </div>
    <div class="flex justify-between text-xs font-bold">
        <span>Totale:</span>
        <span id="totalCount">{{ $total }}</span>
    </div>
</div>