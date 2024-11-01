<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout_admin from '@/Layouts/AuthenticatedLayout_admin.vue';
import DocumentoFormCreate from '@/Components/ComponentsForms/DocumentoFormCreate.vue';

defineProps({
    user: {
        type: Object,
    },
    personal: {
        type: Object,
    },
    departamentos: {
        type: Array,
    },
    expediente_data: {
        type: Array,
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

    <Head title="Nuevo Documento" />

    <AuthenticatedLayout_admin :user="user" :personal="personal">
        <template #header>
            <div class="flex flex-row items-end space-x-4">
                <h2 class="font-semibold text-2xl text-black leading-tight">Subir documento</h2>
                <h2 class="text-gray-500 font-semibold">Recaba información de un documento para un expediente</h2>
            </div>
        </template>
        <DocumentoFormCreate :departamentos="departamentos" :expediente_data="expediente_data"
            :periodosEscolares="periodosEscolares" :tiposDocumentos="tiposDocumentos"></DocumentoFormCreate>
    </AuthenticatedLayout_admin>
</template>
