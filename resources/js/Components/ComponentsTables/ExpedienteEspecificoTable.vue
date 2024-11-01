<script setup>
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import 'datatables.net-responsive';
import 'datatables.net-select';
import DataTablesLib from 'datatables.net';
DataTable.use(DataTablesCore);
DataTable.use(DataTablesLib);
const props = defineProps({
    documentos: {
        type: Array,
    }
})
const options = {
    order: [[0, "desc"]],
    responsive: true,
    autoWidth: true,
    dom: '<"md:flex md:flex-row flex flex-col items-center pb-2 pt-2"<"flex items-center"l><"md:ml-auto"f>>rt<"lg:flex lg:flex-row flex flex-col justify-between text-center items-center pt-2"ip>',
    language: {
        "decimal": "",
            "emptyTable": "No hay documentos",
            "info": "Mostrando del documento _START_ al documento _END_ de un total de _TOTAL_ documentos",
            "infoEmpty": "No hay documentos para mostrar",
            "infoFiltered": "(Filtrado de _MAX_ documentos)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ documentos",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "Sin documentos encontrados",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },
    }
};

const columns = [
    { data: 'fechaExpedicion' },
    { data: 'Titulo' },
    {
        data: null, render: function (data, type, row, meta) {
            return (data.entrega) ?
                `En proceso: 
                <div class=" flex justify-center">
                        <button class="entregarDocumento flex flex-items justify-center bg-green-400 hover:bg-green-600 text-black font-semibold hover:text-white py-2 px-4 hover:border-transparent rounded" data-id="${row.IdDocumento}">
                    <svg class="h-5 w-5 text-black"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round">  <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />  <polyline points="22 4 12 14.01 9 11.01" /></svg>
                    Entregar
                </button>
                </div>
            </div>`
                : (data.fechaEntrega)? 'Entregado el: \t' + data.fechaEntrega: 'En proceso';
        },
    },
    { data: 'region' },
    {
        data: null, render: function (data, type, row, meta) {
            return (data.IdDepartamento == null) ? data.dependencia : data.nombreDepartamento;
        },
    },
    { data: 'nombreTipoDoc' },
    { data: 'nombre_corto' },
    {
        data: null, render: function (data, type, row, meta) {
            return `<div class=" flex justify-center space-x-1">
                    <button title="Ver documento" class="verDocumento flex flex-items justify-center bg-gray-300 hover:bg-gray-500 text-black font-semibold hover:text-white py-2 px-4 hover:border-transparent rounded" data-id="${row.URL}">
                    <svg class="h-5 w-5 text-slate-900"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round">  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />  <circle cx="12" cy="12" r="3" /></svg>
                
                </button>
                ${row.edita ? `<button title="Editar documento" class="EditarDocumento flex flex-items justify-center bg-blue-400 hover:bg-blue-800 text-black font-semibold hover:text-white py-2 px-4 hover:border-transparent rounded" data-id="${row.IdDocumento}">
                    <svg class="h-5 w-5 text-black" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke="none" d="M0 0h24v24H0z"/>  <path d="M9 7 h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" />  <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" />  <line x1="16" y1="5" x2="19" y2="8" /></svg>
                </button>` : ''}
                </div>`;
            },
        searchable: false,
        orderable: false,
    }
]
</script>
<template>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-2">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                <DataTable id="DocumentosDocente"
                    class="w-full table-auto text-sm text-center display stripe compact cell-border order-column"
                    :options="options" :columns="columns" :data="$page.props.documentosDocente">
                    <thead>
                        <tr class="border-2 bg-gray-200 border-black">
                            <th
                                style="text-align: center;"
                                class="py-2 px-4 font-semibold text-base border-2 border-black hover:bg-gray-300">
                                Expedida</th>
                            <th
                                style="text-align: center;"
                                class="py-2 px-4 font-semibold text-base border-2 border-black hover:bg-gray-300">
                                Título</th>
                            <th
                                style="text-align: center;"
                                class="py-2 px-4 font-semibold text-base border-2 border-black hover:bg-gray-300">
                                Estatus</th>
                            <th
                                style="text-align: center;"
                                class="py-2 px-4 font-semibold text-base border-2 border-black hover:bg-gray-300">
                                Región</th>
                            <th
                                style="text-align: center;"
                                class="py-2 px-4 font-semibold text-base border-2 border-black hover:bg-gray-300">
                                dpto./dpncia.</th>
                            <th
                                style="text-align: center;"
                                class="py-2 px-4 font-semibold text-base border-2 border-black hover:bg-gray-300">
                                Tipo</th>
                            <th
                                style="text-align: center;"
                                class="py-2 px-4 font-semibold text-base border-2 border-black hover:bg-gray-300">
                                Período</th>
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
</template>