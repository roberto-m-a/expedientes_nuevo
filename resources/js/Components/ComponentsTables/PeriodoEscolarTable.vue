<script setup>
import { ref, watch } from 'vue';
import AddButton from '@/Components/AddButton.vue';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import 'datatables.net-responsive';
import 'datatables.net-select';
import DataTablesLib from 'datatables.net';
DataTable.use(DataTablesCore);
DataTable.use(DataTablesLib);

const props = defineProps({
    periodosEscolares: {
        type: Array,
    },
    abrir: {
        type: Boolean,
    },
});
const emit = defineEmits(['update:abrir']);
const abrir = ref(props.abrir);
watch(() => props.abrir, (newVal) => {
    abrir.value = newVal;
});
watch(abrir, (newVal) => {
    emit('update:abrir', newVal);
});
const options = {
    order: [[0, "desc"]],
    select: false,
    responsive: true,
    autoWidth: true,
    dom: '<"md:flex md:flex-row flex flex-col items-center pb-2 pt-2"<"flex items-center"l><"md:ml-auto"f>>rt<"lg:flex lg:flex-row flex flex-col justify-between text-center items-center pt-2"ip>',
    language: {
        "decimal": "",
            "emptyTable": "No hay períodos escolares",
            "info": "Mostrando del período _START_ al período _END_ de un total de _TOTAL_ períodos",
            "infoEmpty": "No hay períodos escolares para mostrar",
            "infoFiltered": "(Filtrado de _MAX_ períodos escolares)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ períodos",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "Sin períodos encontrados",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },
    }
};

const columns = [
    { data: 'fechaInicio' },
    { data: 'fechaTermino' },
    { data: 'nombre_corto' },
    {
        data: null, render: function (data, type, row, meta) {
            return (data.numDocumentos == 0) ?
                `<div class=" flex justify-center space-x-1">
                <button title='Editar período escolar' class="EditarPeriodo flex flex-items justify-center bg-blue-400 hover:bg-blue-800 text-black font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded" data-id="${row.IdPeriodoEscolar}">
                    <svg class="h-5 w-5 text-black"  viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke="none" d="M0 0h24v24H0z"/>  <path d="M9 7 h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" />  <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" />  <line x1="16" y1="5" x2="19" y2="8" /></svg>
                    
                </button>
                <button title='Borrar período escolar' class="BorrarPeriodo flex flex-items justify-center bg-red-400 hover:bg-red-600 text-black font-semibold hover:text-white py-2 px-4 border border-red-400 hover:border-transparent rounded" data-id="${row.IdPeriodoEscolar}">
                    <svg class="h-5 w-5 text-slate-900"  width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke="none" d="M0 0h24v24H0z"/>  <line x1="4" y1="7" x2="20" y2="7" />  <line x1="10" y1="11" x2="10" y2="17" />  <line x1="14" y1="11" x2="14" y2="17" />  <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />  <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                    
                </button>
                </div>`
                :
                `<div class=" flex justify-center space-x-1">
                <button title='Editar período escolar' class="EditarPeriodo flex flex-items justify-center bg-blue-400 hover:bg-blue-800 text-black font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded" data-id="${row.IdPeriodoEscolar}">
                    <svg class="h-5 w-5 text-black"  viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke="none" d="M0 0h24v24H0z"/>  <path d="M9 7 h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" />  <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" />  <line x1="16" y1="5" x2="19" y2="8" /></svg>
                    
                </button>
                </div>`;
        },
        searchable: false,
        orderable: false,
    }
]
</script>
<template>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 space-y-3">
                <div class="flex flex-auto justify-end">
                    <AddButton @click="abrir = true">
                        Agregar nuevo período escolar
                    </AddButton>
                </div>
                <DataTable id="TablaPeriodos"
                    class="w-full table-auto text-sm text-center display stripe compact cell-border order-column"
                    :options="options" :columns="columns" :data="$page.props.periodosEscolares">
                    <thead>
                        <tr class="border-2 bg-gray-200 border-black">
                            <th style="text-align: center;"
                                class="py-2 px-4 font-semibold text-base border-2 border-black hover:bg-gray-300">
                                Fecha de inicio</th>
                            <th style="text-align: center;"
                                class="py-2 px-4 font-semibold text-base border-2 border-black hover:bg-gray-300">
                                Fecha de término</th>
                            <th style="text-align: center;"
                                class="py-2 px-4 font-semibold text-base border-2 border-black hover:bg-gray-300">
                                Nombre corto</th>
                            <th style="text-align: center;"
                                class="py-2 px-4 font-semibold text-base border-2 border-black hover:bg-gray-300">
                                Acciones</th>
                        </tr>
                    </thead>
                </DataTable>
            </div>
        </div>
    </div>
</template>