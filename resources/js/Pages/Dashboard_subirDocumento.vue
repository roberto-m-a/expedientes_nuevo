<script setup>
import { Head } from '@inertiajs/vue3';
import DocumentoFormCreate from '@/Components/ComponentsForms/DocumentoFormCreate.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
//informacion cargada desde la base de datos
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
    departamentos: {
        type: Array,
    },
    expediente: {
        type: Object,
    },
    periodosEscolares: {
        type: Array,
    },
    tiposDocumentos: {
        type: Array,
    },
});
</script>

<template>

    <Head title="Subir documento" />

    <AuthenticatedLayout :user="user" :personal="personal">
        <template #header>
            <div class="flex flex-row items-end space-x-4">
                <h2 class="font-semibold text-2xl text-black leading-tight">Subir documento</h2>
                <h2 class="text-gray-500 font-semibold">Recaba información para un documento nuevo en tu expediente</h2>
            </div>
        </template>
        <DocumentoFormCreate :departamentos="departamentos" :expediente="expediente"
            :periodosEscolares="periodosEscolares" :tiposDocumentos="tiposDocumentos"></DocumentoFormCreate>
    </AuthenticatedLayout>
</template>