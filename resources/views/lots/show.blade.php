<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $lot->title }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        .price-updated { animation: highlight 1s ease-out; }
        @keyframes highlight {
            0% { transform: scale(1.1); color: #16a34a; }
            100% { transform: scale(1); }
        }
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen p-8">

<div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-2 w-80"></div>

<div class="max-w-3xl mx-auto">

    <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
        <div class="p-8">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $lot->title }}</h1>
                    <p class="text-gray-500 mt-2">{{ $lot->description }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">До конца</p>
                    <div id="auction-timer" class="text-2xl font-mono font-bold text-gray-800 bg-gray-100 px-3 py-1 rounded transition-colors">
                        --:--:--
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 text-center mb-8">
                <p class="text-gray-500 uppercase text-xs font-semibold tracking-wider mb-1">Текущая цена</p>
                <p class="text-5xl font-black text-gray-900 tracking-tight">
                        <span id="current-price" class="inline-block transition-all">
                            {{ number_format($lot->current_price, 0, '.', ' ') }}
                        </span>
                    <span class="text-2xl text-gray-400">₽</span>
                </p>
            </div>

            <div id="bidding-area" class="flex flex-col gap-3">
                <div class="flex gap-3">
                    <div class="relative w-full">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">₽</span>
                        <input type="number" id="bid-amount"
                               class="pl-8 border-2 border-gray-200 p-4 rounded-xl w-full text-xl font-bold focus:border-blue-500 focus:ring-0 outline-none transition-colors"
                               placeholder="Минимум {{ $lot->current_price + 10 }}"
                               value="{{ $lot->current_price + 10 }}"
                               min="{{ $lot->current_price + 10 }}">
                    </div>
                    <button id="bid-btn" onclick="placeBid({{ $lot->id }})"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-bold text-lg transition-transform active:scale-95 shadow-md shadow-blue-200">
                        Ставка
                    </button>
                </div>
                <p id="error-message" class="text-red-500 text-sm font-medium h-5 ml-1"></p>
            </div>

            <div id="auction-ended-msg" class="hidden text-center p-4 bg-red-50 text-red-600 rounded-xl font-bold border border-red-100">
                Аукцион завершен
            </div>
        </div>
    </div>

    <div class="mt-8">
        <h3 class="text-lg font-bold text-gray-700 mb-4 flex items-center gap-2">История ставок</h3>
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <ul id="bids-list" class="divide-y divide-gray-100">
                @forelse($lot->bids()->with('user')->latest()->get() as $bid)
                    <li class="p-4 flex justify-between items-center hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                                {{ substr($bid->user->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="font-bold text-gray-800 text-sm">{{ $bid->user->name }}</p>
                                <p class="text-gray-400 text-xs">{{ $bid->created_at->format('H:i:s') }}</p>
                            </div>
                        </div>
                        <span class="font-mono font-bold text-gray-700">{{ number_format($bid->amount, 0, '.', ' ') }} ₽</span>
                    </li>
                @empty
                    <li id="empty-history" class="p-8 text-center text-gray-400 text-sm">История пуста</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

<script>
    let currentEndTime = {{ $lot->ends_at ? $lot->ends_at->timestamp * 1000 : 'null' }};
    let timerInterval = null;

    document.addEventListener('DOMContentLoaded', function () {
        if (currentEndTime) startTimer(currentEndTime);

        window.Echo.channel('lots.{{ $lot->id }}')
            .listen('.BidPlaced', (e) => {
                if (e.ends_at && e.ends_at !== currentEndTime) {
                    currentEndTime = e.ends_at;
                    startTimer(currentEndTime);
                    showNotification("Система", "Аукцион продлен на 1 минуту!");
                }
                handleNewBid(e);
            })
            .listen('.LotWon', (e) => {
                console.log('Аукцион закрыт!', e);

                if (timerInterval) clearInterval(timerInterval);

                document.getElementById('auction-timer').innerText = "ЗАВЕРШЕН";
                document.getElementById('auction-timer').className = "text-2xl font-bold text-red-600 bg-red-100 px-3 py-1 rounded";

                document.getElementById('bidding-area').classList.add('hidden');

                const endedMsg = document.getElementById('auction-ended-msg');
                endedMsg.classList.remove('hidden');
                endedMsg.innerHTML = `
            <div class="text-xl">🏆 Лот продан!</div>
            <div class="mt-2 text-gray-700">Победитель: <span class="font-bold text-green-600 text-2xl">${e.winner_name}</span></div>
            <div class="text-sm text-gray-500">Финальная цена: ${e.final_price} ₽</div>
        `;

                showNotification("АУКЦИОН ЗАВЕРШЕН", `Победил ${e.winner_name}!`);
            });
    });

    function startTimer(endTimeMs) {
        if (timerInterval) clearInterval(timerInterval);

        const timerElement = document.getElementById('auction-timer');
        const bidArea = document.getElementById('bidding-area');
        const endedMsg = document.getElementById('auction-ended-msg');

        function update() {
            const now = new Date().getTime();
            const distance = endTimeMs - now;

            if (distance < 0) {
                clearInterval(timerInterval);
                timerElement.innerText = "00:00:00";
                timerElement.classList.replace('text-gray-800', 'text-red-600');
                bidArea.classList.add('hidden');
                endedMsg.classList.remove('hidden');
                return;
            }

            // Сброс красного цвета, если время продлили
            if (distance > 60000) {
                timerElement.classList.remove('text-red-500', 'animate-pulse');
                timerElement.classList.add('text-gray-800');
            }

            const hours = Math.floor((distance / (1000 * 60 * 60)));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            timerElement.innerText = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

            if (distance < 30000) { // Меньше 30 сек
                timerElement.classList.add('text-red-500', 'animate-pulse');
            }
        }

        update();
        timerInterval = setInterval(update, 1000);
    }

    function handleNewBid(e) {
        showNotification(e.user_name, e.amount);

        const priceEl = document.getElementById('current-price');
        if (priceEl) {
            priceEl.innerText = new Intl.NumberFormat('ru-RU').format(e.amount);
            priceEl.classList.add('price-updated');
            setTimeout(() => priceEl.classList.remove('price-updated'), 1000);
        }

        const bidInput = document.getElementById('bid-amount');
        if (bidInput) {
            const nextMin = parseInt(e.amount) + 10;
            bidInput.setAttribute('min', nextMin);
            bidInput.setAttribute('placeholder', `Минимум ${nextMin}`);
            if (parseInt(bidInput.value) < nextMin) bidInput.value = nextMin;
        }

        const list = document.getElementById('bids-list');
        if (list) {
            const empty = document.getElementById('empty-history');
            if (empty) empty.remove();

            const fmtAmount = new Intl.NumberFormat('ru-RU').format(e.amount);
            const html = `
                    <li class="p-4 flex justify-between items-center bg-blue-50 animate-fade-in">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs">${e.user_name.charAt(0)}</div>
                            <div>
                                <p class="font-bold text-gray-800 text-sm">${e.user_name}</p>
                                <p class="text-gray-400 text-xs">${e.created_at}</p>
                            </div>
                        </div>
                        <span class="font-mono font-bold text-blue-600">${fmtAmount} ₽</span>
                    </li>
                `;
            list.insertAdjacentHTML('afterbegin', html);
        }
    }

    async function placeBid(lotId) {
        const amountInput = document.getElementById('bid-amount');
        const errorElement = document.getElementById('error-message');
        errorElement.innerText = '';

        try {
            const response = await fetch(`/lots/${lotId}/bids`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ amount: amountInput.value })
            });

            const data = await response.json();
            if (!response.ok) errorElement.innerText = data.error || data.message || 'Ошибка';
        } catch (error) {
            errorElement.innerText = 'Ошибка сети';
        }
    }

    function showNotification(name, amount) {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `bg-white border-l-4 border-blue-500 shadow-xl p-4 rounded-lg flex items-center justify-between transform transition-all duration-500 translate-x-full opacity-0`;

        toast.innerHTML = `
                <div>
                    <p class="font-bold text-gray-900 text-sm">${name === "Система" ? "ℹ️ " + name : "🔥 Новая ставка!"}</p>
                    <p class="text-xs text-gray-500 mt-1">${name === "Система" ? amount : name + " дает " + amount + " ₽"}</p>
                </div>
            `;

        container.appendChild(toast);
        requestAnimationFrame(() => toast.classList.remove('translate-x-full', 'opacity-0'));
        setTimeout(() => {
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }
</script>
</body>
</html>
