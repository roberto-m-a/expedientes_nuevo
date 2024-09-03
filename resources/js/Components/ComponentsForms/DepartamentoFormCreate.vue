<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import { ref, watch } from 'vue';

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

const nuevoDepartamento = () => {
    form.put(route('departamento.nuevo'), {
        preserveScroll: true,
        onSuccess: () => {
            abrir.value = false;
            form.reset()
        },
        onError: () => {
            console.log(form.errors);
        },
    });
};

</script>
<template>
    <Modal :show='abrir'>
        <div class="p-8 flex flex-col space-y-4">
            <div class="flex flex-row-reverse items-end justify-between overflow-hidden">
                <DangerButton @click="abrir = false">X</DangerButton>
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
                        <PrimaryButton>Guardar</PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </Modal>
</template>