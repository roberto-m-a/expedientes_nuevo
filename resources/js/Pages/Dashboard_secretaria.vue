<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout_secretaria from '../Layouts/AuthenticatedLayout_secretaria.vue';
import FirstUpdateDataForm from '@/Components/ComponentsForms/FirstUpdateDataForm.vue';
import GraficasYConsultas from '@/Components/ComponentsGraphs/GraficasYConsultas.vue'

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
    tipo_documentos: {
        type: Array,
    },
    periodos_escolares: {
        type: Array,
    },
    departamentos: {
        type: Array,
    },
    dataHombres: {
        type: Array,
    },
    dataMujeres: {
        type: Array
    },
    labelsTipoDoc: {
        type: Array,
    },
    interpretacion: {
        type: String,
    },
});
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout_secretaria :user="user" :personal="personal">
        <template #header>
            <div class="flex flex-row items-end space-x-4">
                <h2 class="font-semibold text-2xl text-black leading-tight">Dashboard</h2>
                <h2 class="text-gray-500 font-semibold">Información del sistema con gráficas</h2>
            </div>
        </template>
        <FirstUpdateDataForm :verificarContraseña="verificarContraseña" :personal="personal" :departamentos="departamentos"></FirstUpdateDataForm>

        <GraficasYConsultas :tipo_documentos="tipo_documentos" :periodos_escolares="periodos_escolares"
            :departamentos="departamentos" :dataHombres="dataHombres" :dataMujeres="dataMujeres"
            :labelsTipoDoc="labelsTipoDoc" :interpretacion="interpretacion"></GraficasYConsultas>
    </AuthenticatedLayout_secretaria>
</template>