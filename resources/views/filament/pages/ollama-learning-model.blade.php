<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Estadísticas del Modelo de Aprendizaje (Ollama)
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

           {{-- Las 4 tarjetas de estadísticas centradas con charts dinámicos reales --}}
<div class="lg:col-start-1 lg:col-span-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <x-filament::card class="flex flex-col justify-center items-center h-40">
        <p class="text-lg font-medium text-center">Total Peticiones:</p>
        <p class="text-3xl font-bold text-center">{{ $this->totalRequests }}</p>
        <canvas id="chartTotal" class="w-full h-16 mt-2"></canvas>
    </x-filament::card>

    <x-filament::card class="flex flex-col justify-center items-center h-40">
        <p class="text-lg font-medium text-center">Peticiones Exitosas:</p>
        <p class="text-3xl font-bold text-center">{{ $this->totalSuccessfulRequests }}</p>
        <canvas id="chartSuccess" class="w-full h-16 mt-2"></canvas>
    </x-filament::card>

    <x-filament::card class="flex flex-col justify-center items-center h-40">
        <p class="text-lg font-medium text-center">Peticiones Fallidas:</p>
        <p class="text-3xl font-bold text-center">{{ $this->totalFailedRequests }}</p>
        <canvas id="chartFailed" class="w-full h-16 mt-2"></canvas>
    </x-filament::card>

    <x-filament::card class="flex flex-col justify-center items-center h-40">
        <p class="text-lg font-medium text-center">Tasa de Éxito:</p>
        <p class="text-3xl font-bold text-center">{{ $this->successRate }}%</p>
        <canvas id="chartRate" class="w-full h-16 mt-2"></canvas>
    </x-filament::card>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const cardCharts = [
    { id: 'chartTotal', value: {{ $this->totalRequests }}, color: '#3b82f6' },
    { id: 'chartSuccess', value: {{ $this->totalSuccessfulRequests }}, color: '#10b981' },
    { id: 'chartFailed', value: {{ $this->totalFailedRequests }}, color: '#ef4444' },
    { id: 'chartRate', value: {{ $this->successRate }}, color: '#f59e0b' },
];

cardCharts.forEach(card => {
    // Creamos un array de 7 puntos escalados proporcional al valor de la estadística
    const points = 7;
    const data = Array.from({length: points}, (_, i) => Math.round(card.value * (i + 1) / points));

    new Chart(document.getElementById(card.id), {
        type: 'line',
        data: {
            labels: data.map((_, i) => i + 1),
            datasets: [{
                data: data,
                borderColor: card.color,
                backgroundColor: card.color + '33', // color con transparencia
                tension: 0.4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { display: false },
                y: { display: false, min: 0, max: card.value } // escala proporcional
            }
        }
    });
});
</script>


            {{-- La tarjeta de últimas peticiones --}}
           <x-filament::card class="md:col-span-2 lg:col-span-4 lg:col-start-1">
    <p class="text-lg font-medium text-left mb-4">Últimas 3 Peticiones:</p>

    @forelse ($this->latestRequests as $request)
        <div class="mb-4 p-4 border rounded-lg {{ $request->status === 'success' ? 'bg-green-50 dark:bg-green-800' : 'bg-red-50 dark:bg-red-800' }}">
            <p class="text-sm text-gray-500 dark:text-gray-400 text-left">
                Estado: <span class="font-semibold">{{ ucfirst($request->status) }}</span>,
                Tipo: <span class="font-semibold">{{ $request->context['type'] ?? 'N/A' }}</span>,
                Edad: <span class="font-semibold">{{ $request->context['age'] ?? 'N/A' }}</span>,
                Tema: <span class="font-semibold">{{ $request->context['topic'] ?? 'N/A' }}</span>
            </p>
            <p class="text-xs text-gray-400 dark:text-gray-500 text-left">
                {{ $request->created_at->diffForHumans() }} por {{ $request->user->name ?? 'Invitado' }}
            </p>
            <p class="text-sm font-mono mt-2 truncate max-w-full text-left">
                Prompt: {{ Str::limit(json_decode($request->prompt, true)[1]['content'] ?? 'N/A', 150) }}
            </p>
            @if ($request->status === 'failed' && $request->response)
                <p class="text-sm text-red-700 dark:text-red-300 mt-2 text-left">
                    Error: {{ Str::limit(json_decode($request->response, true)['error'] ?? 'N/A', 150) }}
                </p>
            @endif
        </div>
    @empty
        <p class="text-gray-500 dark:text-gray-400 text-left">No hay peticiones registradas todavía.</p>
    @endforelse
</x-filament::card>


        </div>
    </x-filament::section>
</x-filament-panels::page>
