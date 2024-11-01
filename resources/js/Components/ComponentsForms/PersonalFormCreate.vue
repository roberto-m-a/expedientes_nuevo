<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { ref, watch } from 'vue';
import vSelect from 'vue-select';
import FlashMessageCreate from '../ComponentsFlashMessages/FlashMessageCreate.vue';

const props = defineProps({
    abrirModal: {
        type: Boolean,
    },
    departamentos: {
        type: Array,
    },
    gradoAcademico: {
        type: Array,
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
const crearUser = ref(false);

const form = useForm({
    Nombre: '',
    Apellidos: '',
    Sexo: 'Hombre',
    Departamento: '',
    tipoUsuario: 'Docente',
    GradoAcademico: '',
    crearUsuario: false,
    email: '',
    email_confirmation: '',
});
const flashMessage= ref('');
const disableButtonForm = ref(false);
const nuevoPersonal = () => {
    form.clearErrors();
    const formDataJson = JSON.stringify(form); // Convertimos a JSON
    $.ajax({
        url: route('personal.nuevo'),
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
            form.reset();;
            flashMessage.value = 'Personal creado correctamente';
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
    /* form.put(route('personal.nuevo'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            abrir.value = false;
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
                    Favor de rellenar los campos solicitados para agregar un nuevo personal.
                </p>
                <form @submit.prevent="nuevoPersonal">
                    <InputLabel for="Nombre" value="Nombre(s)" class="pt-2" />
                    <TextInput id="Nombre" type="text" class="mt-1 block w-full" v-model="form.Nombre" required />
                    <InputError class="mt-2" :message="form.errors.Nombre" />
                    <InputLabel for="Apellidos" value="Apellido(s)" class="pt-2" />
                    <TextInput id="Apellidos" type="text" class="mt-1 block w-full" v-model="form.Apellidos" required />
                    <InputError class="mt-2" :message="form.errors.Apellidos" />
                    <div>
                        <InputLabel for="Estatus" value="Sexo" class="" />
                        <div class="flex flex-auto justify-evenly">
                            <div class="space-x-2">
                                <label for="Hombre">Hombre</label>
                                <input type="radio" id="Hombre" value="Hombre" v-model="form.Sexo" />
                            </div>
                            <div class="space-x-2">
                                <label for="Mujer">Mujer</label>
                                <input type="radio" id="entregado" value="Mujer" v-model="form.Sexo" />
                            </div>
                        </div>
                        <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
                            form.Sexo }}</div>
                        <InputError class="mt-2" :message="form.errors.Sexo" />
                    </div>
                    <div>
                        <InputLabel for="Departamento" value="Departamento" class="pt-2" />
                        <v-select type="text" id="Departamento" label="nombreDepartamento"
                            placeholder="Introduce el departamento del que proviene" :options="departamentos"
                            :filterable="true" v-model="form.Departamento" class="border-white" />
                        <InputError class="mt-2" :message="form.errors.Departamento" />
                    </div>
                    <InputLabel for="" value="Selecciona el tipo de personal" />
                    <div>
                        <div class="flex flex-auto justify-evenly">
                            <div class="space-x-2">
                                <label for="Docente">Docente</label>
                                <input type="radio" id="Docente" value="Docente" v-model="form.tipoUsuario" />
                            </div>
                            <div class="space-x-2">
                                <label for="Secretaria">Secretaria</label>
                                <input type="radio" id="Secretaria" value="Secretaria" v-model="form.tipoUsuario" />
                            </div>
                            <div class="space-x-2">
                                <label for="Administrador">Administrador</label>
                                <input type="radio" id="Administrador" value="Administrador"
                                    v-model="form.tipoUsuario" />
                            </div>
                        </div>
                        <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
                            form.tipoUsuario }}</div>
                    </div>
                    <InputError class="mt-2" :message="form.errors.Docente" />
                    <div v-if="form.tipoUsuario == 'Docente'">
                        <InputLabel for="" value="Grado académico del docente" />
                        <v-select type="text" id="gradoAcademico" label="nombreGradoAcademico"
                            placeholder="Selecciona el grado académico" :options="gradoAcademico"
                            :filterable="true" v-model="form.GradoAcademico" class="border-white" />
                            <InputError class="mt-2" :message="form.errors.GradoAcademico" />
                    </div>
                    <div class="flex flex-auto align-middle justify-evenly p-2 space-x-2">
                        <InputLabel for="crearUser"
                            value="Marque la casilla en caso de querer crear un usuario para este personal ->" />
                        <Checkbox v-model:checked="form.crearUsuario" @click="crearUser = !crearUser;" id="crearUser"
                            value="crearUser" v-model="form.crearUsuario" class="mt-1 block" />
                    </div>
                    <div v-if="form.crearUsuario">
                        <div class="mt-4" oncopy="return false" onpaste="return false">
                            <InputLabel for="email" value="Correo" />
                            <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required
                                autocomplete="username" />
                            <InputError class="mt-2" :message="form.errors.email" />

                        </div>
                        <div class="mt-4" oncopy="return false" onpaste="return false">
                            <InputLabel for="email" value="Confirmar correo" />
                            <TextInput id="email" type="email" class="mt-1 block w-full"
                                v-model="form.email_confirmation" required autocomplete="username" />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>
                    </div>
                    <div class="flex flex-items justify-between items-center pt-4">
                        <p class="text-red-500 font-semibold">
                            *Corrobore todos los datos antes de guardarlos
                        </p>
                        <PrimaryButton :class="{ 'opacity-25': disableButtonForm }" :disabled="disableButtonForm">Guardar</PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </Modal>
</template>
<style>
@import "vue-select/dist/vue-select.css";
</style>