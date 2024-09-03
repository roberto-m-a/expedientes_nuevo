<script setup>
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: Boolean,
    },
});

const emit = defineEmits(['update:modelValue']);

const abrirModal = ref(props.modelValue);

watch(() => props.modelValue, (newVal) => {
    abrirModal.value = newVal;
});

watch(abrirModal, (newVal) => {
    emit('update:modelValue', newVal);
});

const form = useForm({
    nombreTipoDoc: '',
});

const nuevoTipoDoc = () => {
    form.put(route('tipoDoc.nuevo'), {
        preserveScroll: true,
        onSuccess: () => {
            abrirModal.value = false;
            form.reset();
        },
        onError: () => {
            console.log(form.errors);
        },
    });
};
</script>

<template>
    <Modal :show="abrirModal" @close="abrirModal = false">
        <div class="p-8 flex flex-col space-y-4">
            <div class="flex flex-row-reverse items-end justify-between overflow-hidden">
                <DangerButton @click="abrirModal = false; form.reset();">X</DangerButton>
            </div>
            <div>
                <p>
                    Favor de rellenar el único campo visible para registrar un nuevo tipo de documento.
                </p>
                <form @submit.prevent="nuevoTipoDoc">
                    <InputLabel for="tipoDoc" value="Nombre del tipo de documento" class="pt-2" />
                    <TextInput id="tipoDoc" type="text" class="mt-1 block w-full" v-model="form.nombreTipoDoc" autofocus
                        required />
                    <InputError class="mt-2" :message="form.errors.nombreTipoDoc" />
                    <div class="flex justify-between items-center pt-4">
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
