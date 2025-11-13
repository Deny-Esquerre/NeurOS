from bleak import BleakClient
address = "02:27:5E:98:C7:E4"  # MAC de tu reloj

async def connect():
    async with BleakClient(address) as client:
        print("Conectado:", client.is_connected)
        services = await client.get_services()
        for service in services:
            print(service)

import asyncio
asyncio.run(connect())
