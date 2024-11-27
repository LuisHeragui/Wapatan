<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue'

// Definición de variables.

// Variable para saber si se ha selccionado un producto.
const productSelected = ref(false);
// Variable para saber si debemos mostrar la modal.
const showModal = ref(false);
// Variable para saber si debemos mostrar el ticket.
const showTicket = ref(false);
// Variable para almacenar los Wapatan seleccionados.
const currentWaps = ref([]);
// Variable para almacenar el precio total.
const total = ref(0);

// Definición de los elementos recibidos desde el back.
const props = defineProps({
    products: {
        type: Array,
        required: true,
    },
    ingredients: {
        type: Array,
        required: true,
    },
});

// Función encargada de agregar el Wapatan seleccionado a la lista.
function addProduct(product) {
    productSelected.value = true;
    currentWaps.value.push({
        product: product,
        ingredients: props.ingredients.map(i => ({ ...i, portions: 0 })),
        total: product.price
    });
}

// Función encargada de habilitar la modal y mostrar el precio total.
function checkOrder() {
    showModal.value = true;
    total.value = currentWaps.value.reduce((accumulator, current) => accumulator + parseFloat(current.total), 0).toFixed(2);
}

// Función encargada de realizar la orden.
// TODO: Hacer petición al back para almacenar cada Wapatan en la base de datos.
function makeOrder() {
    showModal.value = false;
    showTicket.value = true;
    console.log('order', currentWaps.value);
}

// Función encargada de eliminar el Wapatan seleccionado.
function removeWap(index) {
    currentWaps.value.splice(index, 1);
    if (!currentWaps.value.length) {
        productSelected.value = false;
    }
}

// Función encargada de resetear todas las variables al estado inicial.
function restart() {
    productSelected.value = false;
    showTicket.value = false;
    currentWaps.value = [];
}

// Función encargada de actualizar el valor del total.
function updateTotal(index) {
    let total = 0;
    currentWaps.value[index].ingredients.forEach(ing => {
        total += (ing.price * ing.portions)
    });
    total += parseFloat(currentWaps.value[index].product.price);
    currentWaps.value[index].total = total.toFixed(2);
}
</script>

