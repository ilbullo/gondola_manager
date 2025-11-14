<div>
    <!-- Modale caricamento -->
    <div id="loading-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-4 rounded-lg shadow-lg flex items-center space-x-2">
            <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-700 text-sm sm:text-base">Caricamento...</span>
        </div>
    </div>

    <!-- Layout principale -->
    <div class="flex flex-col md:flex-row gap-3 sm:gap-4 h-[calc(100vh-2rem)]">
        <!-- Sidebar -->
           <livewire:layout.sidebar />

        <!-- Tabella -->
        <div class="w-full bg-white p-3 sm:p-4 shadow-md overflow-x-auto">
            <div class="min-w-[1200px] table-fixed w-full">
                <table class="w-full border-collapse table-fixed">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2 text-left text-sm font-semibold text-gray-700 sticky left-0 bg-gray-100 border-b w-20 z-10">Licenza</th>
                            <th class="p-2 text-center text-sm font-semibold text-gray-700 border-b w-10">1</th>
                            <th class="p-2 text-center text-sm font-semibold text-gray-700 border-b w-10">2</th>
                            <th class="p-2 text-center text-sm font-semibold text-gray-700 border-b w-10">3</th>
                            <th class="p-2 text-center text-sm font-semibold text-gray-700 border-b w-10">4</th>
                            <th class="p-2 text-center text-sm font-semibold text-gray-700 border-b w-10">5</th>
                            <th class="p-2 text-center text-sm font-semibold text-gray-700 border-b w-10">6</th>
                            <th class="p-2 text-center text-sm font-semibold text-gray-700 border-b w-10">7</th>
                            <th class="p-2 text-center text-sm font-semibold text-gray-700 border-b w-10">8</th>
                            <th class="p-2 text-center text-sm font-semibold text-gray-700 border-b w-10">9</th>
                            <th class="p-2 text-center text-sm font-semibold text-gray-700 border-b w-10">10</th>
                            <th class="p-2 text-center text-sm font-semibold text-gray-700 border-b w-10">11</th>
                            <th class="p-2 text-center text-sm font-semibold text-gray-700 border-b w-10">12</th>
                            <th class="p-2 text-center text-sm font-semibold text-gray-700 border-b w-10">13</th>
                            <th class="p-2 text-center text-sm font-semibold text-gray-700 border-b w-10">14</th>
                            <th class="p-2 text-center text-sm font-semibold text-gray-700 border-b w-10">15</th>
                            <th class="p-2 text-center text-sm font-semibold text-gray-700 border-b w-10">16</th>
                            <th class="p-2 text-center text-sm font-semibold text-gray-700 border-b w-10">17</th>
                            <th class="p-2 text-center text-sm font-semibold text-gray-700 border-b w-10">18</th>
                            <th class="p-2 text-center text-sm font-semibold text-gray-700 border-b w-10">19</th>
                            <th class="p-2 text-center text-sm font-semibold text-gray-700 border-b w-10">20</th>
                            <th class="p-2 text-center text-sm font-semibold text-gray-700 border-b w-10">21</th>
                            <th class="p-2 text-center text-sm font-semibold text-gray-700 border-b w-10">22</th>
                            <th class="p-2 text-center text-sm font-semibold text-gray-700 border-b w-10">23</th>
                            <th class="p-2 text-center text-sm font-semibold text-gray-700 border-b w-10">24</th>
                            <th class="p-2 text-center text-sm font-semibold text-gray-700 border-b w-10">25</th>
                        </tr>
                    </thead>
                    <tbody id="sortable">
                        @foreach($licenses as $license)
                        <tr class="border-b" draggable="true" wire:sortable.item="item-{{ $license['id'] }}"
                        wire:key="selected-item-{{ $license['id'] }}">
                            <td wire:sortable.handle class="p-2 text-sm text-gray-900 font-medium sticky left-0 bg-white z-10">
                                <div class="flex items-center space-x-2">
                                    <svg class="drag-handle w-5 h-5 text-gray-500 cursor-grab active:cursor-grabbing" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                    </svg>
                                    <span>{{$license['user']->license_number}}</span>
                                </div>
                            </td>
                            <td class="p-1 text-center text-xs border bg-gray-50 hover:bg-gray-100 cursor-pointer"><span class="text-gray-400">-</span></td>
                            <td class="p-1 text-center text-xs border bg-gray-50 hover:bg-gray-100 cursor-pointer"><span class="text-gray-400">-</span></td>
                            <td class="p-1 text-center text-xs border bg-gray-50 hover:bg-gray-100 cursor-pointer"><span class="text-gray-400">-</span></td>
                            <td class="p-1 text-center text-xs border bg-gray-50 hover:bg-gray-100 cursor-pointer"><span class="text-gray-400">-</span></td>
                            <td class="p-1 text-center text-xs border bg-gray-50 hover:bg-gray-100 cursor-pointer"><span class="text-gray-400">-</span></td>
                            <td class="p-1 text-center text-xs border bg-gray-50 hover:bg-gray-100 cursor-pointer"><span class="text-gray-400">-</span></td>
                            <td class="p-1 text-center text-xs border bg-gray-50 hover:bg-gray-100 cursor-pointer"><span class="text-gray-400">-</span></td>
                            <td class="p-1 text-center text-xs border bg-gray-50 hover:bg-gray-100 cursor-pointer"><span class="text-gray-400">-</span></td>
                            <td class="p-1 text-center text-xs border bg-gray-50 hover:bg-gray-100 cursor-pointer"><span class="text-gray-400">-</span></td>
                            <td class="p-1 text-center text-xs border bg-gray-50 hover:bg-gray-100 cursor-pointer"><span class="text-gray-400">-</span></td>
                            <td class="p-1 text-center text-xs border bg-gray-50 hover:bg-gray-100 cursor-pointer"><span class="text-gray-400">-</span></td>
                            <td class="p-1 text-center text-xs border bg-gray-50 hover:bg-gray-100 cursor-pointer"><span class="text-gray-400">-</span></td>
                            <td class="p-1 text-center text-xs border bg-gray-50 hover:bg-gray-100 cursor-pointer"><span class="text-gray-400">-</span></td>
                            <td class="p-1 text-center text-xs border bg-gray-50 hover:bg-gray-100 cursor-pointer"><span class="text-gray-400">-</span></td>
                            <td class="p-1 text-center text-xs border bg-gray-50 hover:bg-gray-100 cursor-pointer"><span class="text-gray-400">-</span></td>
                            <td class="p-1 text-center text-xs border bg-gray-50 hover:bg-gray-100 cursor-pointer"><span class="text-gray-400">-</span></td>
                            <td class="p-1 text-center text-xs border bg-gray-50 hover:bg-gray-100 cursor-pointer"><span class="text-gray-400">-</span></td>
                            <td class="p-1 text-center text-xs border bg-gray-50 hover:bg-gray-100 cursor-pointer"><span class="text-gray-400">-</span></td>
                            <td class="p-1 text-center text-xs border bg-gray-50 hover:bg-gray-100 cursor-pointer"><span class="text-gray-400">-</span></td>
                            <td class="p-1 text-center text-xs border bg-gray-50 hover:bg-gray-100 cursor-pointer"><span class="text-gray-400">-</span></td>
                            <td class="p-1 text-center text-xs border bg-gray-50 hover:bg-gray-100 cursor-pointer"><span class="text-gray-400">-</span></td>
                            <td class="p-1 text-center text-xs border bg-gray-50 hover:bg-gray-100 cursor-pointer"><span class="text-gray-400">-</span></td>
                            <td class="p-1 text-center text-xs border bg-gray-50 hover:bg-gray-100 cursor-pointer"><span class="text-gray-400">-</span></td>
                            <td class="p-1 text-center text-xs border bg-gray-50 hover:bg-gray-100 cursor-pointer"><span class="text-gray-400">-</span></td>
                            <td class="p-1 text-center text-xs border bg-gray-50 hover:bg-gray-100 cursor-pointer"><span class="text-gray-400">-</span></td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
