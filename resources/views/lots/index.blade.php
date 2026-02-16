<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Активные аукционы') }}
            </h2>
            @auth
                <a href="{{ route('lots.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-bold hover:bg-blue-700">
                    + Создать лот
                </a>
            @endauth
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if($lots->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-10 text-center">
                    <p class="text-gray-500 text-lg">Пока нет активных лотов.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($lots as $lot)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-300 flex flex-col h-full relative">

                            @if($lot->status === 'closed' || ($lot->ends_at && $lot->ends_at->isPast()))
                                <div class="absolute top-0 right-0 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-bl-lg z-10">
                                    ЗАВЕРШЕН
                                </div>
                            @else
                                <div class="absolute top-0 right-0 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-bl-lg z-10">
                                    ИДУТ ТОРГИ
                                </div>
                            @endif

                            <div class="p-6 flex flex-col flex-grow">
                                <h3 class="text-xl font-bold text-gray-900 mb-2 truncate" title="{{ $lot->title }}">
                                    {{ $lot->title }}
                                </h3>
                                <p class="text-gray-500 text-sm mb-4 line-clamp-3 flex-grow">
                                    {{ $lot->description }}
                                </p>

                                <div class="mt-4 border-t pt-4">
                                    <div class="flex justify-between items-end">
                                        <div>
                                            <p class="text-xs text-gray-400 uppercase font-bold">Текущая цена</p>
                                            <p class="text-2xl font-black text-gray-800">
                                                {{ number_format($lot->current_price, 0, '.', ' ') }} ₽
                                            </p>
                                        </div>
                                        <a href="{{ route('lots.show', $lot) }}" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-bold transition-colors">
                                            Участвовать
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
