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
        type: Boolean
    },
    departamento: {
        type: Object,
    }
})
const emit = defineEmits(['update:abrirModal', 'update:departamento']);

const abrirEdit = ref(props.abrirModal);

watch(() => props.abrirModal, (newVal) => {
    abrirEdit.value = newVal;
});

watch(abrirEdit, (newVal) => {
    emit('update:abrirModal', newVal);
});

const departamento = ref(props.departamento);
watch(() => props.departamento, (newVal) => {
    departamento.value = newVal;
    formEdit.idDepartamento = departamento.value?.IdDepartamento;
    formEdit.nombreDepartamento = departamento.value?.nombreDepartamento;
});

watch(departamento, (newVal) => {
    emit('update:departamento', newVal);
});
//Editar departamento
const formEdit = useForm({
    idDepartamento: departamento.value?.IdDepartamento,
    nombreDepartamento: departamento.value?.nombreDepartamento,
});
const editarDepartamento = () => {
    formEdit.post(route('validar.departamento'), {
        preserveScroll: true,
        onSuccess: () => {
            let registrosDocs = departamento.value?.numDocumentos;
            let registrosPers = departamento.value?.numPersonal;
            const randomCode = Math.floor(1000 + Math.random() * 9000); // Genera un código aleatorio de 4 dígitos
            Swal.fire({
                title: 'Confirmación necesaria',
                text: `Esta accion afectará a ${registrosDocs} documentos y a ${registrosPers} personales. Para continuar, ingresa el código de confirmación: ${randomCode}`,
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
                    Swal.fire('Edición confirmada', 'El código es correcto.', 'success');
                    // Aquí puedes agregar la lógica para realizar la acción deseada después de la confirmación
                    formEdit.put(route('departamento.editar'), {
                        preserveScroll: true,
                        onSuccess: () => {
                            abrirEdit.value = false;
                            formEdit.reset()
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
            console.log('no pasó')
            console.log(formEdit.errors);
        },
    });
};
</script>
<template>
    <Modal :show='abrirEdit' >
        <div class="p-8 flex flex-col space-y-4">
            <div class="flex flex-row-reverse items-end justify-between overflow-hidden">
                <DangerButton @click="abrirEdit = false">X</DangerButton>
            </div>
            <div>
                <p>
                    Edita el departamento que seleccionaste
                </p>
                <form @submit.prevent="editarDepartamento">
                    <InputLabel for="nombreDepartamento" value="Nombre del departamento" class="pt-2" />
                    <TextInput id="nombreDepartamento" type="text" class="mt-1 block w-full"
                        v-model="formEdit.nombreDepartamento" autofocus required />
                    <InputError class="mt-2" :message="formEdit.errors.nombreDepartamento" />
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