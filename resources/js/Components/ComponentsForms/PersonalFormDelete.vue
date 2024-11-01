<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import FlashMessageDelete from '@/Components/ComponentsFlashMessages/FlashMessageDelete.vue'; 1
const props = defineProps({
    personal_data: {
        type: Array,
    },
})
//Formulario para borrar a un personal
const formDelete = useForm({
    IdPersonal: '',
});
const flashMessage = ref('');
onMounted(() => {
    //Manejar clic del botón con la clase BorrarPersonal
    $('#TablaPersonal').on('click', '.BorrarPersonal', function () {
        formDelete.IdPersonal = $(this).data('id');
        const personal = props.personal_data.find(a => a.IdPersonal === formDelete.IdPersonal);
        const randomCode = Math.floor(1000 + Math.random() * 9000); // Genera un código aleatorio de 4 dígitos
        Swal.fire({
            title: 'Confirmación necesaria',
            text: `¿Desea borrar a ${personal.Nombre} ${personal.Apellidos}?. 
            Para continuar, ingresa el código de confirmación: ${randomCode}`,
            input: 'number',
            footer: 'Esta acción se puede realizar ya que el usuario/personal no tiene documentos en su expediente o no ha subido algun documento.',
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
                const formDataJson = JSON.stringify(formDelete);
                $.ajax({
                    url: route('personal.borrar'),
                    method: 'POST',
                    contentType: 'application/json',
                    data: formDataJson,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        formDelete.reset();
                        flashMessage.value = 'Personal borrado correctamente';
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                        window.location.reload();
                    },
                    error: function (xhr) {
                        console.log(xhr.responseJSON.errors)
                    }
                });
                /* formDelete.delete(route('personal.borrar'), {
                    preserveScroll: true,
                    onSuccess: () => {
                        formDelete.reset()
                    },
                    onError: () => {
                        console.log(formDelete.errors);
                    },
                }); */
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                Swal.fire('Acción cancelada', 'No se realizó ninguna acción.', 'error');
            }
        });
    });
});
</script>
<template>
    <!-- Mensaje flash cuando se borra un personal -->
    <FlashMessageDelete :flashMessage="flashMessage"></FlashMessageDelete>
</template>