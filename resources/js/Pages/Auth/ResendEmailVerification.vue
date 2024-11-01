<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import FlashMessageCreate from '@/Components/ComponentsFlashMessages/FlashMessageCreate.vue';

const props = defineProps({
    tokenExpirado: {
        type: String,
        required: true,
    },
});

const form = useForm({
    tokenExpirado: props.tokenExpirado ,
    email: '',
});
const flashMessage = ref('');
const submit = () => {
    form.clearErrors();
    const formDataJson = JSON.stringify(form); // Convertimos a JSON
    $.ajax({
        url: route('reenviar.verificacionemail'),
        method: 'POST',
        contentType: 'application/json',
        data: formDataJson,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            flashMessage.value = 'Se te ha enviado un nuevo correo de verificación'
            setTimeout(() => {
                window.location.href = '/';
            }, 3000);
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
        }
    });
    //form.post(route('reenviar.verificacionemail'));
};
</script>

<template>
    <GuestLayout>

        <Head title="Reenviar email de verificación" />

        <div class="mb-4 text-sm text-gray-600">
            Al parecer tu token ha expirado, introduce tu correo para enviarte un nuevo correo con un nuevo token y
            puedas verificar tu correo.
        </div>
        <FlashMessageCreate :flashMessage="flashMessage"></FlashMessageCreate>
        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Correo electronico" />

                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required autofocus
                    autocomplete="username" />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Reenviar email
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
