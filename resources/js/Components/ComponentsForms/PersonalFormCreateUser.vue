<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import { ref, watch } from 'vue';
import FlashMessageCreate from '../ComponentsFlashMessages/FlashMessageCreate.vue';
const props = defineProps({
    abrirModal: {
        type: Boolean,
    },
    personal: {
        type: Object
    }
});
const emit = defineEmits(['update:abrirModal', 'update:personal']);
const abrirAniadirUsuario = ref(props.abrirModal);

watch(() => props.abrirModal, (newVal) => {
    abrirAniadirUsuario.value = newVal;
});

watch(abrirAniadirUsuario, (newVal) => {
    emit('update:abrirModal', newVal);
});

const personal = ref(props.personal);
watch(() => props.personal, (newVal) => {
    personal.value = newVal;
    formUser.IdPersonal = personal.value?.IdPersonal;
    formUser.Nombre = personal.value?.Nombre;
    formUser.Apellidos = personal.value?.Apellidos;
});

watch(personal, (newVal) => {
    emit('update:personal', newVal);
});

const formUser = useForm({
    IdPersonal: personal.value?.IdPersonal,
    Nombre: personal.value?.Nombre,
    Apellidos: personal.value?.Apellidos,
    email: '',
    email_confirmation: '',
});
const flashMessage = ref('');
const disableButtonForm = ref(false);
const registrarUsuario = () => {
    formUser.clearErrors();
    const randomCode = Math.floor(1000 + Math.random() * 9000); // Genera un código aleatorio de 4 dígitos
    Swal.fire({
        title: 'Confirmación necesaria',
        text: `Para continuar, ingresa el código de confirmación: ${randomCode}`,
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
            const formDataJson = JSON.stringify(formUser); // Convertimos a JSON
            $.ajax({
                url: route('aniadir.usuario'),
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
                    abrirAniadirUsuario.value = false;
                    formUser.reset();
                    flashMessage.value = 'Usuario creado correctamente';
                    disableButtonForm.value = false;
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                    window.location.reload();
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function (field, messages) {
                            formUser.setError({
                                [field]: messages[0] || {}
                            });
                        });
                    }
                    disableButtonForm.value = false;
                }
            });
            /* formUser.post(route('aniadir.usuario'), {
                preserveScroll: true,
                onSuccess: () => {
                    formUser.reset()
                    personal.value = null;
                    abrirAniadirUsuario.value = false;
                },
                onError: () => {
                    console.log(formUser.errors);
                },
            }); */
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            Swal.fire('Acción cancelada', 'No se realizó ninguna acción.', 'error');
            formUser.reset();
            abrirAniadirUsuario.value = false;
        }
    });
};
</script>
<template>
    <FlashMessageCreate :flashMessage></FlashMessageCreate>
    <Modal :show="abrirAniadirUsuario">
        <div class="p-8 flex flex-col space-y-4">
            <div class="flex flex-row-reverse items-end justify-between overflow-hidden">
                <DangerButton
                    @click="abrirAniadirUsuario = false; formUser.reset(); formUser.clearErrors(); personal = null">X
                </DangerButton>
            </div>
            <p>
                Agregue el correo para <span class="font-bold">{{ formUser.Nombre + ' ' + formUser.Apellidos
                    }}</span>
            </p>
            <form @submit.prevent="registrarUsuario">
                <div class="mt-4" oncopy="return false" onpaste="return false">
                    <InputLabel for="email" value="Correo" />
                    <TextInput id="email" type="email" class="mt-1 block w-full" v-model="formUser.email" required />
                    <InputError class="mt-2" :message="formUser.errors.email" />
                </div>
                <div class="mt-4" oncopy="return false" onpaste="return false">
                    <InputLabel for="email" value="Confirmar correo" />
                    <TextInput id="email_confirmation" type="email" class="mt-1 block w-full"
                        v-model="formUser.email_confirmation" required />
                    <InputError class="mt-2" :message="formUser.errors.email" />
                </div>
                <div class="flex flex-items justify-between items-center pt-4">
                    <p class="text-red-500 font-semibold">
                        *Corrobore todos los datos antes de guardarlos
                    </p>
                    <PrimaryButton :class="{ 'opacity-25': disableButtonForm }" :disabled="disableButtonForm">Guardar
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>