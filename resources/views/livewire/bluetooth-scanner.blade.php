<div>
    <h2 class="text-xl font-semibold mb-4">Escáner de Pulseras Activas Bluetooth</h2>

    @if ($connectedDevice)
        <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-md mb-4">
            <p class="font-bold">Dispositivo Conectado:</p>
            <p>{{ $connectedDevice['name'] ?? 'Dispositivo Desconocido' }} (ID: {{ $connectedDevice['id'] }})</p>
            <button wire:click="disconnect" class="mt-2 filament-button filament-button-danger text-sm">Desconectar</button>
        </div>
    @else
        <button wire:click="startScan" wire:loading.attr="disabled" class="filament-button bg-orange-500 text-white hover:bg-orange-600 focus:ring-orange-500">
            <span wire:loading.remove>Escanear Dispositivos Bluetooth</span>
            <span wire:loading>Escaneando...</span>
        </button>

        @if ($scanActive)
            <p class="mt-2 text-gray-500 text-center">Buscando dispositivos...</p>
        @endif

        @if (count($devices) > 0)
            <h3 class="text-lg font-medium mt-4">Dispositivos Encontrados:</h3>
            <ul class="mt-2 space-y-2">
                @foreach ($devices as $device)
                    <li class="flex items-center justify-between p-3 bg-gray-100 rounded-md">
                        <span>{{ $device['name'] ?? 'Dispositivo Desconocido' }} (ID: {{ $device['id'] }})</span>
                        <button class="filament-button filament-button-secondary text-sm" onclick="connectToDevice('{{ $device['id'] }}')">Conectar</button>
                    </li>
                @endforeach
            </ul>
        @elseif (!$scanActive && count($devices) === 0)
            <div class="mt-4 p-4 text-center text-gray-500 bg-gray-50 rounded-md">
                <p>No se encontraron dispositivos.</p>
                <p class="text-sm mt-1">Asegúrate de que tu pulsera Bluetooth esté encendida y visible.</p>
            </div>
        @endif
    @endif

    <script>
        document.addEventListener('livewire:initialized', () => {
            let bluetoothDevice = null;
            let gattServer = null; // Store the GATT server connection
            let disconnectionListener = null;

            @this.on('start-bluetooth-scan', async () => {
                try {
                    @this.set('devices', []); // Clear previous devices
                    @this.set('scanActive', true);

                    // Add a timeout for the scan
                    const scanTimeout = setTimeout(() => {
                        if (@this.get('scanActive')) {
                            console.warn('Escaneo de Bluetooth ha excedido el tiempo de espera.');
                            @this.set('scanActive', false);
                            // Do not clear devices here, user might want to try connecting to previously found ones
                        }
                    }, 15000); // 15 seconds timeout

                    if (!navigator.bluetooth) {
                        alert('Web Bluetooth API no soportada en este navegador.');
                        @this.set('scanActive', false);
                        clearTimeout(scanTimeout);
                        return;
                    }

                    const device = await navigator.bluetooth.requestDevice({
                        //filters: [{ services: ['heart_rate'] }], // Example: Filter for heart rate service
                        acceptAllDevices: true, // Or accept all devices
                        optionalServices: ['battery_service', 'heart_rate'] // Request access to optional services
                    });

                    clearTimeout(scanTimeout); // Clear the timeout if a device is found

                    @this.call('updateDevices', [{
                        id: device.id,
                        name: device.name
                    }]);

                    bluetoothDevice = device;
                    if (disconnectionListener) {
                        bluetoothDevice.removeEventListener('gattserverdisconnected', disconnectionListener);
                    }
                    disconnectionListener = onDisconnected;
                    bluetoothDevice.addEventListener('gattserverdisconnected', disconnectionListener);

                    console.log('Dispositivo Bluetooth encontrado:', device.name || device.id);

                } catch (error) {
                    console.error('Error al escanear dispositivos Bluetooth:', error);
                    @this.set('scanActive', false);
                    clearTimeout(scanTimeout); // Clear the timeout on error
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
                    // If Livewire thinks it's connected but JS doesn't, force state update
                    @this.call('deviceDisconnected', { id: deviceIdToDisconnect, name: bluetoothDevice ? bluetoothDevice.name : 'Unknown' });
                }
            });

            function onDisconnected(event) {
                const device = event.target;
                console.log(`Dispositivo Bluetooth ${device.name || device.id} desconectado.`);
                @this.call('deviceDisconnected', { id: device.id, name: device.name });
                gattServer = null;
                bluetoothDevice = null; // Clear the device reference
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

                        // Re-add event listener if we fetched a new device instance
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

                    // Example: Read Battery Service (uncomment and customize as needed)
                    // const batteryService = await gattServer.getPrimaryService('battery_service');
                    // const batteryLevelCharacteristic = await batteryService.getCharacteristic('battery_level');
                    // const batteryLevel = await batteryLevelCharacteristic.readValue();
                    // console.log('Nivel de batería:', batteryLevel.getUint8(0) + '%');

                    // Example: Read Heart Rate Measurement (uncomment and customize as needed)
                    // const heartRateService = await gattServer.getPrimaryService('heart_rate');
                    // const heartRateMeasurementCharacteristic = await heartRateService.getCharacteristic('heart_rate_measurement');
                    // await heartRateMeasurementCharacteristic.startNotifications();
                    // heartRateMeasurementCharacteristic.addEventListener('characteristicvaluechanged', (event) => {
                    //     const value = event.target.value;
                    //     // Parse heart rate data (refer to Bluetooth GATT specification for details)
                    //     const heartRate = value.getUint8(1); // Assuming 2nd byte is heart rate value
                    //     console.log('Frecuencia cardíaca:', heartRate);
                    //     // You can then send this data back to Livewire if needed
                    //     // @this.call('updateHeartRate', heartRate);
                    // });

                } catch (error) {
                    console.error('Error al conectar o leer del dispositivo Bluetooth:', error);
                    alert('Error al conectar con el dispositivo Bluetooth: ' + error.message);
                    gattServer = null; // Ensure gattServer is null on error
                    if (bluetoothDevice) {
                        // If connection failed, remove listener to avoid multiple onDisconnected calls
                        bluetoothDevice.removeEventListener('gattserverdisconnected', disconnectionListener);
                        disconnectionListener = null;
                    }
                    @this.call('deviceDisconnected', { id: deviceId, name: bluetoothDevice ? bluetoothDevice.name : 'Unknown' });
                }
            };
        });
    </script>
</div>
