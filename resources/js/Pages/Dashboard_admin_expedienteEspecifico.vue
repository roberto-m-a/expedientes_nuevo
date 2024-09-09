<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import AuthenticatedLayout_admin from '@/Layouts/AuthenticatedLayout_admin.vue';
import ExpedienteEspecificoTable from '@/Components/ComponentsTables/ExpedienteEspecificoTable.vue';
import DocumentoFormEdit from '@/Components/ComponentsForms/DocumentoFormEdit.vue';
import DocumentoFormEntrega from '@/Components/ComponentsForms/DocumentoFormEntrega.vue';
const props = defineProps({
    user: {
        type: Object,
    },
    personal: {
        type: Object,
    },
    personalDocente: {
        type: Object,
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
    departamentos: {
        type: Array,
    },
    expedientes:{
        type: Array,
    },
    expediente: {
        type: Object,
    },
});
//Editar el documento
const abrirEdit = ref(false);
const documentoEdit = ref(null);
//Entregar archivo
const abrirEntrega = ref(false);
const documentoEntrega = ref(null);
onMounted(() => {
    // Manejar clic en el botón de ver
    $('#DocumentosDocente').on('click', '.verDocumento', function () {
        const urlDocumento = $(this).data('id');
        window.open(urlDocumento, '_blank');
    });
    //Manejar clic del boton de entrega
    $('#DocumentosDocente').on('click', '.entregarDocumento', function () {
        const id = $(this).data('id');
        documentoEntrega.value = props.documentosDocente.find(a => a.IdDocumento === id);
        abrirEntrega.value = true
    });
    //Manejar el clic del boton de editar
    $('#DocumentosDocente').on('click', '.EditarDocumento', function () {
        const id = $(this).data('id');
        documentoEdit.value = props.documentosDocente.find(a => a.IdDocumento === id);
        abrirEdit.value = true;
    });
});
</script>

<template>
    <Head title="Expediente" />

    <AuthenticatedLayout_admin :user="user" :personal="personal">
        <template #header>
            <div class="flex flex-row items-end space-x-4">
                <h2 class="font-semibold text-2xl text-black leading-tight">Expediente de</h2>
                <h2 class="text-gray-500 font-semibold">{{ personalDocente.Nombre + ' ' +
                    personalDocente.Apellidos }}</h2>
            </div>
        </template>
        <!-- Modal para entregar el documento -->
        <DocumentoFormEntrega v-model:abrirModal="abrirEntrega" v-model:documento="documentoEntrega" ></DocumentoFormEntrega>
        <!-- Modal para editar el documento -->
        <DocumentoFormEdit v-model:abrirModal="abrirEdit" v-model:documento="documentoEdit" 
        :departamentos="props.departamentos" :periodos_escolares="props.periodos_escolares" 
        :expedientes="props.expedientes" :tipo_documentos="props.tipo_documentos" ></DocumentoFormEdit>
        <!-- Componente para la tabla de documentos de un expediente especifico -->
        <ExpedienteEspecificoTable :documentos="documentosDocente"></ExpedienteEspecificoTable>
    </AuthenticatedLayout_admin>
</template>