<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">

             <div class="rounded-xl bg-purple-600 text-white p-4 shadow-md">
                <h3 class="text-lg font-semibold">Proyectos activos</h3>
                <p class="text-2xl font-bold">5</p>
                <a href="#" class="text-sm underline">Ver más</a>
            </div>

            <div class="rounded-xl bg-purple-600 text-white p-4 shadow-md">
                <h3 class="text-lg font-semibold">Tareas por hacer</h3>
                <p class="text-2xl font-bold">3</p>
                <a href="#" class="text-sm underline">Ver más</a>
            </div>



        </div>
    </div>
</x-layouts.app>
