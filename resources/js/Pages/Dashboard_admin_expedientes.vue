<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout_admin from '@/Layouts/AuthenticatedLayout_admin.vue';
import { onMounted } from 'vue';
import ExpedienteTable from '@/Components/ComponentsTables/ExpedienteTable.vue';

const props = defineProps({
    user: {
        type: Object,
    },
    personal: {
        type: Object,
    },
    expedientes: {
        type: Array,
    },
});
//Funcion para poder redirigir a un expediente en especifico
const formExpediente = useForm({
});
onMounted(() => {
    // Manejar clic en el botón de abrirExpediente
    $('#tablaExpedientes').on('click', '.abrirExpediente', function () {
        const idExpediente = $(this).data('id');
        formExpediente.get(route('expediente.especifico', { id: idExpediente }));
    });
});
</script>
<template>
    <Head title="Expedientes" />
    <AuthenticatedLayout_admin :user="user" :personal="personal">
        <template #header>
            <div class="flex flex-row items-end space-x-4">
                <h2 class="font-semibold text-2xl text-black leading-tight">Expedientes</h2>
                <h2 class="text-gray-500 font-semibold">Consulta los expedientes</h2>
            </div>
        </template>
        <!-- Componente de tabla de expedientes -->
        <ExpedienteTable :expedientes="expedientes"></ExpedienteTable>
    </AuthenticatedLayout_admin>
</template>