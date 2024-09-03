<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AddButton from '@/Components/AddButton.vue';
import { ref, onMounted } from 'vue';
import vSelect from 'vue-select';
import AuthenticatedLayout_admin from '@/Layouts/AuthenticatedLayout_admin.vue';
import PersonalFormCreate from '@/Components/ComponentsForms/PersonalFormCreate.vue';
import PersonalFormEdit from '@/Components/ComponentsForms/PersonalFormEdit.vue';
import PersonalFormCreateUser from '@/Components/ComponentsForms/PersonalFormCreateUser.vue';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import 'datatables.net-responsive';
import 'datatables.net-select';
import DataTablesLib from 'datatables.net';
DataTable.use(DataTablesCore);
DataTable.use(DataTablesLib);

const options = {
    select: false,
    responsive: true,
    autoWidth: true,
    dom: '<"md:flex md:flex-row flex flex-col items-center pb-2 pt-2"<"flex items-center"l><"md:ml-auto"f>>rt<"lg:flex lg:flex-row flex flex-col justify-between text-center items-center pt-2"ip>',
    language: {
        "decimal": "",
        "emptyTable": "No hay personal",
        "info": "Mostrando del personal _START_ al personal _END_ de un total de _TOTAL_ personales",
        "infoEmpty": "No hay personal para mostrar",
        "infoFiltered": "(Filtrado de _MAX_ personales)",
        "infoPostFix": "",
        "thousands": ",",
        "lengthMenu": "Mostrar _MENU_ personales",
        "loadingRecords": "Cargando...",
        "processing": "Procesando...",
        "search": "Buscar:",
        "zeroRecords": "Sin personal encontrado",
        "paginate": {
            "first": "Primero",
            "last": "Último",
            "next": "Siguiente",
            "previous": "Anterior"
        },
    }
};

