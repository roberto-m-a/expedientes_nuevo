<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});
const flashMessage = ref('');
const disableButtonForm = ref(false);
const updatePassword = () => {
    form.clearErrors();
    const formDataJson = JSON.stringify(form);
    $.ajax({
        url: route('password.update'),
        method: 'PUT',
        contentType: 'application/json',
        data: formDataJson,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function(){
            disableButtonForm.value = true
        },
        success: function (response) {
            form.reset();
            flashMessage.value = 'Contraseña actualizada'
            setTimeout(() => {
                flashMessage.value = '';
            }, 3000);
            disableButtonForm.value = false;
        },
        error: function (xhr) {
            console.log(xhr.responseJSON);
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                $.each(errors, function (field, messages) {
                    form.setError({
                        [field]: messages[0] || {}
                    });
                });
            }
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
            disableButtonForm.value = false;
        }
    });
    /* form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    }); */
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Actualizar contraseña</h2>

            <p class="mt-1 text-sm text-gray-600">
                Actualiza tu contraseña para mantener tu cuenta segura.
                Recuerda que tu contraseña debe de contener:
                <p>
                    *Entre 8 y 18 caracteres.
                </p>
                <p>*Mínimo una minúscula y una mayúscula</p>
                <p>*Un símbolo (!#$%&/=?¡¿*+-~`[])</p>
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
            <div>
                <InputLabel for="current_password" value="Contraseña actual" />

                <TextInput
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="current-password"
                />

                <InputError :message="form.errors.current_password" class="mt-2" />
            </div>

            <div>
                <InputLabel for="password" value="Nueva contraseña" />

                <TextInput
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                />

                <InputError :message="form.errors.password" class="mt-2" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Confirmar nueva contraseña" />

                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                />

                <InputError :message="form.errors.password_confirmation" class="mt-2" />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :class="{ 'opacity-25': disableButtonForm }"
                :disabled="disableButtonForm">Guardar</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="flashMessage" class="text-sm text-gray-600">{{ flashMessage }}</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
