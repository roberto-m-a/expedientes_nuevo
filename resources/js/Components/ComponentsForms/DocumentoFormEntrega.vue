<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    abrirModal: {
        type: Boolean,
    },
    documento: {
        type: Object,
    },
})

const emit = defineEmits(['update:abrirModal', 'update:documento']);
const abrirEntrega = ref(props.abrirModal);
watch(() => props.abrirModal, (newVal) => {
    abrirEntrega.value = newVal;
});
watch(abrirEntrega, (newVal) => {
    emit('update:abrirModal', newVal);
});
const documento = ref(props.documento);
watch(() => props.documento, (newVal) => {
    documento.value = newVal;
    formEntrega.IdDocumento = documento.value?.IdDocumento;
    formEntrega.Titulo = documento.value?.Titulo;
    formEntrega.FechaExpedicion = documento.value?.fechaExpedicion;
    formEntrega.URL = documento.value?.URL;
});
watch(documento, (newVal) => {
    emit('update:documento', newVal);
});

const fecha = new Date();
const fechaActual = fecha.getFullYear() + '-' + ((fecha.getMonth() + 1) < 10 ? '0' + (fecha.getMonth() + 1) : (fecha.getMonth() + 1)) + '-' + ((fecha.getDate()) < 10 ? '0' + (fecha.getDate()) : (fecha.getDate()));
const pesoMax = ref(false);
const formEntrega = useForm({
    IdDocumento: documento.value?.IdDocumento,
    Titulo: documento.value?.Titulo,
    FechaExpedicion: documento.value?.FechaExpedicion,
    FechaEntrega: '',
    Archivo: '',
    URL: documento.value?.URL,
});

const entregarDocumento = () => {
    formEntrega.post(route('validar.entrega'), {
        onSuccess: () =>{
            const randomCode = Math.floor(1000 + Math.random() * 9000); // Genera un código aleatorio de 4 dígitos
            Swal.fire({
                title: 'Confirmación necesaria',
                text: `Esta por entregar el documento ${formEntrega.Titulo}. Para continuar, ingresa el código de confirmación: ${randomCode}`,
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
                    Swal.fire('Entrega confirmada', 'El código es correcto.Espere a recargar la pagina', 'success');
                    formEntrega.post(route('entregar.documento'), {
                        preserveScroll: true,
                        onSuccess: () => {
                            abrirEdit.value = false;
                            formEntrega.reset();
                            document.getElementById('Archivo').value = null;
                            document.querySelector('#vistaPrevia').setAttribute('src', '');
                            location.reload();
                        },
                        onError: () => {
                            console.log(formEntrega.errors);
                        },
                    });
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    abrirEdit.value = false;
                    formEntrega.reset();
                    Swal.fire('Acción cancelada', 'No se realizó ninguna acción.', 'error');
                }
            });
        },
        onError: () => {
            console.log(formEntrega.errors);
        },
    })
}
//Metodo para limpiar el input del archivo
const limpiar = () => {
    formEntrega.Archivo = '';
    document.getElementById('Archivo').value = null;
    document.querySelector('#vistaPrevia').setAttribute('src', '');
    pesoMax.value = false;
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
        formEntrega.Archivo = '';
        pesoMax.value = false;
    }
    const file = e.target.files[0];
    console.log(file);
    if (file.size > 5000000) {
        document.getElementById('Archivo').value = null;
        document.querySelector('#vistaPrevia').setAttribute('src', '');
        formEntrega.Archivo = '';
        pesoMax.value = true;
    }
    else {
        formEntrega.Archivo = file;
        pesoMax.value = false;
        let pdffFile = document.getElementById('Archivo').files[0];
        let pdffFileURL = URL.createObjectURL(pdffFile) + "#toolbar=0";
        document.querySelector('#vistaPrevia').setAttribute('src', pdffFileURL);
        console.log(formEntrega);
    }
};
</script>
<template>
    <Modal :show="abrirEntrega">
        <div class="p-8 flex flex-col space-y-4">
            <div class="flex flex-row-reverse items-end justify-between overflow-hidden">
                <DangerButton @click="abrirEntrega = false; formEntrega.reset(); documento = null">X</DangerButton>
            </div>
            <form @submit.prevent="entregarDocumento">
                <InputLabel for="FechaExpedicion" value="Fecha de expedición" class="pt-2" />
                <InputLabel for="FechaExpedicion" :value="formEntrega.FechaExpedicion" class="pt-2 font-semibold text-xl" />
                <InputLabel for="FechaEntrega" value="Fecha de entrega" class="pt-2" />
                    <TextInput id="FechaEntrega" type="date" :min="formEntrega.FechaExpedicion"
                        :max="fechaActual" class="mt-1 block w-full" required
                        v-model="formEntrega.FechaEntrega" />
                    <InputError class="mt-2" :message="formEntrega.errors.FechaEntrega" />

                <p class="font-bold text-xl text-center">Vista del documento subido</p>
                <div class="flex justify-center">
                    <iframe :src="formEntrega.URL" frameborder="0" class="w-full h-60"></iframe>
                </div>

                <InputLabel for="archivo"
                    value="Si desea actualizar el archivo ingrese un nuevo archivo.(peso máx. 5MB)"
                    class="pt-2 text-l font-semibold text-center text-red-500" />
                <div class="space-y-2">
                    <TextInput id="Archivo" type="file" class="mt-1 block w-full" accept="application/pdf"
                        @change="documentoT" />
                    <div v-if="formEntrega.Archivo != ''"
                        class="flex flex-auto align-middle justify-center space-x-4 pr-5">
                        <DangerButton @click="limpiar">Quitar archivo</DangerButton>
                    </div>
                </div>
                <InputError class="mt-2" :message="formEntrega.errors.Archivo" />

                <div v-show="pesoMax">
                    <p class="text-red-500">
                        El peso excede el permitido.
                    </p>
                </div>

                <div v-show="formEntrega.Archivo != ''"
                    class="justify-items-center content-center p-2 text-gray-900 space-y-4">
                    <InputLabel for="vistaPrevia" value="Vista previa del nuevo documento"
                        class="text-center text-xl" />
                    <div class="flex justify-center">
                        <embed id="vistaPrevia" type="application/pdf" class="bg-gray-700 w-full h-60">
                    </div>
                    <InputLabel v-if="formEntrega.Archivo == ''" for="vistaPrevia"
                        value="Aún no se ha subido nada" class="text-center text-l text-red-600" />
                </div>

                <div class="flex flex-col justify-center pt-2 text-center">
                    <div>
                        <p class="text-sm p-2">
                            *Antes de entregar asegurese de corroborar su información y su archivo.
                        </p>
                    </div>
                    <PrimaryButton class="flex justify-center">Entregar archivo</PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>