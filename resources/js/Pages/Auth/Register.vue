<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import FlashMessageCreate from '@/Components/ComponentsFlashMessages/FlashMessageCreate.vue';
import { ref } from 'vue';

const form = useForm({
    name: '',
    lastname: '',
    email: '',
    email_confirmation: '',
});
const flashMessage = ref('');
const disableButtonForm = ref(false)
const submit = () => {
    form.clearErrors();
    const formDataJson = JSON.stringify(form); // Convertimos a JSON
    $.ajax({
        url: route('register'),
        method: 'POST',
        contentType: 'application/json',
        data: formDataJson,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function(){
            disableButtonForm.value = true;
        },
        success: function (response) {
            form.reset();
            flashMessage.value = 'Se te ha enviado un correo electrónico para que verifiques tu cuenta';
            disableButtonForm.value = false;
            setTimeout(() => {
                flashMessage.value = '';
            }, 3000);
        },
        error: function (xhr) {
            console.log(xhr);
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
    /* form.post(route('register'), {
        onFinish: () => form.reset(),
        onError: () => console.log(form.errors),
    }); */
};
</script>

<template>
    <GuestLayout>

        <Head title="Register" />

        <form @submit.prevent="submit">
            <FlashMessageCreate :flashMessage="flashMessage" ></FlashMessageCreate>
            <div>
                <InputLabel for="name" value="Nombre" />

                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus
                    autocomplete="name" />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="lastname" value="Apellido(s)" />

                <TextInput id="lastname" type="text" class="mt-1 block w-full" v-model="form.lastname" required
                    autocomplete="lastname" />

                <InputError class="mt-2" :message="form.errors.lastname" />
            </div>

            <div class="mt-4" oncopy="return false" onpaste="return false">
                <InputLabel for="email" value="Correo" />

                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required
                    autocomplete="username" />

                <InputError class="mt-2" :message="form.errors.email" />

            </div>
            <div class="mt-4" oncopy="return false" onpaste="return false">
                <InputLabel for="email_confirmation" value="Confirmar correo" />

                <TextInput id="email_confirmation" type="email" class="mt-1 block w-full"
                    v-model="form.email_confirmation" required autocomplete="username" />

                <InputError class="mt-2" :message="form.errors.email" />

            </div>

            <div class="flex items-center justify-end mt-4">
                <Link :href="route('login')"
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                ¿Ya estás registrado?
                </Link>

                <PrimaryButton class="ms-4" :class="{ 'opacity-25': disableButtonForm }" :disabled="disableButtonForm">
                    Registrarse
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
