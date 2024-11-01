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
            <svg class="h-5 w-5 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2" />
                <line x1="15" y1="9" x2="9" y2="15" />
                <line x1="9" y1="9" x2="15" y2="15" />
            </svg>
            {{ props.flashMessage }}
        </div>
    </div>
</template>