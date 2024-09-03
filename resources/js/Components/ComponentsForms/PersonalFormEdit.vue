<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import { ref, watch } from 'vue';
import vSelect from 'vue-select';
const props = defineProps({
    abrirModal: {
        type: Boolean,
    },
    personal: {
        type: Object,
    },
    departamentos: {
        type: Array,
    }
});
const emit = defineEmits(['update:abrirModal', 'update:personal']);
const abrirEdit = ref(props.abrirModal);

watch(() => props.abrirModal, (newVal) => {
    abrirEdit.value = newVal;
});

watch(abrirEdit, (newVal) => {
    emit('update:abrirModal', newVal);
});

const personal = ref(props.personal);
watch(() => props.personal, (newVal) => {
    personal.value = newVal;
    formEdit.IdPersonal = personal.value?.IdPersonal;
    formEdit.Nombre = personal.value?.Nombre;
    formEdit.Apellidos = personal.value?.Apellidos;
    formEdit.Departamento = props.departamentos.find(b => b.nombreDepartamento === personal.value?.nombreDepartamento);
    formEdit.Docente = (personal.value?.IdDocente == null)? false : true;
    formEdit.GradoAcademico = (personal.value?.IdDocente == null)? '': personal.value?.GradoAcademico;
    formEdit.Sexo = personal.value?.Sexo;
    formEdit.email = personal.value?.email;
    formEdit.email_confirmation = personal.value?.email;1
});

watch(personal, (newVal) => {
    emit('update:personal', newVal);
});
const formEdit = useForm({
    IdPersonal: personal.value?.IdPersonal,
    Nombre: personal.value?.Nombre,
    Apellidos: personal.value?.Apellidos,
    Departamento: props.departamentos.find(b => b.nombreDepartamento === personal.value?.nombreDepartamento),
    Docente: (personal.value?.IdDocente == null)? false : true,
    GradoAcademico: (personal.value?.IdDocente == null)? '': personal.value?.GradoAcademico,
    Sexo: personal.value?.Sexo,
    email: personal.value?.email,
    email_confirmation: personal.value?.email,
});
const editarPersonal = () => {
    formEdit.post(route('validar.personal'), {
        preserveScroll: true,
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
                    formEdit.put(route('personal.editar'), {
                        preserveScroll: true,
                        onSuccess: () => {
                            personal.value = null;
                            formEdit.reset()
                            abrirEdit.value = false;
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
            console.log(formEdit.errors);
        },
    });
};
</script>
<template>
    <!-- Modal para editar un usuario -->
    <Modal :show="abrirEdit">
        <div class="p-8 flex flex-col space-y-4">
            <div class="flex flex-row-reverse items-end justify-between overflow-hidden">
                <DangerButton @click="formEdit.reset(); personal = null; abrirEdit = false;">X</DangerButton>
            </div>
            <p>
                Edite el personal seleccionado
            </p>
            <form @submit.prevent="editarPersonal">
                <InputLabel for="Nombre" value="Nombre(s)" class="pt-2" />
                <TextInput id="Nombre" type="text" class="mt-1 block w-full" v-model="formEdit.Nombre" required />
                <InputError class="mt-2" :message="formEdit.errors.Nombre" />
                <InputLabel for="Apellidos" value="Apellido(s)" class="pt-2" />
                <TextInput id="Apellidos" type="text" class="mt-1 block w-full" v-model="formEdit.Apellidos"
                    required />
                <InputError class="mt-2" :message="formEdit.errors.Apellidos" />

                <InputLabel for="Estatus" value="Sexo" />
                <div>
                    <div class="flex flex-auto justify-evenly">
                        <div class="space-x-2">
                            <label for="Hombre">Hombre</label>
                            <input type="radio" id="Hombre" value="Hombre" v-model="formEdit.Sexo" />
                        </div>
                        <div class="space-x-2">
                            <label for="Mujer">Mujer</label>
                            <input type="radio" id="entregado" value="Mujer" v-model="formEdit.Sexo" />
                        </div>
                    </div>
                    <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
                        formEdit.Sexo }}</div>
                </div>
                <InputError class="mt-2" :message="formEdit.errors.Sexo" />
                <div>
                    <InputLabel for="Departamento" value="Departamento" class="pt-2" />
                    <v-select type="text" id="Departamento" label="nombreDepartamento"
                        placeholder="Introduce el departamento del que proviene" :options="departamentos"
                        :filterable="true" v-model="formEdit.Departamento" class="border-white" />
                    <InputError class="mt-2" :message="formEdit.errors.Departamento" />
                </div>
                <div v-show="formEdit.Docente">
                    <InputLabel for="" value="Grado Academico del docente" />
                    <select value="" id="GradoAcademico" v-model="formEdit.GradoAcademico"
                        class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="Licenciatura">Licenciatura</option>
                        <option value="Posgrado">Posgrado</option>
                        <option value="Mestria">Maestria</option>
                        <option value="Doctorado">Doctorado</option>
                        <option value="Otro">otro</option>
                    </select>
                </div>
                <InputError class="mt-2" :message="formEdit.errors.GradoAcademico" />
                <div v-if="formEdit.email != null">
                    <div class="mt-4" oncopy="return false" onpaste="return false">
                        <InputLabel for="email" value="Correo" />

                        <TextInput id="email" type="email" class="mt-1 block w-full" v-model="formEdit.email"
                            required autocomplete="username" />

                        <InputError class="mt-2" :message="formEdit.errors.email" />

                    </div>
                    <div class="mt-4" oncopy="return false" onpaste="return false">
                        <InputLabel for="email" value="Confirmar correo" />

                        <TextInput id="email_confirmation" type="email" class="mt-1 block w-full"
                            v-model="formEdit.email_confirmation" required />

                        <InputError class="mt-2" :message="formEdit.errors.email" />

                    </div>
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
<style>
@import "vue-select/dist/vue-select.css";
</style>