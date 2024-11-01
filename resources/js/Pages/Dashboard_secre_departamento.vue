<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import AuthenticatedLayout_secretaria from '@/Layouts/AuthenticatedLayout_secretaria.vue';
import DepartamentoFormCreate from '@/Components/ComponentsForms/DepartamentoFormCreate.vue';
import DepartamentoFormEdit from '@/Components/ComponentsForms/DepartamentoFormEdit.vue';
import DepartamentoTable from '@/Components/ComponentsTables/DepartamentoTable.vue';
import DepartamentoFormDelete from '@/Components/ComponentsForms/DepartamentoFormDelete.vue';

const props = defineProps({
    user: {
        type: Object,
    },
    personal: {
        type: Object,
    },
    departamentos: {
        type: Array,
    }
});
const abrir = ref(false); //Variable para manejar el componente de DepartamentoFormCreate
const abrirEdit = ref(false); //Variable para manejar el componente de DepartamentoFormEdit
const departamento = ref(null); //Variable para manejar el departamento del componente DepartamentoFormEdit
onMounted(() => {
    // Manejar clic en del botón con la clase EditarDepartamento
    $('#TablaDepartamentos').on('click', '.EditarDepartamento', function () {
        abrirEdit.value = true;
        const id = $(this).data('id');
        departamento.value = props.departamentos.find(a => a.IdDepartamento === id);
    });
});
</script>

<template>

    <Head title="Departamento" />

    <AuthenticatedLayout_secretaria :user="user" :personal="personal">
        <template #header>
            <div class="flex flex-row items-end space-x-4">
                <h2 class="font-semibold text-2xl text-black leading-tight">Departamentos</h2>
                <h2 class="text-gray-500 font-semibold">Agrega, edita y borra departamentos</h2>
            </div>
        </template>
        <!-- Componente para eliminar un departamento -->
        <DepartamentoFormDelete :departamentos="departamentos"></DepartamentoFormDelete>
        <!-- Modal para editar departamento -->
        <DepartamentoFormEdit v-model:abrirModal="abrirEdit" v-model:departamento="departamento"></DepartamentoFormEdit>
        <!-- Modal para agregar un nuevo departamento -->
        <DepartamentoFormCreate v-model:abrirModal="abrir"></DepartamentoFormCreate>
        <DepartamentoTable v-model:abrir="abrir" :departamentos="departamentos"></DepartamentoTable>
    </AuthenticatedLayout_secretaria>
</template>
