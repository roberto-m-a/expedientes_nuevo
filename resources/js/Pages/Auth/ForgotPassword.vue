<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});
const flashMessage= ref('');
const disableButtonForm = ref(false);
const submit = () => {
    form.clearErrors();
    const formDataJson = JSON.stringify(form);
    $.ajax({
        url: route('password.email'),
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
            flashMessage.value = 'Email enviado'
            setTimeout(() => {
                flashMessage.value = '';
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
    //form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Reestablecer contraseña" />

        <div class="mb-4 text-sm text-gray-600">
            ¿Olvidaste tu contraseña? Ingresa tu correo electrónico con el que lo has registrado para mandarte un email con
             un link para reestablecer tu contraseña.
        </div>

        <div v-if="flashMessage" class="mb-4 font-medium text-sm text-green-600">
            {{ flashMessage }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Correo electronico" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <PrimaryButton :class="{ 'opacity-25': disableButtonForm }" :disabled="disableButtonForm">
                    Enviar link
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
