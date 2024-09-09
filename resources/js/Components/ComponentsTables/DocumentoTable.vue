<script setup>
import {  useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
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
//para filtrar la tabla
const show_filtros = ref(true);
const form = useForm({
    DptoDpencia: '',
    TipoDocumento: '',
    PeriodoEscolar: '',
    Region: 'Todos',
    Estatus: 'Todos',
});
//Agregacion de la busqueda en orden alfabetico
var _alphabetSearch;
//Funcion que permite retornar los registros que cumplen con el filtro alfabetico
$.fn.dataTable.ext.search.push(function (settings, searchData) {
    if (!_alphabetSearch) { // No search term - all results shown
        return true;
    }
    if (searchData[3].charAt(0).toUpperCase() === _alphabetSearch) {
        return true;
    }
    return false;
});
//Funcion que permite inicializar la tabla
$(document).ready(function () {
    var table = $('#tablaDocs').DataTable({
        order: [[0, "desc"]],
        paging: true,
        searching: true,
        responsive: true,
        autoWidth: false,
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
        },
        data: props.documentos,
        columns: [
            { title: "Expedida", data: 'fechaExpedicion'},
            {
                title: "Estatus", data: null, render: function (data, type, row, meta) {
                    return (data.entrega) ?
                        `En proceso: 
                        <div class=" flex justify-center">
                        <button class="entregarDocumento flex flex-items justify-center bg-green-400 hover:bg-green-600 text-black font-semibold hover:text-white py-2 px-4 hover:border-transparent rounded" data-id="${row.IdDocumento}">
                    <svg class="h-5 w-5 text-black"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round">  <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />  <polyline points="22 4 12 14.01 9 11.01" /></svg>
                    Entregar
                </button>
                </div>
            </div>`
                        : 'Entregado el: \t' + data.fechaEntrega;
                },
            },
            { title: "Nombre", data: 'Nombre' },
            { title: "Apellidos", data: 'Apellidos' },
            { title: "Título", data: 'Titulo' },
            { title: 'Región', data: 'region' },
            {
                title: 'Dpto/Dpncia', data: null, render: function (data, type, row, meta) {
                    return (data.IdDepartamento == null) ? data.dependencia : data.nombreDepartamento;
                },
            },
            { title: 'Tipo', data: 'nombreTipoDoc' },
            { title: 'Período', data: 'nombre_corto' },
            {
                title: "Acciones", data: null, render: function (data, type, row, meta) {
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
        ],
    });
    //Filtros que retornan los registros que cumplen con el valor ingresado en cada input
    //filtros estatus
    $("#ipt_todos_estatus").change(function () {
        table.column($(this).data('index')).search('').draw();
    });

    $("#ipt_proceso").change(function () {
        table.column($(this).data('index')).search(this.value).draw();
    });

    $("#ipt_entregado").change(function () {
        table.column($(this).data('index')).search(this.value).draw();
    });
    //filtros region
    $("#ipt_todos_region").change(function () {
        table.column($(this).data('index')).search('').draw();
    });

    $("#ipt_interno").change(function () {
        table.column($(this).data('index')).search(this.value).draw();
    });

    $("#ipt_externo").change(function () {
        table.column($(this).data('index')).search(this.value).draw();
    });
    //filtro tipo de documento
    $("#tipoDocumento").keyup(function () {
        table.column($(this).data('index')).search(this.value).draw();
    });
    //filtro departamento o dependencia
    $("#inpt_dpto_dpencia").keyup(function () {
        table.column($(this).data('index')).search(this.value).draw();
    });
    //filtro por periodo
    $("#inpt_periodo").keyup(function () {
        table.column($(this).data('index')).search(this.value).draw();
    });
    //Filtro para las fechas de expedicion
    $("#FechaExpedicion_ini").keyup(function () {
        table.draw();
    });
    //Accionar el boton para limpiar los filtros
    $("#btn_limpiarFiltros").click(function () {
        form.reset();
        table.columns().search(this.value).draw();

        alphabet.find('.active').removeClass('active');
        $("#clear").addClass('active');

        _alphabetSearch = $(this).data('letter');
        table.draw();
    });

    var alphabet = $('<div class="alphabet"/>').append('Apellido: ');

    $('<span class="clear active"/>')
        .data('letter', '')
        .html('Todos')
        .appendTo(alphabet);

    for (var i = 0; i < 26; i++) {
        var letter = String.fromCharCode(65 + i);

        $('<span/>')
            .data('letter', letter)
            .html(letter)
            .appendTo(alphabet);
    }

    alphabet.insertBefore(table.table().container());

    alphabet.on('click', 'span', function () {
        alphabet.find('.active').removeClass('active');
        $(this).addClass('active');

        _alphabetSearch = $(this).data('letter');
        table.draw();
    });

});

</script>
<template>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-2">
            <!-- Contenido de la pagina -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 space-y-2">

                <div class="text-gray-900 text-xl border-b-2 border-gray-400 text-center">Parámetros para filtrar
                </div>
                <div class="flex flex-row content-end justify-end">
                    <div :hidden="!show_filtros" class="space-x-2">
                        <SecondaryButton id="btn_limpiarFiltros" class="bg-blue-300 hover:bg-blue-700 hover:text-white">Limpiar filtros</SecondaryButton>
                        <SecondaryButton @click="show_filtros = false" class="bg-green-400 hover:bg-green-700 hover:text-white">Ocultar filtros</SecondaryButton>
                    </div>
                    <div :hidden="show_filtros">
                        <SecondaryButton @click="show_filtros = true" class="bg-green-300 hover:bg-green-700 hover:text-white">Mostrar filtros</SecondaryButton>
                    </div>
                </div>

                <div v-show="show_filtros" class="flex flex-col space-x-1">
                    <div class="lg:flex lg:flex-row lg:space-x-2">
                        <div class="flex flex-col w-full">
                            <InputLabel for="tipoDocumento" value="Tipo de documento" class="pt-2" />
                            <TextInput type="text" placeholder="Escribe el tipo de documento" id="tipoDocumento"
                                data-index="7" class="w-full" v-model="form.TipoDocumento" />
                        </div>
                        <div class="flex flex-col w-full">
                            <InputLabel for="Departamento" value="Departamento o dependencia" class="pt-2" />
                            <TextInput type="text" placeholder="Escribe el departamento o dependencia"
                                id="inpt_dpto_dpencia" data-index="6" class="w-full" v-model="form.DptoDpencia" />
                        </div>
                        <div class="flex flex-col w-full">
                            <InputLabel for="PeriodoEscolar" value="Período escolar" class="pt-2" />
                            <TextInput type="text" placeholder="Escribe el período. Ejemplo: 'ENE-JUN 2024'"
                                id="inpt_periodo" data-index="8" class="w-full" v-model="form.PeriodoEscolar" />
                        </div>
                    </div>
                    <div class="md:flex md:flex-row justify-evenly p-3 space-x-5">
                        <div class=" align-middle justify-evenly space-x-2 w-full">
                            <InputLabel for="Estatus" value="Estatus" class="" />
                            <div class="flex flex-auto justify-evenly">
                                <div class="space-x-2">
                                    <label for="Todos">Todos</label>
                                <input type="radio" id="ipt_todos_estatus" value="Todos" v-model="form.Estatus"
                                    data-index="1" />
                                </div>
                                <div class="space-x-2">
                                    <label for="Interno">En proceso</label>
                                <input type="radio" id="ipt_proceso" value="En proceso" v-model="form.Estatus"
                                    data-index="1" />
                                </div>
                                <div class="space-x-2">
                                    <label for="Externo">Entregado</label>
                                <input type="radio" id="ipt_entregado" value="Entregado" v-model="form.Estatus"
                                    data-index="1" />
                                </div>
                                

                            </div>
                            <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
                                form.Estatus }}</div>
                        </div>
                        
                        <div class=" align-middle justify-evenly space-x-2 w-full">
                            <InputLabel for="Region" value="Región del documento" class="pt-2" />
                            <div class="flex flex-auto justify-evenly">
                                <div class="space-x-2">
                                    <label for="Todos">Todos</label>
                                    <input type="radio" id="ipt_todos_region" value="Todos" v-model="form.Region"
                                    data-index="5" />
                                </div>
                                
                                <div class="space-x-2">
                                    <label for="Interno">Interno</label>
                                    <input type="radio" id="ipt_interno" value="Interno" v-model="form.Region"
                                    data-index="5" />
                                </div>
                                
                                <div class="space-x-2">
                                    <label for="Externo">Externo</label>
                                <input type="radio" id="ipt_externo" value="Externo" v-model="form.Region"
                                    data-index="5" />
                                </div>
                            </div>
                            <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
                                form.Region
                            }}</div>
                        </div>
                    </div>
                    
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 space-y-2">
                <table id="tablaDocs" class="md:w-min md:l-min">
                </table>
            </div>
        </div>
    </div>
</template>
<style>
div.alphabet {
    display: table;
    width: 100%;
    margin-bottom: 1em;
}

div.alphabet span {
    display: table-cell;
    color: #3174c7;
    cursor: pointer;
    text-align: center;
    width: 3.5%
}

div.alphabet span:hover {
    text-decoration: underline;
}

div.alphabet span.active {
    color: black;
}

#tablaDocs {
    font-size: 14px;
}

#tablaDocs thead {
    background-color: lightgray;
}

#tablaDocs thead tr th {
    border: 2px solid black;
    text-align: center;
}

#tablaDocs tbody tr td {
    border: 1px solid lightgrey;
    text-align: center;
}
</style>