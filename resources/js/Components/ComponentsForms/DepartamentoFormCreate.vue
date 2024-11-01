<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import { ref, watch } from 'vue';
import FlashMessageCreate from '../ComponentsFlashMessages/FlashMessageCreate.vue';

const props = defineProps({
    abrirModal: {
        type: Boolean,
    },
})
const emit = defineEmits(['update:abrirModal']);
const abrir = ref(props.abrirModal);
watch(() => props.abrirModal, (newVal) => {
    abrir.value = newVal;
});
watch(abrir, (newVal) => {
    emit('update:abrirModal', newVal);
});
const form = useForm({
    nombreDepartamento: '',
});
const flashMessage = ref('');
const disableButtonForm = ref(false);
const nuevoDepartamento = () => {
    form.clearErrors();
    const formDataJson = JSON.stringify(form); // Convertimos a JSON
    $.ajax({
        url: route('departamento.nuevo'),
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
            abrir.value = false;
            form.reset(); // Reseteamos el formulario
            console.log('Formulario enviado exitosamente');
            flashMessage.value = 'Departamento creado correctamente';
            disableButtonForm.value = false;
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
            window.location.reload();
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                form.setError({
                    nombreDepartamento: xhr.responseJSON.errors.nombreDepartamento[0] || {},
                });
            }
            disableButtonForm.value = false;
        }
    });
    /* form.put(route('departamento.nuevo'), {
        preserveScroll: true,
        onSuccess: () => {
            abrir.value = false;
            form.reset()
        },
        onError: () => {
            console.log(form.errors);
        },
    }); */
};

</script>
<template>
    <FlashMessageCreate :flashMessage="flashMessage"></FlashMessageCreate>
    <Modal :show='abrir'>
        <div class="p-8 flex flex-col space-y-4">
            <div class="flex flex-row-reverse items-end justify-between overflow-hidden">
                <DangerButton @click="abrir = false; form.reset(); form.clearErrors()">X</DangerButton>
            </div>
            <div>
                <p>
                    Favor de rellenar el único campo visible para registrar un nuevo departamento.
                </p>
                <form @submit.prevent="nuevoDepartamento">
                    <InputLabel for="nombreDepartamento" value="Nombre del departamento" class="pt-2" />
                    <TextInput id="nombreDepartamento" type="text" class="mt-1 block w-full"
                        v-model="form.nombreDepartamento" autofocus required />
                    <InputError class="mt-2" :message="form.errors.nombreDepartamento" />
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