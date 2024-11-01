<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DocumentoFormEdit from '@/Components/ComponentsForms/DocumentoFormEdit.vue';
import ExpedienteEspecificoTable from '@/Components/ComponentsTables/ExpedienteEspecificoTable.vue';
const props = defineProps({
    user: {
        type: Object,
    },
    personal: {
        type: Object,
    },
    departamentos: {
        type: Array,
    },
    documentosDocente: {
        type: Array,
    },
    tipo_documentos: {
        type: Array,
    },
    periodos_escolares: {
        type: Array,
    },
    expediente: {
        type: Object,
    },
});
const abrirEdit = ref(false); //Constante para abrir el modal de la edicion de documento
const documento = ref(null); //Constante para asignar a un documento

onMounted(() => {
    // Manejar clic en el botón de editar
    $('#DocumentosDocente').on('click', '.EditarDocumento', function () {
        abrirEdit.value = true;
        const id = $(this).data('id');
        documento.value = props.documentosDocente.find(a => a.IdDocumento === id);
    });
    // Manejar clic en el botón de ver
    $('#DocumentosDocente').on('click', '.verDocumento', function () {
        const urlDocumento = $(this).data('id');
        window.open(urlDocumento, '_blank');

    });
});
</script>

<template>

    <Head title="Mi expediente" />

    <AuthenticatedLayout :user="user" :personal="personal">
        <template #header>
            <div class="flex flex-row items-end space-x-4">
                <h2 class="font-semibold text-2xl text-black leading-tight">Mi expediente</h2>
                <h2 class="text-gray-500 font-semibold">Ve y edita tus documentos</h2>
            </div>
        </template>
        <!-- Modal para editar el documento -->
        <DocumentoFormEdit v-model:abrirModal="abrirEdit" v-model:documento="documento"
            :departamentos="props.departamentos" :periodos_escolares="props.periodos_escolares"
            :expediente="props.expediente" :tipo_documentos="props.tipo_documentos"></DocumentoFormEdit>
        <!-- Componente para la tabla del expediente -->
        <ExpedienteEspecificoTable :documentos="props.documentosDocente"></ExpedienteEspecificoTable>
    </AuthenticatedLayout>
</template>