<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import { ref, watch } from 'vue';
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

const registrarUsuario = () => {
    formUser.post(route('validar.usuario'), {
        onSuccess: () => {
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
                    formUser.post(route('aniadir.usuario'), {
                        preserveScroll: true,
                        onSuccess: () => {
                            formUser.reset()
                            personal.value = null;
                            abrirAniadirUsuario.value = false;
                        },
                        onError: () => {
                            console.log(formUser.errors);
                        },
                    });
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    Swal.fire('Acción cancelada', 'No se realizó ninguna acción.', 'error');
                    formEdit.reset();
                    abrirAniadirUsuario.value = false;
                }
            });
        },
        onError: () => {
            console.log(formUser.errors);
        },
    })
};
</script>
<template>
    <Modal :show="abrirAniadirUsuario">
        <div class="p-8 flex flex-col space-y-4">
            <div class="flex flex-row-reverse items-end justify-between overflow-hidden">
                <DangerButton @click="abrirAniadirUsuario = false; formUser.reset(); personal = null">X</DangerButton>
            </div>
            <p>
                Agregue el correo para <span class="font-bold">{{ formUser.Nombre + ' ' + formUser.Apellidos
                    }}</span>
            </p>
            <form @submit.prevent="registrarUsuario">
                <div class="mt-4" oncopy="return false" onpaste="return false">
                    <InputLabel for="email" value="Correo" />

                    <TextInput id="email" type="email" class="mt-1 block w-full" v-model="formUser.email"
                        required />

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
                    <PrimaryButton>Guardar</PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>