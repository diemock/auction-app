<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Мои ставки и участие в аукционах') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if($lots->isEmpty())
                        <div class="text-center py-8">
                            <p class="text-gray-500 italic">{{ __('Вы еще не сделали ни одной ставки.') }}</p>
                            <a href="{{ route('lots.index') }}" class="mt-4 inline-block text-indigo-600 hover:text-indigo-900 font-bold">
                                {{ __('Перейти к списку лотов →') }}
                            </a>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Лот</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Текущая цена</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ваш статус</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действие</th>
                                </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="dashboard-bids-table">
                                @foreach($lots as $lot)
                                    <tr id="lot-row-{{ $lot->id }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $lot->title }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 font-bold" id="lot-price-{{ $lot->id }}">
                                                {{ number_format($lot->current_price, 0, '.', ' ') }} ₽
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap" id="lot-status-{{ $lot->id }}">
                                            @if($lot->user_status === 'won')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        🏆 Победа
                                                    </span>
                                            @elseif($lot->user_status === 'leading')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                        🔥 Ваша ставка лучшая
                                                    </span>
                                            @elseif($lot->user_status === 'outbid')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        ⚠️ Ставка перебита
                                                    </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        Завершено
                                                    </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('lots.show', $lot) }}" class="text-indigo-600 hover:text-indigo-900">Смотреть</a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @foreach($lots as $lot)
            window.Echo.channel('lots.{{ $lot->id }}')
                .listen('.BidPlaced', (e) => {
                    // Обновляем цену в таблице
                    const priceEl = document.getElementById('lot-price-{{ $lot->id }}');
                    if (priceEl) {
                        priceEl.innerText = new Intl.NumberFormat('ru-RU').format(e.lot.current_price) + ' ₽';
                    }

                    // Обновляем статус (перебита ставка или нет)
                    const statusEl = document.getElementById('lot-status-{{ $lot->id }}');
                    const currentUserId = {{ Auth::id() }};

                    if (statusEl) {
                        if (e.bid.user_id === currentUserId) {
                            statusEl.innerHTML = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">🔥 Ваша ставка лучшая</span>';
                        } else {
                            statusEl.innerHTML = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">⚠️ Ставка перебита</span>';
                        }
                    }
                })
                .listen('.LotWon', (e) => {
                    const statusEl = document.getElementById('lot-status-{{ $lot->id }}');
                    const currentUserId = {{ Auth::id() }};

                    if (statusEl) {
                        if (e.lot.winner_id === currentUserId) {
                            statusEl.innerHTML = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">🏆 Победа</span>';
                        } else {
                            statusEl.innerHTML = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Завершено</span>';
                        }
                    }
                });
            @endforeach
        });
    </script>
</x-app-layout>
