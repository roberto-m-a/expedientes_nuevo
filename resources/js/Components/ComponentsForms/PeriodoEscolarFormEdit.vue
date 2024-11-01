<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import FlashMessageEdit from '../ComponentsFlashMessages/FlashMessageEdit.vue';
const props = defineProps({
    abrirModal: {
        type: Boolean,
    },
    periodoEscolar: {
        type: Object,
    },
});
const emit = defineEmits(['update:abrirModal', 'update:periodoEscolar']);

const abrirEdit = ref(props.abrirModal);

watch(() => props.abrirModal, (newVal) => {
    abrirEdit.value = newVal;
});

watch(abrirEdit, (newVal) => {
    emit('update:abrirModal', newVal);
});

const periodoEscolar = ref(props.periodoEscolar);
watch(() => props.periodoEscolar, (newVal) => {
    periodoEscolar.value = newVal;
    formEdit.IdPeriodoEscolar = periodoEscolar.value?.IdPeriodoEscolar;
    formEdit.fechaInicio = periodoEscolar.value?.fechaInicio;
    formEdit.fechaTermino = periodoEscolar.value?.fechaTermino;
    formEdit.nombre_corto = periodoEscolar.value?.nombre_corto;
});

watch(periodoEscolar, (newVal) => {
    emit('update:periodoEscolar', newVal);
});
const formEdit = useForm({
    IdPeriodoEscolar: periodoEscolar.value?.IdPeriodoEscolar,
    fechaInicio: periodoEscolar.value?.fechaInicio,
    fechaTermino: periodoEscolar.value?.fechaTermino,
    nombre_corto: periodoEscolar.value?.nombre_corto,
});
const flashMessage = ref('');
const disableButtonForm = ref(false);
//Editar un periodo escolar
const editarPeriodoEscolar = () => {
    formEdit.clearErrors();
    let registros = periodoEscolar.value?.numDocumentos;
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
                url: route('periodoEscolar.editar'),
                method: 'POST',
                contentType: 'application/json',
                data: formDataJson,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function () {
                    disableButtonForm.value = true;
                },
                success: function (response) {
                    abrirEdit.value = false;
                    formEdit.reset();
                    flashMessage.value = 'Período escolar editado correctamente';
                    disableButtonForm.value = false;
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                    window.location.reload();
                },
                error: function (xhr) {
                    console.log(xhr.responseJSON.errors)
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function (field, messages) {
                            formEdit.setError({
                                [field]: messages[0] || {}
                            });
                        });
                    }
                    disableButtonForm.value = false
                }
            });
            /* formEdit.put(route('periodoEscolar.editar'), {
                preserveScroll: true,
                onSuccess: () => {
                    abrirEdit.value = false;
                    formEdit.reset()
                },
                onError: () => {
                    console.log(formEdit.errors);
                },
            }); */
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            abrirEdit.value = false;
            formEdit.reset();
            Swal.fire('Acción cancelada', 'No se realizó ninguna acción.', 'error');
        }
    });
};
</script>
<template>
    <FlashMessageEdit :flashMessage="flashMessage"></FlashMessageEdit>
    <Modal :show='abrirEdit'>
        <div class="p-8 flex flex-col space-y-4">
            <div class="flex flex-row-reverse items-end justify-between overflow-hidden">
                <DangerButton @click="abrirEdit = false; formEdit.reset(); formEdit.clearErrors()">X</DangerButton>
            </div>
            <div>
                <p>
                    Edita los datos del período escolar seleccionado
                </p>
                <form @submit.prevent="editarPeriodoEscolar">
                    <div class="space-y-2">
                        <InputLabel for="fechaInicio" value="Fecha de inicio" class="" />
                        <TextInput id="fechaInicio" type="date" class="block w-full" v-model="formEdit.fechaInicio"
                            required />
                        <InputError class="mt-2" :message="formEdit.errors.fechaInicio" />
                        <InputLabel for="fechaTermino" value="Fecha de término" class="" />
                        <TextInput id="fechaTermino" type="date" class="block w-full" v-model="formEdit.fechaTermino"
                            required />
                        <InputError class="mt-2" :message="formEdit.errors.fechaTermino" />
                    </div>
                    <InputLabel for="nombre_corto" value="Nombre corto" class="pt-2" />
                    <TextInput id="nombre_corto" type="text" class="mt-1 block w-full" v-model="formEdit.nombre_corto"
                        autofocus required />
                    <InputError class="mt-2" :message="formEdit.errors.nombre_corto" />
                    <div class="flex flex-items justify-between items-center pt-4">
                        <p class="text-red-500 font-semibold">
                            *Corrobore su información antes de guardarla
                        </p>
                        <PrimaryButton :class="{ 'opacity-25': disableButtonForm }" :disabled="disableButtonForm">
                            Guardar</PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </Modal>
</template>