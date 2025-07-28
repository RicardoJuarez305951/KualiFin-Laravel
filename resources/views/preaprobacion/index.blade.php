{{-- resources/views/preaprobaciones/index.blade.php --}}
<x-layouts.authenticated>
    @php
        // Summary cards
        $summaryCards = [
            [ 'title' => 'Pre Aprobaciones', 'value' => 24, 'change' => '+3.5%',  'changeType' => 'positive' ],
            [ 'title' => 'Pendientes',       'value' => 8,  'change' => '-2.1%',  'changeType' => 'negative' ],
            [ 'title' => 'Aprobadas',        'value' => 12, 'change' => '+5.2%',  'changeType' => 'positive' ],
            [ 'title' => 'Rechazadas',       'value' => 4,  'change' => '-1.5%',  'changeType' => 'negative' ],
        ];

        // Active applications
        $activeApplications = [
            [
                'cliente'   => 'Juan Carlos Martínez',
                'monto'     => 35000,
                'estado'    => 'Pendiente',
                'fecha'     => '2025-07-28',
                'score'     => 85,
                'avatar'    => 'JM',
                'documents' => [
                    ['name' => 'Contrato.pdf',       'url' => '/storage/docs/contrato_jc.pdf'],
                    ['name' => 'Identificación.pdf', 'url' => '/storage/docs/id_jc.pdf'],
                ],
            ],
            [
                'cliente'   => 'Ana María López',
                'monto'     => 42000,
                'estado'    => 'Aprobado',
                'fecha'     => '2025-07-27',
                'score'     => 92,
                'avatar'    => 'AL',
                'documents' => [
                    ['name' => 'Solicitud.pdf', 'url' => '/storage/docs/solicitud_am.pdf'],
                ],
            ],
            // …más registros…
        ];

        function formatCurrency($v) {
            return '$' . number_format($v, 0, ',', '.');
        }
        function getScoreColor($s) {
            return match (true) {
                $s >= 90 => 'bg-green-100 text-green-800 border-green-300',
                $s >= 70 => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                default  => 'bg-red-100 text-red-800 border-red-300',
            };
        }
        function getStatusBadgeColor($e) {
            return match ($e) {
                'Aprobado'  => 'bg-green-100 text-green-800 border-green-300',
                'Pendiente' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                'Rechazado' => 'bg-red-100 text-red-800 border-red-300',
                default     => 'bg-gray-100 text-gray-800 border-gray-300',
            };
        }
    @endphp

    <div class="min-h-screen bg-gray-50 p-6">
        <div class="max-w-7xl mx-auto space-y-8">
            {{-- Header --}}
            <div class="text-center space-y-2">
                <h1 class="text-4xl font-bold text-gray-900">Panel de Pre Aprobación</h1>
                <p class="text-lg text-gray-600">Gestión y seguimiento de solicitudes de pre aprobación</p>
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($summaryCards as $card)
                    <div class="bg-white rounded-lg shadow-sm border p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between pb-2">
                            <div class="text-2xl">📋</div>
                            <div class="text-sm font-medium {{ $card['changeType'] === 'positive' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $card['change'] }}
                            </div>
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-gray-600">{{ $card['title'] }}</p>
                            <p class="text-2xl font-bold">{{ $card['value'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Active Applications Table --}}
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="p-6 border-b flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                        <span class="text-2xl">📝</span> Solicitudes Activas
                    </h2>
                    <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        Nueva Pre Aprobación
                    </button>
                </div>
                <div class="p-6 overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="py-3 px-4 text-left font-medium text-gray-700">Cliente</th>
                                <th class="py-3 px-4 text-left font-medium text-gray-700">Monto</th>
                                <th class="py-3 px-4 text-left font-medium text-gray-700">Score</th>
                                <th class="py-3 px-4 text-left font-medium text-gray-700">Estado</th>
                                <th class="py-3 px-4 text-left font-medium text-gray-700">Fecha</th>
                                <th class="py-3 px-4 text-left font-medium text-gray-700">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($activeApplications as $app)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-medium text-sm">
                                                {{ $app['avatar'] }}
                                            </div>
                                            <span class="font-medium">{{ $app['cliente'] }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 font-semibold">{{ formatCurrency($app['monto']) }}</td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border {{ getScoreColor($app['score']) }}">
                                            Score: {{ $app['score'] }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border {{ getStatusBadgeColor($app['estado']) }}">
                                            {{ $app['estado'] }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">{{ $app['fecha'] }}</td>
                                    <td class="py-3 px-4">
                                        <button
                                            class="view-btn flex items-center gap-1 text-blue-600 hover:text-blue-800"
                                            data-index="{{ $loop->index }}"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span class="text-sm">Ver</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Modal --}}
        <div
            id="detailModal"
            class="hidden fixed inset-0 z-50 flex items-center justify-center"
        >
            {{-- Backdrop --}}
            <div
                id="modalBackdrop"
                class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm"
            ></div>

            {{-- Panel --}}
            <div
                class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-8"
            >
                {{-- Close --}}
                <button
                    id="modalClose"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <h3 class="text-2xl font-bold text-gray-900 mb-6">Detalle de Solicitud</h3>

                {{-- Details Grid --}}
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="font-medium text-gray-700">Cliente:</dt>
                        <dd id="modalCliente" class="mt-1 text-gray-900"></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-700">Monto:</dt>
                        <dd id="modalMonto" class="mt-1 text-gray-900"></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-700">Score:</dt>
                        <dd>
                            <span
                                id="modalScore"
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border"
                            ></span>
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-700">Estado:</dt>
                        <dd>
                            <span
                                id="modalEstado"
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border"
                            ></span>
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="font-medium text-gray-700">Fecha:</dt>
                        <dd id="modalFecha" class="mt-1 text-gray-900"></dd>
                    </div>
                </dl>

                {{-- Documents --}}
                <div class="mt-6">
                    <h4 class="font-medium text-gray-700 mb-2">Documentos Subidos</h4>
                    <ul id="modalDocsList" class="list-disc pl-5 space-y-1 text-sm"></ul>
                </div>

                {{-- Approve / Reject --}}
                <div class="mt-8 flex justify-end gap-3">
                    <button
                        id="approveBtn"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                    >
                        Aprobar
                    </button>
                    <button
                        id="rejectBtn"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                    >
                        Rechazar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Vanilla JS to handle modal --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const applications = @json($activeApplications);

        const modal        = document.getElementById('detailModal');
        const backdrop     = document.getElementById('modalBackdrop');
        const closeBtn     = document.getElementById('modalClose');
        const approveBtn   = document.getElementById('approveBtn');
        const rejectBtn    = document.getElementById('rejectBtn');

        const elCliente    = document.getElementById('modalCliente');
        const elMonto      = document.getElementById('modalMonto');
        const elScore      = document.getElementById('modalScore');
        const elEstado     = document.getElementById('modalEstado');
        const elFecha      = document.getElementById('modalFecha');
        const elDocsList   = document.getElementById('modalDocsList');

        function openModal(idx) {
            const app = applications[idx];

            elCliente.textContent = app.cliente;
            elMonto.textContent   = new Intl.NumberFormat('es-MX', {
                                        style: 'currency',
                                        currency: 'MXN'
                                     }).format(app.monto);

            elScore.textContent = app.score;
            elScore.className   = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border ' +
                                  (app.score >= 90 ? 'bg-green-100 text-green-800 border-green-300' :
                                  app.score >= 70 ? 'bg-yellow-100 text-yellow-800 border-yellow-300' :
                                                    'bg-red-100 text-red-800 border-red-300');

            elEstado.textContent = app.estado;
            elEstado.className   = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border ' +
                                  (app.estado === 'Aprobado'  ? 'bg-green-100 text-green-800 border-green-300' :
                                  app.estado === 'Pendiente' ? 'bg-yellow-100 text-yellow-800 border-yellow-300' :
                                                                'bg-red-100 text-red-800 border-red-300');

            elFecha.textContent = app.fecha;

            // Documents
            elDocsList.innerHTML = '';
            if (app.documents && app.documents.length) {
                app.documents.forEach(doc => {
                    const li = document.createElement('li');
                    const a  = document.createElement('a');
                    a.href        = doc.url;
                    a.target      = '_blank';
                    a.textContent = doc.name;
                    a.className   = 'text-blue-600 hover:underline';
                    li.append(a);
                    elDocsList.append(li);
                });
            } else {
                const li = document.createElement('li');
                li.textContent = 'No hay documentos adjuntos.';
                li.className   = 'text-gray-500';
                elDocsList.append(li);
            }

            modal.classList.remove('hidden');
        }

        function closeModal() {
            modal.classList.add('hidden');
        }

        // Attach handlers
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                openModal(btn.dataset.index);
            });
        });
        closeBtn.addEventListener('click', closeModal);
        backdrop.addEventListener('click', closeModal);
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeModal();
        });

        approveBtn.addEventListener('click', () => {
            // TODO: enviar aprobación via AJAX
            closeModal();
        });
        rejectBtn.addEventListener('click', () => {
            // TODO: enviar rechazo via AJAX
            closeModal();
        });
    });
    </script>
</x-layouts.authenticated>
