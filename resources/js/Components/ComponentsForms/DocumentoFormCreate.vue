<script setup>
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import { ref } from 'vue';
import vSelect from 'vue-select';
import FlashMessageCreate from '../ComponentsFlashMessages/FlashMessageCreate.vue';
const props = defineProps({
    departamentos: {
        type: Array,
    },
    expediente_data: {
        type: Array,
    },
    periodosEscolares: {
        type: Array,
    },
    tiposDocumentos: {
        type: Array,
    },
    expediente: {
        type: Object,
    }
})
const fecha = new Date();
const pesoMax = ref(false);
const fechaActual = fecha.getFullYear() + '-' + ((fecha.getMonth() + 1) < 10 ? '0' + (fecha.getMonth() + 1) : (fecha.getMonth() + 1)) + '-' + ((fecha.getDate()) < 10 ? '0' + (fecha.getDate()) : (fecha.getDate()));

const form = useForm({
    Titulo: '',
    FechaExpedicion: '',
    FechaEntrega: '',
    Departamento: '',
    TipoDocumento: '',
    PeriodoEscolar: '',
    Expediente: props.expediente ? props.expediente : '',
    Archivo: '',
    Dependencia: '',
    Region: 'Interno',
    Estatus: 'En proceso',
    path: '',
});
const flashMessage = ref('');
const disableButtonForm = ref(false);
const nuevoDocumento = () => {
    form.clearErrors();
    // Crear una instancia de FormData
    const formData = new FormData();
    formData.append('Titulo', form.Titulo);
    formData.append('FechaExpedicion', form.FechaExpedicion);
    formData.append('FechaEntrega', form.FechaEntrega);
    formData.append('Departamento', form.Departamento?.IdDepartamento);
    formData.append('TipoDocumento', form.TipoDocumento?.IdTipoDocumento);
    formData.append('PeriodoEscolar', form.PeriodoEscolar?.IdPeriodoEscolar);
    formData.append('Expediente', form.Expediente?.IdExpediente);
    formData.append('Dependencia', form.Dependencia);
    formData.append('Region', form.Region);
    formData.append('Estatus', form.Estatus);
    formData.append('Archivo', form.Archivo);
    //console.log(formData.Departamento);
    $.ajax({
        url: route('registrar.documento'),
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function () {
            disableButtonForm.value = true;
        },
        success: function (response) {
            form.reset();
            limpiar();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
            flashMessage.value = 'Documento agregado correctamente';
            disableButtonForm.value = false;
            setTimeout(() => {
                flashMessage.value = '';
            }, 3000);
        },
        error: function (xhr) {
            console.log(xhr);
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                $.each(errors, function (field, messages) {
                    form.setError({
                        [field]: messages[0] || {}
                    });
                });
            }
            disableButtonForm.value = false;
        }
    });
    /* form.post(route('registrar.documento'), {
        onSuccess: () => {
            form.reset();
            limpiar();
        },
        onError: () => {
            console.log(form.errors);
        },
    }); */
};
//Método para validar la validacion del documento
const documentoT = async (e) => {
    if (!(e.target instanceof HTMLInputElement)) {
        return Promise.reject(new Error("no es HTMLInputElement"));
    }
    if (e.target.files[0] == null) {
        document.getElementById('Archivo').value = null;
        document.querySelector('#vistaPrevia').setAttribute('src', '');
        form.Archivo = '';
        pesoMax.value = false;
    }
    const file = e.target.files[0];
    if (file.size > 5000000) {
        document.getElementById('Archivo').value = null;
        document.querySelector('#vistaPrevia').setAttribute('src', '');
        form.Archivo = '';
        pesoMax.value = true;
    }
    else {
        form.Archivo = file;
        pesoMax.value = false;
        let pdffFile = document.getElementById('Archivo').files[0];
        let pdffFileURL = URL.createObjectURL(pdffFile) + "#toolbar=0";
        document.querySelector('#vistaPrevia').setAttribute('src', pdffFileURL);
    }
};
//metodo para limpiar el archivo
const limpiar = () => {
    form.Archivo = '';
    document.getElementById('Archivo').value = null;
    document.querySelector('#vistaPrevia').setAttribute('src', '');
    pesoMax.value = false;
}
</script>
<template>
    <FlashMessageCreate :flashMessage="flashMessage"></FlashMessageCreate>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div
                class="sm:flex sm:bg-white sm:overflow-hidden sm:shadow-sm sm:rounded-lg md:bg-white md:l-400 bg-white">
                <div class="text-gray-900 sm:w-max sm:h-max p-6">
                    <form action="" @submit.prevent="nuevoDocumento" class="flex-col" enctype="multipart/form-data">
                        <div class="sm:flex sm:flex-row">
                            <div class="sm:flex-grow">
                                <div v-if="props.expediente_data">
                                    <InputLabel for="Expediente"
                                        value="Seleccione el expediente al que desea agregar el documento"
                                        class="pt-2" />
                                    <v-select type="text" id="Expediente" label="generalInfo"
                                        placeholder="Introduce el nombre del docente al que quieras añadir el documento"
                                        :options="expediente_data" :filterable="true" v-model="form.Expediente"
                                        class="border-white" />
                                    <InputError class="mt-2" :message="form.errors.Expediente" />
                                </div>
                                <InputLabel for="tipoDocumento" value="Seleccione el tipo de documento" class="pt-2" />
                                <v-select type="text" id="tipoDocumento" label="nombreTipoDoc"
                                    placeholder="Introduce el tipo de documento" :options="tiposDocumentos"
                                    :filterable="true" v-model="form.TipoDocumento" class="border-white" />
                                <InputError class="mt-2" :message="form.errors.TipoDocumento" />

                                <InputLabel for="Titulo" value="Título del documento" class="pt-2" />
                                <TextInput id="Titulo" type="text" class="mt-1 block w-full" required
                                    v-model="form.Titulo" />
                                <InputError class="mt-2" :message="form.errors.Titulo" />
                                <InputLabel for="FechaExpedicion" value="Fecha de expedición" class="pt-2" />
                                <TextInput id="FechaExpedición" type="date" :max="fechaActual" class="mt-1 block w-full"
                                    required v-model="form.FechaExpedicion" />
                                <InputError class="mt-2" :message="form.errors.FechaExpedicion" />
                                <InputLabel for="Region" value="Región del documento" class="pt-2" />
                                <div class=" align-middle justify-evenly space-x-2">

                                    <div class="flex flex-auto justify-evenly">
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
                                <InputError class="mt-2" :message="form.errors.Region" />

                                <div v-if="form.Region == 'Interno'">
                                    <InputLabel for="Departamento" value="Seleccione el departamento" class="pt-2" />
                                    <v-select type="text" id="Departamento" label="nombreDepartamento"
                                        placeholder="Introduce el departamento del que proviene"
                                        :options="departamentos" :filterable="true" v-model="form.Departamento"
                                        class="border-white" />
                                    <InputError class="mt-2" :message="form.errors.Departamento" />
                                </div>
                                <div v-if="form.Region == 'Externo'">
                                    <InputLabel for="Dependencia" value="Dependencia" class="pt-2" />
                                    <TextInput id="Dependencia" type="text" class="mt-1 block w-full" required
                                        v-model="form.Dependencia" />
                                    <InputError class="mt-2" :message="form.errors.Dependencia" />
                                </div>

                                <div v-if="form.Region == 'Interno'" class=" align-middle justify-evenly space-x-2">
                                    <InputLabel for="Estatus" value="Seleccione el estatus del documento" class="" />
                                    <div class="flex flex-auto justify-evenly">
                                        <div class="space-x-2">
                                            <label for="Interno">En proceso</label>
                                            <input type="radio" id="proceso" value="En proceso"
                                                v-model="form.Estatus" />
                                        </div>
                                        <div class="space-x-2">
                                            <label for="Externo">Entregado</label>
                                            <input type="radio" id="entregado" value="Entregado"
                                                v-model="form.Estatus" />
                                        </div>

                                    </div>
                                    <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
                                        form.Estatus }}</div>
                                </div>
                                <div v-if="form.Estatus == 'Entregado'">
                                    <InputLabel for="FechaEntrega" value="Fecha de entrega" class="pt-2" />
                                    <TextInput id="FechaEntrega" type="date" :min="form.FechaExpedicion"
                                        :max="fechaActual" class="mt-1 block w-full" required
                                        v-model="form.FechaEntrega" />
                                    <InputError class="mt-2" :message="form.errors.FechaEntrega" />
                                </div>
                                <InputLabel for="periodoEscolar" value="Seleccione el período escolar" class="pt-2" />
                                <v-select type="text" id="periodoEscolar" label="generalInfo"
                                    placeholder="Periodo escolar al que pertenece" :options="periodosEscolares"
                                    :filterable="true" v-model="form.PeriodoEscolar" class="border-white" />
                                <InputError class="mt-2" :message="form.errors.PeriodoEscolar" />

                                <InputLabel for="archivo" value="Archivo (peso máximo: 5MB)" class="pt-2" />
                                <div class="space-y-2">
                                    <TextInput id="Archivo" type="file" class="mt-1 block w-full"
                                        accept="application/pdf" required @change="documentoT" v-model="form.path" />
                                    <div v-if="form.Archivo != ''"
                                        class="flex flex-auto align-middle justify-center space-x-4 pr-5">
                                        <DangerButton @click="limpiar">Quitar archivo</DangerButton>
                                    </div>
                                </div>
                                <InputError class="mt-2" :message="form.errors.Archivo" />

                                <div v-show="pesoMax">
                                    <p class="text-red-500">
                                        El peso excede el permitido.
                                    </p>
                                </div>
                            </div>
                            <p class="sm:hidden justify-center text-center font-semibold">Las vistas previas no son
                                soportadas en dispositivos móviles</p>
                            <div class="justify-items-center content-center p-2 text-gray-900 space-y-4">
                                <InputLabel for="vistaPrevia" value="Vista previa del nuevo documento"
                                    class="hidden sm:flex sm:text-center sm:justify-center text-xl" />
                                <div class="hidden sm:flex justify-center">
                                    <embed id="vistaPrevia" type="application/pdf" width="470" height="600"
                                        class="bg-gray-700">
                                </div>
                                <InputLabel v-if="form.Archivo == ''" for="vistaPrevia" value="Aún no se ha subido nada"
                                    class="text-center text-l text-red-600" />
                            </div>

                        </div>

                        <div class="flex flex-col justify-center pt-2 text-center">
                            <div>
                                <p class="text-sm p-2">
                                    *Antes de guardar el archivo asegurese de corroborar su información. Ya que
                                    en caso
                                    de que
                                    algun dato este mal tendrá que ir al apartado de expedientes y buscar el
                                    documento
                                    entre
                                    todos los expedientes y editarlo desde ahi.
                                </p>
                            </div>
                            <PrimaryButton class="flex justify-center" :class="{ 'opacity-25': disableButtonForm }"
                                :disabled="disableButtonForm">Guardar archivo</PrimaryButton>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</template>
<style>
@import "vue-select/dist/vue-select.css";
</style>