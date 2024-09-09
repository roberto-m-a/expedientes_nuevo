<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import AuthenticatedLayout_secretaria from '@/Layouts/AuthenticatedLayout_secretaria.vue';
import TipoDocumentoFormCreate from '@/Components/ComponentsForms/TipoDocumentoFormCreate.vue';
import TipoDocumentoFormEdit from '@/Components/ComponentsForms/TipoDocumentoFormEdit.vue';
import TipoDocumentoTable from '@/Components/ComponentsTables/TipoDocumentoTable.vue';

const props = defineProps({
    user: {
        type: Object,
    },
    personal: {
        type: Object,
    },
    tipoDocs: {
        type: Object,
    }
});

const abrir = ref(false);
const abrirEdit = ref(false);
const tipoDoc = ref(null);

const formDelete = useForm({
    idtipoDoc: '',
});
onMounted(() => {
    // Manejar clic del botón con la clase EditarTipoDoc
    $('#TablaTipoDoc').on('click', '.EditarTipoDoc', function () {
        const id = $(this).data('id');
        tipoDoc.value = props.tipoDocs.find(a => a.IdTipoDocumento === id );
        abrirEdit.value = true;
    });
    //Manejar clic del botón con la clase BorrarTipoDoc
    $('#TablaTipoDoc').on('click', '.BorrarTipoDoc', function () {
        formDelete.idtipoDoc = $(this).data('id');
        const TipoDoc = props.tipoDocs.find(a => a.IdTipoDocumento === formDelete.idtipoDoc);
        const randomCode = Math.floor(1000 + Math.random() * 9000); // Genera un código aleatorio de 4 dígitos
        Swal.fire({
            title: 'Confirmación necesaria',
            text: `¿Desea borrar ${TipoDoc.nombreTipoDoc}?. 
            Para continuar, ingresa el código de confirmación: ${randomCode}`,
            input: 'number',
            footer: 'Esta acción se puede realizar ya que el registro no tiene relaciones con otros registros.',
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
                formDelete.delete(route('tipoDoc.borrar'), {
                    preserveScroll: true,
                    onSuccess: () => {
                        formDelete.reset()
                    },
                    onError: () => {
                        console.log(formEdit.errors);
                    },
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                Swal.fire('Acción cancelada', 'No se realizó ninguna acción.', 'error');
            }
        });
    });
});
</script>

<template>

    <Head title="Tipo de documentos" />

    <AuthenticatedLayout_secretaria :user="user" :personal="personal">
        <template #header>
            <div class="flex flex-row items-end space-x-4">
                <h2 class="font-semibold text-2xl text-black leading-tight">Tipos de documentos</h2>
                <h2 class="text-gray-500 font-semibold">Agrega, edita y borra tipos de documentos</h2>
            </div>
        </template>
        <!-- Componente para crear un nuevo tipo de documento -->
        <TipoDocumentoFormCreate v-model:modelValue="abrir" ></TipoDocumentoFormCreate>
        <!-- Componente para editar un tipo de documento -->
        <TipoDocumentoFormEdit v-model:modelValue="abrirEdit" v-model:tipoDoc="tipoDoc" ></TipoDocumentoFormEdit>
        <!-- Componente para mostrar la tabla de tipo documentos -->
         <TipoDocumentoTable v-model:abrir="abrir" :tipoDocs="tipoDocs"> </TipoDocumentoTable>
    </AuthenticatedLayout_secretaria>
</template>
