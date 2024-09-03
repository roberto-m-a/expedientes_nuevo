<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AddButton from '@/Components/AddButton.vue';
import InputError from '@/Components/InputError.vue';
import { ref, onMounted } from 'vue';

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import 'datatables.net-responsive';
import 'datatables.net-select';
import DataTablesLib from 'datatables.net';
import AuthenticatedLayout_admin from '@/Layouts/AuthenticatedLayout_admin.vue';
import DepartamentoFormCreate from '@/Components/ComponentsForms/DepartamentoFormCreate.vue';
import DepartamentoFormEdit from '@/Components/ComponentsForms/DepartamentoFormEdit.vue';
DataTable.use(DataTablesCore);
DataTable.use(DataTablesLib);

const options = {
    select: false,
    responsive: true,
    autoWidth: true,
    dom: '<"md:flex md:flex-row flex flex-col items-center pb-2 pt-2"<"flex items-center"l><"md:ml-auto"f>>rt<"lg:flex lg:flex-row flex flex-col justify-between text-center items-center pt-2"ip>',
    language: {
        "decimal": "",
            "emptyTable": "No hay departamentos",
            "info": "Mostrando del departamento _START_ al departamento _END_ de un total de _TOTAL_ departamentos",
            "infoEmpty": "No hay departamentos para mostrar",
            "infoFiltered": "(Filtrado de _MAX_ departamentos)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ departamentos",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "Sin departamentos encontrados",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },
    }
};

const columns = [
    { data: 'nombreDepartamento' },
    {
        data: null, render: function (data, type, row, meta) {
            return (data.numDocumentos == 0 && data.numPersonal == 0)? 
            `<div class=" flex justify-center space-x-1">
                <button title='Editar departamento' class="EditarDepartamento flex flex-items justify-center bg-blue-400 hover:bg-blue-800 text-black font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded" data-id="${row.IdDepartamento}">
                    <svg class="h-5 w-5 text-black"  viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke="none" d="M0 0h24v24H0z"/>  <path d="M9 7 h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" />  <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" />  <line x1="16" y1="5" x2="19" y2="8" /></svg>
                    
                </button>
                <button title='Borrar departamento' class="BorrarDepartamento flex flex-items justify-center bg-red-400 hover:bg-red-600 text-black font-semibold hover:text-white py-2 px-4 border border-red-400 hover:border-transparent rounded" data-id="${row.IdDepartamento}">
                    <svg class="h-5 w-5 text-slate-900"  width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke="none" d="M0 0h24v24H0z"/>  <line x1="4" y1="7" x2="20" y2="7" />  <line x1="10" y1="11" x2="10" y2="17" />  <line x1="14" y1="11" x2="14" y2="17" />  <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />  <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                    
                </button>
                </div>`
            :
             `<div class=" flex justify-center space-x-1">
                <button title='Editar departamento' class="EditarDepartamento flex flex-items justify-center bg-blue-400 hover:bg-blue-800 text-black font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded" data-id="${row.IdDepartamento}">
                    <svg class="h-5 w-5 text-black"  viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke="none" d="M0 0h24v24H0z"/>  <path d="M9 7 h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" />  <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" />  <line x1="16" y1="5" x2="19" y2="8" /></svg>
                    
                </button>
                </div>`;
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
    }
});

const abrir = ref(false); //Variable para manejar el componente de DepartamentoFormCreate
const abrirEdit = ref(false); //Variable para manejar el componente de DepartamentoFormEdit
const departamento = ref(null); //Variable para manejar el departamento del componente DepartamentoFormEdit

const formDelete = useForm({
    idDepartamento: '',
})
onMounted(() => {
    // Manejar clic en del botón con la clase EditarDepartamento
    $('#TablaDepartamentos').on('click', '.EditarDepartamento', function () {
        abrirEdit.value = true;
        const id = $(this).data('id');
        departamento.value = props.departamentos.find(a => a.IdDepartamento === id);
    });
    //Manejar el clic del botón con la clase BorrarDepartamento
    $('#TablaDepartamentos').on('click', '.BorrarDepartamento', function () {
        formDelete.idDepartamento = $(this).data('id');
        const departamentoD = props.departamentos.find(a => a.IdDepartamento === formDelete.idDepartamento);
        const randomCode = Math.floor(1000 + Math.random() * 9000); // Genera un código aleatorio de 4 dígitos
        Swal.fire({
            title: 'Confirmación necesaria',
            text: `¿Desea borrar ${departamentoD.nombreDepartamento}?. 
            Para continuar, ingresa el código de confirmación: ${randomCode}`,
            input: 'number',
            footer: 'Esta acción se puede realizar ya que el registro no tiene relaciones con otros registros.',
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
                formDelete.delete(route('departamento.borrar'), {
                    preserveScroll: true,
                    onSuccess: () => {
                        formDelete.reset()
                    },
                    onError: () => {
                        console.log(formEdit.errors);
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

    <Head title="Departamentos" />

    <AuthenticatedLayout_admin :user="user" :personal="personal">
        <template #header>
            <div class="flex flex-row items-end space-x-4">
                <h2 class="font-semibold text-2xl text-black leading-tight">Departamentos</h2>
                <h2 class="text-gray-500 font-semibold">Agrega, edita y borra departamentos</h2>
            </div>
        </template>
        <!-- Modal para editar departamento -->
        <DepartamentoFormEdit v-model:abrirModal="abrirEdit" v-model:departamento="departamento"></DepartamentoFormEdit>
        <!-- Modal para agregar un nuevo departamento -->
        <DepartamentoFormCreate v-model:abrirModal="abrir"></DepartamentoFormCreate>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 space-y-3">
                    <div class="flex flex-auto justify-end">
                        <AddButton @click="abrir = true">
                            Agregar nuevo departamento
                        </AddButton>
                    </div>
                    <DataTable id="TablaDepartamentos"
                        class="w-full table-auto text-sm text-center display stripe compact cell-border order-column"
                        :options="options" :columns="columns" :data="$page.props.departamentos">
                        <thead>
                            <tr class="border-2 bg-gray-200 border-black">
                                <th
                                    style="text-align: center;"
                                    class="py-2 px-4 font-semibold text-base border-2 border-black hover:bg-gray-300">
                                    Nombre</th>
                                <th
                                    style="text-align: center;"
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