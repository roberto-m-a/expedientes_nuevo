<script setup>
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import vSelect from 'vue-select';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

const props = defineProps({
    tipo_documentos: {
        type: Array,
    },
    periodos_escolares: {
        type: Array,
    },
    departamentos: {
        type: Array,
    },
    dataHombres: {
        type: Array,
    },
    dataMujeres: {
        type: Array
    },
    labelsTipoDoc: {
        type: Array,
    },
    interpretacion: {
        type: String,
    },
});
const dataHombres = ref(props.dataHombres);
const dataMujeres = ref(props.dataMujeres);
const labelsTipoDoc = ref(props.labelsTipoDoc);
const interpretacion = ref(props.interpretacion);
const show_filtros = ref(true); //Variable que maneja la vista de los filtros
//Variable que permite regresar los datasets de cada tipo de documento por sexo
let datos_grafica = computed(() => {
    return [
        {
            label: 'Hombres',
            backgroundColor: 'rgba(75, 192, 192, 0.6)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1,
            data: dataHombres.value,
        },
        {
            label: 'Mujeres',
            backgroundColor: 'rgba(255, 182, 193, 0.6)',
            borderColor: 'rgba(255, 182, 193, 1)',
            borderWidth: 1,
            data: dataMujeres.value,
        },
    ];
})
//Variable que permite regresar el arreglo con las cantidades de hombres y mujeres para la grafica de pastel
let dataPastel = computed(() => {
    if (labelsTipoDoc.value.length == 1) {
        return dataHombres.value.concat(props.dataMujeres);
    } else {
        return [];
    }
});
//Metodo para limpiar el formulario
const limpiarFiltros = () => {
    form.Region = 'Todos';
    form.TipoDocumento = '';
    form.Departamento = '';
    form.Estatus = 'Todos';
    form.PeriodoEscolar = '';
}
const flashMessage = ref('');
const disableButtonForm = ref(false);
//variable que permite manejar el formulario de la consulta
const form = useForm({
    Region: 'Todos',
    TipoDocumento: '',
    Departamento: '',
    Estatus: 'Todos',
    PeriodoEscolar: '',
});
//Metodo para poder retornar la consulta con los datos enviados al formulario
const filtrarConsulta = () => {
    const formDataJson = JSON.stringify(form); // Convertimos a JSON
    $.ajax({
        url: route('filtrar.consulta'),
        method: 'POST',
        contentType: 'application/json',
        data: formDataJson,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function(){
            disableButtonForm.value = true;
        },  
        success: function (response) {
            dataHombres.value = response.dataHombres;
            dataMujeres.value = response.dataMujeres;
            interpretacion.value = response.interpretacion;
            labelsTipoDoc.value = response.labelsTipoDoc;
            disableButtonForm.value = false;
        },
        error: function (xhr) {
            console.log(xhr.responseJSON);
            if (xhr.status === 404) {
                flashMessage.value = xhr.responseJSON.error;
                setTimeout(() => {
                    flashMessage.value = '';
                }, 5000);
            }
            disableButtonForm.value = false;
        }
    });
    /* form.post(route('filtrar.consulta'), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('si jaló')
        },
        onError: () => {
            console.log('salio error')
        }
    }) */
}
</script>
<template>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-2">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 space-x-2">
                <div class="text-gray-900 text-2xl border-b-2 border-gray-400 text-center">Consultar gráfica con
                    parámetros
                </div>
                <div class="flex flex-row content-end justify-end pt-2">
                    <div :hidden="!show_filtros" class="space-x-2">
                        <SecondaryButton @click="limpiarFiltros" class="bg-blue-300 hover:bg-blue-700 hover:text-white">
                            Limpiar filtros</SecondaryButton>
                        <SecondaryButton @click="show_filtros = false"
                            class="bg-green-300 hover:bg-green-700 hover:text-white">Ocultar filtros
                        </SecondaryButton>
                    </div>
                    <div :hidden="show_filtros">
                        <SecondaryButton @click="show_filtros = true"
                            class="bg-green-300 hover:bg-green-700 hover:text-white">Mostrar filtros
                        </SecondaryButton>
                    </div>
                </div>
                <div v-show="show_filtros">
                    <!-- Se estructura el formulario para la consulta -->
                    <form @submit.prevent="filtrarConsulta" class="space-y-2" enctype="multipart/form-data">
                        <div class="lg:flex lg:flex-row lg:space-x-2">
                            <div class="flex flex-col w-full">
                                <div class="flex flex-row items-center">
                                    <InputLabel for="tipoDocumento" value="Por tipo de documento" class="pt-2" />
                                    <div
                                        title="Se mostrará una grafica de pastel si ingresa un tipo de documento en específico">
                                        <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>
                                <v-select type="text" id="tipoDocumento" label="nombreTipoDoc"
                                    placeholder="Introduce el tipo de documento" :options="tipo_documentos"
                                    :filterable="true" v-model="form.TipoDocumento" class="border-white" />
                            </div>
                            <div class="flex flex-col w-full">
                                <InputLabel for="periodoEscolar" value="Por período escolar" class="pt-2" />
                                <v-select type="text" id="periodoEscolar" label="generalInfo"
                                    placeholder="Introduce el período escolar" :options="periodos_escolares"
                                    :filterable="true" v-model="form.PeriodoEscolar" class="border-white" />
                            </div>
                            <div class="flex flex-col w-full">
                                <InputLabel for="Departamento" value="Por departamento" class="pt-2" />
                                <v-select type="text" id="Departamento" label="nombreDepartamento"
                                    placeholder="Introduce el departamento" :options="departamentos" :filterable="true"
                                    v-model="form.Departamento" class="border-white" />
                            </div>
                        </div>
                        <div class="md:flex md:flex-row justify-evenly p-3 space-x-5">
                            <div class=" align-middle justify-evenly space-x-2  w-full"
                                v-show="form.Departamento == '' || form.Departamento == null">
                                <InputLabel for="Region" value="Por región del documento" class="pt-2"
                                    v-show="form.Departamento == '' || form.Departamento == null" />

                                <div class="flex flex-auto justify-evenly">
                                    <div class="space-x-2">
                                        <label for="Todos">Todos</label>
                                        <input type="radio" id="Todos" value="Todos" v-model="form.Region" />
                                    </div>
                                    <div class="space-x-2">
                                        <label for="Interno">Interno</label>
                                        <input type="radio" id="interno" value="Interno" v-model="form.Region" />
                                    </div>
                                    <div class="space-x-2">
                                        <label for="Externo">Externo</label>
                                        <input type="radio" id="externo" value="Externo" v-model="form.Region" />
                                    </div>
                                </div>
                                <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
                                    form.Region
                                }}</div>
                            </div>
                            <div class=" align-middle justify-evenly space-x-2 w-full">
                                <InputLabel for="Estatus" value="Por su estatus" />
                                <div class="flex flex-auto justify-evenly">
                                    <div class="space-x-2">
                                        <label for="Todos">Todos</label>
                                        <input type="radio" id="Todos" value="Todos" v-model="form.Estatus" />
                                    </div>
                                    <div class="space-x-2">
                                        <label for="En proceso">En proceso</label>
                                        <input type="radio" id="proceso" value="En proceso" v-model="form.Estatus" />
                                    </div>
                                    <div class="space-x-2">
                                        <label for="Externo">Entregado</label>
                                        <input type="radio" id="entregado" value="Entregado" v-model="form.Estatus" />
                                    </div>
                                </div>
                                <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
                                    form.Estatus }}</div>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <PrimaryButton :class="{ 'opacity-25': disableButtonForm }"
                            :disabled="disableButtonForm">Consultar con filtros</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
            <!--Area de graficas-->
            <div v-if="flashMessage" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 space-x-2">
                <FlashMessageError :flashMessage="flashMessage"></FlashMessageError>
            </div>
            <div class="md:flex md:flex-row space-x-2">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 space-x-4 content-center">
                    <div id="app">
                        <GraficaBarras :labels="labelsTipoDoc" :datasets="datos_grafica"
                            :text="interpretacion" :legend="true" />
                    </div>
                </div>
                <div v-if="labelsTipoDoc.length == 1"
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 space-x-4">
                    <div>
                        <GraficaPastel :data="dataPastel" />
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>
<script>
//Este script permite importar los componentes de las graficas de forma correcta
import GraficaBarras from '@/Components/GraficaBarras.vue';
import GraficaPastel from '@/Components/GraficaPastel.vue';
import { computed } from 'vue';
import FlashMessageError from '../ComponentsFlashMessages/FlashMessageError.vue';
export default {
    name: 'App',
    components: {
        GraficaBarras,
        GraficaPastel,
    },
};
</script>
<style>
/*Se importa el estilo de los inputs que utilizan el autocompletado*/
@import "vue-select/dist/vue-select.css";
</style>