<template>
    <Head title="Wapatan" />
    <div class="relative bg-black bg-[url('/storage/bg.jpg')]">
        <div
            class="relative flex min-h-screen flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <header class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3">
                    <div class="flex lg:col-start-2 lg:justify-center">
                        <img :src="`/storage/logo.png`" alt="Logo" width="100">
                    </div>
                </header>

                <main>
                    <!-- Sección principal -->
                    <div v-if="!productSelected" class="grid gap-6 lg:grid-cols-2 lg:gap-8">
                        <div class="flex items-start gap-4 justify-center rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring ring-white ring-inset ring-offset-2 bg-[#E6D0BE] hover:bg-[#DDBFA7] hover:cursor-pointer"
                            v-for="product in products" :key="product.id" @click="addProduct(product)">
                            <div
                                class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FBFFAF] sm:size-16">
                                <img :src="`/storage/${product.svg}.svg`" :alt="product.name">
                            </div>

                            <div>
                                <h2 class="text-xl font-semibold text-black">
                                    {{ product.name }} (${{ product.price }})
                                </h2>

                                <p class="mt-4 text-sm/relaxed text-black">
                                    {{ product.description }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Sección para agregar ingredientes y más Wapatan's -->
                    <div v-else>
                        <!-- Muestra del ticket -->
                        <div v-if="showTicket">
                            <h2 class="text-3xl font-semibold text-white text-center mb-2">
                                Resumen de tu pedido
                            </h2>
                            <h3 class="text-xl font-semibold text-white text-center mb-2">
                                Puedes pasar a la caja a pagar
                            </h3>

                            <div class="bg-gray-100 p-2 rounded-lg h-auto max-h-[330px] overflow-y-auto">
                                <div class="grid grid-cols-2 gap-4">
                                    <h3 class="text-center text-lg font-bold">Wapatan</h3>
                                    <h3 class="text-center text-lg font-bold">Precio</h3>
                                </div>
    
                                <div v-for="(wapatan, key) in currentWaps" :key="key" class="my-2">
                                    <div class="grid grid-cols-2 gap-x-4">
                                        <h4 class="font-bold text-lg">{{ wapatan.product.name }}</h4>
                                        <h4 class="font-bold text-lg text-center">${{ wapatan.total }}</h4>
                                        <template v-for="ing in wapatan.ingredients" :key="ing.id">
                                            <template v-if="ing.portions != 0">
                                                <p class="ms-3">${{ ing.name }} (x{{ ing.portions }})</p>
                                                <p class="text-center">${{ (ing.price * ing.portions).toFixed(2) }}</p>
                                            </template>
                                        </template>
                                    </div>
                                </div>
    
                                <div class="grid grid-cols-2 gap-4">
                                    <h3 class="text-center text-lg font-bold">Total</h3>
                                    <h3 class="text-center text-lg font-bold">${{ total }}</h3>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="button"
                                    class="mt-6 px-6 py-4 text-center rounded-lg bg-[#D0E37F] text-black font-bold text-xl hover:bg-[#9EB828] hover:text-white"
                                    @click="restart()">Volver al inicio</button>
                            </div>
                        </div>

                        <!-- Selección de ingredientes -->
                        <div v-else class="text-center">
                            <div class="container mx-auto flex gap-4 overflow-x-auto p-4">
                                <div v-for="(wapatan, key) in currentWaps" :key="key"
                                    class="relative flex flex-col gap-4 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] bg-[#E6D0BE] ring ring-white ring-offset-2 shrink-0">
                                    <button type="button" class="absolute right-2 top-2 w-4" @click="removeWap(key)">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
                                            <path
                                                d="M376.6 84.5c11.3-13.6 9.5-33.8-4.1-45.1s-33.8-9.5-45.1 4.1L192 206 56.6 43.5C45.3 29.9 25.1 28.1 11.5 39.4S-3.9 70.9 7.4 84.5L150.3 256 7.4 427.5c-11.3 13.6-9.5 33.8 4.1 45.1s33.8 9.5 45.1-4.1L192 306 327.4 468.5c11.3 13.6 31.5 15.4 45.1 4.1s15.4-31.5 4.1-45.1L233.7 256 376.6 84.5z" />
                                        </svg>
                                    </button>
                                    <h2 class="text-xl font-semibold text-black">
                                        Selecciona los ingredientes para tu {{ wapatan.product.name }}
                                    </h2>

                                    <div class="flex flex-col gap-2 rounded-lg px-4 py-2 bg-[#FBFFAF] text-center"
                                        v-for="(ing, ingKey) in ingredients" :key="ing.id">
                                        <h3 class="text-xl font-semibold text-black">
                                            {{ ing.name }} (${{ ing.price }})
                                        </h3>
                                        <div>
                                            <label :for="`${ing.name}-${key}`" class="text-black">Porciones: </label>
                                            <input type="number" :name="`${ing.name}-${key}`" min="0"
                                                v-model="wapatan.ingredients[ingKey].portions" class="text-black"
                                                @change="updateTotal(key)">
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-6 rounded-lg p-6 text-center shrink-0">
                                    <h2 class="text-xl font-semibold text-white">
                                        Agregar un Wapatan
                                    </h2>

                                    <div class="flex items-start gap-4 justify-center rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring ring-white ring-inset ring-offset-2 bg-[#E6D0BE] hover:bg-[#DDBFA7] hover:cursor-pointer"
                                        v-for="product in products" :key="product.id" @click="addProduct(product)">
                                        <div
                                            class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FBFFAF] sm:size-16">
                                            <img :src="`/storage/${product.svg}.svg`" :alt="product.name">
                                        </div>

                                        <div>
                                            <h2 class="text-xl font-semibold text-black">
                                                {{ product.name }} (${{ product.price }})
                                            </h2>

                                            <p class="mt-4 text-sm/relaxed text-black">
                                                {{ product.description }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button"
                                class="mt-6 px-6 py-4 rounded-lg bg-[#D0E37F] text-black font-bold text-xl hover:bg-[#9EB828] hover:text-white"
                                @click="checkOrder()">Realizar pedido</button>
                        </div>
                    </div>
                </main>

                <footer class="py-16 text-center text-sm text-white font-bold">
                    Wapatan© 2024
                </footer>
            </div>
        </div>

        <!-- Contenido de la modal -->
        <div v-if="showModal" class="absolute flex w-full h-full top-0 right-0 bg-black/50">
            <div class="m-auto bg-emerald-50 p-5 rounded-lg w-1/3 max-h-[500px]">
                <h2 class="font-bold text-xl mb-5 text-center">Confirma tu pedido</h2>

                <div class="bg-gray-100 p-2 rounded-lg h-auto max-h-[330px] overflow-y-auto">
                    <div class="grid grid-cols-2 gap-4">
                        <h3 class="text-center text-lg font-bold">Wapatan</h3>
                        <h3 class="text-center text-lg font-bold">Precio</h3>
                    </div>

                    <div v-for="(wapatan, key) in currentWaps" :key="key" class="my-2">
                        <div class="grid grid-cols-2 gap-x-4">
                            <h4 class="font-bold text-lg">{{ wapatan.product.name }}</h4>
                            <h4 class="font-bold text-lg text-center">${{ wapatan.total }}</h4>
                            <template v-for="ing in wapatan.ingredients" :key="ing.id">
                                <template v-if="ing.portions != 0">
                                    <p class="ms-3">${{ ing.name }} (x{{ ing.portions }})</p>
                                    <p class="text-center">${{ (ing.price * ing.portions).toFixed(2) }}</p>
                                </template>
                            </template>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <h3 class="text-center text-lg font-bold">Total</h3>
                        <h3 class="text-center text-lg font-bold">${{ total }}</h3>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-x-4">
                    <button type="button"
                        class="mt-6 px-6 py-4 rounded-lg bg-[#F2A6B5] text-black font-bold text-xl hover:bg-[#EA7189] hover:text-white"
                        @click="showModal = false">Cancelar</button>
                    <button type="button"
                        class="mt-6 px-6 py-4 rounded-lg bg-[#D0E37F] text-black font-bold text-xl hover:bg-[#9EB828] hover:text-white"
                        @click="makeOrder()">Confirmar</button>
                </div>

            </div>
        </div>
    </div>
</template>
