<script setup>
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import FlashMessageEdit from '../ComponentsFlashMessages/FlashMessageEdit.vue';
const props = defineProps({
    modelValue: {
        type: Boolean,
    },
    tipoDoc: {
        type: Object,
    }
});
const emit = defineEmits(['update:modelValue', 'update:tipoDoc']);

const abrirModal = ref(props.modelValue);

watch(() => props.modelValue, (newVal) => {
    abrirModal.value = newVal;
});

watch(abrirModal, (newVal) => {
    emit('update:modelValue', newVal);
});

const tipoDocumento = ref(props.tipoDoc);
watch(() => props.tipoDoc, (newVal) => {
    tipoDocumento.value = newVal;
    formEdit.idtipoDoc = tipoDocumento.value?.IdTipoDocumento;
    formEdit.nombreTipoDoc = tipoDocumento.value?.nombreTipoDoc;
});

watch(tipoDocumento, (newVal) => {
    emit('update:tipoDoc', newVal);
});
//Editar documentos
const formEdit = useForm({
    idtipoDoc: tipoDocumento.value?.IdTipoDocumento,
    nombreTipoDoc: tipoDocumento.value?.nombreTipoDoc,
});
const flashMessage = ref('');
const disableButtonForm = ref(false);
const editTipoDoc = () => {
    formEdit.clearErrors();
    let registros = tipoDocumento.value.numDocumentos;
    const randomCode = Math.floor(1000 + Math.random() * 9000); // Genera un código aleatorio de 4 dígitos
    Swal.fire({
        title: 'Confirmación necesaria',
        text: `Esta accion afectará a ${registros} registros. Para continuar, ingresa el código de confirmación: ${randomCode}`,
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
            const formDataJson = JSON.stringify(formEdit); // Convertimos a JSON
            $.ajax({
                url: route('tipoDoc.editar'),
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
                    abrirModal.value = false;
                    formEdit.reset();
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                    flashMessage.value = 'Tipo de documento editado correctamente';
                    disableButtonForm.value = false;
                    window.location.reload();
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        formEdit.setError({
                            nombreTipoDoc: xhr.responseJSON.errors.nombreTipoDoc[0] || {},
                        });
                    }
                    disableButtonForm.value = false;
                }
            });
            /* formEdit.put(route('tipoDoc.editar'), {
                preserveScroll: true,
                onSuccess: () => {
                    tipoDocumento.value = null;
                    formEdit.reset();
                    abrirModal.value = false;
                },
                onError: () => {
                    console.log(formEdit.errors);
                },
            }); */
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            Swal.fire('Acción cancelada', 'No se realizó ninguna acción.', 'error');
            abrirModal.value = false;
            formEdit.reset();
        }
    });
};
</script>
<template>
    <FlashMessageEdit :flashMessage="flashMessage"></FlashMessageEdit>
    <Modal :show='abrirModal'>
        <div class="p-8 flex flex-col space-y-4">
            <div class="flex flex-row-reverse items-end justify-between overflow-hidden">
                <DangerButton @click="abrirModal = false; formEdit.reset(); tipoDocumento = null; formEdit.clearErrors()">X</DangerButton>
            </div>
            <div>
                <p>
                    Edita el tipo de documento
                </p>
                <form @submit.prevent="editTipoDoc">
                    <InputLabel for="tipoDoc" value="Nombre del tipo de documento" class="pt-2" />
                    <TextInput id="tipoDoc" type="text" class="mt-1 block w-full" v-model="formEdit.nombreTipoDoc"
                        autofocus required />
                    <InputError class="mt-2" :message="formEdit.errors.nombreTipoDoc" />
                    <div class="flex flex-items justify-between items-center pt-4">
                        <p class="text-red-500 font-semibold">
                            *Corrobore su información antes de guardarla
                        </p>
                        <PrimaryButton :class="{ 'opacity-25': disableButtonForm }" :disabled="disableButtonForm">Guardar</PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </Modal>
</template>