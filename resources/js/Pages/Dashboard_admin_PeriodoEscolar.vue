<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import AuthenticatedLayout_admin from '@/Layouts/AuthenticatedLayout_admin.vue';
import PeriodoEscolarFormCreate from '@/Components/ComponentsForms/PeriodoEscolarFormCreate.vue';
import PeriodoEscolarFormEdit from '@/Components/ComponentsForms/PeriodoEscolarFormEdit.vue';
import PeriodoEscolarTable from '@/Components/ComponentsTables/PeriodoEscolarTable.vue';
const props = defineProps({
    user: {
        type: Object,
    },
    personal: {
        type: Object,
    },
    periodosEscolares: {
        type: Array,
    },
});

const abrir = ref(false); //constante para controlar el componente PeriodoEscolarFormCreate
const abrirEdit = ref(false); //constante para controlar el componente PeriodoEscolarFormEdit
const periodoEscolar = ref(null); //constante para manejar el periodo a editar en el componente PeriodoEscolarFormEdit
//formulario para borrar un periodo escolar
const formDelete = useForm({
    IdPeriodoEscolar: '',
})
onMounted(() => {
    // Manejar clic en el botón de editarDoc
    $('#TablaPeriodos').on('click', '.EditarPeriodo', function () {
        const id = $(this).data('id');
        periodoEscolar.value = props.periodosEscolares.find(a => a.IdPeriodoEscolar === id);
        abrirEdit.value = true;
    });
    $('#TablaPeriodos').on('click', '.BorrarPeriodo', function () {
        formDelete.IdPeriodoEscolar = $(this).data('id');
        const periodoEscolarD = props.periodosEscolares.find(a => a.IdPeriodoEscolar === formDelete.IdPeriodoEscolar);
        const randomCode = Math.floor(1000 + Math.random() * 9000); // Genera un código aleatorio de 4 dígitos
        Swal.fire({
            title: 'Confirmación necesaria',
            text: `¿Desea borrar ${periodoEscolarD.nombre_corto}?. 
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
                formDelete.delete(route('periodoEscolar.borrar'), {
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
    <Head title="Períodos Escolares" />
    <AuthenticatedLayout_admin :user="user" :personal="personal">
        <template #header>
            <div class="flex flex-row items-end space-x-4">
                <h2 class="font-semibold text-2xl text-black leading-tight">Períodos escolares</h2>
                <h2 class="text-gray-500 font-semibold">Agrega, edita y borra períodos escolares</h2>
            </div>
        </template>
        <!-- Modal para editar un período escolar -->
        <PeriodoEscolarFormEdit v-model:abrirModal="abrirEdit" v-model:periodoEscolar="periodoEscolar"></PeriodoEscolarFormEdit>
        <!-- Modal para agregar un nuevo período escolar -->
        <PeriodoEscolarFormCreate v-model:abrirModal="abrir"></PeriodoEscolarFormCreate>
        <!-- Componente de la tabla de periodos escolares -->
         <PeriodoEscolarTable v-model:abrir="abrir" :periodosEscolares="periodosEscolares"></PeriodoEscolarTable>
    </AuthenticatedLayout_admin>
</template>
