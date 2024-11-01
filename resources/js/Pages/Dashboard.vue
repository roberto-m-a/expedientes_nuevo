<script setup>
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import vSelect from 'vue-select';
import { ref } from 'vue';
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)
const props = defineProps({
    verificarContraseña: {
        type: Boolean,
    },
    user: {
        type: Object,
    },
    personal: {
        type: Object,
    },
    docente: {
        type: Object,
    },
    departamentos: {
        type: Array,
    },
    gradoAcademico:{
        type: Array,
    },
    documentos_data: {
        type: Array,
    },
    interpretacion: {
        type: String,
    }
});
//Para las graficas
let cantidades = [];
let labels = [];
props.documentos_data.forEach((tipo_documento) => {
    cantidades.push(tipo_documento.cantidad);
    labels.push(tipo_documento.nombreTipoDoc);
})
const dataset = [
    {
        label: 'cantidad',
        backgroundColor: 'rgba(75, 192, 192, 0.6)',
        borderColor: 'rgba(75, 192, 192, 1)',
        borderWidth: 1,
        data: cantidades,
    },]
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout :user="user" :personal="personal">
        <template #header>
            <div class="flex flex-row items-end space-x-4">
                <h2 class="font-semibold text-2xl text-black leading-tight">Dashboard</h2>
                <h2 class="text-gray-500 font-semibold">Documentos en tu expediente</h2>
            </div>
        </template>

        <FirstUpdateDataForm :verificarContraseña="verificarContraseña" :personal="personal" :docente="docente" :departamentos="departamentos" :gradoAcademico="gradoAcademico"></FirstUpdateDataForm>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!--Area de graficas-->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 space-x-2">
                    <div id="app">
                        <GraficaBarras :labels="labels" :datasets="dataset" :text="props.interpretacion" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script>
//Este script permite importar los componentes de las graficas de forma correcta
import GraficaBarras from '@/Components/GraficaBarras.vue';
import FirstUpdateDataForm from '@/Components/ComponentsForms/FirstUpdateDataForm.vue';

export default {
    name: 'App',
    components: {
        GraficaBarras,
    },
};
</script>
<style>
@import "vue-select/dist/vue-select.css";
</style>