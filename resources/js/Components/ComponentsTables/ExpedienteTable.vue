<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import 'datatables.net-responsive';
import 'datatables.net-select';
import DataTablesLib from 'datatables.net';
import { ref, onMounted } from 'vue';
DataTable.use(DataTablesCore);
DataTable.use(DataTablesLib)

const props = defineProps({
    expedientes: {
        type: Array,
    }
})
//Agregacion de la busqueda en orden alfabetico
var _alphabetSearch;

$.fn.dataTable.ext.search.push(function (settings, searchData) {
    if (!_alphabetSearch) { // No search term - all results shown
        return true;
    }

    if (searchData[2].charAt(0).toUpperCase() === _alphabetSearch) {
        return true;
    }

    return false;
});

$(document).ready(function () {
    var table = $('#tablaExpedientes').DataTable({
        order: [[2, "desc"]],
        paging: true,
        searching: true,
        responsive: true,
        autoWidth: false,
        language: {
            "decimal": "",
            "emptyTable": "No hay información",
            "info": "Mostrando del expediente _START_ al expediente _END_ de un total de _TOTAL_ expedientes",
            "infoEmpty": "No hay expedientes",
            "infoFiltered": "(Filtrado de un total de _MAX_ expedientes)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ expedientes",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "Sin expedientes",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },},
            data: props.expedientes,
            columns: [
                { title: "Cantidad de documentos", data: 'numDocumentos' },
                { title: "Nombre", data: 'Nombre' },
                { title: "Apellidos", data: 'Apellidos' },
                { title: "Departamento", data: 'nombreDepartamento' },
                {
                    title: "Acciones", data: null, render: function (data, type, row, meta) {
                        return `<div class=" flex justify-center">
                <button class="abrirExpediente flex flex-items justify-center bg-blue-400 hover:bg-blue-800 text-black font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded" data-id="${row.IdExpediente}">
                <svg class="h-5 w-5 text-slate-900"  fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                </svg>
                Abrir
                </button>
                
                </div>`;
                    },
                    searchable: false,
                    orderable: false,
                }
            ],
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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                <table id="tablaExpedientes"> </table>
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

#tablaExpedientes thead{
    background-color: lightgray;
}
#tablaExpedientes thead tr th{
    border: 2px solid black;
    text-align: center;
}
#tablaExpedientes tbody tr td{
    border: 1px solid lightgrey;
    text-align: center;
}
</style>