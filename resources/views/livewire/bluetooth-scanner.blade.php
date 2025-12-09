<div>


    @if ($connectedDevice)
        <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-md mb-4">
            <p class="font-bold">Dispositivo Conectado:</p>
            <p>{{ $connectedDevice['name'] ?? 'Dispositivo Desconocido' }} (ID: {{ $connectedDevice['id'] }})</p>
            <x-filament::button
                wire:click="disconnect"
                color="danger"
                size="sm"
                class="mt-2"
            >
                Desconectar
            </x-filament::button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4"> {{-- Changed to grid-cols-3 for more control --}}
            {{-- Columna izquierda: Botón y Mensajes --}}
            <div class="md:col-span-2"> {{-- Occupy 2/3 of the space --}}
                <x-filament::button
                    wire:click="startScan"
                    wire:loading.attr="disabled"
                    color="warning"
                    class="mb-4"
                >
                    <span wire:loading.remove wire:target="startScan">
                        Escanear Dispositivos Bluetooth
                    </span>
                    <span wire:loading wire:target="startScan">
                        Escaneando...
                    </span>
                </x-filament::button>

                
                @if (count($devices) > 0)
                    <h3 class="text-lg font-medium mt-6 mb-2">Dispositivos Encontrados:</h3>
                    <div class="fi-ta overflow-hidden border border-gray-200 dark:border-white/10 rounded-xl">
                        <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                            <thead class="bg-gray-50 dark:bg-white/5">
                                <tr class="fi-ta-header-row">
                                                                <th class="fi-ta-header-cell px-3 py-2 text-sm font-medium text-gray-900 dark:text-white text-left">
                                                                    Dispositivo
                                                                </th>
                                                                <th class="fi-ta-header-cell px-3 py-2 text-sm font-medium text-gray-900 dark:text-white text-left hidden md:table-cell"> {{-- Hidden on small screens --}}
                                                                    ID
                                                                </th>
                                                                <th class="fi-ta-header-cell px-3 py-2 text-sm font-medium text-gray-900 dark:text-white text-end">
                                                                    Acción
                                                                </th>                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                                @foreach ($devices as $device)
                                    <tr class="fi-ta-row">
                                                                        <td class="fi-ta-cell p-3">
                                                                            <span class="font-medium">{{ $device['name'] ?? 'Dispositivo Desconocido' }}</span>
                                                                        </td>
                                                                        <td class="fi-ta-cell p-3 text-xs text-gray-500 dark:text-gray-400 hidden md:table-cell"> {{-- Hidden on small screens --}}
                                                                            {{ $device['id'] }}
                                                                        </td>
                                                                        <td class="fi-ta-cell p-3 text-end" x-data="{}">                                            <x-filament::button
                                                color="warning"
                                                size="sm"
                                                x-on:click="connectToDevice('{{ $device['id'] }}')"
                                            >
                                                Conectar
                                            </x-filament::button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                                </div>
                                {{-- Bloque "Buscando dispositivos..." movido aquí --}}
                                            @if ($scanActive)
                                                <div class="fi-ta-empty-state px-6 py-12">
                                                    <h4 class="fi-ta-empty-state-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                                                        Buscando dispositivos...
                                                    </h4>
                                                    <p class="fi-ta-empty-state-description text-sm text-gray-500 dark:text-gray-400">
                                                        Por favor, selecciona un dispositivo desde la ventana del navegador.
                                                    </p>
                                                                        <div class="fi-ta-empty-state-icon-ctn mb-4 flex justify-center mt-6"> {{-- Increased mt-4 to mt-6 for more separation --}}
                                                                             <x-filament::loading-indicator class="h-8 w-8 text-gray-500" />
                                                                        </div>                                                </div>
                                            @endif                    
                            @elseif (!$scanActive && count($devices) === 0)
                                <div class="p-4 text-center text-gray-500 bg-gray-50 rounded-md dark:bg-gray-800 dark:text-gray-400">
                                    <p>No se han encontrado dispositivos previamente.</p>
                                    <p class="text-sm mt-1">Usa el botón de escaneo para encontrar una pulsera Bluetooth.</p>
                                </div>
                            @endif            </div>

            {{-- Columna derecha: Panel de Instrucciones --}}
            <div class="md:col-span-1"> {{-- Occupy 1/3 of the space --}}
                <x-filament::card>
                                    <h4 class="text-lg font-bold text-center mb-2">Conecta tu Pulsera Bluetooth</h4>
                                                                    <ol class="text-gray-600 dark:text-gray-400 space-y-2"> {{-- Removed list-decimal list-inside --}}
                                                                        <li><span class="font-bold">1.</span> Haz clic en el botón <span class="font-semibold">"Escanear Dispositivos Bluetooth"</span> de arriba.</li>
                                                                        <li><span class="font-bold">2.</span> Aparecerá una ventana de tu navegador con una lista de dispositivos cercanos.</li>
                                                                        <li><span class="font-bold">3.</span> Selecciona tu pulsera de la lista y haz clic en <span class="font-semibold">"Emparejar"</span> o <span class="font-semibold">"Conectar"</span>.</li>
                                                                        <li><span class="font-bold">4.</span> Si la pulsera se empareja correctamente, aparecerá en la sección <span class="font-semibold">"Dispositivos Encontrados"</span>.</li>
                                                                        <li><span class="font-bold">5.</span> Haz clic en <span class="font-semibold">"Conectar"</span> junto al nombre de tu pulsera en la tabla.</li>
                                                                    </ol>
                                                                                                    <p class="text-center text-red-800 font-bold text-sm mt-6"> {{-- Changed to text-red-800 and added font-bold --}}
                                                                                                        Si no aparece, inténtalo de nuevo o revisa las instrucciones de tu pulsera.
                                                                                                    </p>                </x-filament::card>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('livewire:initialized', () => {
            let bluetoothDevice = null;
            let gattServer = null; // Store the GATT server connection
            let disconnectionListener = null;

            @this.on('start-bluetooth-scan', async () => {
                try {
                    @this.set('scanActive', true);

                    const scanTimeout = setTimeout(() => {
                        if (@this.get('scanActive')) {
                            console.warn('Escaneo de Bluetooth ha excedido el tiempo de espera.');
                            @this.set('scanActive', false);
                        }
                    }, 15000); // 15 seconds timeout

                    if (!navigator.bluetooth) {
                        alert('Web Bluetooth API no soportada en este navegador.');
                        @this.set('scanActive', false);
                        clearTimeout(scanTimeout);
                        return;
                    }

                    const device = await navigator.bluetooth.requestDevice({
                        acceptAllDevices: true,
                        optionalServices: ['battery_service', 'heart_rate']
                    });

                    clearTimeout(scanTimeout);

                    @this.call('addDevice', {
                        id: device.id,
                        name: device.name
                    });

                    bluetoothDevice = device;
                    if (disconnectionListener) {
                        bluetoothDevice.removeEventListener('gattserverdisconnected', disconnectionListener);
                    }
                    disconnectionListener = onDisconnected;
                    bluetoothDevice.addEventListener('gattserverdisconnected', disconnectionListener);

                    console.log('Dispositivo Bluetooth seleccionado:', device.name || device.id);

                } catch (error) {
                    console.error('Error al escanear dispositivos Bluetooth:', error);
                    @this.set('scanActive', false);
                }
            });

            @this.on('disconnect-bluetooth-device', async (event) => {
                const deviceIdToDisconnect = event.detail.deviceId;
                if (bluetoothDevice && bluetoothDevice.id === deviceIdToDisconnect && gattServer && gattServer.connected) {
                    console.log('Desconectando dispositivo Bluetooth...');
                    await bluetoothDevice.gatt.disconnect();
                    gattServer = null;
                    @this.call('deviceDisconnected', { id: deviceIdToDisconnect, name: bluetoothDevice.name });
                } else {
                    console.log('No hay dispositivo conectado para desconectar o no coincide el ID.');
                    @this.call('deviceDisconnected', { id: deviceIdToDisconnect, name: bluetoothDevice ? bluetoothDevice.name : 'Unknown' });
                }
            });

            function onDisconnected(event) {
                const device = event.target;
                console.log(`Dispositivo Bluetooth ${device.name || device.id} desconectado.`);
                @this.call('deviceDisconnected', { id: device.id, name: device.name });
                gattServer = null;
                bluetoothDevice = null;
                if (disconnectionListener) {
                    device.removeEventListener('gattserverdisconnected', disconnectionListener);
                    disconnectionListener = null;
                }
            }

            window.connectToDevice = async (deviceId) => {
                try {
                    if (!navigator.bluetooth) {
                        alert('Web Bluetooth API no soportada en este navegador.');
                        return;
                    }

                    if (!bluetoothDevice || bluetoothDevice.id !== deviceId) {
                        console.warn('Intentando conectar a un dispositivo no escaneado o diferente al último. Buscando en dispositivos previamente emparejados.');
                        const devices = await navigator.bluetooth.getDevices();
                        bluetoothDevice = devices.find(d => d.id === deviceId);

                        if (!bluetoothDevice) {
                             alert('Dispositivo no encontrado o no emparejado. Escanee de nuevo.');
                             return;
                        }

                        if (disconnectionListener) {
                            bluetoothDevice.removeEventListener('gattserverdisconnected', disconnectionListener);
                        }
                        disconnectionListener = onDisconnected;
                        bluetoothDevice.addEventListener('gattserverdisconnected', disconnectionListener);
                    }

                    if (gattServer && gattServer.connected) {
                        console.log('Ya conectado al dispositivo.');
                        @this.call('deviceConnected', { id: bluetoothDevice.id, name: bluetoothDevice.name });
                        return;
                    }

                    console.log('Conectando al servidor GATT...');
                    gattServer = await bluetoothDevice.gatt.connect();
                    console.log('Servidor GATT conectado.', gattServer);

                    @this.call('deviceConnected', { id: bluetoothDevice.id, name: bluetoothDevice.name });

                } catch (error) {
                    console.error('Error al conectar o leer del dispositivo Bluetooth:', error);
                    alert('Error al conectar con el dispositivo Bluetooth: ' + error.message);
                    gattServer = null;
                    if (bluetoothDevice) {
                        bluetoothDevice.removeEventListener('gattserverdisconnected', disconnectionListener);
                        disconnectionListener = null;
                    }
                    @this.call('deviceDisconnected', { id: deviceId, name: bluetoothDevice ? bluetoothDevice.name : 'Unknown' });
                }
            };
        });
    </script>
</div>


