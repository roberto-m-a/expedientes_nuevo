<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import vSelect from 'vue-select';
const props = defineProps({
    abrirModal: {
        type: Boolean,
    },
    documento: {
        type: Object,
    },
    periodos_escolares: {
        type: Array,
    },
    departamentos: {
        type: Array,
    },
    tipo_documentos: {
        type: Array,
    },
    expedientes: {
        type: Array,
    },
});
const emit = defineEmits(['update:abrirModal', 'update:documento']);
const abrirEdit = ref(props.abrirModal);
watch(() => props.abrirModal, (newVal) => {
    abrirEdit.value = newVal;
});
watch(abrirEdit, (newVal) => {
    emit('update:abrirModal', newVal);
});
const documento = ref(props.documento);
watch(() => props.documento, (newVal) => {
    documento.value = newVal;
    formEdit.IdDocumento = documento.value?.IdDocumento;
    formEdit.Titulo = documento.value?.Titulo;
    formEdit.FechaExpedicion = documento.value?.fechaExpedicion;
    formEdit.FechaEntrega = documento.value?.fechaEntrega;
    formEdit.Region = documento.value?.region;
    formEdit.Estatus = documento.value?.Estatus;
    formEdit.Departamento = props.departamentos.find(a => a.IdDepartamento === documento.value?.IdDepartamento);
    formEdit.TipoDocumento = props.tipo_documentos.find(a => a.IdTipoDocumento === documento.value?.IdTipoDocumento);
    formEdit.PeriodoEscolar = props.periodos_escolares.find(a => a.IdPeriodoEscolar === documento.value?.IdPeriodoEscolar);
    formEdit.Expediente = props.expedientes.find(a => a.IdExpediente === documento.value?.IdExpediente);
    formEdit.Dependencia = documento.value?.dependencia;
    
    formEdit.URL = documento.value?.URL;
});
watch(documento, (newVal) => {
    emit('update:documento', newVal);
});
const fecha = new Date();
const fechaActual = fecha.getFullYear() + '-' + ((fecha.getMonth() + 1) < 10 ? '0' + (fecha.getMonth() + 1) : (fecha.getMonth() + 1)) + '-' + ((fecha.getDate()) < 10 ? '0' + (fecha.getDate()) : (fecha.getDate()));
const pesoMax = ref(false);

const formEdit = useForm({
    IdDocumento: documento.value?.IdDocumento,
    Titulo: documento.value?.Titulo,
    FechaExpedicion: documento.value?.fechaExpedicion,
    FechaEntrega: documento.value?.fechaEntrega,
    Departamento: '',
    TipoDocumento: '',
    PeriodoEscolar: '',
    Expediente: '',
    Archivo: '',
    Dependencia: documento.value?.dependencia,
    Region: documento.value?.region,
    Estatus: documento.value?.Estatus,
    URL: documento.value?.URL,
})

