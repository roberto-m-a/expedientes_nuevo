<script setup>
import AuthenticatedLayout_admin from '@/Layouts/AuthenticatedLayout_admin.vue';
import { Head} from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import DocumentoFormEntrega from '@/Components/ComponentsForms/DocumentoFormEntrega.vue';
import DocumentoTable from '@/Components/ComponentsTables/DocumentoTable.vue';
import DocumentoFormEdit from '@/Components/ComponentsForms/DocumentoFormEdit.vue';

const props = defineProps({
    user: {
        type: Object,
    },
    personal: {
        type: Object,
    },
    documentos: {
        type: Array,
    },
    periodos_escolares: {
        type: Array,
    },
    departamentos: {
        type: Array,
    },
    tipo_documentos: {
        type: Array,
    },
    expedientes: {
        type: Array,
    },
});

//Editar el documento
const abrirEdit = ref(false);
const documentoEdit = ref(null);
//Entregar archivo
const abrirEntrega = ref(false);
const documentoEntrega = ref(null);
//Metodo para manejar los botones
onMounted(() => {
    // Manejar clic en el botón de ver
    $('#tablaDocs').on('click', '.verDocumento', function () {
        const urlDocumento = $(this).data('id');
        window.open(urlDocumento, '_blank');
    });
    //Manejar clic del boton de entrega
    $('#tablaDocs').on('click', '.entregarDocumento', function () {
        const id = $(this).data('id');
        documentoEntrega.value = props.documentos.find(a => a.IdDocumento === id);
        abrirEntrega.value = true
    });
    //Manejar el clic del boton de editar
    $('#tablaDocs').on('click', '.EditarDocumento', function () {
        const id = $(this).data('id');
        documentoEdit.value = props.documentos.find(a => a.IdDocumento === id);
        abrirEdit.value = true;
    });
});

</script>

<template>

    <Head title="Documentos" />

    <AuthenticatedLayout_admin :user="user" :personal="personal">
        <template #header>
            <div class="flex flex-row items-end space-x-4">
                <h2 class="font-semibold text-2xl text-black leading-tight">Documentos</h2>
                <h2 class="text-gray-500 font-semibold">Ve y edita documentos</h2>
            </div>
        </template>
        <!-- Modal para entregar el documento -->
        <DocumentoFormEntrega v-model:abrirModal="abrirEntrega" v-model:documento="documentoEntrega" ></DocumentoFormEntrega>
        <!-- Modal para editar el documento -->
        <DocumentoFormEdit v-model:abrirModal="abrirEdit" v-model:documento="documentoEdit" 
        :departamentos="props.departamentos" :periodos_escolares="props.periodos_escolares" 
        :expedientes="props.expedientes" :tipo_documentos="props.tipo_documentos" ></DocumentoFormEdit>
        <!-- Componente de la tabla de documentos -->
        <DocumentoTable :documentos="documentos"></DocumentoTable>
    </AuthenticatedLayout_admin>
</template>