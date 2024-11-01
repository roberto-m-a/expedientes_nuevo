<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import AuthenticatedLayout_admin from '@/Layouts/AuthenticatedLayout_admin.vue';
import PersonalFormCreate from '@/Components/ComponentsForms/PersonalFormCreate.vue';
import PersonalFormEdit from '@/Components/ComponentsForms/PersonalFormEdit.vue';
import PersonalFormCreateUser from '@/Components/ComponentsForms/PersonalFormCreateUser.vue';
import PersonalTable from '@/Components/ComponentsTables/PersonalTable.vue';
import PersonalFormDelete from '@/Components/ComponentsForms/PersonalFormDelete.vue';

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
    personal_data: {
        type: Array,
    },
    gradoAcademico: {
        type: Array,
    }
});
//Constante para manejar el componente PersonalFormCreate
const abrir = ref(false);
//Constantes para manejar el componente PersonalFormEdit
const abrirEdit = ref(false);
const personal = ref(null);
//Constantes para manejar el componente PersonalFormCreateUser
const abrirAniadirUsuario = ref(false);
const personalNewUser = ref(null);

//Escuchador de eventos
onMounted(() => {

    // Manejar clic del botón con la clase EditarPersonal
    $('#TablaPersonal').on('click', '.EditarPersonal', function () {
        abrirEdit.value = true;
        const id = $(this).data('id');
        personal.value = props.personal_data.find(a => a.IdPersonal === id);
    });
    //Manejar clic del botón con la clase CrearUsuario
    $('#TablaPersonal').on('click', '.CrearUsuario', function () {
        abrirAniadirUsuario.value = true;
        const id = $(this).data('id');
        personalNewUser.value = props.personal_data.find(a => a.IdPersonal === id);
    });
});
</script>

<template>

    <Head title="Personal" />
    <AuthenticatedLayout_admin :user="props.user" :personal="props.personal">
        <template #header>
            <div class="flex flex-row items-end space-x-4">
                <h2 class="font-semibold text-2xl text-black leading-tight">Personal</h2>
                <h2 class="text-gray-500 font-semibold">Administre a su personal y usuarios</h2>
            </div>
        </template>
        <!-- Componente para borrar un personal -->
         <PersonalFormDelete :personal_data="personal_data" ></PersonalFormDelete>
        <!-- Modal para añadir un usuario a un personal ya creado -->
        <PersonalFormCreateUser v-model:abrirModal="abrirAniadirUsuario" v-model:personal="personalNewUser">
        </PersonalFormCreateUser>
        <!-- Modal para editar un usuario -->
        <PersonalFormEdit v-model:abrirModal="abrirEdit" v-model:personal="personal" :gradoAcademico="gradoAcademico" :departamentos="departamentos">
        </PersonalFormEdit>
        <!-- Modal para agregar un nuevo personal a plataforma -->
        <PersonalFormCreate v-model:abrirModal="abrir" :gradoAcademico="gradoAcademico" :departamentos="departamentos"></PersonalFormCreate>
        <!-- Componente para mostrar la tabla del personal -->
        <PersonalTable :personal_data="personal_data" v-model:abrir="abrir"></PersonalTable>
    </AuthenticatedLayout_admin>
</template>