const editarDocumento = () => {
    formEdit.post(route('validar.documento'), {
        onSuccess: () => {
            console.log('validado correctamente')
            const randomCode = Math.floor(1000 + Math.random() * 9000); // Genera un código aleatorio de 4 dígitos
            Swal.fire({
                title: 'Confirmación necesaria',
                text: `Esta por editar el documento ${formEdit.Titulo}. Para continuar, ingresa el código de confirmación: ${randomCode}`,
                input: 'number',
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
                    Swal.fire('Edición confirmada', 'El código es correcto.Espere a recargar la página', 'success');
                    formEdit.post(route('documento.editar'), {
                        preserveScroll: true,
                        onSuccess: () => {
                            abrirEdit.value = false;
                            formEdit.reset();
                            document.getElementById('Archivo').value = null;
                            document.querySelector('#vistaPrevia').setAttribute('src', '');
                            location.reload();
                        },
                        onError: () => {
                            console.log(formEdit.errors);
                        },
                    });
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    abrirEdit.value = false;
                    formEdit.reset();
                    Swal.fire('Acción cancelada', 'No se realizó ninguna acción.', 'error');
                }
            });
        },
        onError: () => {
            console.log(formEdit.errors)
        }
    })
}
//Metodo para validar el archivo que se introduce en el input
const documentoT = async (e) => {
    if (!(e.target instanceof HTMLInputElement)) {
        return Promise.reject(new Error("no es HTMLInputElement"));
    }
    console.log('xd');
    if (e.target.files[0] == null) {
        document.getElementById('Archivo').value = null;
        document.querySelector('#vistaPrevia').setAttribute('src', '');
        formEdit.Archivo = '';
        pesoMax.value = false;
    }
    const file = e.target.files[0];
    console.log(file);
    if (file.size > 5000000) {
        document.getElementById('Archivo').value = null;
        document.querySelector('#vistaPrevia').setAttribute('src', '');
        formEdit.Archivo = '';
        pesoMax.value = true;
    }
    else {
        formEdit.Archivo = file;
        pesoMax.value = false;

        let pdffFile = document.getElementById('Archivo').files[0];
        let pdffFileURL = URL.createObjectURL(pdffFile) + "#toolbar=0";
        document.querySelector('#vistaPrevia').setAttribute('src', pdffFileURL);
        console.log(formEdit);
    }
};
//Metodo para limpiar el input del archivo
const limpiar = () => {
    formEdit.Archivo = '';
    document.getElementById('Archivo').value = null;
    document.querySelector('#vistaPrevia').setAttribute('src', '');
    pesoMax.value = false;
}
</script>
<template>
    <Modal :show="abrirEdit">
        <div class="p-8 flex flex-col space-y-4">
            <div class="flex flex-row-reverse items-end justify-between overflow-hidden">
                <DangerButton @click="abrirEdit = false; formEdit.reset(); documento = null">X</DangerButton>
            </div>

            <form @submit.prevent="editarDocumento" class="flex-row" enctype="multipart/form-data">

                <InputLabel for="Expediente" value="Seleccione el expediente al que desea agregar el documento"
                    class="pt-2" />
                <v-select type="text" id="Expediente" label="generalInfo"
                    placeholder="Introduce el nombre del docente al que quieras añadir el documento"
                    :options="expedientes" :filterable="true" v-model="formEdit.Expediente" class="border-white" />
                <InputError class="mt-2" :message="formEdit.errors.Expediente" />

                <InputLabel for="tipoDocumento" value="Seleccione el tipo de documento" class="pt-2" />
                <v-select type="text" id="tipoDocumento" label="nombreTipoDoc"
                    placeholder="Introduce el tipo de documento" :options="tipo_documentos" :filterable="true"
                    v-model="formEdit.TipoDocumento" class="border-white" />
                <InputError class="mt-2" :message="formEdit.errors.TipoDocumento" />

                <InputLabel for="Titulo" value="Título del documento" class="pt-2" />
                <TextInput id="Titulo" type="text" class="mt-1 block w-full" required v-model="formEdit.Titulo" />
                <InputError class="mt-2" :message="formEdit.errors.Titulo" />
                <InputLabel for="FechaExpedicion" value="Fecha de expedición" class="pt-2" />
                <TextInput id="FechaExpedición" type="date" :max="fechaActual" class="mt-1 block w-full" required
                    v-model="formEdit.FechaExpedicion" />
                <InputError class="mt-2" :message="formEdit.errors.FechaExpedicion" />
                <InputLabel for="Region" value="Región del documento" class="pt-2" />
                <div class=" align-middle justify-evenly space-x-2">

                    <div class="flex flex-auto justify-evenly">
                        <input @change="controlRegion" type="radio" id="interno" value="Interno"
                            v-model="formEdit.Region" />
                        <label for="Interno">Interno</label>

                        <input @change="controlRegion" type="radio" id="externo" value="Externo"
                            v-model="formEdit.Region" />
                        <label for="Externo">Externo</label>
                    </div>
                    <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
                        formEdit.Region
                    }}</div>
                </div>
                <InputError class="mt-2" :message="formEdit.errors.Region" />

                <div v-if="formEdit.Region == 'Interno'">
                    <InputLabel for="Departamento" value="Departamento del documento" class="pt-2" />
                    <v-select type="text" id="Departamento" label="nombreDepartamento"
                        placeholder="Introduce el departamento del que proviene" :options="departamentos"
                        :filterable="true" v-model="formEdit.Departamento" class="border-white" />
                    <InputError class="mt-2" :message="formEdit.errors.Departamento" />
                </div>
                <div v-if="formEdit.Region == 'Externo'">
                    <InputLabel for="Dependencia" value="Dependencia del documento" class="pt-2" />
                    <TextInput id="Dependencia" type="text" class="mt-1 block w-full" required
                        v-model="formEdit.Dependencia" />
                    <InputError class="mt-2" :message="formEdit.errors.Dependencia" />
                </div>

                <div v-if="formEdit.Region == 'Interno'" class=" align-middle justify-evenly space-x-2">
                    <InputLabel for="Estatus" value="Seleccione le estatus del documento" class="" />
                    <div class="flex flex-auto justify-evenly">
                        <input type="radio" id="proceso" value="En proceso" v-model="formEdit.Estatus" />
                        <label for="Interno">En proceso</label>

                        <input type="radio" id="entregado" value="Entregado" v-model="formEdit.Estatus" />
                        <label for="Externo">Entregado</label>
                    </div>
                    <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
                        formEdit.Estatus }}</div>
                </div>
                <div v-if="formEdit.Estatus == 'Entregado'">
                    <InputLabel for="FechaEntrega" value="Fecha de entrega" class="pt-2" />
                    <TextInput id="FechaEntrega" type="date" :min="formEdit.FechaExpedicion" :max="fechaActual"
                        class="mt-1 block w-full" required v-model="formEdit.FechaEntrega" />
                    <InputError class="mt-2" :message="formEdit.errors.FechaEntrega" />
                </div>
                <InputLabel for="periodoEscolar" value="Seleccione el período escolar" class="pt-2" />
                <v-select type="text" id="periodoEscolar" label="generalInfo"
                    placeholder="Periodo escolar al que pertenece" :options="periodos_escolares" :filterable="true"
                    v-model="formEdit.PeriodoEscolar" class="border-white" />
                <InputError class="mt-2" :message="formEdit.errors.PeriodoEscolar" />

                <p class="font-bold text-xl text-center">Vista del documento subido</p>
                <div class="flex justify-center">
                    <iframe :src="formEdit.URL" frameborder="0" class="w-full h-60"></iframe>
                </div>

                <InputLabel for="archivo"
                    value="Si desea actualizar el archivo ingrese un nuevo archivo.(peso máx. 5MB)"
                    class="pt-2 text-l font-semibold text-center text-red-500" />
                <div class="space-y-2">
                    <TextInput id="Archivo" type="file" class="mt-1 block w-full" accept="application/pdf"
                        @change="documentoT" />
                    <div v-if="formEdit.Archivo != ''"
                        class="flex flex-auto align-middle justify-center space-x-4 pr-5">
                        <DangerButton @click="limpiar">Quitar archivo</DangerButton>
                    </div>
                </div>
                <InputError class="mt-2" :message="formEdit.errors.Archivo" />

                <div v-show="pesoMax">
                    <p class="text-red-500">
                        El peso excede el permitido.
                    </p>
                </div>

                <div v-show="formEdit.Archivo != ''"
                    class="justify-items-center content-center p-2 text-gray-900 space-y-4">
                    <InputLabel for="vistaPrevia" value="Vista previa del nuevo documento"
                        class="text-center text-xl" />
                    <div class="flex justify-center">
                        <embed id="vistaPrevia" type="application/pdf" class="bg-gray-700 w-full h-60">
                    </div>
                    <InputLabel v-if="formEdit.Archivo == ''" for="vistaPrevia" value="Aún no se ha subido nada"
                        class="text-center text-l text-red-600" />
                </div>

                <div class="flex flex-col justify-center pt-2 text-center">
                    <div>
                        <p class="text-sm p-2">
                            *Antes de guardar asegurese de corroborar su información.
                        </p>
                    </div>
                    <PrimaryButton class="flex justify-center">Guardar archivo</PrimaryButton>
                </div>
            </form>
        </div>

    </Modal>
</template>
<style>
@import "vue-select/dist/vue-select.css";
</style>