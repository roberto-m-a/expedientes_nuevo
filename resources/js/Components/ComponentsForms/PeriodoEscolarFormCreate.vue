<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, watch } from 'vue';
import InputError from '@/Components/InputError.vue';
import FlashMessageCreate from '../ComponentsFlashMessages/FlashMessageCreate.vue';
const props = defineProps({
    abrirModal: {
        type: Boolean,
    },
});
const emit = defineEmits(['update:abrirModal']);
const abrir = ref(props.abrirModal);
watch(() => props.abrirModal, (newVal) => {
    abrir.value = newVal;
});
watch(abrir, (newVal) => {
    emit('update:abrirModal', newVal);
});
const form = useForm({
    fechaInicio: '',
    fechaTermino: '',
    nombre_corto: '',
});
const flashMessage = ref('');
const disableButtonForm = ref(false);
const nuevoPeriodoEscolar = () => {
    form.clearErrors();
    const formDataJson = JSON.stringify(form); // Convertimos a JSON
    $.ajax({
        url: route('periodoEscolar.nuevo'),
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
            form.reset();
            flashMessage.value = 'Período escolar creado correctamente';
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
                    form.setError({
                        [field]: messages[0] || {}
                    });
                });
            }
            disableButtonForm.value = false;
        }
    });
    /* form.put(route('periodoEscolar.nuevo'), {
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
                    Favor de rellenar los campos para registrar un nuevo período escolar.
                </p>
                <form @submit.prevent="nuevoPeriodoEscolar">
                    <div class="space-y-2">
                        <InputLabel for="fechaInicio" value="Fecha de inicio" class="" />
                        <TextInput id="fechaInicio" type="date" class="block w-full" v-model="form.fechaInicio"
                            required />
                        <InputError class="mt-2" :message="form.errors.fechaInicio" />
                        <InputLabel for="fechaTermino" value="Fecha de término" class="" />
                        <TextInput id="fechaTermino" type="date" class="block w-full" v-model="form.fechaTermino"
                            required />
                        <InputError class="mt-2" :message="form.errors.fechaTermino" />

                        <InputLabel for="nombre_corto" value="Nombre corto" class="pt-2" />
                        <TextInput id="nombre_corto" type="text" class="mt-1 block w-full" v-model="form.nombre_corto"
                            autofocus required />
                        <InputError class="mt-2" :message="form.errors.nombre_corto" />
                    </div>
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