const columns = [
    { data: 'Nombre' },
    { data: 'Apellidos' },
    { data: 'Sexo' },
    {
        data: null, render: function (data, type, row, meta) {
            return data.email? data.email : `<div class=" flex justify-center space-x-1">
                <button class="CrearUsuario flex flex-items justify-center bg-green-400 hover:bg-green-600 text-black font-semibold hover:text-white py-2 px-4 border border-green-500 hover:border-transparent rounded" data-id="${row.IdPersonal}">
                    <svg class="h-5 w-5 text-black"  fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Crear usuario
                </button>
                </div>`;
        }
    },
    { data: 'nombreDepartamento' },
    {
        data: null, render: function (data, type, row, meta) {
            return data.IdDocente? 'Docente' : (data.IdAdministrador? 'Administrador' : 'Secretaria');
        }
    },
    {
        data: null, render: function (data, type, row, meta) {
            return `<div class=" flex justify-center space-x-1">
                <button title="Editar personal" class="EditarPersonal flex flex-items justify-center bg-blue-400 hover:bg-blue-800 text-black font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded" data-id="${row.IdPersonal}">
                    <svg class="h-5 w-5 text-black"  viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke="none" d="M0 0h24v24H0z"/>  <path d="M9 7 h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" />  <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" />  <line x1="16" y1="5" x2="19" y2="8" /></svg>
                    
                </button>
                ${row.has_no_relations? `<button title="borrar personal" class="BorrarPersonal flex flex-items justify-center bg-red-400 hover:bg-red-600 text-black font-semibold hover:text-white py-2 px-4 border border-red-400 hover:border-transparent rounded" data-id="${row.IdPersonal}">
                    <svg class="h-5 w-5 text-slate-900"  width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke="none" d="M0 0h24v24H0z"/>  <line x1="4" y1="7" x2="20" y2="7" />  <line x1="10" y1="11" x2="10" y2="17" />  <line x1="14" y1="11" x2="14" y2="17" />  <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />  <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                </button>` :''}
                </div>`
                ;
        },
        searchable: false,
        orderable: false,
    }
]

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
    }
});
//Constante para manejar el componente PersonalFormCreate
const abrir = ref(false);
//Constantes para manejar el componente PersonalFormEdit
const abrirEdit = ref(false);
const personal =ref(null);
//Constantes para manejar el componente PersonalFormCreateUser
const abrirAniadirUsuario = ref(false);
const personalNewUser = ref(null);
//Formulario para borrar a un personal
const formDelete = useForm({
    IdPersonal: '',
});
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
    //Manejar clic del botón con la clase BorrarPersonal
    $('#TablaPersonal').on('click', '.BorrarPersonal', function () {
        formDelete.IdPersonal = $(this).data('id');
        const personal = props.personal_data.find(a => a.IdPersonal === formDelete.IdPersonal);
        const randomCode = Math.floor(1000 + Math.random() * 9000); // Genera un código aleatorio de 4 dígitos
        Swal.fire({
            title: 'Confirmación necesaria',
            text: `¿Desea borrar a ${personal.Nombre} ${personal.Apellidos}?. 
            Para continuar, ingresa el código de confirmación: ${randomCode}`,
            input: 'number',
            footer: 'Esta acción se puede realizar ya que el usuario/personal no tiene documentos en su expediente o no ha subido algun documento.',
            inputAttributes: {
                maxlength: 4,
                autocapitalize: 'off',
                autocorrect: 'off'
            },
            showCancelButton: true,
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar',
            preConfirm: (inputValue) => {
                return new Promise((resolve) => {
                    if (inputValue === randomCode.toString()) {
                        resolve(true);
                    } else {
                        Swal.showValidationMessage('Código incorrecto');
                        resolve(false);
                    }
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                formDelete.delete(route('personal.borrar'), {
                    preserveScroll: true,
                    onSuccess: () => {
                        formDelete.reset()
                    },
                    onError: () => {
                        console.log(formDelete.errors);
                    },
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                Swal.fire('Acción cancelada', 'No se realizó ninguna acción.', 'error');
            }
        });
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
        <!-- Modal para añadir un usuario a un personal ya creado -->
        <PersonalFormCreateUser v-model:abrirModal="abrirAniadirUsuario" v-model:personal="personalNewUser"></PersonalFormCreateUser>
        <!-- Modal para editar un usuario -->
        <PersonalFormEdit v-model:abrirModal="abrirEdit" v-model:personal="personal" :departamentos="departamentos"></PersonalFormEdit>
         <!-- Modal para agregar un nuevo personal a plataforma -->
        <PersonalFormCreate v-model:abrirModal="abrir" :departamentos="departamentos"></PersonalFormCreate>
        
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 space-y-3">
                    <div class="flex flex-auto justify-end">
                        <AddButton @click="abrir = true">
                            Agregar nuevo personal
                        </AddButton>
                    </div>
                    <DataTable id="TablaPersonal"
                        class="w-full table-auto text-sm text-center display stripe compact cell-border order-column"
                        :options="options" :columns="columns" :data="$page.props.personal_data">
                        <thead>
                            <tr class="border-2 bg-gray-200 border-black">
                                <th style="text-align: center;"
                                    class="py-2 px-4 font-semibold text-base border-2 border-black hover:bg-gray-300">
                                    Nombre</th>
                                <th style="text-align: center;"
                                    class="py-2 px-4 font-semibold text-base border-2 border-black hover:bg-gray-300">
                                    Apellidos</th>
                                <th style="text-align: center;"
                                    class="py-2 px-4 font-semibold text-base border-2 border-black hover:bg-gray-300">
                                    Sexo</th>
                                <th style="text-align: center;"
                                    class="py-2 px-4 font-semibold text-base border-2 border-black hover:bg-gray-300">
                                    Email</th>
                                <th style="text-align: center;"
                                    class="py-2 px-4 font-semibold text-base border-2 border-black hover:bg-gray-300">
                                    Departamento</th>
                                <th style="text-align: center;"
                                    class="py-2 px-4 font-semibold text-base border-2 border-black hover:bg-gray-300">
                                    Tipo</th>
                                <th style="text-align: center;"
                                    class="py-2 px-4 font-semibold text-base border-2 border-black hover:bg-gray-300">
                                    Acciones</th>
                            </tr>
                        </thead>
                    </DataTable>
                </div>
            </div>
        </div>
    </AuthenticatedLayout_admin>
</template>
