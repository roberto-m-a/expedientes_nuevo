<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import AuthenticatedLayout_admin from '@/Layouts/AuthenticatedLayout_admin.vue';
import PeriodoEscolarFormCreate from '@/Components/ComponentsForms/PeriodoEscolarFormCreate.vue';
import PeriodoEscolarFormEdit from '@/Components/ComponentsForms/PeriodoEscolarFormEdit.vue';
import PeriodoEscolarTable from '@/Components/ComponentsTables/PeriodoEscolarTable.vue';
import PeriodoEscolarFormDelete from '@/Components/ComponentsForms/PeriodoEscolarFormDelete.vue';

const props = defineProps({
    user: {
        type: Object,
    },
    personal: {
        type: Object,
    },
    periodosEscolares: {
        type: Array,
    },
});

const abrir = ref(false); //constante para controlar el componente PeriodoEscolarFormCreate
const abrirEdit = ref(false); //constante para controlar el componente PeriodoEscolarFormEdit
const periodoEscolar = ref(null); //constante para manejar el periodo a editar en el componente PeriodoEscolarFormEdit
onMounted(() => {
    // Manejar clic en el botón de editarDoc
    $('#TablaPeriodos').on('click', '.EditarPeriodo', function () {
        const id = $(this).data('id');
        periodoEscolar.value = props.periodosEscolares.find(a => a.IdPeriodoEscolar === id);
        abrirEdit.value = true;
    });
});
</script>

<template>

    <Head title="Períodos Escolares" />
    <AuthenticatedLayout_admin :user="user" :personal="personal">
        <template #header>
            <div class="flex flex-row items-end space-x-4">
                <h2 class="font-semibold text-2xl text-black leading-tight">Períodos escolares</h2>
                <h2 class="text-gray-500 font-semibold">Agrega, edita y borra períodos escolares</h2>
            </div>
        </template>
        <!-- Componente pare borrar un periodo escolar -->
        <PeriodoEscolarFormDelete :periodosEscolares="periodosEscolares"></PeriodoEscolarFormDelete>
        <!-- Modal para editar un período escolar -->
        <PeriodoEscolarFormEdit v-model:abrirModal="abrirEdit" v-model:periodoEscolar="periodoEscolar">
        </PeriodoEscolarFormEdit>
        <!-- Modal para agregar un nuevo período escolar -->
        <PeriodoEscolarFormCreate v-model:abrirModal="abrir"></PeriodoEscolarFormCreate>
        <!-- Componente de la tabla de periodos escolares -->
        <PeriodoEscolarTable v-model:abrir="abrir" :periodosEscolares="periodosEscolares"></PeriodoEscolarTable>
    </AuthenticatedLayout_admin>
</template>
