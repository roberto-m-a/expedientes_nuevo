<script setup>
const props = defineProps({
    flashMessage: {
        type: String,
    }
});
</script>
<template>
    <div v-if="props.flashMessage"
        class="alert bg-white text-center text-xl text-red-600 border-gray-300 border-solid border-2 p-2">
        <div class=" flex flex-row justify-center items-center">
            <svg class="h-5 w-5 text-red-600" width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" />
                <path d="M19 19h-11l-4 -4a1 1 0 0 1 0 -1.41l10 -10a1 1 0 0 1 1.41 0l5 5a1 1 0 0 1 0 1.41l-9 9" />
                <path d="M18 12.3l-6.3 -6.3" />
            </svg>
            {{ props.flashMessage }}
        </div>
    </div>
</template>