<script setup>
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import vSelect from 'vue-select';
import FlashMessageEdit from '../ComponentsFlashMessages/FlashMessageEdit.vue';

const props = defineProps({
    verificarContraseña: {
        type: Boolean,
    },
    personal: {
        type: Object,
    },
    docente: {
        type: Object,
    },
    departamentos: {
        type: Array,
    },
    gradoAcademico: {
        type: Array,
    },
})
const abrirModal = ref(props.verificarContraseña);
const formUserData = useForm({
    password: '',
    password_confirmation: '',
    Departamento: (props.personal.IdDepartamento != null) ? props.departamentos.find(b => b.IdDepartamento === props.personal.IdDepartamento) : '',
    Sexo: (props.personal.Sexo != null) ? props.personal.Sexo : 'Hombre',
    GradoAcademico: (props.docente?.GradoAcademico != null)? props.gradoAcademico.find(b => b.nombreGradoAcademico === props.docente.GradoAcademico) : '',
});
const flashMessage = ref('');
const updatePassword = () => {
    formUserData.clearErrors();
    const formDataJson = JSON.stringify(formUserData); // Convertimos a JSON
    $.ajax({
        url: route('completar.informacion'),
        method: 'POST',
        contentType: 'application/json',
        data: formDataJson,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            abrirModal.value = false;
            formUserData.reset();
            flashMessage.value = 'Datos actualizados correctamente';
            setTimeout(() => {
                flashMessage.value = '';
            }, 3000);
        },
        error: function (xhr) {
            console.log(xhr.responseJSON);
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                $.each(errors, function (field, messages) {
                    formUserData.setError({
                        [field]: messages[0] || {}
                    });
                });
            }
        }
    });
    /* formUserData.put(route('password.first'), {
        preserveScroll: true,
        onSuccess: () => formUserData.reset(),
        onError: () => {
            if (formUserData.errors.password) {
                formUserData.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
        },
    }); */
};
</script>
<template>
    <FlashMessageEdit :flashMessage="flashMessage"></FlashMessageEdit>
    <Modal :show=abrirModal>
        <div class="p-8">
            <form @submit.prevent="updatePassword">
                <p>
                    Ingresa los siguientes datos
                </p>
                <div>
                    <InputLabel for="Departamento" value="Departamento" class="pt-2" />
                    <v-select type="text" id="Departamento" label="nombreDepartamento"
                        placeholder="Introduce el departamento del que proviene" :options="departamentos"
                        :filterable="true" v-model="formUserData.Departamento" class="border-white" />
                    <InputError class="mt-2" :message="formUserData.errors.Departamento" />
                </div>
                <div v-if="props.gradoAcademico">
                    <InputLabel for="" value="Grado académico del docente" />
                    <v-select type="text" id="gradoAcademico" label="nombreGradoAcademico"
                        placeholder="Selecciona el grado académico" :options="gradoAcademico" :filterable="true"
                        v-model="formUserData.GradoAcademico" class="border-white" />
                    <InputError class="mt-2" :message="formUserData.errors.GradoAcademico" />
                </div>
                <InputLabel for="Estatus" value="Sexo" />
                <div class="flex flex-auto justify-evenly">
                    <div class="space-x-2">
                        <label for="Hombre">Hombre</label>
                        <input type="radio" id="Hombre" value="Hombre" v-model="formUserData.Sexo" />
                    </div>
                    <div class="space-x-2">
                        <label for="Mujer">Mujer</label>
                        <input type="radio" id="entregado" value="Mujer" v-model="formUserData.Sexo" />
                    </div>
                </div>
                <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
                    formUserData.Sexo }}</div>
                <p>
                    Para continuar con tu registro, por favor ingresa una contraseña.
                </p>
                <p>
                    Debe de contener minimo una minúscula, una mayúscula, un número, un símbolo y tener entre 8 y 18
                    caracteres.
                </p>
                <InputLabel for="password" value="Contraseña" class="pt-2" />
                <TextInput id="password" type="password" class="mt-1 block w-full" v-model="formUserData.password"
                    required autocomplete="new-password" />
                <InputError class="mt-2" :message="formUserData.errors.password" />
                <InputLabel for="password-confirmation" value="Confirmar contraseña" class="pt-2" />
                <TextInput id="password-confirmation" type="password" class="mt-1 block w-full"
                    v-model="formUserData.password_confirmation" required autocomplete="new-password" />
                <InputError class="mt-2" :message="formUserData.errors.password" />
                <div class="flex flex-items justify-end pt-4">
                    <PrimaryButton>Guardar</PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
<style>
/*Se importa el estilo de los inputs que utilizan el autocompletado*/
@import "vue-select/dist/vue-select.css";
</style>